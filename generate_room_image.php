<?php

require("params.php");

$room_name = $_REQUEST['room_name'];

$tile_name = $_REQUEST['tile_name'];

$angle = 0;
if(isset($_REQUEST['angle']))
	$angle = $_REQUEST['angle'];
else if($angle != 0 && $angle != 1 && $angle != 2)
	$angle = 0;
$angle = $angle * 45;

$filename = "pre_rendered/$room_name/$angle/$tile_name.jpg";

if (!file_exists($filename))
{
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		$python = "C:\\Users\\artye\\AppData\\Local\\Programs\\Python\\Python36\\python.exe";
	else
		$python = "python3";
	$cmd = escapeshellcmd("$python generate_room_image.py $rooms_root$room_name.json $tiles_root$tile_name.json $angle $filename");
	$out = shell_exec($cmd);
}

//echo "data:image/png;base64," . base64_encode(file_get_contents($filename));
$url = $_SERVER['REQUEST_URI'];
$parts = explode('/',$url);
$dir = $_SERVER['SERVER_NAME'];
for ($i = 0; $i < count($parts) - 1; $i++)
	$dir .= $parts[$i] . "/";
$dir = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$dir;
echo "$dir$filename";
