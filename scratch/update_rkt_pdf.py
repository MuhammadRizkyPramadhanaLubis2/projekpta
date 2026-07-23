import os
import re

file_path = 'pages/rkt_rka.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add isPdf detection
content = content.replace('$isDocx = ($_GET[\'export\'] ?? \'\') === \'doc\';', 
                          '$isDocx = ($_GET[\'export\'] ?? \'\') === \'doc\';\n$isPdf = ($_GET[\'export\'] ?? \'\') === \'pdf\';\n$isExport = $isDocx || $isPdf;')

# Change render_header condition
content = content.replace('if (!$isDocx) {\n    render_header', 
                          'if (!$isExport) {\n    render_header')

# Change Cetak PDF button
content = re.sub(r'<button type="button" onclick="window\.print\(\)">Cetak PDF</button>', 
                 r'<a href="index.php?page=rkt_rka&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Cetak PDF</a>', 
                 content)

# Change <?php else: ?> to <?php elseif ($isExport): ?> block
content = content.replace('<?php else: ?>\n<html>\n<head>', 
                          '<?php elseif ($isExport): ?>\n<html>\n<head>')

content = content.replace('</style>\n</head>', 
                          '@page { margin: 20mm; }\n</style>\n</head>')

# Change footer condition
find_footer = '''<?php if ($isDocx): ?>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>'''

replace_footer = '''<?php if ($isExport): ?>
    <?php if ($isPdf): ?>
        <script>window.onload = function() { window.print(); };</script>
    <?php endif; ?>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>'''

content = content.replace(find_footer, replace_footer)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done rkt_rka!')
