<?php

require("params.php");

$room_name = filter_input(INPUT_GET, 'room_name');
$tile_name = filter_input(INPUT_GET, 'tile_name');
$angle = filter_input(INPUT_GET, 'angle');
if(!isset($room_name))
{
	echo "<b>Error: room_name is undefined.</b>";
	exit();
}
if(!isset($tile_name))
	$tile_name = "1";
if(!isset($angle))
	$angle = 0;
if($angle != 0 && $angle != 1 && $angle != 2)
{
	echo "<b>Error: angle should match 0, 1 or 2(0, 45 or 90 degrees).</b>";
	exit();
}

function add_tile_icons()
{
	global $tiles_root;
    $handle = opendir($tiles_root);
	$id = 0;
    while (false !== ($filename = readdir($handle))) 
    {
        if ($filename != "." && $filename != ".." && strtolower(substr($filename, strrpos($filename, '.') + 1)) == 'json') 
        {
            $tile_data = json_decode(file_get_contents($tiles_root.$filename), TRUE);
			$id_text = "tile_icon_$id";
			$id++;
                
			echo "<div class='floor_texture' onclick='tile_icon_click(\"$id_text\", \"$tile_data[name]\");'>";
			echo 	"<div class='texture_img  texture_block'>";
			echo 		"<img id='$id_text' src='$tile_data[preview]'>";
			echo 	"</div>";
			echo 	"<div class='texture_info texture_block'>";
			echo 		"<p class='texture_title'>$tile_data[name]</p>";
			echo 		"<span>Size: ".round($tile_data['size']['x']/(0.0393701))."mm &times; ".round($tile_data['size']['y']/(0.0393701))."mm</span> <br>";
			echo 		"<span>Finish: Matt</span> <br>";
			echo 		"<a href='#'>Buy Now</a>";
			echo 	"</div>";
			echo "</div>";
        }
    }
    closedir($handle);
}
?>

<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="styles/style.css">
		<title></title>
		<script src="js/jquery.min.js"></script>
		<script src="js/jquery.lazyload.min.js"></script>
        <script src="js/jquery.redirect.js"></script>
        <script src="js/floor_renderer.js"></script>
	</head>

	<body>
		<div class="container">
			<img class="canvas" id="output"></img>
			<img  id="indicator" class="indicator" src="">
			
			<div class="copyright">
				<p>&copy; copyright - We Speak Flooring</p>
			</div>
			<div class="reset">
				<button class="reset-btn sidebar_btn" onclick='edit_floor();'>Edit floor</button>
				<button class="reset-btn sidebar_btn" onclick='reset();'>Reset</button>
				<a href='/perspective_editor.php?room_name=<?php echo $room_name;?>' style="display:none" id="edit_floor_url"></a>
			</div>
			<a href="#" class="right_sidebar_open_btn">
				<span></span>
			</a>
			<div class="right_sidebar">
				<!--<a href="#" class="right_sidebar_close_btn"></a>-->
				<a href="#" class="right_sidebar_btn"></a>
				<div class="sidebar_content">
					<div class="selections">
						
							<div class="selections_top">
								<button class="sidebar_btn">Floors</button>
								<button class="sidebar_btn">Layout</button>
							</div>
							<div class="selections_top">
								<input type="text" class="sidebar_search" name="search" placeholder="Search Product"> 
								<button class="sidebar_btn">Filter</button>
							</div>
							<!--<div class="selections_mid">
								<span class="sidebar_text">Grout Size</span>
								<input type="range" min="1" max="100" value="1" id="r1" oninput="fun1()">
								<span id="one">1</span><span class="one"> mm</span>
							</div>
							
							<div class="selections_mid">
								<span class="sidebar_text">Grout Color</span>
								<input class="grout_color_input" type="text">
								<div class="grout_color grout_color_1"></div>
								<div class="grout_color grout_color_2"></div>
								<div class="grout_color grout_color_3"></div>
								<div class="grout_color grout_color_4"></div>
							</div>-->
							
							<div class="selections_mid">
								<span class="sidebar_text">Laying angle: </span>
					
								<input type="radio" name="angle_btn" value="angle_btn" class="angle_btn" id="0deg" checked onclick="angle_btn_click(0);"></input>
								<label class="angle_label" for="0deg">0°</label>

								<input type="radio" name="angle_btn" value="angle_btn" class="angle_btn" id="45deg" onclick="angle_btn_click(1);"></input>
								<label class="angle_label" for="45deg">45°</label>

								<input type="radio" name="angle_btn" value="angle_btn" class="angle_btn" id="90deg" onclick="angle_btn_click(2);"></input>
								<label class="angle_label" for="90deg">90°</label>
							</div>
						

						<div class="floor_textures">
							<?php add_tile_icons(); ?>
						</div>
				</div>
			</div>
		</div>
		<script src="js/main.js"></script>
		<script>
			$('.right_sidebar_btn').on('click', function(e) {
				e.preventDefault();
				$('.right_sidebar').toggleClass('right_sidebar__inactive');
			});

			$('.floor_texture').on('click', function(e) {
				e.preventDefault();
				$('.right_sidebar').toggleClass('right_sidebar__inactive');
			});

			$('.right_sidebar_close_btn').on('click', function(e) {
				e.preventDefault();
				$('.right_sidebar').toggleClass('right_sidebar__inactive');
			});

			$('.right_sidebar_open_btn').on('click', function(e) {
				e.preventDefault();
				$('.right_sidebar').removeClass('right_sidebar__inactive');
			});
			
			
			var renderer = new FloorRenderer("output");
			var canvas = document.getElementById("output");
			var current_tile_icon = document.getElementById("current_tile_icon");
			var indicator = document.getElementById("indicator");
			var angle = '<?php echo $angle;?>';
			var tile_name = '<?php echo $tile_name;?>';
			var room_name = '<?php echo $room_name; ?>';
			
			function reset()
			{
				$.post("functions.php", { method_name : "delete_room", room_name : "<?php echo $room_name;?>" }, "GET");
				$.redirect("index.php", {}, "GET");
			}
			
			function edit_floor()
			{
				$.redirect("perspective_editor.php", { room_name : "<?php echo $room_name;?>" }, "GET");
			}
			
			function angle_btn_click(value)
			{
				if(value == angle)
					return;
				angle = value;
				render_tile();
			}
            
			function tile_icon_click(id, selected_tile_name)
			{
				if(tile_name == selected_tile_name)
					return;
				tile_name = selected_tile_name;
				render_tile();
			}
		
			function render_tile()
			{
				var base = location.protocol + '//' + location.host + location.pathname;
				var params = 'room_name='+room_name+'&tile_name='+tile_name+'&angle='+angle;
				var url = base + '?' + params;
				history.pushState(null, '', url);
				renderer.render(room_name, tile_name, angle, 0, function() {});
			}
			render_tile();
		</script>
	</body>
</html>
