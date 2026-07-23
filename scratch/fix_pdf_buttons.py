import os

for file in ['pages/renaksi.php', 'pages/pk.php', 'pages/rkt_rka.php']:
    if not os.path.exists(file):
        continue
    with open(file, 'r', encoding='utf-8') as f:
        c = f.read()
    
    if file == 'pages/renaksi.php':
        c = c.replace('<button type="button" onclick="window.print()">Cetak PDF</button>', 
                      '<a href="index.php?page=renaksi&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Cetak PDF</a>')
    elif file == 'pages/pk.php':
        c = c.replace('<button type="button" class="button" onclick="window.print()"><i class="ph ph-printer"></i> Cetak PDF</button>', 
                      '<a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button"><i class="ph ph-printer"></i> Cetak PDF</a>')
    elif file == 'pages/rkt_rka.php':
        c = c.replace('<button type="button" onclick="window.print()">Cetak PDF</button>', 
                      '<a href="index.php?page=rkt_rka&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Cetak PDF</a>')
    
    with open(file, 'w', encoding='utf-8') as f:
        f.write(c)

print('Done replacing buttons!')
