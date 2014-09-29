<?php

/*
 * Talker Town - Backend
 * http://www.pleets.org
 *
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

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
$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;		# The first time the timestamp is equal to zero
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

?>