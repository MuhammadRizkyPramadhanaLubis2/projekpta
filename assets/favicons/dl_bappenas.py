import urllib.request

url = "https://www.bappenas.go.id/favicon.ico"
req = urllib.request.Request(url, headers={
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36",
    "Accept": "image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8",
    "Accept-Language": "id-ID,id;q=0.9,en-US;q=0.8",
    "Referer": "https://www.bappenas.go.id/",
    "Sec-Fetch-Dest": "image",
    "Sec-Fetch-Mode": "no-cors",
    "Sec-Fetch-Site": "same-origin",
})
with urllib.request.urlopen(req, timeout=15) as r:
    data = r.read()
out = r"d:\pta\assets\favicons\bappenas.ico"
with open(out, "wb") as f:
    f.write(data)
print(f"OK: {len(data)} bytes -> {out}")
