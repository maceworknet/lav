from PIL import Image

img_path = r"C:\Users\yaser\.gemini\antigravity\brain\23b18921-8fcb-4931-9cd4-d7c8a05698b9\media__1780918327515.png"
try:
    img = Image.open(img_path)
    width, height = img.size
    rgb_img = img.convert('RGB')
    
    for y in [100, 200, 300, 400]:
        print(f"\nHorizontal profile at Y = {y}:")
        pixels = []
        for x in range(1000, 1024):
            r, g, b = rgb_img.getpixel((x, y))
            pixels.append(f"X={x:d}:({r},{g},{b})")
        print(" | ".join(pixels))
        
except Exception as e:
    print(f"Error: {e}")
