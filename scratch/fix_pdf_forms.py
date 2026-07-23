import os

for file in ['pages/renaksi.php', 'pages/pk.php', 'pages/rkt_rka.php']:
    with open(file, 'r', encoding='utf-8') as f:
        c = f.read()
    
    # Replace render_header check
    c = c.replace('if (!$isDocx) {', 'if (!$isExport) {')
    
    # Replace template/form checks
    c = c.replace('<?php if (!$isDocx): ?>', '<?php if (!$isExport): ?>')
    
    with open(file, 'w', encoding='utf-8') as f:
        f.write(c)

print('Done fixing isDocx to isExport!')
