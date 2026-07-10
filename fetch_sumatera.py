import urllib.request
import json
import time

# Daftar provinsi di Pulau Sumatera
provinces = [
    'Aceh',
    'Sumatera Utara',
    'Sumatera Barat',
    'Riau',
    'Jambi',
    'Sumatera Selatan',
    'Bengkulu',
    'Lampung',
    'Kepulauan Bangka Belitung',
    'Kepulauan Riau',
]

all_geojson = {}

for prov in provinces:
    query = prov.replace(' ', '+')
    url = f'https://nominatim.openstreetmap.org/search.php?q={query}+Indonesia&polygon_geojson=1&polygon_threshold=0.01&format=json'
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
    try:
        with urllib.request.urlopen(req) as response:
            data = json.loads(response.read().decode('utf-8'))
            if data:
                # Find the administrative boundary result
                for item in data:
                    if item.get('class') == 'boundary' or item.get('type') == 'administrative':
                        all_geojson[prov] = item['geojson']
                        print(f"OK: {prov} -> {item['geojson']['type']}")
                        break
                else:
                    # Fallback to first result
                    all_geojson[prov] = data[0]['geojson']
                    print(f"OK (fallback): {prov} -> {data[0]['geojson']['type']}")
    except Exception as e:
        print(f"FAIL: {prov} -> {e}")
    time.sleep(1.2)  # Rate limiting

with open('d:/pta/sumatera_all.json', 'w') as f:
    json.dump(all_geojson, f)

print(f"\nTotal provinces downloaded: {len(all_geojson)}")
