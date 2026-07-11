import json
import urllib.request
import math

# Bounding box as specified
LAT_TOP = 4.4
LAT_BOTTOM = -0.2
LNG_LEFT = 96.9
LNG_RIGHT = 100.6

# We want to output an SVG.
# Let's define the SVG dimensions.
WIDTH = 800
HEIGHT = int(WIDTH * ((LAT_TOP - LAT_BOTTOM) / (LNG_RIGHT - LNG_LEFT)))

def lonlat_to_xy(lon, lat):
    # Map lon to 0..WIDTH
    x = ((lon - LNG_LEFT) / (LNG_RIGHT - LNG_LEFT)) * WIDTH
    # Map lat to 0..HEIGHT (Note: lat is flipped, higher lat = smaller y)
    y = ((LAT_TOP - lat) / (LAT_TOP - LAT_BOTTOM)) * HEIGHT
    return x, y

url = "https://raw.githubusercontent.com/superpikar/indonesia-geojson/master/indonesia-prov.geojson"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode('utf-8'))
except Exception as e:
    print("Failed to download GeoJSON:", e)
    exit(1)

svg_paths = []

for feature in data.get('features', []):
    prop = feature.get('properties', {})
    name = prop.get('Propinsi', '')
    if name.upper() == 'SUMATERA UTARA':
        geom = feature.get('geometry', {})
        geom_type = geom.get('type')
        coords = geom.get('coordinates', [])
        
        polygons = []
        if geom_type == 'Polygon':
            polygons = [coords]
        elif geom_type == 'MultiPolygon':
            polygons = coords
            
        for polygon in polygons:
            # polygon[0] is the exterior ring
            ring = polygon[0]
            path_d = ""
            for i, (lon, lat) in enumerate(ring):
                x, y = lonlat_to_xy(lon, lat)
                if i == 0:
                    path_d += f"M {x:.2f} {y:.2f} "
                else:
                    path_d += f"L {x:.2f} {y:.2f} "
            path_d += "Z"
            svg_paths.append(path_d)

if not svg_paths:
    print("Could not find SUMATERA UTARA in GeoJSON")
    exit(1)

svg_content = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {WIDTH} {HEIGHT}" width="100%" height="100%">
  <!-- Background is transparent or we can leave it empty to inherit CSS -->
  <g fill="#064e3b" stroke="rgba(255,255,255,0.1)" stroke-width="0.5">
"""

for d in svg_paths:
    svg_content += f'    <path d="{d}" />\n'

svg_content += """  </g>
</svg>
"""

with open("d:/pta/assets/peta_sumut.svg", "w", encoding="utf-8") as f:
    f.write(svg_content)

print("SVG Generated successfully.")
