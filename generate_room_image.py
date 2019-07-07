import os
import sys
import cv2
import json
import numpy as np
import math
import random
from PIL import Image

ROOT = os.path.dirname(os.path.abspath(__file__)) + '/'
ROOT = ROOT.replace('\\','/')
MAX_SIZE = 6000
PADDING = 0.3

def load_tile_textures(tile_data):
	dir = os.path.join(ROOT, tile_data['src'])
	tile_texture_files = [file for file in os.listdir(dir) if os.path.isfile(os.path.join(dir, file))]
	out = []
	for file in tile_texture_files:
		path = os.path.join(dir, file)
		tile = cv2.imread(path, cv2.IMREAD_COLOR)
		out.append(tile)
		
	return out
	
def perform_tiling(shape, dtype, tile_textures, offset_ampl, rotation):
	tile_height = tile_textures[0].shape[0]
	tile_width = tile_textures[0].shape[1]
	
	oldY = shape[0]
	oldX = shape[1]
	newX,newY = oldX,oldY
	r = np.deg2rad(rotation)
	newX,newY = (int(abs(np.sin(r)*newY) + abs(np.cos(r)*newX)),int(abs(np.sin(r)*newX) + abs(np.cos(r)*newY)))
	(tx,ty) = ((newX-oldX)/2,(newY-oldY)/2)
	M = cv2.getRotationMatrix2D(center=(newX/2,newY/2), angle=-rotation, scale=1) 
	M[0,2] -= tx
	M[1,2] -= ty
	
	img = np.zeros((newY, newX, shape[2]), dtype)
	tiles = []
	last_offset = 0
	min_offset = int(offset_ampl * 0.33)
	for X in np.arange(0.0, img.shape[1], tile_width):
		ampl = max(last_offset, offset_ampl-last_offset)
		delta = random.randint(min_offset, ampl)*random.choice([1,-1])
		offset_y = last_offset + delta
		if(offset_y >= offset_ampl or offset_y < 0):
			offset_y = last_offset - delta
		last_offset = offset_y
		
		for Y in np.arange(-offset_y, img.shape[0], tile_height):
			if len(tiles) == 0:
				tiles = [x for x in range(0, len(tile_textures))]
			id = random.choice(tiles)
			tiles.remove(id)
			
			x = min(int(X), img.shape[1]-1)
			y = min(int(Y), img.shape[0]-1)
			height = min(img.shape[0]-y, tile_height)
			width = min(img.shape[1]-x, tile_width)
			
			_oy = max(0, -y) # to crop height
			
			img[y+_oy:y+height, x:x+width] = tile_textures[id][_oy:height, 0:width]
			
	img = cv2.warpAffine(img, M, dsize=(int(oldX),int(oldY)))
	
	return img
	
def create_floor_texture(room_data, tile_data, tile_textures, angle):
	tile_x = tile_textures[0].shape[1]
	tile_y = tile_textures[0].shape[0]
	tile_c = tile_textures[0].shape[2]
	
	tiles_per_x = room_data['plane_size']['x']*12/tile_data['size']['x']
	tiles_per_y = room_data['plane_size']['y']*12/tile_data['size']['y']
	
	width = full_width = tile_x * tiles_per_x
	height = full_height = tile_y * tiles_per_y
	
	w = int(math.ceil(tile_data['border_width']/tile_data['size']['x']*tile_x * 0.0393701))
	
	# fit to MAX_SIZE and keep aspect ratio
	if max(width, height) > MAX_SIZE:
		ratio = width/height
		if width > height:
			width = MAX_SIZE
			height = int(width/ratio)
		else:
			height = MAX_SIZE
			width = int(height*ratio)
	
	# rescale tile textures to fit new size
	tile_x = int(tile_x*width/full_width)
	tile_y = int(tile_y*height/full_height)
	for i in range(0, len(tile_textures)):
		tile_textures[i] = cv2.resize(tile_textures[i], (tile_x, tile_y), interpolation=cv2.INTER_LINEAR)
		if w > 0:
			cv2.rectangle(tile_textures[i], (0,0), (tile_x-1, tile_y-1), color=tuple(tile_data['border_color']), thickness=w)
	
	offset_ampl = 0
	if tile_data['pattern'] == '1':
		offset_ampl = int(tile_y/2)
		
	# create floor texture
	floor_image = perform_tiling((int(height), int(width), tile_c), tile_textures[0].dtype, tile_textures, offset_ampl, angle)
	floor_image = cv2.GaussianBlur(floor_image, (5, 5), 0)
	
	return floor_image 
	
def add_shadows(foreground, background):
	G_LEN = int(foreground.shape[0] * 101/1410)
	if G_LEN % 2 == 0:
		G_LEN += 1
	template = 1 - (foreground[:, :, 3] / 255)
	template = np.reshape(template, (template.shape[0], template.shape[1], 1))
	template = cv2.GaussianBlur(template, (G_LEN, G_LEN), 0)
	for c in range(0, 3):
		background[:, :, c] = np.multiply(template, background[:, :, c])
	return background
	
def main():
	global MAX_SIZE
	room_json_path = sys.argv[1]
	tile_json_path = sys.argv[2]
	angle = sys.argv[3]
	output_filename = sys.argv[4]
	
	with open(os.path.join(ROOT, room_json_path)) as file:
		room_data = json.load(file)
	with open(os.path.join(ROOT, tile_json_path)) as file:
		tile_data = json.load(file)

	room_image = cv2.imread(ROOT + room_data['src'], -1)
	
	ratio = room_image.shape[0]/room_image.shape[1]
	width = MAX_SIZE
	height = int(ratio*width)
	
	tile_textures = load_tile_textures(tile_data)
	
	floor_texture = create_floor_texture(room_data, tile_data, tile_textures, int(angle))
	
	W = floor_texture.shape[1]
	H = floor_texture.shape[0]
	
	pts = []
	for i in range(0, 4):
		p = []
		offset = i*2
		p.append(room_data['perspective_points'][offset+0] * width)
		p.append(room_data['perspective_points'][offset+1] * height)
		pts.append(p)
	dst_points = np.float32(pts)
	
	src_points = np.float32([[W*PADDING, H*PADDING], [W*(1-PADDING), H*PADDING], [W*(1-PADDING), H*(1-PADDING)], [W*PADDING, H*(1-PADDING)]])

	# generate floor image
	M = cv2.getPerspectiveTransform(src_points, dst_points)
	floor_image = cv2.warpPerspective(floor_texture, M, dsize = (width, height), flags = cv2.INTER_LINEAR)
	
	sym = Image.fromarray(floor_image)
	sym = sym.resize((room_image.shape[1], room_image.shape[0]), Image.ANTIALIAS)
	floor_image = np.array(sym)
	
	floor_image = add_shadows(room_image, floor_image)
	
	# blend floor and room images
	output_image = cv2.cvtColor(floor_image, cv2.COLOR_BGR2BGRA)
	alpha = room_image[:, :, 3]/255
	for c in range(0, 3):
		foreground = alpha*room_image[:, :, c]
		background = (1-alpha)*floor_image[:, :, c]
		output_image[:, :, c] = background + foreground

	filename = ROOT + output_filename
	try:
		path = os.path.dirname(filename)
		os.makedirs(path)
		os.chmod(path, 0o777)
	except Exception as e:
		pass
	cv2.imwrite(filename, output_image)
	
main()