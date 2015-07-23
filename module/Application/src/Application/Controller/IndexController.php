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

use Application\Form\MessageForm;
use Application\Model\Entity\Message;

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
        $data = array("foo" => "bar");

        $request = $this->getRequest();

        if (!$request->isPost())
            return $this->redirect()->toRoute('home');

        if (!is_null($this->getAnonymousIdentity()))
        {
            $data['Exception'] = "You have other session, try reload the page or press F5";
            $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
            return $response;
        }
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

                    $data["user"] = $form_data->username;

                    $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
                    return $response;                    
                }
                else
                    $data["formErrors"] = $form->getMessages();
            }
            catch (\Exception $e) 
            {
                $data['Exception'] = $e->getMessage();
                $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
                return $response;
            }
        }

        $response = $this->getResponse()->setContent(\Zend\Json\Json::encode( $data ));
        return $response;
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

    private function parseMessage($message, $last_user, $currentmodif, $user_color, $receiver)
    {
        $parsed_message = $message;

        /* Only when start text ... */
        if (substr($parsed_message, 0, 7) == 'http://' || substr($parsed_message, 0, 8) == 'https://')
        {
            if (in_array(strtolower(substr($parsed_message, strlen($parsed_message) - 4, strlen($parsed_message))), array(".png", ".jpg", ".jpeg", ".ico")))
                $parsed_message = "<p id='$currentmodif' data-user='$last_user' data-receiver='$receiver'><strong style='color: $user_color'>$last_user</strong>: <br /> <img class='responsive-image' src='". $parsed_message ."' alt='image' /></p>";
            else
                $parsed_message = "<p id='$currentmodif' data-user='$last_user' data-receiver='$receiver'><strong style='color: $user_color'>$last_user</strong>: <a target='_blank' href='". $parsed_message ."' >". $parsed_message ."</a></p>";
        }
        else {
            $replaced = str_replace(">:(", "<a class='emoticon emoticon_grumpy'></a>", $message);
            $replaced = str_replace("3:)", "<a class='emoticon emoticon_devil'></a>", $replaced);
            $replaced = str_replace("O:)", "<a class='emoticon emoticon_angel'></a>", $replaced);
            $replaced = str_replace(">:o", "<a class='emoticon emoticon_upset'></a>", $replaced);

            $replaced = str_replace(":)", "<a class='emoticon emoticon_smile'></a>", $replaced);
            $replaced = str_replace(":(", "<a class='emoticon emoticon_frown'></a>", $replaced);
            $replaced = str_replace(":P", "<a class='emoticon emoticon_tongue'></a>", $replaced);
            $replaced = str_replace("=D", "<a class='emoticon emoticon_grin'></a>", $replaced);
            $replaced = str_replace(":o", "<a class='emoticon emoticon_gasp'></a>", $replaced);
            $replaced = str_replace(";)", "<a class='emoticon emoticon_wink'></a>", $replaced);
            $replaced = str_replace(":v", "<a class='emoticon emoticon_pacman'></a>", $replaced);
            $replaced = str_replace(":/", "<a class='emoticon emoticon_unsure'></a>", $replaced);
            $replaced = str_replace(":'(", "<a class='emoticon emoticon_cry'></a>", $replaced);
            $replaced = str_replace("^_^", "<a class='emoticon emoticon_kiki'></a>", $replaced);
            $replaced = str_replace("8-)", "<a class='emoticon emoticon_glasses'></a>", $replaced);
            $replaced = str_replace("<3", "<a class='emoticon emoticon_heart'></a>", $replaced);
            $replaced = str_replace("-_-", "<a class='emoticon emoticon_squint'></a>", $replaced);
            $replaced = str_replace("o.O", "<a class='emoticon emoticon_confused'></a>", $replaced);
            $replaced = str_replace(":3", "<a class='emoticon emoticon_colonthree'></a>", $replaced);
            $parsed_message = $message = str_replace("(y)", "<a class='emoticon emoticon_like'></a>", $replaced);

            // Convert the current message in HTML
            $parsed_message  = "<p id='$currentmodif' data-user='$last_user' data-receiver='$receiver'><strong style='color: $user_color'>$last_user</strong>: ". $parsed_message ."</p>";
        }

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
