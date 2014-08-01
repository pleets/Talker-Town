<?php
if (!isset($_COOKIE["username"]))
	header("location: ./index.php");

// Delete persist file
unlink('cache/users/'. $_COOKIE["username"]);					# Delete persistent user file
setcookie("username", $_COOKIE["username"], time()-3600);		# Expires cookie
header("location: ./index.php");
?>