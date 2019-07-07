<?php
require('params.php');
require('util.php');

$room_name = $_REQUEST['room_name'];

$room_image_src = get_room_data($room_name)['original_src'];
?>

<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="styles/4_style.css">
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.redirect.js"></script>
        <script src="js/perspective-transform.min.js"></script>
		<script src="js/perspective_editor.js" type="text/javascript"></script>
    </head>
    <body>
        <div class="content-wrap">
            <div class="content">
                <img id="room_image" class="room_image" src="<?php echo $room_image_src; ?>">
                <canvas id="grid_canvas" class="grid_canvas">
            </div>
        </div>
        <a href="#" class="right_sidebar_open_btn">
            <span></span>
        </a>

        <div class="right_sidebar">
            <a href="#" class="right_sidebar_close_btn"></a>
            <div class="sidebar_content">
                <h4>STEP 2</h4>
                <h3>Define Floor Viewing Angle</h3>
                
                <div class="main_container">
                    <div class="img">
                        <img src="img/example_png.png">
                        <div class="fullscreen">
                            <a href="" id="fullscreen">FULLSCREEN</a>
                        </div>
                    </div>
                    <div class="text">
                        <ol>
                            <li>Drag the green dots on each of the corners to move the plane until the angle matches how your floor should be viewed in your room.</li>
                        </ol>
                    </div>
                </div>
                <div class="save">
                    <button onclick="finish();">SAVE & SELECT FLOORING</button>
                </div>
            </div>
        </div>
    </body>

    <script>
        $('.right_sidebar_close_btn').on('click', function(e) {
                e.preventDefault();
                $('.right_sidebar').toggleClass('right_sidebar__inactive');
        });

            $('.right_sidebar_open_btn').on('click', function(e) {
                e.preventDefault();
                $('.right_sidebar').removeClass('right_sidebar__inactive');
            });
    </script>

    <script>
        $('#fullscreen').click(fullscreen);
        function fullscreen(e)
        {
            e.preventDefault();
            return false;
        }

        function finish()
        {
            points = getRelativePoints();
            var room_name = '<?php echo $room_name?>';
            var perspective_points_json = JSON.stringify(points);
            $.post("functions.php", { method_name: "save_perspective_points", room_name: room_name, perspective_points: perspective_points_json }, function()
			{
                $.redirect("tiling_editor.php", { room_name: room_name }, "GET");
            });
        };
    </script>

    <script>
        var height = $('.content').height();
        var width = $('.content').width();
        var visibleWidth = $('content-wrap').width();
        $('.content-wrap').scrollTop((height / 2) - document.documentElement.clientHeight / 2);
        $('.content-wrap').scrollLeft((width / 2) - (document.documentElement.clientWidth - document.documentElement.clientWidth * 0.3) / 2 );
    </script>
</html>