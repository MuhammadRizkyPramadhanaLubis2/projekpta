import urllib.request
import re
from bs4 import BeautifulSoup

url = "https://semar.mahkamahagung.go.id/detailEvaluasi/R2FzVlp6UlE5M0x3VFhhaVBBZ0NnUEZZeXVsSDhFa0RWK0dMTlBubGxSclZuYy95NitXbTZ0SjJGdzVuczlNWXVZNXNjdWROZVVMdW5JYlBvNFlMVnc9PQ=="

try:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req) as response:
        html = response.read().decode('utf-8')
        
    soup = BeautifulSoup(html, 'html.parser')
    
    print("Title:", soup.title.string if soup.title else "No Title")
    
    # Let's find tables, forms, inputs, and general layout
    tables = soup.find_all('table')
    print(f"\nFound {len(tables)} tables.")
    
    for i, t in enumerate(tables):
        headers = [th.get_text(strip=True) for th in t.find_all('th')]
        print(f"Table {i+1} headers: {headers}")
        
        # print first row data structure
        first_row = t.find('tr', recursive=False)
        if not first_row and t.find('tbody'):
            first_row = t.find('tbody').find('tr')
            
        if first_row:
            inputs = first_row.find_all(['input', 'select', 'textarea'])
            if inputs:
                print(f"  First row has inputs: {[inp.name + (' (' + inp.get('type', '') + ')' if inp.name == 'input' else '') for inp in inputs]}")
                
    # Check for specific accordion/panel structures
    panels = soup.find_all(class_=re.compile(r'(panel|card|accordion)'))
    print(f"\nFound {len(panels)} panels/cards.")
    for p in panels[:3]:
        heading = p.find(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', '.card-header', '.panel-heading'])
        if heading:
            print(f"  Panel heading: {heading.get_text(strip=True)[:100]}")
            
except Exception as e:
    print(f"Error fetching: {e}")
