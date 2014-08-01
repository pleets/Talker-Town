<?php

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

header("Content-Type: application/javascript; charset=UTF-8");

$message = dirname(__FILE__).'/message.txt';
$username  = dirname(__FILE__).'/username.txt';

// store new message in the file
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$user = getIp();

if (!file_exists("cache"))
   mkdir("cache");

if ($msg != '')
{
	file_put_contents($message, $user ." ~ ". $msg);
	file_put_contents($username, $user);
}

file_put_contents("cache/" .$user, date("Y-m-d H:i:s"));

// infinite loop until the data file is not modified
$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
$currentmodif = filemtime($message);

$latest_users = getFiles("cache");
$online_users = array();

foreach ($latest_users as $_user)
{
   if (time() - filemtime($_user) < 10)
      $online_users[] = basename($_user);
   else
      unlink($_user);
}

$current_users = getFiles("cache");

// check if the data file has been modified or an user login/logout
while ($currentmodif <= $lastmodif  && count($current_users) == count($latest_users))
{
	clearstatcache();
	$currentmodif = filemtime($message);

   file_put_contents("cache/" .$user, date("Y-m-d H:i:s"));

   $current_users = getFiles("cache");
   $online_users = array();

   foreach ($current_users as $_user) {
      if (time() - filemtime($_user) < 10)
         $online_users[] = basename($_user);
      else
         unlink($_user);
   }
}

$last_user = file_get_contents($username);

// return a json array
$response = array();
$response['msg']       = ($last_user != $user) ? file_get_contents($message) : '';
$response['timestamp'] = $currentmodif;
$response['online_users'] = $online_users;
$json = json_encode($response);

if (array_key_exists("doRequest", $_GET))
    echo "comet.successRequest('$json')";
else
    echo "comet.successConnection('$json')";

flush();
?>