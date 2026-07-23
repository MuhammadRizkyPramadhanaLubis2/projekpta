import os

files = ['pages/renaksi.php', 'pages/pk.php', 'pages/rkt_rka.php']
for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        c = f.read()
    
    if '$isPdf' not in c:
        find = "$isDocx = ($_GET['export'] ?? '') === 'doc';"
        replace = "$isDocx = ($_GET['export'] ?? '') === 'doc';\n$isPdf = ($_GET['export'] ?? '') === 'pdf';\n$isExport = $isDocx || $isPdf;"
        c = c.replace(find, replace)
        
        with open(file, 'w', encoding='utf-8') as f:
            f.write(c)

print('Done fixing!')
