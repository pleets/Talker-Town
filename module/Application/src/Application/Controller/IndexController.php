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

            $data["genders"] = array(
                array("genders_id" => 1, "name" => "Male"),
                array("genders_id" => 2, "name" => "Female")
            );
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

    public function databaseAction()
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

                    /* create json file with users settings */
                    $user_info = array(
                        "username" => $form_data->username,
                        "avatar" => $form_data->avatar
                    );
                    file_put_contents('data/cache/' . $form_data->username . '.json', json_encode($user_info));                    

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

    public function getIdentityInformationAction()
    {
        $data = array(
            "username" => $this->getAnonymousIdentity()
        );

        $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
        return $response;

        /*$response = \Zend\Json\Json::encode( $data );

        if (file_put_contents($buffer, $response) === false)
            throw new \Exception("Processing error!", 1);

        $data = \Zend\Json\Json::decode( file_get_contents($buffer) );*/
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


        /*$basePath = $this->getServiceLocator()->get('Zend\ServiceManager\ServiceManager')->get('ViewHelperManager')->get('basePath');   
        $file = $basePath->getView()->basePath('foo');*/


        // return a json array
        $response = array();
        $response["errors"] = array();


        /* create some folders */

        if (!file_exists('data/cache/conversations'))
            mkdir('data/cache/conversations');

        if (!file_exists('data/cache/users'))
            mkdir('data/cache/users');


        // Files that store the last message and its respective user
        $message_file = 'data/cache/message.txt';
        $username_file  = 'data/cache/username.txt';


        /* create message_file and username_file */

        if (!file_exists($message_file))
            file_put_contents($message_file, '');

        if (!file_exists($username_file))
            file_put_contents($username_file, '');

        if (!file_exists('data/cache/conversations/history.txt'))
            file_put_contents('data/cache/conversations/history.txt', '');


        // Get username and message to store
        $message = isset($_GET['msg']) ? trim($_GET['msg']) : '';
        $username = $this->getAnonymousIdentity();

        // Get the current and last timestamp of the message file
        $lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;     # The first time the timestamp is equal to zero
        $currentmodif = filemtime($message_file);

        // If you are logged
        if (!is_null($username) && !empty($username))
        {
            if (!empty($message))
            {
                // Convert the current message in HTML
                $message = "<p id='$currentmodif'>$username ~ $message</p>";

                // Store message and username
                file_put_contents($message_file, $message);
                file_put_contents($username_file, $username);

                // Store message in the chat history
                $hd = fopen("data/cache/conversations/history.txt", "a");
                fwrite($hd, $message . "\n");
                fclose($hd);
            }

            file_put_contents("data/cache/users/" . $username, date("Y-m-d H:i:s"));
        }
        else if (is_null($username))
            $response["errors"][] = array(
                "code" => 101,
                "name" => "Lost session",
                "message" => "The session has been lost!"
            );


        /* infinite loop until the data file is not modified */

        $last_users = getFiles("data/cache/users");
        $online_users = array();

        foreach ($last_users as $_user)
        {
            if (time() - filemtime($_user) < 5)
                $online_users[] = basename($_user);
            else
                unlink($_user);
        }

        $current_users = getFiles("data/cache/users");


        /* Check the following rules
         * - The message file has been modified
         * - An user has logged in or logged out
         * - The session has been lost
         */

        while ($currentmodif <= $lastmodif && count($current_users) == count($last_users))
        {
            clearstatcache();
            $currentmodif = filemtime($message_file);
            session_write_close();

            /* refresh identity */
            $username = $this->getAnonymousIdentity();

            if (!is_null($username) && !empty($username))
            {
                file_put_contents("data/cache/users/" . $username, date("Y-m-d H:i:s"));

                $current_users = getFiles("data/cache/users");
                $online_users = array();

                foreach ($current_users as $_user) {
                    if (time() - filemtime($_user) < 3)
                        $online_users[] = basename($_user);
                    else
                        unlink($_user);
                }
            }
            else if (is_null($username)) 
            {
                $response["errors"][] = array(
                    "code" => 101,
                    "name" => "Lost session",
                    "message" => "The session has been lost!"
                );
                break;
            }
        }

        $last_user = file_get_contents($username_file);


        if (isset($_GET["doRequest"]))
            $data = file_get_contents($message_file);
        # First request when the timestamp is zero
        else if ($lastmodif == 0) {
            if (file_exists("data/cache/conversations/history.txt"))
                $data = file_get_contents("data/cache/conversations/history.txt");
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

        $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $response ));
        return $response;
    }

    public function fileUploadAction()
    {

        $files = array();

        foreach ($_FILES as $file) 
        {
            if (!file_exists('data/cache/files'))
                mkdir('data/cache/files');

            if (move_uploaded_file($file['tmp_name'], "data/cache/files/". basename($file['tmp_name']) . $file['name']))
                $files[] = basename($file['tmp_name']) . $file['name'];
        }

        $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $files ));
        return $response;        
    }
}
