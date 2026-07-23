import os
import re

file_path = 'pages/pk.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add isPdf detection
content = content.replace('$isDocx = ($_GET[\'export\'] ?? \'\') === \'doc\';', 
                          '$isDocx = ($_GET[\'export\'] ?? \'\') === \'doc\';\n$isPdf = ($_GET[\'export\'] ?? \'\') === \'pdf\';\n$isExport = $isDocx || $isPdf;')

# Change render_header condition
content = content.replace('if (!$isDocx) {\n    render_header', 
                          'if (!$isExport) {\n    render_header')

# Change Cetak PDF button
content = re.sub(r'<button type="button" class="button" onclick="window\.print\(\)"><i class="ph ph-printer"></i> Cetak PDF</button>', 
                 r'<a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button"><i class="ph ph-printer"></i> Cetak PDF</a>', 
                 content)
content = re.sub(r'<button type="button" onclick="window\.print\(\)">Cetak PDF</button>', 
                 r'<a href="index.php?page=pk&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button"><i class="ph ph-printer"></i> Cetak PDF</a>', 
                 content)
# Actually, pk.php has: <button type="button" class="button" onclick="window.print()"><i class="ph ph-printer"></i> Cetak PDF</button>

# Change <?php else: ?> to <?php elseif ($isExport): ?> block
content = content.replace('<?php else: ?>\n<html>\n<head>', 
                          '<?php elseif ($isExport): ?>\n<html>\n<head>')
# Wait, pk.php doesn't have WordSection1, so no landscape needed?
# Wait, is pk.php landscape or portrait? In Word, it uses `perjanjian_kinerja.php` which is just HTML. It's usually portrait.
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
print('Done pk!')
