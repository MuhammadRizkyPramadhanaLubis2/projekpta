import os

file_path = 'pages/renaksi.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

find_approval = """    <table class="renaksi-approval" border="0" align="right" style="width: 310px; margin-top: 22px; margin-right: 20px;">
        <tr><td style="text-align: center; border: none; padding: 0;">
            <div><?= h((string) ($meta['lokasi'] ?: 'Medan')) ?>, <?= h($displayDate) ?></div>
            <div><?= h($approvalRole ?: 'Pimpinan') ?></div>
            <div class="signature-space">
                <?php if ($approvalSignature !== ''): ?>
                    <?= get_signature_img_tag($approvalSignature, 150, 80, isset($isDocx) ? $isDocx : false) ?>
                <?php else: ?>
                    <br><br><br><br>
                <?php endif; ?>
            </div>
            <div class="name" style="font-weight: bold; text-decoration: underline;"><?= h($approvalName ?: '-') ?></div>
        </td></tr>
    </table>"""

replace_approval = """    <table border="0" width="100%" style="margin-top: 30px; page-break-inside: avoid;">
        <tr>
            <td width="65%" style="border: none;"></td>
            <td width="35%" style="border: none; text-align: center; padding: 0;">
                <div><?= h((string) ($meta['lokasi'] ?: 'Medan')) ?>, <?= h($displayDate) ?></div>
                <div><?= h($approvalRole ?: 'Pimpinan') ?></div>
                <div class="signature-space">
                    <?php if ($approvalSignature !== ''): ?>
                        <?= get_signature_img_tag($approvalSignature, 150, 80, isset($isDocx) ? $isDocx : false) ?>
                    <?php else: ?>
                        <br><br><br><br>
                    <?php endif; ?>
                </div>
                <div style="font-weight: bold; text-decoration: underline;"><?= h($approvalName ?: '-') ?></div>
            </td>
        </tr>
    </table>"""

content = content.replace(find_approval, replace_approval)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')
