from PIL import Image

img_path = r"C:\Users\yaser\.gemini\antigravity\brain\23b18921-8fcb-4931-9cd4-d7c8a05698b9\media__1780918327515.png"
try:
    img = Image.open(img_path)
    width, height = img.size
    
    rgb_img = img.convert('RGB')
    arrow_pixels = []
    # Search on the right side only
    for x in range(800, width):
        for y in range(150, height):
            r, g, b = rgb_img.getpixel((x, y))
            # pure hand drawn arrow red color
            if r > 180 and g < 50 and b < 50:
                arrow_pixels.append((x, y))
                
    print(f"Arrow pixels count (right side): {len(arrow_pixels)}")
    if arrow_pixels:
        xs = [p[0] for p in arrow_pixels]
        ys = [p[1] for p in arrow_pixels]
        min_x, max_x = min(xs), max(xs)
        min_y, max_y = min(ys), max(ys)
        print(f"Right Arrow Bounding Box: X: {min_x} to {max_x}, Y: {min_y} to {max_y}")
        
        # Let's save a crop of this right arrow area
        crop_box = (max(0, min_x-50), max(0, min_y-50), min(width, max_x+50), min(height, max_y+50))
        cropped = img.crop(crop_box)
        cropped.save(r"C:\Users\yaser\.gemini\antigravity\brain\23b18921-8fcb-4931-9cd4-d7c8a05698b9\right_arrow_crop.png")
        print("Cropped right arrow image saved as right_arrow_crop.png")
    else:
        print("No right arrow pixels found")
except Exception as e:
    print(f"Error: {e}")
