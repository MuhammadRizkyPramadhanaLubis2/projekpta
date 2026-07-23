import os

file_path = 'pages/renaksi.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

find_html = """<?php else: ?>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: "Times New Roman", Times, serif; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #000; padding: 5px; text-align: left; }
</style>
</head>
<body>
<?php endif; ?>"""

replace_html = """<?php else: ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
@page WordSection1 {
    size: 841.9pt 595.3pt; /* A4 Landscape */
    mso-page-orientation: landscape;
    margin: 36.0pt 36.0pt 36.0pt 36.0pt;
}
div.WordSection1 { page: WordSection1; }
body { font-family: "Times New Roman", Times, serif; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: top; }
th { background-color: #f6bc8b; font-weight: bold; text-align: center; vertical-align: middle; }
.center { text-align: center; }
.number { text-align: right; }
.strong { font-weight: bold; }
.indicator-band td { background-color: #fce8d7; font-weight: bold; }
.renaksi-title { text-align: center; font-size: 14pt; font-weight: bold; }
.renaksi-subtitle { text-align: center; font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; }
.renaksi-approval { text-align: center; margin-left: 60%; margin-top: 30px; font-size: 11pt; }
</style>
</head>
<body>
<div class="WordSection1">
<?php endif; ?>"""

content = content.replace(find_html, replace_html)

find_end = """<?php if ($isDocx): ?>
</body>
</html>"""

replace_end = """<?php if ($isDocx): ?>
</div>
</body>
</html>"""

content = content.replace(find_end, replace_end)

# Also add border="1" to tables to ensure Word draws borders properly even if CSS fails
content = content.replace('<table class="renaksi-table renaksi-summary">', '<table class="renaksi-table renaksi-summary" border="1">')
content = content.replace('<table class="renaksi-table renaksi-activities">', '<table class="renaksi-table renaksi-activities" border="1">')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Done!")
