import requests
from bs4 import BeautifulSoup
import sys

login_url = "https://semar.mahkamahagung.go.id/login"
target_url = "https://semar.mahkamahagung.go.id/detailEvaluasi/R2FzVlp6UlE5M0x3VFhhaVBBZ0NnUEZZeXVsSDhFa0RWK0dMTlBubGxSclZuYy95NitXbTZ0SjJGdzVuczlNWXVZNXNjdWROZVVMdW5JYlBvNFlMVnc9PQ=="

session = requests.Session()

# 1. Get the login page to fetch any CSRF tokens
print("Fetching login page...")
try:
    response = session.get(login_url, headers={'User-Agent': 'Mozilla/5.0'})
    soup = BeautifulSoup(response.text, 'html.parser')
except Exception as e:
    print(f"Error fetching login page: {e}")
    sys.exit(1)

# Find form inputs
payload = {
    'username': 'smr401777',
    'password': 'ptamedan'
}

# Look for csrf or hidden tokens
for input_tag in soup.find_all('input', type='hidden'):
    if input_tag.get('name'):
        payload[input_tag.get('name')] = input_tag.get('value', '')

print(f"Login payload prepared: {payload.keys()}")

# 2. Post login
print("Attempting login...")
try:
    login_response = session.post(login_url, data=payload, headers={'User-Agent': 'Mozilla/5.0'})
    print(f"Login response URL: {login_response.url}")
except Exception as e:
    print(f"Error during login: {e}")
    sys.exit(1)

# 3. Fetch target page
print(f"Fetching target URL: {target_url}")
try:
    target_response = session.get(target_url, headers={'User-Agent': 'Mozilla/5.0'})
    if target_response.status_code == 200:
        print("Successfully fetched target page!")
        # Save to file
        with open("d:\\pta\\semar_page.html", "w", encoding="utf-8") as f:
            f.write(target_response.text)
        print("Page HTML saved to d:\\pta\\semar_page.html")
    else:
        print(f"Failed to fetch target. Status code: {target_response.status_code}")
except Exception as e:
    print(f"Error fetching target page: {e}")
