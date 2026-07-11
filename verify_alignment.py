import json

# Lokasi peradilan - FAKTA, tidak ada karangan
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
  {'nama':'PA Gunungsitoli','lat':1.283,'lng':97.617},
]

# Read the SVG
with open('d:/pta/assets/peta_sumut.svg', 'r', encoding='utf-8') as f:
    svg_content = f.read()

# Bounding box SAMA PERSIS dengan yang dipakai generate_svg.py
LAT_TOP = 4.29478
LAT_BOTTOM = -0.5866
LNG_LEFT = 97.06365
LNG_RIGHT = 100.44398
W = 1000
H = 1444

# Remove closing </svg> tag, we'll add markers then close
svg_content = svg_content.replace('</svg>', '')

# Add red dots for each location
for loc in lokasi_peradilan:
    x = ((loc['lng'] - LNG_LEFT) / (LNG_RIGHT - LNG_LEFT)) * W
    y = ((LAT_TOP - loc['lat']) / (LAT_TOP - LAT_BOTTOM)) * H
    svg_content += f'  <circle cx="{x:.2f}" cy="{y:.2f}" r="6" fill="red" stroke="white" stroke-width="1.5"/>\n'
    svg_content += f'  <text x="{x+10:.2f}" y="{y+4:.2f}" fill="white" font-size="10" font-family="sans-serif">{loc["nama"]}</text>\n'

svg_content += '</svg>'

# Save as a test SVG
with open('d:/pta/test_alignment.svg', 'w', encoding='utf-8') as f:
    f.write(svg_content)

# Also create an HTML file to view it
html = f"""<!DOCTYPE html>
<html>
<head><title>Verifikasi Titik vs Peta SVG</title></head>
<body style="background:#1a1a2e; margin:0; padding:20px;">
<h2 style="color:white; font-family:sans-serif; text-align:center;">Verifikasi: Titik Merah = Lokasi Pengadilan di atas Peta SVG</h2>
<div style="max-width:700px; margin:0 auto;">
{svg_content}
</div>
</body>
</html>"""

with open('d:/pta/test_alignment.html', 'w', encoding='utf-8') as f:
    f.write(html)

print("Done! Open test_alignment.html in browser to verify.")
print(f"SVG viewBox: 0 0 {W} {H}")
print(f"Bounding box: lat {LAT_BOTTOM} to {LAT_TOP}, lng {LNG_LEFT} to {LNG_RIGHT}")

# Print each location's pixel position
print("\n--- Posisi piksel tiap titik ---")
for loc in lokasi_peradilan:
    x = ((loc['lng'] - LNG_LEFT) / (LNG_RIGHT - LNG_LEFT)) * W
    y = ((LAT_TOP - loc['lat']) / (LAT_TOP - LAT_BOTTOM)) * H
    pct_x = ((loc['lng'] - LNG_LEFT) / (LNG_RIGHT - LNG_LEFT)) * 100
    pct_y = ((LAT_TOP - loc['lat']) / (LAT_TOP - LAT_BOTTOM)) * 100
    print(f"{loc['nama']:30s} -> SVG ({x:7.1f}, {y:7.1f}) = CSS ({pct_x:5.1f}%, {pct_y:5.1f}%)")
