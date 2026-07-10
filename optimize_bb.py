import random
from PIL import Image

lokasi_peradilan = [
  {'nama':'PTA Medan','lat':3.597,'lng':98.679},
  {'nama':'PA Medan (IA)','lat':3.597,'lng':98.679},
  {'nama':'PA Lubuk Pakam (IA)','lat':3.566,'lng':98.875},
  {'nama':'PA Binjai','lat':3.600,'lng':98.485},
  {'nama':'PA Stabat (IB)','lat':3.762,'lng':98.451},
  {'nama':'PA Tanjung Balai','lat':2.967,'lng':99.798},
  {'nama':'PA Kisaran (IA)','lat':2.983,'lng':99.619},
  {'nama':'PA Tebing Tinggi','lat':3.328,'lng':99.162},
  {'nama':'PA Pematang Siantar','lat':2.970,'lng':99.068},
  {'nama':'PA Simalungun','lat':2.900,'lng':99.050},
  {'nama':'PA Sidikalang','lat':2.745,'lng':98.309},
  {'nama':'PA Kabanjahe','lat':3.100,'lng':98.490},
  {'nama':'PA Sei Rampah','lat':3.450,'lng':99.157},
  {'nama':'PA Balige','lat':2.333,'lng':99.067},
  {'nama':'PA Tarutung','lat':2.017,'lng':98.967},
  {'nama':'PA Pandan','lat':1.683,'lng':98.800},
  {'nama':'PA Sibolga','lat':1.750,'lng':98.777},
  {'nama':'PA Padangsidimpuan','lat':1.379,'lng':99.271},
  {'nama':'PA Kota Padangsidimpuan','lat':1.379,'lng':99.271},
  {'nama':'PA Panyabungan','lat':0.850,'lng':99.566},
  {'nama':'PA Sibuhuan','lat':1.300,'lng':99.850},
  {'nama':'PA Rantauprapat (IB)','lat':2.100,'lng':99.833},
  {'nama':'PA Gunungsitoli','lat':1.283,'lng':97.617}
]

im = Image.open('d:/pta/assets/peta_sumut.png').convert('RGBA')
pixels = im.load()
width, height = im.size

def evaluate(lat_top, lat_bottom, lng_left, lng_right):
    score = 0
    for loc in lokasi_peradilan:
        x = int(((loc['lng'] - lng_left) / (lng_right - lng_left)) * width)
        y = int(((lat_top - loc['lat']) / (lat_top - lat_bottom)) * height)
        
        if x < 0 or x >= width or y < 0 or y >= height:
            score -= 10
            continue
            
        pixel = pixels[x, y]
        if pixel[3] != 0: # Land
            score += 1
            # Check 3x3 neighborhood for safety (we want the dot to be well within land)
            for dx in [-2, 0, 2]:
                for dy in [-2, 0, 2]:
                    nx, ny = x+dx, y+dy
                    if 0 <= nx < width and 0 <= ny < height:
                        if pixels[nx, ny][3] != 0:
                            score += 0.1
        else: # Ocean
            score -= 5
    return score

best_params = (4.4, -0.2, 96.9, 100.6)
best_score = evaluate(*best_params)

# Random search around best
for _ in range(50000):
    t = best_params[0] + random.uniform(-0.5, 0.5)
    b = best_params[1] + random.uniform(-0.5, 0.5)
    l = best_params[2] + random.uniform(-0.5, 0.5)
    r = best_params[3] + random.uniform(-0.5, 0.5)
    
    score = evaluate(t, b, l, r)
    if score > best_score:
        best_score = score
        best_params = (t, b, l, r)

print("Best score:", best_score)
print(f"LAT_TOP = {best_params[0]:.4f}")
print(f"LAT_BOTTOM = {best_params[1]:.4f}")
print(f"LNG_LEFT = {best_params[2]:.4f}")
print(f"LNG_RIGHT = {best_params[3]:.4f}")

# Verify
print("Verification:")
for loc in lokasi_peradilan:
    x = int(((loc['lng'] - best_params[2]) / (best_params[3] - best_params[2])) * width)
    y = int(((best_params[0] - loc['lat']) / (best_params[0] - best_params[1])) * height)
    if 0 <= x < width and 0 <= y < height:
        pixel = pixels[x, y]
        if pixel[3] == 0:
            print(f"{loc['nama']} is in OCEAN")
        else:
            print(f"{loc['nama']} is on LAND")
