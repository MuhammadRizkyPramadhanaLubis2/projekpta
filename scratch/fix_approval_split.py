import os

file_path = 'pages/renaksi.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

find_approval = """    <table border="0" width="100%" style="margin-top: 30px; page-break-inside: avoid;">
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

replace_approval = """    <table border="0" width="100%" style="margin-top: 30px; page-break-inside: avoid;">
        <tr style="page-break-inside: avoid;">
            <td width="65%" style="border: none;"></td>
            <td width="35%" style="border: none; text-align: center; padding: 0;">
                <p style="margin: 0; padding: 0; page-break-after: avoid;">
                    <?= h((string) ($meta['lokasi'] ?: 'Medan')) ?>, <?= h($displayDate) ?><br>
                    <?= h($approvalRole ?: 'Pimpinan') ?>
                </p>
                <table border="0" cellspacing="0" cellpadding="0" width="100%" style="page-break-inside: avoid; page-break-before: avoid; page-break-after: avoid;">
                    <tr height="90">
                        <td align="center" valign="middle" style="border: none; padding: 0;">
                            <?php if ($approvalSignature !== ''): ?>
                                <?= get_signature_img_tag($approvalSignature, 150, 80, isset($isDocx) ? $isDocx : false) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <p style="margin: 0; padding: 0; font-weight: bold; text-decoration: underline; page-break-before: avoid;">
                    <?= h($approvalName ?: '-') ?>
                </p>
            </td>
        </tr>
    </table>"""

content = content.replace(find_approval, replace_approval)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')
