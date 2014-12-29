<?php

include "FileSystem/IShellCommands.php";
include "FileSystem/Shell.php";

# New shell
$filesystem = new \Pleets\FileSystem\Shell();

$command = explode(" ", $_POST["command"]);
$command[1] = array_key_exists(1, $command) ? $command[1] : "";
$command[2] = array_key_exists(2, $command) ? $command[2] : "";

switch ($command[0]) {
	case 'pwd':
		echo $filesystem->$command[0]();
		break;
	case 'ls':
		echo implode(" ", $filesystem->$command[0]($command[1]));
		break;
	case 'cd':
		$filesystem->$command[0]($command[1]);
		break;
	case 'touch':
		$filesystem->$command[0]($command[1]);
		break;
	case 'rm':
		$filesystem->$command[0]($command[1]);
		break;
	case 'cp':
		$filesystem->$command[0]($command[1], $command[2]);
		break;
	case 'mv':
		$filesystem->$command[0]($command[1], $command[2]);
		break;
	case 'mkdir':
		$filesystem->$command[0]($command[1], $command[2]);
		break;
	case 'rmdir':
		$filesystem->$command[0]($command[1]);
		break;
	default:
		echo "Command not found!";
		break;
}

?>