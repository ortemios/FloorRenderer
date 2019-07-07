<?php

// Upload image to clippingmagic

require("params.php");
require("util.php");


$room_name = $_REQUEST['room_name']; 

$filename = get_room_data($room_name)['original_src'];

$headers = array('Content-Type: multipart/form-data');
$post = array('image' => new CURLFile(realpath($filename)), true);
$ch = curl_init();
curl_setopt($ch, CURLOPT_USERPWD, $clippingmagic_key);
curl_setopt($ch, CURLOPT_URL, 'https://clippingmagic.com/api/v1/images?test=true');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, TRUE);
// Get id and secret to access editor

$id = $data['image']['id'];
$secret = $data['image']['secret'];

?>

<html>
    <head>
        <meta charset="utf-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="styles/3_style.css">
        <script src="js/jquery.min.js" type="text/javascript"></script>
        <script src="js/jquery.redirect.js"></script>
        <script src="https://clippingmagic.com/api/v1/ClippingMagic.js" type="text/javascript"></script>
    </head>
    <body>
        <div class="modal" id="content">
            <div class="modal_content">
                <div class="foreground">
                    <div class="textarea_1">
                        <h2>2. Mark <strong class="greentext">foreground</strong></h2>
                        <p>Use the <strong class="greentext">green tool</strong><img src="img/green_tool.png"> to mark areas of the room that will stay (walls, Furniture, Appliances etc)</p>
                    </div>
                    <div class="img">
                        <img src="img/mark_foreground.png">
                    </div>
                </div>
                <hr>
                <div class="floor">
                    <div class="textarea_2">
                        <h2>3. Mark <strong class="redheader">The Floor</strong></h2>
                        <p>Use the <strong class="redtext">red tool <img src="img/red_tool.png"></strong> to mark part of the floor</p>
                    </div>
                    <div class="img">
                        <img src="img/mark_floor.png">
                    </div>
                </div>
                <div class="proceed">
                    <button onclick="openClippingmagic();">Proceed</button>
                </div>
            </div>
        </div>

        <script type="text/javascript">
			var room_name = '<?php echo $room_name; ?>';
			var id = <?php echo $id; ?>;
				var secret = '<?php echo $secret; ?>';
                
			
            function openClippingmagic()
            {
                document.getElementById('content').style = "display: none";
                
                var errorsArray = ClippingMagic.initialize({apiId: 6506});
                if (errorsArray.length > 0) 
                {
                    alert("Sorry, your browser is missing some required features: \n\n " + errorsArray.join("\n "));
                }
				
				
				ClippingMagic.edit(
                {
                    "image": 
                    {
                        'secret' : secret,
                        'id' : id
                    },
                    "locale" : "en-US"
                }, callback);
            }

            function callback()
            {
				$.post("functions.php", { method_name: "save_image_from_clippingmagic", id : id, room_name : room_name  }, function()
				{
					$.redirect("perspective_editor.php", { room_name: room_name }, "GET");
				});
            }
        </script>
    </body>
</html>
