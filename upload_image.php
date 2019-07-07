<?php

// Upload image to clippingmagic
// Save filename and id to session

require("params.php");
require("util.php");

$room_name = uniqid();
$basename = $rooms_root.$room_name;
$filename = $basename.'.original.png';

$img = $_POST['image'];
$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);

if (file_put_contents($filename, $data)) {
	$plane_size = array();
	$size = getimagesize($filename);
	$plane_size['x'] = round($size[0] * (6/1600), 2);
	$plane_size['y'] = round($size[1] * (6/900), 2);
	
	$room_data = array();
	$room_data['src'] = $basename.'.png';
    $room_data['original_src'] = $filename;
	$room_data['plane_size'] = $plane_size;
    save_room_data($room_name, $room_data);
	
	echo $room_name;
	die();
} else {
    echo "Image uploading failed.";
}

?>