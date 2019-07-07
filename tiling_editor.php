<?php
$room_name = $_REQUEST['room_name'];
?>

<html>
    <head>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.redirect.js"></script>
        <script src="js/floor_renderer.js"></script>
		<script src="js/jquery.lazyload.min.js"></script>
        <link rel="stylesheet" type="text/css" href="styles/2_style.css">
    </head>
    <body>
        <div id="preview">
            <div class="main">
                <div class="size_select">
                    <p>Floor width: <input type="number" name="plane_width" value="3" step="0.01" onchange="changeSize();"></p>
                    <p>Floor height: <input type="number" name="plane_height" value="3" step="0.01" onchange="changeSize();"></p>
                </div>
                <div class="outter_img">
                    <img class="inner_img" id="output"></img>
                    <a style="background-color: #FFFFFF" id="statusbar">Rendering...</a>
                </div>
            </div>

            <div class="panel">
                <div class="btns">
					<font color="white">Make sure you set correct sizes.</font>
					<br>
					<br>
                    <input class="upload_btn" type="button" onclick="$.redirect('perspective_editor.php', { room_name: '<?php echo $room_name; ?>' });" value="Back"></input>
                    <input class="proceed_btn" type="button" onclick="$.redirect('<?php echo "floor_viewer.php?room_name=$room_name"; ?>');" value="Proceed"></button>
                </div>
            </div>
        </div>
    </body>
    
    <script>
		var room_name = '<?php echo $room_name; ?>';
        var renderer = new FloorRenderer("output");
        var statusbar = document.getElementById("statusbar");
        var plane_width_input = document.getElementsByName("plane_width")[0];
        var plane_height_input = document.getElementsByName("plane_height")[0];
        
		loadSizes();
		function loadSizes()
		{
			$.post("functions.php", { method_name: "get_plane_size", room_name: '<?php echo $room_name; ?>' }, function(resp) {
				var data = JSON.parse(resp);
				plane_width_input.value = data['x'];
				plane_height_input.value = data['y'];
			});
		}
		
        function changeSize()
        {
            var plane_size = {x: parseFloat(plane_width_input.value), y: parseFloat(plane_height_input.value)};
            var plane_size_json = JSON.stringify(plane_size);
            $.post("functions.php", { method_name: "save_plane_size", room_name: room_name, plane_size: plane_size_json }, function() {
                statusbar.style.display = "";
                setTimeout(function() {
                    renderer.render('<?php echo $room_name; ?>', '1', 0, 1, function() {
                        statusbar.style.display = "none";
                    });
                }, 1);
            });
        }
        changeSize();
    </script>
</html>
