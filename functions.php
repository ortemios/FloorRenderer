<?php

require('params.php');
require('util.php');

function save_image_from_clippingmagic()
{
	clean_content();
	
    global $rooms_root;
    global $clippingmagic_key;
	
    $id = $_REQUEST['id'];
    $room_name = $_REQUEST['room_name'];
    $src = get_room_data($room_name)['src'];
	
    $url = "https://clippingmagic.com/api/v1/images/$id";

    set_time_limit(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERPWD, $clippingmagic_key);
    $contents = curl_exec($ch);
    curl_close($ch);
    header('Content-Transfer-Encoding: binary');
    file_put_contents($src, $contents);
}

function save_perspective_points()
{
	$room_name = $_REQUEST['room_name'];
    $data = get_room_data($room_name);
    $data['perspective_points'] = json_decode($_REQUEST['perspective_points'], TRUE);
    save_room_data($room_name, $data);
	
	clean_room_rendering($_REQUEST['room_name']);
}

function save_plane_size()
{
    $room_name = $_REQUEST['room_name'];
    $data = get_room_data($room_name);
    $data['plane_size'] = json_decode($_REQUEST['plane_size'], TRUE);
    save_room_data($room_name, $data);

	clean_room_rendering($room_name);
}

function delete_room()
{
	$room_name = $_REQUEST['room_name'];
	clean_room_rendering($room_name);
	unlink($rooms_root.$room_name.'.json');
	unlink($rooms_root.$room_name.'.png');
	unlink($rooms_root.$room_name.'.original.png');
}

function clean_room_rendering($room_name)
{
	$path = "pre_rendered/$room_name";
	rrmdir($path);
	rmdir($path);
}

function get_plane_size()
{
    $room_name = $_REQUEST['room_name'];
    $data = get_room_data($room_name);
    echo json_encode($data['plane_size'], TRUE);
}

$method_name = $_REQUEST['method_name'];
call_user_func($method_name);

