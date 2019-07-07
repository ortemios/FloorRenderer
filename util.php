<?php

function get_room_data($room_name)
{
	global $rooms_root;
	$filename = $rooms_root.$room_name.'.json';
    $json = file_get_contents($filename);
	if ($json == FALSE)
		return FALSE;
	$room_data = json_decode($json, TRUE);
	return $room_data;
}

function save_room_data($room_name, $room_data)
{
	global $rooms_root;
	$filename = $rooms_root.$room_name.'.json';
	$json = json_encode($room_data);
    file_put_contents($filename, $json);
}


function rrmdir($path) {
	if(!is_dir($path)) return;
    $i = new DirectoryIterator($path);
    foreach($i as $f) {
        if($f->isFile()) {
            unlink($f->getRealPath());
        } else if(!$f->isDot() && $f->isDir()) {
            rrmdir($f->getRealPath());
			rmdir($f->getRealPath());
        }
    }
}

function clean_content()
{
	$now = time();
	$dir = new DirectoryIterator("pre_rendered");
	foreach ($dir as $fileinfo) {
		if ($fileinfo->isDot() || !$fileinfo->isDir()) continue;
		$file = $fileinfo->getRealPath();
		if (($now - $fileinfo->getMTime()) > (60 * 60 * 24 * 2 * 30))
			rrmdir($file);
	}
}