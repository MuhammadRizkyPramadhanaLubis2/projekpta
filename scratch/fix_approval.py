import os

file_path = 'pages/renaksi.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

find_approval = """    <div class="renaksi-approval">
        <div><?= h((string) ($meta['lokasi'] ?: 'Medan')) ?>, <?= h($displayDate) ?></div>
        <div><?= h($approvalRole ?: 'Pimpinan') ?></div>
        <div class="signature-space">
            <?php if ($approvalSignature !== ''): ?>
                <?= get_signature_img_tag($approvalSignature, 150, 80, isset($isDocx) ? $isDocx : false) ?>
            <?php endif; ?>
        </div>
        <div class="name"><?= h($approvalName ?: '-') ?></div>
    </div>"""

replace_approval = """    <div class="renaksi-approval">
        <div><?= h((string) ($meta['lokasi'] ?: 'Medan')) ?>, <?= h($displayDate) ?></div>
        <div><?= h($approvalRole ?: 'Pimpinan') ?></div>
        <div class="signature-space">
            <?php if ($approvalSignature !== ''): ?>
                <?= get_signature_img_tag($approvalSignature, 150, 80, isset($isDocx) ? $isDocx : false) ?>
            <?php else: ?>
                <br><br><br><br>
            <?php endif; ?>
        </div>
        <div class="name"><?= h($approvalName ?: '-') ?></div>
    </div>"""

content = content.replace(find_approval, replace_approval)

# Fix CSS for word export
find_css = '.renaksi-approval { text-align: center; margin-left: 60%; margin-top: 30px; font-size: 11pt; }'
replace_css = '.renaksi-approval { text-align: left; margin-top: 30px; font-size: 11pt; }'
content = content.replace(find_css, replace_css)

# Fix CSS for web preview
find_web_css = """.renaksi-approval {
    width: 310px;
    margin: 22px 20px 0 auto;
    text-align: center;"""
replace_web_css = """.renaksi-approval {
    width: 310px;
    margin: 22px 0 0 20px;
    text-align: left;"""
content = content.replace(find_web_css, replace_web_css)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')
