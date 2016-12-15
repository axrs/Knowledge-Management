<?php

// This is a simplified example, which doesn't cover security of uploaded files. 
// This example just demonstrate the logic behind the process.

$dir = $_SERVER['DOCUMENT_ROOT'].'/uploads/';

copy($_FILES['file']['tmp_name'], $dir.$_FILES['file']['name']);
					
$array = array(
	'filelink' => '/uploads/'.$_FILES['file']['name'],
	'filename' => $_FILES['file']['name']
);

echo stripslashes(json_encode($array));
	
?>