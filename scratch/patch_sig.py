import os

helpers_path = 'app/helpers.php'
with open(helpers_path, 'r', encoding='utf-8') as f:
    helpers_content = f.read()

find_sig = "function get_signature_img_tag(string $base64Data, int $maxWidth = 150, int $maxHeight = 80): string"
replace_sig = "function get_signature_img_tag(string $base64Data, int $maxWidth = 150, int $maxHeight = 80, bool $forceFileUrl = false): string"
helpers_content = helpers_content.replace(find_sig, replace_sig)

find_return = """        // Return tag with explicit width and height
        return sprintf(
            '<img src="%s" width="%d" height="%d" style="%s">',
            h($base64Data),
            $newWidth,
            $newHeight,
            $style
        );"""

replace_return = """        $src = $base64Data;
        if ($forceFileUrl) {
            $tempDir = __DIR__ . '/../assets/temp_signatures';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            $filename = md5($base64Data) . '.jpg';
            $filePath = $tempDir . '/' . $filename;
            if (!file_exists($filePath)) {
                file_put_contents($filePath, $imgData);
            }
            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $src = "$scheme://$host/projekpta-1/assets/temp_signatures/$filename";
        }

        // Return tag with explicit width and height
        return sprintf(
            '<img src="%s" width="%d" height="%d" style="%s">',
            h($src),
            $newWidth,
            $newHeight,
            $style
        );"""

helpers_content = helpers_content.replace(find_return, replace_return)

with open(helpers_path, 'w', encoding='utf-8') as f:
    f.write(helpers_content)

renaksi_path = 'pages/renaksi.php'
with open(renaksi_path, 'r', encoding='utf-8') as f:
    renaksi_content = f.read()

find_renaksi_sig = "<?= get_signature_img_tag($approvalSignature) ?>"
replace_renaksi_sig = "<?= get_signature_img_tag($approvalSignature, 150, 80, isset($isDocx) ? $isDocx : false) ?>"
renaksi_content = renaksi_content.replace(find_renaksi_sig, replace_renaksi_sig)

with open(renaksi_path, 'w', encoding='utf-8') as f:
    f.write(renaksi_content)

print("Done patching helpers and renaksi.")
