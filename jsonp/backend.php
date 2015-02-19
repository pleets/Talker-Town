<?php

header("Content-Type: application/javascript; charset=UTF-8");

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

function getIp()
{
   if (!empty($_SERVER['HTTP_CLIENT_IP']))
      return $_SERVER['HTTP_CLIENT_IP'];
   if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
     return $_SERVER['HTTP_X_FORWARDED_FOR'];

   return $_SERVER['REMOTE_ADDR'];
}


function getIdentity() 
{
   return isset($_GET["logged_user"]) ? $_GET["logged_user"] : getIp();
}


// return a json array
$response = array();
$response['errors'] = array();


/* create some folders */

if (!file_exists('data'))
   mkdir('data');

if (!file_exists('data/cache'))
   mkdir('data/cache');

if (!file_exists('data/cache/conversations'))
   mkdir('data/cache/conversations');

if (!file_exists('data/cache/conversations/timestamp'))
   mkdir('data/cache/conversations/timestamp');

if (!file_exists('data/cache/users'))
   mkdir('data/cache/users');


/* create json file with users settings */
$user_info = array(
   "username" => getIdentity(),
   "avatar" => 11
);

if (!file_exists('data/cache/' . getIdentity() . '.json'))
   file_put_contents('data/cache/' . getIdentity() . '.json', 'jsonpClient(' . json_encode($user_info) . ')');


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
$data_username = $username = getIdentity();

$message = base64_decode($message);


// Get the current and last timestamp of the message file
$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;     # The first time the timestamp is equal to zero
$data_id = $currentmodif = filemtime($message_file);

// Timestamp file
$timestamp_file = "data/cache/conversations/timestamp/" . $currentmodif . ".txt";


// If you are logged
if (!is_null($username) && !empty($username))
{
   if (!empty($message))
   {
       // Store message and username
       file_put_contents($message_file, $message);
       file_put_contents($username_file, $username);

       // Store timestamp file
       file_put_contents($timestamp_file, htmlentities($message));
   }

   file_put_contents("data/cache/users/" . $username, date("Y-m-d H:i:s"));
}
else if (is_null($username))
   $response['errors'][] = array(
       'code' => 101,
       'name' => 'Lost session',
       'message' => 'The session has been lost!'
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
   $username = getIdentity();

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
       $response['errors'][] = array(
           'code' => 101,
           'name' => 'Lost session',
           'message' => 'The session has been lost!'
       );
       break;
   }
}

$last_user = file_get_contents($username_file);

if (isset($_GET["doRequest"]))
       $data_contents_message = file_get_contents($timestamp_file);
# First request when the timestamp is zero
else if ($lastmodif == 0) {
   if (file_exists("data/cache/conversations/history.txt"))
       $data_contents_message = file_get_contents("data/cache/conversations/history.txt");
   else
       $data_contents_message = "";
}
else {
# The user gets the message of other users
   if ($last_user != $username) 
       $data_contents_message = ($lastmodif > $currentmodif) ? file_get_contents($timestamp_file) : file_get_contents("data/cache/conversations/timestamp/" . $lastmodif . ".txt");
   else
       $data_contents_message = '';
}


// If detects user does not send message
$response['msg'] = ($currentmodif == $lastmodif) ? '': $data_contents_message;

// Parse msg
if (!empty($message))
{ 
   $replaced = str_replace(">:(", "<a class='emoticon emoticon_grumpy'></a>", $response['msg']);
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
   $message = str_replace("(y)", "<a class='emoticon emoticon_like'></a>", $replaced);

   /* Only when start text ... */
   if (substr($message, 0, 7) == 'http://' || substr($message, 0, 8) == 'https://')
   {
       $message_to_send = "<p id='$currentmodif'>$last_user ~ <a target='_blank' href='". $message ."' >". $message ."</a></p>";
   }
   else {
       // Convert the current message in HTML
       $message_to_send  = "<p id='$currentmodif'>$last_user ~ $message</p>";
   }

   // Store message in the chat history
   $hd = fopen("data/cache/conversations/history.txt", "a");
   fwrite($hd, $message_to_send . "\n");
   fclose($hd);

   $response['msg'] = $message_to_send;
}

$response["msg"] = base64_encode($response["msg"]);
$response['user'] = $last_user;
$response['timestamp'] = $currentmodif;
$response['firstTimestamp'] = $lastmodif;
$response['online_users'] = $online_users;

$json = json_encode($response);

if (array_key_exists("doRequest", $_GET))
   echo "comet.successRequest('$json')";
else
   echo "comet.successConnection('$json')";

flush();
?>