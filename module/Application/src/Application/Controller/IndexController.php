<?php

/*
 * Index Module
 * http://www.pleets.org
 *
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Auth\Form\UserForm;
use Auth\Model\Entity\User;

use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    private $gendersTable;
    private $anonymousIdentity;

    private function getGendersTable()
    {
        if (!$this->gendersTable)
            $this->gendersTable = $this->getServiceLocator()->get('Application\Model\Entity\GendersTable');
        return $this->gendersTable;
    }

    private function getAnonymousIdentity()
    {
        $session = new Container('anonymous_identity');
        return $session->username;
    }

    private function setAnonymousIdentity($username)
    {
        $session = new Container('anonymous_identity');
        $session->username = $username;        
    }

    public function indexAction()
    {
        $data = array();

        $xmlHttpRequest = $this->getRequest()->isXmlHttpRequest();
        $data['xmlHttpRequest'] = $xmlHttpRequest;

        if (!is_null($this->getAnonymousIdentity()))
            return $this->redirect()->toRoute('application/talker');

        try {
            $form = new UserForm();
            $form->get('submit')->setValue('Login');
            $data['form'] = $form;

            $gendersTable = $this->getGendersTable();
            $data["genders"] = $gendersTable->fetchAll()->toArray();
        }
        catch (\Exception $e) {

            $data['Exception'] = $e->getMessage();
            $view = new ViewModel($data);

            if ($xmlHttpRequest)
                $view->setTerminal(true);
            return $view;
        }

        $view = new ViewModel($data);

        if ($xmlHttpRequest)
            $view->setTerminal(true);
        return $view;
    }

    public function loginAction()
    {
        $data = array();

        $request = $this->getRequest();

        if (!$request->isPost())
            return $this->redirect()->toRoute('home');

        if (!is_null($this->getAnonymousIdentity()))
            return $this->redirect()->toRoute('home');
        else
        {
            $form_data = $this->request->getPost();

            try {
                $user = new User();
                $form = new UserForm($this);

                $form->setValidationGroup('username', 'genders_id');
                $form->setInputFilter($user->getInputFilter());
                $form->setData($request->getPost());

                if ($form->isValid())
                {
                    $this->setAnonymousIdentity($form_data->username);
                    return $this->redirect()->toRoute('home');
                }
            }
            catch (\Exception $e) {
                $data['Exception'] = $e->getMessage();
                $view = new ViewModel($data);
                return $view;
            }
        }
    }

    public function talkerAction()
    {
        if (is_null($this->getAnonymousIdentity()))
            return $this->redirect()->toRoute('home');

        $data = array();
        $data["username"] = $this->getAnonymousIdentity();

        $view = new ViewModel($data);
        return $view;
    }

    public function logoutAction()
    {
        $session = new Container('anonymous_identity');
        $session->getManager()->getStorage()->clear();

        return $this->redirect()->toRoute('home');
    }

    public function backendAction()
    {
        function getFiles($path)
        {
            $files = array();

            if (is_dir($path))
            {
                if ($dh = opendir($path))
                {
                    while (($file = readdir($dh)) !== false)
                    {
                        $_file = $path."/".$file;
                        if (is_file($_file) && $file!="." && $file!="..")
                            $files[] = $_file;
                    }
                    closedir($dh);
                }
            }

           return $files;
        }

        // Files that store the last message and its respective user
        $message_file = dirname(dirname(__FILE__)).'/message.txt';
        $username_file  = dirname(dirname(__FILE__)).'/username.txt';

        // Get username and message to store
        $message = isset($_GET['msg']) ? trim($_GET['msg']) : '';
        $username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';

        // Get the current and last timestamp of the message file
        $lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;     # The first time the timestamp is equal to zero
        $currentmodif = filemtime($message_file);

        // If you are logged
        if (isset($_COOKIE['username']) && !empty($_COOKIE['username']))
        {
            if (!empty($message))
            {
                // Convert the current message in HTML
                $message = "<p id='$currentmodif'>$username ~ $message</p>";

                // Store message and username
                file_put_contents($message_file, $message);
                file_put_contents($username_file, $username);

                // Store message in the chat history
                $hd = fopen("../cache/conversations/history.txt", "a");
                fwrite($hd, $message . "\n");
                fclose($hd);
            }

            file_put_contents("../cache/users/" .$username, date("Y-m-d H:i:s"));
        }

        /* infinite loop until the data file is not modified */

        $last_users = getFiles("../cache/users");
        $online_users = array();

        foreach ($last_users as $_user)
        {
            if (time() - filemtime($_user) < 5)
                $online_users[] = basename($_user);
            else
                unlink($_user);
        }

        $current_users = getFiles("../cache/users");

        // check if the data file has been modified or an user has logged in or logged out
        while ($currentmodif <= $lastmodif && count($current_users) == count($last_users))
        {
            clearstatcache();
            $currentmodif = filemtime($message_file);

            if (isset($_COOKIE['username']))
            {
                file_put_contents("../cache/users/" .$username, date("Y-m-d H:i:s"));

                $current_users = getFiles("../cache/users");
                $online_users = array();

                foreach ($current_users as $_user) {
                    if (time() - filemtime($_user) < 3)
                        $online_users[] = basename($_user);
                    else
                        unlink($_user);
                }
            }
        }

        $last_user = file_get_contents($username_file);

        // return a json array
        $response = array();

        if (isset($_GET["doRequest"]))
            $data = file_get_contents($message_file);
        # First request when the timestamp is zero
        else if ($lastmodif == 0) {
            if (file_exists("../cache/conversations/history.txt"))
                $data = file_get_contents("../cache/conversations/history.txt");
            else
                $data = "";
        }
        else {
        # The user gets the message of other users
            if ($last_user != $username)
                $data = file_get_contents($message_file);
            else
                $data = '';
        }

        // If detects user does not send message
        $response['msg'] = ($currentmodif == $lastmodif) ? '': $data;
        $response['user'] = $last_user;
        $response['timestamp'] = $currentmodif;
        $response['online_users'] = $online_users;
        echo json_encode($response);
        flush();

        $view = new ViewModel($data);
        $view->setTerminal(true);
        return $view;

    }
}
