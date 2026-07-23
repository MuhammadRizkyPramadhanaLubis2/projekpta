import os
import re

file_path = 'pages/renaksi.php'
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
                 r'<a href="index.php?page=renaksi&tahun=<?= $tahun ?>&user_id=<?= $selectedUserId ?>&export=pdf" target="_blank" class="button" style="background:#475569; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;">Cetak PDF</a>', 
                 content)

# Change <?php else: ?> to <?php elseif ($isExport): ?> block
content = content.replace('<?php else: ?>\n<html xmlns:o=', 
                          '<?php elseif ($isExport): ?>\n<html xmlns:o=')

# Add @page { size: landscape; }
content = content.replace('size: 841.9pt 595.3pt; /* A4 Landscape */', 
                          'size: 841.9pt 595.3pt; /* A4 Landscape */\n}\n@page { size: landscape; margin: 20mm; ')

# Change footer condition
find_footer = '''<?php if ($isDocx): ?>
</div>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>'''

replace_footer = '''<?php if ($isExport): ?>
    <?php if ($isDocx): ?></div><?php endif; ?>
    <?php if ($isPdf): ?>
        <script>window.onload = function() { window.print(); };</script>
    <?php endif; ?>
</body>
</html>
<?php else: ?>
<?php render_footer(); ?>
<?php endif; ?>'''

content = content.replace(find_footer, replace_footer)

# Conditional WordSection1 wrap
content = content.replace('<div class="WordSection1">', '<?php if ($isDocx): ?><div class="WordSection1"><?php endif; ?>')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done renaksi!')
