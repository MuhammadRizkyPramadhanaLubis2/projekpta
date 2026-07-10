import urllib.request
import json
url = 'https://nominatim.openstreetmap.org/search.php?q=Sumatera+Utara&polygon_geojson=1&polygon_threshold=0.005&format=json'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
try:
    with urllib.request.urlopen(req) as response:
        data = json.loads(response.read().decode('utf-8'))
        for idx, item in enumerate(data):
            print(f"Result {idx}: {item['osm_type']} {item['osm_id']}, class: {item['class']}, type: {item['type']}, geom: {item.get('geojson',{}).get('type')}")
            if item.get('geojson',{}).get('type') == 'MultiPolygon':
                with open('d:/pta/sumut_multipolygon.geojson', 'w') as f:
                    json.dump(item['geojson'], f)
                print('Saved MultiPolygon!')
                break
except Exception as e:
    print('Failed:', e)
