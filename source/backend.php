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

$message = dirname(dirname(__FILE__)).'/message.txt';
$username  = dirname(dirname(__FILE__)).'/username.txt';

// store new message in the file
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$user = isset($_COOKIE['username']) ? $_COOKIE['username'] : 'anonymous';

$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
$currentmodif = filemtime($message);

// If you are logged
if (isset($_COOKIE['username']))
{
	if (!file_exists("../cache"))
		mkdir("../cache");

	if (!file_exists("../cache/users"))
		mkdir("../cache/users");

	if (!file_exists("../cache/conversations"))
		mkdir("../cache/conversations");

	if ($msg != '')
	{
		// Parsing to HTML
		$msg = "<p id='$currentmodif'>$user ~ $msg</p>";

		file_put_contents($message, $msg);
		file_put_contents($username, $user);

		// History
		$hd = fopen("../cache/conversations/history.txt", "a");
		fwrite($hd, $msg . "\n");
		fclose($hd);
	}

	file_put_contents("../cache/users/" .$user, date("Y-m-d H:i:s"));
}

/* infinite loop until the data file is not modified */

$latest_users = getFiles("../cache/users");
$online_users = array();

foreach ($latest_users as $_user)
{
	if (time() - filemtime($_user) < 3)
		$online_users[] = basename($_user);
	else
		unlink($_user);
}

$current_users = getFiles("../cache/users");

// check if the data file has been modified or an user has logged in or logged out
while ($currentmodif <= $lastmodif && count($current_users) == count($latest_users))
{
	clearstatcache();
	$currentmodif = filemtime($message);

	if (isset($_COOKIE['username']))
	{
		file_put_contents("../cache/users/" .$user, date("Y-m-d H:i:s"));

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

$last_user = file_get_contents($username);

// return a json array
$response = array();

if (isset($_GET["doRequest"]))
	$data = file_get_contents($message);
# First request when the timestamp is zero
else if ($lastmodif == 0) {
	if (file_exists("../cache/conversations/history.txt"))
		$data = file_get_contents("../cache/conversations/history.txt");
	else
		$data = "";
}
else {
# The user gets the message of other users
	if ($last_user != $user)
		$data = file_get_contents($message);
	else
		$data = '';
}

// If detects user does not send message
$response['msg']       = ($currentmodif == $lastmodif) ? '': $data;
$response['timestamp'] = $currentmodif;
$response['online_users'] = $online_users;
echo json_encode($response);
flush();

?>