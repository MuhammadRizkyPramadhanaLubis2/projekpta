import json

with open('d:/pta/sumatera_all.json', 'r') as f:
    all_provinces = json.load(f)

# Hanya gunakan provinsi di daratan Sumatera utama (tanpa Kep. Riau & Bangka Belitung)
mainland_provinces = [
    'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau',
    'Jambi', 'Sumatera Selatan', 'Bengkulu', 'Lampung'
]

# Collect coordinates only from mainland for bounding box
all_coords = []
def extract_coords(obj):
    if type(obj) == list and len(obj) >= 2 and type(obj[0]) in (float, int):
        all_coords.append(obj[:2])
    elif type(obj) == list:
        for item in obj:
            extract_coords(item)

for prov_name in mainland_provinces:
    if prov_name in all_provinces:
        extract_coords(all_provinces[prov_name]['coordinates'])

min_lon = min(c[0] for c in all_coords)
max_lon = max(c[0] for c in all_coords)
min_lat = min(c[1] for c in all_coords)
max_lat = max(c[1] for c in all_coords)

# Add small padding
pad_lon = (max_lon - min_lon) * 0.03
pad_lat = (max_lat - min_lat) * 0.03
min_lon -= pad_lon
max_lon += pad_lon
min_lat -= pad_lat
max_lat += pad_lat

print(f"Bounding box: lon [{min_lon:.4f}, {max_lon:.4f}], lat [{min_lat:.4f}, {max_lat:.4f}]")

W = 800
H = int(W * ((max_lat - min_lat) / (max_lon - min_lon)))

print(f"SVG size: {W} x {H}")

def lonlat_to_xy(lon, lat):
    x = ((lon - min_lon) / (max_lon - min_lon)) * W
    y = ((max_lat - lat) / (max_lat - min_lat)) * H
    return x, y

def geojson_to_paths(geojson):
    paths = []
    geom_type = geojson['type']
    coords = geojson['coordinates']
    
    polygons = []
    if geom_type == 'Polygon':
        polygons = [coords]
    elif geom_type == 'MultiPolygon':
        polygons = coords
    
    for polygon in polygons:
        ring = polygon[0]
        path_d = ""
        for i, coord in enumerate(ring):
            lon, lat = coord[0], coord[1]
            x, y = lonlat_to_xy(lon, lat)
            if i == 0:
                path_d += f"M {x:.1f} {y:.1f} "
            else:
                path_d += f"L {x:.1f} {y:.1f} "
        path_d += "Z"
        paths.append(path_d)
    return paths

# Build SVG
svg_parts = []
svg_parts.append(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" width="100%" height="100%">')

# Layer 1: Other provinces (dark, background)
svg_parts.append('  <g fill="#0a3622" stroke="none" opacity="0.4">')
for prov_name in mainland_provinces:
    if prov_name != 'Sumatera Utara' and prov_name in all_provinces:
        paths = geojson_to_paths(all_provinces[prov_name])
        for p in paths:
            svg_parts.append(f'    <path d="{p}" />')
svg_parts.append('  </g>')

# Layer 2: Sumatera Utara (bright, highlighted)
svg_parts.append('  <g fill="#064e3b" stroke="none">')
if 'Sumatera Utara' in all_provinces:
    paths = geojson_to_paths(all_provinces['Sumatera Utara'])
    for p in paths:
        svg_parts.append(f'    <path d="{p}" />')
svg_parts.append('  </g>')

# Layer 3: Border highlight for Sumut
svg_parts.append('  <g fill="none" stroke="#5b9e32" stroke-width="1.5" opacity="0.7">')
if 'Sumatera Utara' in all_provinces:
    paths = geojson_to_paths(all_provinces['Sumatera Utara'])
    for p in paths:
        svg_parts.append(f'    <path d="{p}" />')
svg_parts.append('  </g>')

svg_parts.append('</svg>')

svg_content = '\n'.join(svg_parts)

with open('d:/pta/assets/peta_sumut.svg', 'w', encoding='utf-8') as f:
    f.write(svg_content)

print(f"\nSaved SVG!")
print(f"Bounding box for PHP/Python markers:")
print(f"  LAT_TOP = {max_lat}")
print(f"  LAT_BOTTOM = {min_lat}")
print(f"  LNG_LEFT = {min_lon}")
print(f"  LNG_RIGHT = {max_lon}")
print(f"  Aspect ratio: {W} / {H}")
