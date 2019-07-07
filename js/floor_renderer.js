
var initizalized = false;

function min(a, b)
{
	return (a < b ? a : b);
}

function FloorRenderer(canvas_id, FLOOR_TEXTURE_SIZE = 8000)
{
    this.FLOOR_TEXTURE_SIZE = FLOOR_TEXTURE_SIZE;
    this.canvas = document.getElementById(canvas_id);
};

FloorRenderer.prototype.render = function (room_name, tile_name, angle = 0, center = 0, func = null)
{
	var canvas = this.canvas;
	$.get('generate_room_image.php', { room_name: room_name, tile_name: tile_name, angle: angle }, function (response)
    {
		canvas.setAttribute("src", response+'?m'+new Date().getTime());
		$("#"+canvas.id).lazyload();
		if(func != null)
			func();
    });
};

