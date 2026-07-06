<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$page = $_GET['page'] ?? 'beranda';
$allowedPages = ['beranda', 'portal', 'login', 'logout', 'dashboard', 'users', 'monitoring', 'target', 'capaian', 'evaluasi', 'pk', 'renaksi', 'rkt_rka', 'modul', 'info'];

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    $page = 'info';
    $_GET['title'] = 'Halaman tidak ditemukan';
}

if (!in_array($page, ['beranda', 'portal', 'login', 'logout'], true)) {
    require_login();
}

require __DIR__ . "/pages/{$page}.php";
