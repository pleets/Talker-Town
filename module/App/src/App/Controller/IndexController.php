<?php

/*
 * Index Module
 * http://www.pleets.org
 *
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace App\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Auth\Form\UserForm;
use Auth\Model\Entity\User;

use App\Form\MessageForm;
use App\Model\Entity\Message;

use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;

use Parser\Controller\MessageController;

class IndexController extends AbstractActionController
{
    private $anonymousIdentity;

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
        return new ViewModel();
    }

    public function talkerAction()
    {
        if (is_null($this->getAnonymousIdentity()))
            return $this->redirect()->toRoute('home');

        $data = array();
        $data["username"] = $this->getAnonymousIdentity();

        try {
            $form = new MessageForm();
            $data['form'] = $form;
        }
        catch (\Exception $e) {

            $data['Exception'] = $e->getMessage();
            $view = new ViewModel($data);

            if ($xmlHttpRequest)
                $view->setTerminal(true);
            return $view;
        }

        $view = new ViewModel($data);
        return $view;
    }

    public function logoutAction()
    {
        $auth = new AuthenticationService();

        if ($auth->hasIdentity() || !is_null($this->getAnonymousIdentity()))
        {
			$session = new Container('anonymous_identity');

            $auth->clearIdentity();
        	$session->offsetUnset('username');
        }

        return $this->redirect()->toRoute('auth', array(
            'action' => 'login'
        ));
    }

    public function getIdentityInformationAction()
    {
        $data = array(
            "username" => $this->getAnonymousIdentity()
        );

        $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
        return $response;
    }

    private function parseMessage($message, $last_user, $currentmodif, $user_color, $receiver)
    {
        $parsed_message = $message;

        $parser = new MessageController();

        $parser->setMessage($parsed_message);
        $parser->parseURLs();
        $parser->parseEmoticons();

        $parsed_message = $parser->getMessage();

        // Convert the current message in HTML
        $parsed_message  = "<p id='$currentmodif' data-user='$last_user' data-receiver='$receiver'><strong style='color: $user_color'>$last_user</strong>: ". $parsed_message ."</p>";

        return $parsed_message;
    }

    private function getLatestMessages($latest_messages, $lastmodif)
    {
        $_msg = array();
        foreach ($latest_messages as $tmp)
        {
            $_tmp = (integer) basename(substr($tmp, 0, strlen($tmp) - 4));

            if ($_tmp > $lastmodif) {

                $_msg_decoded = base64_encode(file_get_contents($tmp));

                if (!empty($_msg_decoded))
                    $_msg[] = $_msg_decoded;
            }
        }

        return $_msg;
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


        /* create some folders and files */

        if (!file_exists('data/cache/conversations'))
            mkdir('data/cache/conversations');

        if (!file_exists('data/cache/conversations/timestamp'))
            mkdir('data/cache/conversations/timestamp');

        if (!file_exists('data/cache/users'))
            mkdir('data/cache/users');

        if (!file_exists('data/cache/conversations/history.txt'))
            file_put_contents('data/cache/conversations/history.txt', '');

        if (!file_exists('data/cache/conversations/timestamp/1.txt'))
            file_put_contents('data/cache/conversations/timestamp/1.txt', '');


        // Get username and message to store
        $message = isset($_GET['msg']) ? trim($_GET['msg']) : '';
        $user_color = isset($_GET['user_color']) ? trim($_GET['user_color']) : '#2A9426';
        $receiver = isset($_GET['receiver']) ? trim($_GET['receiver']) : '';
        $data_username = $username = $this->getAnonymousIdentity();

        $message = base64_decode($message);


        // Zend Filters (prevent HTML injection)

        $messageObject = new Message();
        $form = new MessageForm($this);

        $form->setValidationGroup('word');
        $messageObject->exchangeArray(array("word" => $message));
        $form->setInputFilter($messageObject->getInputFilter());

        $form->setData($messageObject->getArrayCopy());

        if ($form->isValid())
        {
            $messageObject->exchangeArray($form->getData());
            $message = $messageObject->word;
        }


        // Get the current and last timestamp of the message file
        $lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;     # The first time the timestamp is equal to zero
        $data_id = $currentmodif = count(getFiles("data/cache/conversations/timestamp"));

        // Timestamp file
        $timestamp_file = "data/cache/conversations/timestamp/" . $currentmodif . ".txt";


        // If you are logged
        if (!is_null($username) && !empty($username))
            file_put_contents("data/cache/users/" . $username, date("Y-m-d H:i:s"));        # persistence file
        else if (is_null($username))
            $response["errors"][] = array(
                "code" => 101,
                "name" => "Lost session",
                "message" => "The session has been lost!"
            );


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
        $latest_messages = getFiles("data/cache/conversations/timestamp");


        /* Infinite loop until the data file is not modified */

        /* Check the following rules
         * - The message file has been modified
         * - An user has logged in or logged out
         * - The session has been lost
         */

        $isFirstCall = ($lastmodif == 0);
        $itsForYou = false;
        $_messages = array();

        while (!isset($_GET["doRequest"]) && $lastmodif != 0 && count($current_users) == count($last_users) && ($currentmodif == count($latest_messages) || !$itsForYou) )
        {
            clearstatcache();
            session_write_close();

            /* refresh identity */
            $username = $this->getAnonymousIdentity();

            $latest_messages = getFiles("data/cache/conversations/timestamp");


            /* Filter private messages of other users */

            $array = $this->getLatestMessages($latest_messages, $lastmodif);

            if (isset($_messags))
                $_messags = array();
            foreach ($array as $_msg)
            {
                $_msg = base64_decode($_msg);
                if (strpos($_msg, "data-receiver=''") !== false || strpos($_msg, "data-receiver='$username'") !== false)
                {
                    if (!in_array(base64_encode($_msg), $_messages))
                        $_messages[] = base64_encode($_msg);
                    $itsForYou = true;
                }
            }
            $response["itsForYou"] = $itsForYou;


            if ($currentmodif != $lastmodif)
                break;

            if (!is_null($username) && !empty($username))
            {
                file_put_contents("data/cache/users/" . $username, date("Y-m-d H:i:s"));

                $current_users = getFiles("data/cache/users");
                $online_users = array();

                foreach ($current_users as $_user) {
                    if (time() - filemtime($_user) < 3)
                        $online_users[] = basename($_user);
                    else if (file_exists($_user))
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

        $_msg = $_messages;

        $response["latest_messages"] = $_msg;
        $last_user = $username;


        if (isset($_GET["doRequest"]))
            $data_contents_message = $message;
        # First request when the timestamp is zero
        else if ($lastmodif == 0) {
            if (file_exists("data/cache/conversations/history.txt"))
            {
                /* Filter only public messages */
                $file = fopen("data/cache/conversations/history.txt",'r');

                $data_contents_message = "";

                while(!feof($file))
                {
                    $row = fgets($file);

                    if (strpos($row, "data-receiver=''") !== false)
                        $data_contents_message .= $row;
                }
                fclose($file);
            }
            else
                $data_contents_message = "";
        }
        else {
        # The user gets the message of other users
            if ($last_user != $username)
                $data_contents_message = ($lastmodif > $currentmodif) ? $message : file_get_contents("data/cache/conversations/timestamp/" . $lastmodif . ".txt");
            else
                $data_contents_message = '';
        }


        // If detects user does not send message
        $response['msg'] = ($currentmodif == $lastmodif) ? '': $data_contents_message;

        // Parse msg
        if (!empty($message))
        {
            $response['msg'] = $this->parseMessage($response['msg'], $last_user, $currentmodif, $user_color, $receiver);

            $currentmodif++;

            // Store the timestamp file
            file_put_contents("data/cache/conversations/timestamp/" . $currentmodif . ".txt", $response['msg']);

            // Store message in the chat history
            $hd = fopen("data/cache/conversations/history.txt", "a");
            fwrite($hd, $response['msg'] . "\n");
            fclose($hd);
        }

        $currentmodif = count(getFiles("data/cache/conversations/timestamp"));

        $response["msg"] = base64_encode($response["msg"]);
        $response['user'] = $last_user;
        $response['timestamp'] = $currentmodif;
        $response['firstTimestamp'] = $lastmodif;
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

    public function sendPhotoAction()
    {
        $files = array();

        $state = 0;

        define('UPLOAD_DIR', 'data/cache');
        $img = $_POST['imgBase64'];
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $file = UPLOAD_DIR ."/". uniqid() . '.png';
        $success = file_put_contents($file, $data);

        if ($success !== false)
            $state = 1;

        $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( array("state" => $state, "file" => $file) ));
        return $response;
    }
}
