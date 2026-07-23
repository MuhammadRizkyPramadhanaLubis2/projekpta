import os
import re

for file in ['pages/renaksi.php', 'pages/pk.php', 'pages/rkt_rka.php']:
    if not os.path.exists(file):
        continue
    with open(file, 'r', encoding='utf-8') as f:
        c = f.read()
    
    if file == 'pages/renaksi.php':
        c = c.replace('<a href="index.php?page=renaksi&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Cetak PDF</a>', 
                      '<button type="button" onclick="printPdfSilent(\'index.php?page=renaksi&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf\')" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; border:none; cursor:pointer;">Cetak PDF</button>')
    elif file == 'pages/pk.php':
        c = c.replace('<a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button"><i class="ph ph-printer"></i> Cetak PDF</a>', 
                      '<button type="button" onclick="printPdfSilent(\'index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf\')" class="button" style="border:none; cursor:pointer;"><i class="ph ph-printer"></i> Cetak PDF</button>')
    elif file == 'pages/rkt_rka.php':
        c = c.replace('<a href="index.php?page=rkt_rka&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Cetak PDF</a>', 
                      '<button type="button" onclick="printPdfSilent(\'index.php?page=rkt_rka&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf\')" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none; border:none; cursor:pointer;">Cetak PDF</button>')
    
    with open(file, 'w', encoding='utf-8') as f:
        f.write(c)

# Now inject the javascript into footer.php
footer_path = 'app/views/footer.php'
if os.path.exists(footer_path):
    with open(footer_path, 'r', encoding='utf-8') as f:
        fc = f.read()
    
    if 'function printPdfSilent' not in fc:
        script = """
<script>
function printPdfSilent(url) {
    let iframe = document.getElementById('print-iframe-hidden');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'print-iframe-hidden';
        iframe.style.visibility = 'hidden';
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }
    iframe.src = url;
}
</script>
</body>
</html>
"""
        fc = fc.replace('</body>\n</html>', script.strip() + '\n')
        with open(footer_path, 'w', encoding='utf-8') as f:
            f.write(fc)

print('Done fixing buttons for silent PDF print!')
