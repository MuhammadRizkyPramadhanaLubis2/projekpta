import os

with open('pages/rkt_rka.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, line in enumerate(lines):
    if 'class="panel' in line or '<form' in line or 'print-meta-form' in line:
        for j in range(max(0, i-2), min(len(lines), i+15)):
            print(f'{j+1}: {lines[j].strip()}')
        break
