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

LAT_TOP = 4.4
LAT_BOTTOM = -0.2
LNG_LEFT = 96.9
LNG_RIGHT = 100.6

im = Image.open('d:/pta/assets/peta_sumut.png').convert('RGBA')
pixels = im.load()
width, height = im.size

print('Image size:', width, height)

for loc in lokasi_peradilan:
    x = int(((loc['lng'] - LNG_LEFT) / (LNG_RIGHT - LNG_LEFT)) * width)
    y = int(((LAT_TOP - loc['lat']) / (LAT_TOP - LAT_BOTTOM)) * height)
    
    if x < 0 or x >= width or y < 0 or y >= height:
        print(f"{loc['nama']}: Out of bounds ({x}, {y})")
        continue
        
    pixel = pixels[x, y]
    if pixel[3] == 0:
        print(f"{loc['nama']} is in OCEAN! Pixel: {pixel}")
    else:
        print(f"{loc['nama']} is on LAND. Pixel: {pixel}")
