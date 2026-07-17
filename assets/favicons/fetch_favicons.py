import urllib.request
import re
import os
import sys
from urllib.parse import urljoin, urlparse

HEADERS = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36"}
OUT_DIR = os.path.dirname(os.path.abspath(__file__))

SITES = [
    ("bappenas",      "https://www.bappenas.go.id"),
    ("kpk",           "https://www.kpk.go.id"),
    ("mahkamahagung", "https://mahkamahagung.go.id"),
    ("bua",           "https://bua.mahkamahagung.go.id"),
    ("badilag",       "https://badilag.mahkamahagung.go.id"),
    ("bawas",         "https://bawas.mahkamahagung.go.id"),
]

def get_url(url, timeout=12):
    req = urllib.request.Request(url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=timeout) as r:
        return r.read()

def find_favicon_url(base_url, html):
    text = html.decode("utf-8", errors="replace")
    patterns = [
        r'<link[^>]+rel=["\'](?:shortcut icon|icon)["\'][^>]+href=["\']([^"\']+)["\']',
        r'<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\'](?:shortcut icon|icon)["\']',
        r'<link[^>]+rel=["\']apple-touch-icon["\'][^>]+href=["\']([^"\']+)["\']',
        r'<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\']apple-touch-icon["\']',
    ]
    for pat in patterns:
        m = re.search(pat, text, re.IGNORECASE)
        if m:
            return urljoin(base_url, m.group(1))
    # fallback: /favicon.ico
    p = urlparse(base_url)
    return f"{p.scheme}://{p.netloc}/favicon.ico"

def ext_from_url(url):
    path = urlparse(url).path
    ext = os.path.splitext(path)[1]
    return ext if ext in (".ico", ".png", ".jpg", ".svg", ".gif", ".webp") else ".ico"

for name, base_url in SITES:
    print(f"\n[{name}] Fetching {base_url} ...")
    try:
        html = get_url(base_url)
        fav_url = find_favicon_url(base_url, html)
        print(f"  Favicon URL: {fav_url}")
        data = get_url(fav_url)
        ext = ext_from_url(fav_url)
        out_path = os.path.join(OUT_DIR, name + ext)
        with open(out_path, "wb") as f:
            f.write(data)
        print(f"  Saved: {out_path} ({len(data)} bytes)")
    except Exception as e:
        print(f"  ERROR: {e}")
