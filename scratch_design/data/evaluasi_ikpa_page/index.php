<?php
declare(strict_types=1);

$page = $_GET['page'] ?? 'evaluasi-akip-binjai';
$allowedPages = ['evaluasi-akip-binjai'];

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    $page = 'evaluasi-akip-binjai';
}

require __DIR__ . '/pages/' . $page . '.php';
