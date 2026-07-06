<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $page): never
{
    header('Location: index.php?page=' . urlencode($page));
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('login');
    }
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function num(mixed $value): float
{
    if (is_numeric($value)) {
        return (float) $value;
    }
    return 0.0;
}

function year_value(): int
{
    $year = (int) ($_GET['tahun'] ?? $_POST['tahun'] ?? date('Y'));
    return max(2020, min(2100, $year));
}

function role_tasks(string $role): array
{
    $tasks = [
        'PanmudBanding' => [
            'Memastikan kesesuaian perkara banding yang diselesaikan tepat waktu berdasarkan SIPP dan register perkara.',
            'Memastikan pengiriman salinan putusan tingkat banding tepat waktu kepada pengadilan pengaju.',
            'Menganalisis capaian kinerja perkara tepat waktu dengan narasi keberhasilan atau ketidakberhasilan.',
        ],
        'PanmudHukum' => [
            'Memastikan laporan jumlah putusan yang diunggah ke Direktori Putusan sudah sesuai aturan minutasi.',
            'Memastikan laporan perkara banding yang menggunakan dan tidak menggunakan E-Court.',
            'Menganalisis capaian kinerja unggah putusan dengan narasi pada kertas kerja aplikasi.',
        ],
        'KasubagTURT' => [
            'Memastikan layanan sarana dan prasarana pengadilan melalui data Survei Badilag.',
            'Memastikan laporan persentase indeks kepuasan masyarakat atas layanan pengadilan.',
            'Menganalisis capaian kinerja survei kepuasan masyarakat.',
        ],
        'Kepegawaian' => [
            'Memonitor Indeks Profesional ASN yang terintegrasi dengan My ASN dan SIKEP.',
            'Menganalisis capaian Indeks Profesional ASN PTA Medan.',
        ],
        'Keuangan' => [
            'Memonitor nilai IKPA melalui SAKTI, OMSPAN Kemenkeu, dan E-Bima MARI.',
            'Memonitor nilai IPA melalui SAKTI dan E-SADEWA MARI.',
            'Menganalisis capaian IKPA dan IPA.',
        ],
        'Perencanaan' => [
            'Memonitor nilai kinerja perencanaan anggaran melalui SAKTI, OMSPAN Kemenkeu, dan E-Bima MARI.',
            'Menganalisis nilai kinerja perencanaan anggaran.',
            'Memonitor, mengedit, dan mencetak data capaian seluruh pengguna aplikasi setiap triwulan.',
        ],
        'SatkerPanmudHukum' => [
            'Mengelola data hukum pada satuan kerja PA.',
            'Menyiapkan bahan evaluasi dan laporan hukum untuk korelasi PTA Medan.',
        ],
        'SatkerKasubagPTIP' => [
            'Mengelola dukungan PTIP pada satuan kerja PA.',
            'Menyiapkan data dukung aplikasi dan pelaporan satuan kerja.',
        ],
    ];

    return $tasks[$role] ?? ['Mengelola data dan capaian kinerja sesuai kewenangan pengguna.'];
}

function render_header(string $title): void
{
    $user = current_user();
    $flash = flash();
    require __DIR__ . '/views/header.php';
}

function render_footer(): void
{
    require __DIR__ . '/views/footer.php';
}
