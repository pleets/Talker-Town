<?php

/*
 * Talker Town - File upload
 * http://www.pleets.org
 *
 * Copyright 2014, Pleets Apps
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

$files = array();

foreach ($_FILES as $file) 
{
	if (!file_exists('../public/img/user'))
		mkdir('../public/img/user');

	if (move_uploaded_file($file['tmp_name'], "../public/img/user/". basename($file['tmp_name']) . $file['name']))
		$files[] = basename($file['tmp_name']) . $file['name'];
}

echo json_encode($files);

?>