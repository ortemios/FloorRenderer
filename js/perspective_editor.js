var grid_canvas;
var room_image;
var floor_x_input, floor_y_input;

var points = [];
var current_point = null;

window.onload = function ()
{
    grid_canvas = document.getElementById("grid_canvas");
	grid_canvas.width = $("#"+grid_canvas.id).outerWidth();
	grid_canvas.height = $("#"+grid_canvas.id).outerHeight();
	room_image = document.getElementById("room_image");
	
	var mouse_down = function (mouse)
    {
        var minDist = grid_canvas.width * 0.2;
        for (var i = 0; i < points.length; i++)
        {
            var p = points[i];
            var dx = p.x - mouse.x;
            var dy = p.y - mouse.y;
			var dist = dx * dx + dy * dy;
            if (dist < minDist)
            {
				minDist = dist;
                current_point = p;
            }
        }
    };

    var mouse_move = function (mouse)
    {
        if (current_point !== null)
        {
            current_point.x = mouse.x;
            current_point.y = mouse.y;
            renderGrid();
			return true;
        }
		return false;
    };

    var mouse_up = function ()
    {
        current_point = null;
		console.log(getRelativePoints());
    };
	
	grid_canvas.addEventListener('mousedown', function(e) { mouse_down(getMousePos(e, grid_canvas)); });
	grid_canvas.addEventListener('mousemove', function(e) { mouse_move(getMousePos(e, grid_canvas)); });
	grid_canvas.addEventListener('mouseup', function(e) { mouse_up(getMousePos(e, grid_canvas)); });
	grid_canvas.addEventListener('touchstart', function(e) { mouse_down(getTouchPos(e, grid_canvas)); }, false);
	grid_canvas.addEventListener('touchmove', function(e) { if(mouse_move(getTouchPos(e, grid_canvas))) e.preventDefault(); }, false);
	grid_canvas.addEventListener('touchend', function(e) { mouse_up(); }, false);
	
    floor_x_input = document.getElementById("floor_x");
    floor_y_input = document.getElementById("floor_y");

	var size = room_image.getBoundingClientRect();
    var s = Math.min(size.width, size.height) / 2;
    var r1 = 0.5;
    var r2 = 0.25;
    var ct = {x: grid_canvas.width / 2, y: grid_canvas.height / 2};
    points.push({x: ct.x - s * r1, y: ct.y - s * r2});
    points.push({x: ct.x + s * r1, y: ct.y - s * r2});
    points.push({x: ct.x + s, y: ct.y + s});
    points.push({x: ct.x - s, y: ct.y + s});
    
    renderGrid();
};

function renderGrid()
{
    var w = 10;
    var h = 10;
	var dstCorners = toList(points);
    var srcCorners = [0, 0, w, 0, w, h, 0, h];
    var perspective = PerspT(srcCorners, dstCorners);
    
    var ctx = grid_canvas.getContext("2d");
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
	
	console.log("KAK");

    ctx.strokeStyle = "#00FF00";
    for (var x = 0; x <= w; x++)
    {
        var a = perspective.transform(x, 0);
        var b = perspective.transform(x, h);
        ctx.beginPath();
        ctx.moveTo(a[0], a[1]);
        ctx.lineTo(b[0], b[1]);
        ctx.stroke();
    }
    for (var y = 0; y <= h; y++)
    {
        var a = perspective.transform(0, y);
        var b = perspective.transform(w, y);
        ctx.beginPath();
        ctx.moveTo(a[0], a[1]);
        ctx.lineTo(b[0], b[1]);
        ctx.stroke();
    }
    ctx.fillStyle = "#FF0000";
    for (var i = 0; i < points.length; i++)
    {
        var p = points[i];
        var next_p = points[(i + 1) % points.length];
		var r = 5;
		ctx.beginPath();
		ctx.arc(p.x, p.y, r, 0, 2 * Math.PI, false);
		ctx.fillStyle = 'red';
		ctx.fill();
		ctx.lineWidth = 1;
		ctx.strokeStyle = '#000000';
		ctx.stroke();
    }
}

function toList(input)
{
    var out = [];
    for(var i = 0; i < input.length; i++)
        out = out.concat([input[i].x, input[i].y]);
    return out;
}

function getRelativePoints()
{
	var out = [];
    for(var i = 0; i < points.length; i++)
	{
		var p = { x: points[i].x, y: points[i].y };
		var size = room_image.getBoundingClientRect();
		p.y -= (room_image.offsetTop - size.height / 2);
		p.x -= (room_image.offsetLeft - size.width / 2);
        out = out.concat([p.x / size.width, p.y / size.height]);
	}
    return out;
}

function getMousePos(e, canvas) 
{
    var rect = canvas.getBoundingClientRect();
    var out = 
    {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
    return out;
}

function getTouchPos(e, canvas) 
{
    var rect = canvas.getBoundingClientRect();
    var out = 
    {
        x: e.touches[0].clientX - rect.left,
        y: e.touches[0].clientY - rect.top
    };
    return out;
}