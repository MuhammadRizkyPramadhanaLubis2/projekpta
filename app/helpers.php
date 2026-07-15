<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function get_signature_img_tag(string $base64Data, int $maxWidth = 150, int $maxHeight = 80): string
{
    if (empty($base64Data)) {
        return '';
    }
    
    // Fallback default style
    $style = "display: block; margin: 10px auto; max-width: 100%; height: auto;";
    
    // Try to get intrinsic size from base64 string
    $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Data));
    if ($imgData !== false) {
        $size = @getimagesizefromstring($imgData);
        if ($size !== false) {
            $width = $size[0];
            $height = $size[1];
            
            // Calculate proportional dimensions
            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int) round($width * $ratio);
                $newHeight = (int) round($height * $ratio);
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }
            
            // Return tag with explicit width and height
            return sprintf(
                '<img src="%s" width="%d" height="%d" style="%s">',
                h($base64Data),
                $newWidth,
                $newHeight,
                $style
            );
        }
    }
    
    // If parsing fails, just output it without width/height
    return sprintf('<img src="%s" style="%s">', h($base64Data), $style);
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

function target_for_quarter(array $target, int $quarter): float
{
    $total = 0.0;
    $months = [];
    if ($quarter === 1) $months = ['jan', 'feb', 'mar'];
    elseif ($quarter === 2) $months = ['apr', 'mei', 'jun'];
    elseif ($quarter === 3) $months = ['jul', 'agu', 'sep'];
    elseif ($quarter === 4) $months = ['okt', 'nov', 'des'];

    foreach ($months as $m) {
        $total += num($target['target_' . $m] ?? 0);
    }
    
    return $total;
}

function indicator_type_options(): array
{
    return [
        'max' => 'Semakin tinggi semakin baik',
        'min' => 'Semakin rendah semakin baik',
    ];
}

function indicator_type_label(string $type): string
{
    $options = indicator_type_options();
    return $options[$type] ?? $options['max'];
}

function achievement_value(float $target, float $realisasi, string $type): float
{
    if ($target <= 0 || $realisasi < 0) {
        return 0.0;
    }

    if ($type === 'min') {
        if ($realisasi <= 0) {
            return 100.0;
        }

        return round(($target / $realisasi) * 100, 2);
    }

    return round(($realisasi / $target) * 100, 2);
}

function target_for_month(array $row, int $month): float
{
    $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
    $m = $months[$month - 1] ?? 'jan';
    $monthTarget = num($row['target_' . $m] ?? 0);
    if ($monthTarget > 0) {
        return $monthTarget;
    }

    return round(num($row['target'] ?? 0) / 12, 2);
}

function achievement_for_month(array $row, int $month): float
{
    $month = max(1, min(12, $month));
    $months = ['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'];
    $m = $months[$month - 1] ?? 'jan';
    
    $target = target_for_month($row, $month);
    $realisasi = num($row['real_' . $m] ?? 0);

    return achievement_value($target, $realisasi, (string) ($row['tipe_indikator'] ?? 'max'));
}

function achievement_trend(array $row, int $month): array
{
    $month = max(1, min(12, $month));
    $current = achievement_for_month($row, $month);

    if ($month === 1) {
        return [
            'previous' => null,
            'current' => $current,
            'status' => 'baseline',
            'label' => 'Baseline Bulan 1',
            'jenis' => 'Baseline capaian awal',
            'required' => true,
        ];
    }

    $previous = achievement_for_month($row, $month - 1);
    if ($current > $previous) {
        $status = 'naik';
        $label = 'Capaian naik';
        $jenis = 'Keberhasilan (naik)';
    } elseif ($current < $previous) {
        $status = 'turun';
        $label = 'Capaian turun';
        $jenis = 'Ketidakberhasilan (turun)';
    } else {
        $status = 'tetap';
        $label = 'Capaian tetap';
        $jenis = 'Capaian tetap';
    }

    return [
        'previous' => $previous,
        'current' => $current,
        'status' => $status,
        'label' => $label,
        'jenis' => $jenis,
        'required' => true,
    ];
}

function year_value(): int
{
    $year = (int) ($_GET['tahun'] ?? $_POST['tahun'] ?? date('Y'));
    return max(2020, min(2100, $year));
}

function role_catalog(): array
{
    return [
        'Admin' => [
            'label' => 'Administrator',
            'unit_type' => 'PTA Medan',
            'permissions' => ['manage_users', 'view_all_targets', 'input_target', 'evaluate', 'print_documents', 'view_reports'],
        ],
        'PanmudBanding' => [
            'label' => 'Panmud Banding',
            'unit_type' => 'PTA Medan',
            'permissions' => ['input_target', 'evaluate', 'print_documents'],
        ],
        'PanmudHukum' => [
            'label' => 'Panmud Hukum',
            'unit_type' => 'PTA Medan',
            'permissions' => ['input_target', 'evaluate', 'print_documents'],
        ],
        'KasubagTURT' => [
            'label' => 'Kasubag Tata Usaha dan Rumah Tangga',
            'unit_type' => 'PTA Medan',
            'permissions' => ['input_target', 'evaluate', 'print_documents'],
        ],
        'Kepegawaian' => [
            'label' => 'Kasubag Kepegawaian dan TI',
            'unit_type' => 'PTA Medan',
            'permissions' => ['input_target', 'evaluate', 'print_documents'],
        ],
        'Keuangan' => [
            'label' => 'Kasubag Keuangan dan Pelaporan',
            'unit_type' => 'PTA Medan',
            'permissions' => ['input_target', 'evaluate', 'print_documents'],
        ],
        'Perencanaan' => [
            'label' => 'Kasubag Perencanaan Program dan Anggaran',
            'unit_type' => 'PTA Medan',
            'permissions' => ['manage_users', 'view_all_targets', 'input_target', 'evaluate', 'print_documents', 'view_reports'],
        ],
        'SatkerPanmudHukum' => [
            'label' => 'Panmud Hukum Satker PA',
            'unit_type' => 'Satker PA',
            'permissions' => ['input_target', 'evaluate', 'print_documents'],
        ],
        'SatkerKasubagPTIP' => [
            'label' => 'Kasubag PTIP Satker PA',
            'unit_type' => 'Satker PA',
            'permissions' => ['input_target', 'evaluate', 'print_documents'],
        ],
    ];
}


function format_user_label(?string $nama, ?string $role, bool $multiline = false): string {
    $namaStr = trim((string)$nama);
    $roleStr = trim(role_label((string)$role));
    
    if ($namaStr === '' && $roleStr === '') return '-';
    if ($namaStr === '') return h($roleStr);
    if ($roleStr === '') return h($namaStr);
    
    if (strcasecmp($namaStr, $roleStr) === 0) {
        return h($namaStr);
    }
    
    if ($multiline) {
        return h($namaStr) . '<br><small>' . h($roleStr) . '</small>';
    }
    return h($namaStr) . ' - ' . h($roleStr);
}

function role_label(string $role): string
{
    $roles = role_catalog();
    return $roles[$role]['label'] ?? $role;
}

function role_options(): array
{
    $options = [];
    foreach (role_catalog() as $role => $detail) {
        $options[$role] = $detail['label'];
    }

    return $options;
}

function user_can(string $permission, ?array $user = null): bool
{
    $user ??= current_user();
    if (!$user) {
        return false;
    }

    $roles = role_catalog();
    $role = (string) ($user['role'] ?? '');
    $permissions = $roles[$role]['permissions'] ?? [];

    return in_array($permission, $permissions, true);
}

function require_permission(string $permission): void
{
    if (!user_can($permission)) {
        http_response_code(403);
        render_header('Akses Ditolak');
        ?>
        <section class="panel">
            <h2>Akses Ditolak</h2>
            <p class="muted">Role Anda belum memiliki kewenangan untuk membuka fitur ini.</p>
            <a class="button secondary" href="index.php?page=dashboard">Kembali ke Menu</a>
        </section>
        <?php
        render_footer();
        exit;
    }
}

function can_manage_target_row(array $row, ?array $user = null): bool
{
    $user ??= current_user();
    if (!$user) {
        return false;
    }

    if (user_can('edit_all_targets', $user)) {
        return true;
    }

    return (int) ($row['user_id'] ?? 0) === (int) ($user['id'] ?? 0);
}

function get_all_role_profiles(): array
{
    return [
        'Admin' => [
            'title' => 'Administrator Aplikasi',
            'scope' => 'Pengelolaan teknis aplikasi dan seluruh data pengguna.',
            'checks' => [
                'Memastikan akun pengguna aktif sesuai jabatan dan unit kerja.',
                'Memastikan struktur role dan hak akses aplikasi berjalan sesuai konsep.',
                'Membantu pengelolaan data apabila diperlukan oleh Perencanaan.',
            ],
            'sources' => ['Database aplikasi IKPA', 'Daftar pengguna internal'],
            'outputs' => [
                'Akun pengguna siap digunakan.',
                'Hak akses pengguna sesuai jabatan.',
                'Data aplikasi dapat dipantau lintas user.',
            ],
            'analysis_rule' => 'Admin tidak menjadi penilai substantif, tetapi memastikan aplikasi siap dipakai untuk input, evaluasi, monitoring, dan cetak.',
            'workflows' => [
                ['Manajemen Pengguna', 'users', null, 'Tambah user, ubah role, aktif/nonaktifkan akun, dan reset password.'],
                ['Dashboard Monitoring', 'monitoring', null, 'Lihat status target, capaian, dan EvKin seluruh pengguna.'],
                ['Monitoring Target Semua User', 'target', null, 'Melihat dan memperbaiki target kinerja seluruh pengguna.'],
                ['Rekap Capaian Semua User', 'capaian', null, 'Melihat capaian triwulan seluruh pengguna.'],
                ['Riwayat Evaluasi Semua User', 'evaluasi', null, 'Melihat narasi evaluasi seluruh pengguna.'],
            ],
        ],
        'PanmudBanding' => [
            'title' => 'Kertas Kerja Panmud Banding',
            'scope' => 'Perkara banding dan salinan putusan tingkat banding.',
            'checks' => [
                'Memastikan jumlah perkara banding yang diselesaikan tepat waktu sesuai data SIPP dan register perkara banding.',
                'Memastikan jumlah perkara masuk seluruhnya sebagai pembanding capaian.',
                'Memastikan pengiriman salinan putusan banding tepat waktu kepada pengadilan pengaju secara elektronik.',
            ],
            'sources' => ['SIPP', 'Register perkara banding', 'Data upload salinan putusan pada SIPP'],
            'outputs' => [
                'Target dan realisasi perkara banding selesai tepat waktu.',
                'Target dan realisasi salinan putusan terkirim tepat waktu.',
                'Narasi keberhasilan atau ketidakberhasilan capaian perkara banding.',
            ],
            'analysis_rule' => 'Jika capaian perkara atau salinan putusan naik/turun per triwulan, Panmud Banding wajib memberi narasi alasan keberhasilan atau ketidakberhasilan.',
            'workflows' => [
                ['Input Target Perkara Banding', 'target', null, 'Isi sasaran, indikator, target, dan realisasi triwulan perkara banding.'],
                ['Hitung Capaian Perkara', 'capaian', null, 'Lihat persentase capaian perkara dan salinan putusan.'],
                ['Analisis EvKin Banding', 'evaluasi', null, 'Tulis narasi evaluasi berdasarkan capaian perkara banding.'],
                ['Sumber Data SIPP', 'modul', 'sipp', 'Catatan sumber data perkara dan salinan putusan.'],
            ],
        ],
        'PanmudHukum' => [
            'title' => 'Kertas Kerja Panmud Hukum PTA',
            'scope' => 'Direktori putusan, minutasi, dan perkara banding E-Court/non E-Court.',
            'checks' => [
                'Memastikan laporan jumlah putusan yang diunggah pada Direktori Putusan sesuai aturan dan telah minutasi lengkap.',
                'Memastikan jumlah perkara banding yang menggunakan E-Court.',
                'Memastikan jumlah perkara banding yang tidak menggunakan E-Court.',
            ],
            'sources' => ['Direktori Putusan', 'SIPP', 'Data perkara E-Court dan non E-Court'],
            'outputs' => [
                'Target dan realisasi unggah putusan.',
                'Target dan realisasi perkara banding E-Court/non E-Court.',
                'Narasi analisis capaian unggah putusan dan data perkara.',
            ],
            'analysis_rule' => 'Panmud Hukum wajib menjelaskan kesesuaian atau ketidaksesuaian jumlah putusan unggah dan data perkara E-Court/non E-Court.',
            'workflows' => [
                ['Input Target Hukum', 'target', null, 'Isi target unggah putusan dan indikator perkara hukum.'],
                ['Hitung Capaian Hukum', 'capaian', null, 'Lihat capaian unggah putusan dan indikator hukum.'],
                ['Analisis EvKin Hukum', 'evaluasi', null, 'Tulis narasi evaluasi capaian bidang hukum.'],
                ['Sumber Data SIPP', 'modul', 'sipp', 'Catatan sumber data perkara.'],
                ['Referensi E-SEMAR', 'modul', 'e-semar', 'Ruang referensi evaluasi elektronik.'],
            ],
        ],
        'KasubagTURT' => [
            'title' => 'Kertas Kerja Kasubag Tata Usaha dan Rumah Tangga',
            'scope' => 'Survei kepuasan masyarakat dan layanan sarana prasarana pengadilan.',
            'checks' => [
                'Memastikan layanan sarana dan prasarana kepada masyarakat pencari keadilan terukur melalui Survei Badilag.',
                'Memastikan laporan persentase indeks kepuasan masyarakat tersedia.',
                'Memastikan data kepuasan masyarakat menjadi dasar capaian layanan.',
            ],
            'sources' => ['Aplikasi Survei Badilag', 'Rekap indeks kepuasan masyarakat'],
            'outputs' => [
                'Target dan realisasi indeks kepuasan masyarakat.',
                'Capaian layanan sarana dan prasarana.',
                'Narasi analisis capaian survei kepuasan masyarakat.',
            ],
            'analysis_rule' => 'Kasubag TURT wajib menjelaskan penyebab kenaikan atau penurunan indeks kepuasan masyarakat per triwulan.',
            'workflows' => [
                ['Input Target Layanan', 'target', null, 'Isi target indeks kepuasan dan layanan sarana prasarana.'],
                ['Hitung Capaian Survei', 'capaian', null, 'Lihat capaian indeks kepuasan masyarakat.'],
                ['Analisis EvKin Layanan', 'evaluasi', null, 'Tulis narasi evaluasi layanan dan survei.'],
                ['Info & Pengumuman', 'modul', 'info-pengumuman', 'Ruang informasi layanan dan pengumuman.'],
            ],
        ],
        'Kepegawaian' => [
            'title' => 'Kertas Kerja Kasubag Kepegawaian dan TI',
            'scope' => 'Indeks Profesional ASN PTA Medan.',
            'checks' => [
                'Memonitor Indeks Profesional ASN yang terintegrasi dengan My ASN dan SIKEP.',
                'Memastikan totalitas rekapitulasi IP ASN seluruh pegawai PTA Medan.',
                'Memastikan data IP ASN menjadi dasar capaian kinerja kepegawaian.',
            ],
            'sources' => ['MY ASN', 'SIKEP', 'Rekap IP ASN PTA Medan'],
            'outputs' => [
                'Target dan realisasi IP ASN.',
                'Rekap IP ASN seluruh pegawai PTA Medan.',
                'Narasi analisis capaian IP ASN.',
            ],
            'analysis_rule' => 'Kasubag Kepegawaian dan TI wajib menjelaskan faktor kenaikan atau penurunan IP ASN per triwulan.',
            'workflows' => [
                ['Input Target IP ASN', 'target', null, 'Isi target dan realisasi Indeks Profesional ASN.'],
                ['Hitung Capaian IP ASN', 'capaian', null, 'Lihat capaian indikator kepegawaian.'],
                ['Analisis EvKin ASN', 'evaluasi', null, 'Tulis narasi evaluasi IP ASN.'],
                ['Sumber MY ASN', 'modul', 'my-asn', 'Catatan sumber data MY ASN.'],
            ],
        ],
        'Keuangan' => [
            'title' => 'Kertas Kerja Kasubag Keuangan dan Pelaporan',
            'scope' => 'IKPA dan Indikator Pengelolaan Aset.',
            'checks' => [
                'Memonitor nilai IKPA melalui SAKTI, OMSPAN Kemenkeu, dan E-Bima MARI.',
                'Memonitor nilai IPA melalui SAKTI dan E-SADEWA MARI.',
                'Memastikan capaian IKPA dan IPA sesuai sumber aplikasi pendukung.',
            ],
            'sources' => ['SAKTI', 'OMSPAN Kemenkeu', 'E-BIMA MARI', 'E-SADEWA MARI'],
            'outputs' => [
                'Target dan realisasi nilai IKPA.',
                'Target dan realisasi nilai IPA.',
                'Narasi analisis capaian IKPA dan IPA.',
            ],
            'analysis_rule' => 'Kasubag Keuangan wajib menjelaskan kesesuaian atau ketidaksesuaian capaian IKPA dan IPA berdasarkan aplikasi pendukung.',
            'workflows' => [
                ['Input Target Kinerja', 'target', null, 'Isi target dan realisasi nilai IKPA serta IPA.'],
                ['Hitung Capaian Keuangan', 'capaian', null, 'Lihat capaian indikator keuangan dan aset.'],
                ['Analisis EvKin Keuangan', 'evaluasi', null, 'Tulis narasi evaluasi IKPA dan IPA.'],
                ['Sumber SAKTI', 'modul', 'sakti', 'Catatan sumber data SAKTI.'],
                ['Sumber OMSPAN', 'modul', 'omspan', 'Catatan sumber data OMSPAN.'],
                ['Sumber E-SADEWA', 'modul', 'e-sadewa', 'Catatan sumber data aset.'],
            ],
        ],
        'Perencanaan' => [
            'title' => 'Kertas Kerja Kasubag Perencanaan Program dan Anggaran',
            'scope' => 'Nilai Kinerja Perencanaan Anggaran dan monitoring seluruh user.',
            'checks' => [
                'Memonitor Nilai Kinerja Perencanaan Anggaran melalui SAKTI, OMSPAN Kemenkeu, dan E-Bima MARI.',
                'Melakukan analisis capaian Nilai Kinerja Perencanaan Anggaran.',
                'Memonitor seluruh data capaian Sub Bagian dan Panmud sebagai pengguna aplikasi.',
                'Melakukan editing data dan analisis capaian seluruh pengguna jika diperlukan.',
                'Mencetak data capaian seluruh user dan mengirimkan print out ke Badan Pengawasan MA RI setiap triwulan.',
            ],
            'sources' => ['SAKTI', 'OMSPAN Kemenkeu', 'E-BIMA MARI', 'Data seluruh user aplikasi IKPA'],
            'outputs' => [
                'Target dan realisasi nilai kinerja perencanaan anggaran.',
                'Rekap capaian seluruh user per triwulan.',
                'Cetakan laporan capaian untuk Badan Pengawasan MA RI.',
            ],
            'analysis_rule' => 'Perencanaan wajib menganalisis capaian sendiri dan meninjau kelengkapan analisis seluruh user sebelum pencetakan triwulan.',
            'workflows' => [
                ['Manajemen Pengguna', 'users', null, 'Kelola user PTA Medan dan Satker PA.'],
                ['Dashboard Monitoring', 'monitoring', null, 'Pantau pengisian target, capaian, dan evaluasi seluruh user.'],
                ['Monitoring Target Semua User', 'target', null, 'Monitor dan edit target seluruh user.'],
                ['Rekap Capaian Semua User', 'capaian', null, 'Lihat hasil capaian seluruh user per triwulan.'],
                ['Review Evaluasi Semua User', 'evaluasi', null, 'Tinjau dan lengkapi analisis capaian user.'],
                ['Cetak PK/Renaksi/RKT', 'pk', null, 'Cetak dokumen kinerja sebagai bahan pelaporan.'],
            ],
        ],
        'SatkerPanmudHukum' => [
            'title' => 'Kertas Kerja Panmud Hukum Satker PA',
            'scope' => 'Data hukum satuan kerja PA untuk korelasi PTA Medan.',
            'checks' => [
                'Menyiapkan data hukum satuan kerja PA.',
                'Memastikan dokumen dan laporan hukum satker siap menjadi bahan evaluasi PTA Medan.',
                'Mengisi capaian dan analisis sesuai indikator hukum satker.',
            ],
            'sources' => ['Data hukum Satker PA', 'SIPP Satker', 'Dokumen pendukung Satker PA'],
            'outputs' => [
                'Target dan realisasi indikator hukum satker.',
                'Narasi evaluasi data hukum satker.',
                'Bahan korelasi dan koordinasi dengan PTA Medan.',
            ],
            'analysis_rule' => 'Panmud Hukum Satker wajib memberikan narasi atas capaian indikator hukum yang disampaikan ke PTA Medan.',
            'workflows' => [
                ['Input Target Hukum Satker', 'target', null, 'Isi target dan realisasi indikator hukum satker.'],
                ['Hitung Capaian Satker', 'capaian', null, 'Lihat capaian indikator satker.'],
                ['Analisis EvKin Satker', 'evaluasi', null, 'Tulis narasi evaluasi satker.'],
                ['Upload TOR/KAK ABT/Baseline', 'modul', 'upload-tor-kak', 'Ruang dokumen pendukung satker.'],
            ],
        ],
        'SatkerKasubagPTIP' => [
            'title' => 'Kertas Kerja Kasubag PTIP Satker PA',
            'scope' => 'Dukungan data, dokumen, dan pelaporan satuan kerja PA.',
            'checks' => [
                'Menyiapkan data dukung aplikasi dan pelaporan satker PA.',
                'Memastikan dokumen pendukung tersedia untuk kebutuhan PTA Medan.',
                'Mengisi capaian dan analisis sesuai indikator PTIP satker.',
            ],
            'sources' => ['Data dukung Satker PA', 'Dokumen PTIP', 'Aplikasi pendukung satker'],
            'outputs' => [
                'Target dan realisasi indikator PTIP satker.',
                'Dokumen pendukung pelaporan satker.',
                'Narasi evaluasi dukungan PTIP.',
            ],
            'analysis_rule' => 'Kasubag PTIP Satker wajib menjelaskan capaian dukungan data dan pelaporan satker per triwulan.',
            'workflows' => [
                ['Input Target PTIP Satker', 'target', null, 'Isi target dan realisasi indikator PTIP satker.'],
                ['Hitung Capaian PTIP', 'capaian', null, 'Lihat capaian indikator PTIP.'],
                ['Analisis EvKin PTIP', 'evaluasi', null, 'Tulis narasi evaluasi PTIP satker.'],
                ['Upload TOR/KAK ABT/Baseline', 'modul', 'upload-tor-kak', 'Ruang upload dokumen pendukung.'],
            ],
        ],
    ];
}

function role_profile(string $role): array
{
    $profiles = get_all_role_profiles();
    return $profiles[$role] ?? $profiles['Admin'];
}

function role_tasks(string $role): array
{
    return role_profile($role)['checks'];
}

/**
 * Kertas kerja yang tersedia untuk setiap pengguna yang sudah masuk.
 * Role tetap dipakai sebagai identitas jabatan dan kepemilikan data, bukan
 * untuk membedakan menu kerja Primer, Sekunder, atau Tersier.
 */
function shared_workflow_groups(): array
{
    return [
        'Primer / Pokok' => [
            ['Input Target Kinerja (TK)', 'target', null],
            ['Cetak Perjanjian Kinerja (PK)', 'pk', null],
            ['Cetak Rencana Aksi', 'renaksi', null],
            ['Cetak RKT & RKA', 'rkt_rka', null],
            ['Hitung Capaian Kinerja (HCK)', 'capaian', null],
            ['Evaluasi Kinerja (EvKin)', 'evaluasi', null],
            ['Evaluasi', 'evaluasi-akip', null],
        ],
        'Sekunder' => [
            ['Program Kerja & SOP', 'modul', 'program-kerja'],
            ['Renstra', 'modul', 'renstra'],
            ['IKU', 'modul', 'iku'],
            ['Renaksi', 'modul', 'renaksi'],
            ['RKA-KL & Revisi', 'modul', 'rka-kl-revisi'],
            ['E-Monev Bappenas', 'modul', 'e-monev-bappenas'],
            [
                'Laporan Kinerja',
                'modul',
                'laporan-kinerja',
                [
                    ['SAKIP PTA Medan', 'modul', 'sakip-pta-medan'],
                    ['SAKIP PA Sewilayah', 'modul', 'sakip-pa'],
                ],
            ],
            ['Manajemen Risiko', 'modul', 'manajemen-risiko'],
            ['Hibah & MoU', 'modul', 'hibah-mou'],
            ['Monev Capaian Kinerja', 'modul', 'diagram-capaian'],
        ],
        'Tersier' => [
            ['Portal Informasi Kinerja (IFKIN)', 'portal', 'notifikasi'],
            ['Regulasi & Artikel', 'modul', 'regulasi'],
            ['Info & Pengumuman', 'modul', 'info-pengumuman'],
            ['LHE PA', 'modul', 'lhe-pa'],
            ['Upload TOR/KAK ABT/Baseline', 'modul', 'upload-tor-kak'],
            ['Tupoksi & Tim', 'modul', 'tupoksi-tim'],
        ],
    ];
}

function module_catalog(): array
{
    return [
        'program-kerja' => [
            'title' => 'Program Kerja & SOP',
            'group' => 'Skunder',
            'status' => 'Dasar',
            'description' => 'Ruang kerja untuk menampilkan program kerja tahunan, jadwal kegiatan, dan dokumen pendukung bidang program dan anggaran.',
            'features' => [
                'Daftar program kerja tahunan.',
                'Ruang ringkasan jadwal kegiatan.',
                'Arah pengembangan untuk upload dokumen program kerja.',
            ],
            'next_steps' => [
                'Tambahkan tabel dokumen_program_kerja.',
                'Sediakan form upload dan kategori tahun.',
                'Tambahkan status dokumen aktif dan arsip.',
            ],
            'portal_slug' => 'program-kerja-sop',
        ],
        'renstra' => [
            'title' => 'Renstra',
            'group' => 'Skunder',
            'status' => 'Referensi Resmi',
            'description' => 'Ruang referensi Rencana Strategis sebagai dasar sasaran dan indikator kinerja.',
            'features' => [
                'Ringkasan periode Renstra.',
                'Daftar sasaran strategis.',
                'Ruang relasi ke IKU dan Target Kinerja.',
            ],
            'next_steps' => [
                'Buat data sasaran strategis per periode.',
                'Hubungkan Renstra ke indikator kinerja.',
                'Tambahkan upload dokumen Renstra resmi.',
            ],
            'portal_slug' => 'renstra',
        ],
        'iku' => [
            'title' => 'IKU',
            'group' => 'Skunder',
            'status' => 'Referensi Resmi',
            'description' => 'Ruang referensi Indikator Kinerja Utama untuk mendukung input Target Kinerja.',
            'features' => [
                'Daftar indikator kinerja utama.',
                'Satuan dan tipe perhitungan indikator.',
                'Arah relasi ke target dan capaian.',
            ],
            'next_steps' => [
                'Tambahkan tabel indikator_kinerja.',
                'Tambahkan satuan, tipe indikator, dan bobot.',
                'Gunakan IKU sebagai pilihan saat input Target Kinerja.',
            ],
            'portal_slug' => 'iku',
        ],
        'renaksi' => [
            'title' => 'Renaksi',
            'group' => 'Skunder',
            'status' => 'Referensi Resmi',
            'description' => 'Ruang referensi Rencana Aksi Kinerja per triwulan, mencakup aksi, jadwal, keluaran, program, kegiatan, dan dana.',
            'features' => [
                'Cetak rencana aksi dari Target Kinerja.',
                'Target triwulan sementara dibagi rata.',
                'Ruang pengembangan detail kegiatan.',
            ],
            'next_steps' => [
                'Pisahkan target triwulan dari target tahunan.',
                'Tambahkan input aksi, jadwal, keluaran, program, dan kegiatan.',
                'Tambahkan format cetak resmi.',
            ],
            'portal_slug' => 'renaksi',
        ],
        'rka-kl-revisi' => [
            'title' => 'RKA-KL & Revisi',
            'group' => 'Skunder',
            'status' => 'Dasar',
            'description' => 'Ruang pengelolaan dokumen RKA-KL, DIPA 01, DIPA 04, dan riwayat revisi anggaran.',
            'features' => [
                'Referensi DIPA 01 dan DIPA 04.',
                'Ruang daftar revisi anggaran.',
                'Arah upload dokumen RKA-KL.',
            ],
            'next_steps' => [
                'Tambahkan upload dokumen RKA-KL.',
                'Tambahkan metadata nomor revisi dan tanggal.',
                'Hubungkan nominal anggaran ke Target Kinerja.',
            ],
            'portal_slug' => 'revisi',
        ],
        'e-monev-bappenas' => [
            'title' => 'E-Monev Bappenas',
            'group' => 'Skunder',
            'status' => 'Link Portal',
            'description' => 'Ruang monitoring laporan e-Monev Bappenas dan tautan ke portal resmi.',
            'features' => [
                'Referensi laporan 401777 dan 401778.',
                'Tautan portal e-Monev Bappenas.',
                'Ruang pengembangan impor data pelaporan.',
            ],
            'next_steps' => [
                'Tambahkan upload hasil laporan e-Monev.',
                'Tambahkan status pelaporan per periode.',
                'Siapkan impor data jika format resmi tersedia.',
            ],
            'portal_slug' => 'e-monev-bappenas',
        ],
        'laporan-kinerja' => [
            'title' => 'Laporan Kinerja',
            'group' => 'Skunder',
            'status' => 'Portal SAKIP',
            'description' => 'Akses dokumen Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP).',
            'features' => [
                'Ruang rekap capaian tahunan.',
                'Ruang narasi evaluasi kinerja.',
                'Arah ekspor laporan kinerja.',
            ],
            'next_steps' => [
                'Tambahkan template laporan kinerja.',
                'Ambil data otomatis dari capaian dan evaluasi.',
                'Tambahkan ekspor PDF dan Excel.',
            ],
            'portal_slug' => 'sakip',
        ],
        'sakip-pta-medan' => [
            'title' => 'SAKIP PTA Medan',
            'group' => 'Skunder',
            'status' => 'Portal SAKIP',
            'description' => 'Dokumen SAKIP Pengadilan Tinggi Agama Medan.',
            'features' => ['Arsip dokumen SAKIP PTA Medan.'],
            'next_steps' => ['Perbarui arsip dokumen SAKIP secara berkala.'],
            'portal_slug' => 'sakip-pta-medan',
        ],
        'sakip-pa' => [
            'title' => 'SAKIP PA Sewilayah',
            'group' => 'Skunder',
            'status' => 'Portal SAKIP',
            'description' => 'Dokumen SAKIP Pengadilan Agama sewilayah PTA Medan.',
            'features' => ['Akses dokumen dan pelaporan SAKIP PA.'],
            'next_steps' => ['Perbarui dokumen SAKIP PA sesuai periode pelaporan.'],
            'portal_slug' => 'sakip-pa',
        ],
        'manajemen-risiko' => [
            'title' => 'Manajemen Risiko',
            'group' => 'Skunder',
            'status' => 'Link Portal',
            'description' => 'Modul referensi dan arsip dokumen manajemen risiko.',
            'features' => [
                'Referensi buku manajemen risiko.',
                'Ruang dokumen manajemen risiko.',
                'Arah pengembangan register risiko.',
            ],
            'next_steps' => [
                'Tambahkan register risiko per sasaran.',
                'Tambahkan level kemungkinan dan dampak.',
                'Hubungkan mitigasi risiko ke rencana aksi.',
            ],
            'portal_slug' => 'manajemen-risiko',
        ],
        'hibah-mou' => [
            'title' => 'Hibah & MoU',
            'group' => 'Skunder',
            'status' => 'Link Portal',
            'description' => 'Modul dokumen hibah, MoU, dasar hukum, dan pengajuan dokumen pendukung.',
            'features' => [
                'Referensi dasar hukum hibah.',
                'Ruang pengajuan dokumen hibah.',
                'Arah arsip MoU.',
            ],
            'next_steps' => [
                'Tambahkan upload dokumen hibah dan MoU.',
                'Tambahkan status verifikasi dokumen.',
                'Tambahkan riwayat perubahan dokumen.',
            ],
            'portal_slug' => 'hibah',
        ],
        'diagram-capaian' => [
            'title' => 'Monev Capaian Kinerja',
            'group' => 'Skunder',
            'status' => 'Tersedia Dasar',
            'description' => 'Halaman pemantauan dan visualisasi capaian kinerja per triwulan.',
            'features' => [
                'Data capaian sudah tersedia dari Target Kinerja.',
                'Ruang pengembangan grafik capaian.',
                'Arah rekap seluruh user.',
            ],
            'next_steps' => [
                'Tambahkan grafik per triwulan.',
                'Tambahkan filter unit dan tahun.',
                'Tambahkan rekap rata-rata capaian seluruh indikator.',
            ],
            'portal_slug' => 'monev-capaian-kinerja',
        ],
        'sop' => [
            'title' => 'SOP',
            'group' => 'Tersier',
            'status' => 'Dasar',
            'description' => 'Modul arsip Standard Operating Procedure yang wajib tampil pada aplikasi.',
            'features' => [
                'Ruang daftar SOP.',
                'Referensi Program Kerja dan SOP.',
                'Arah upload dokumen SOP resmi.',
            ],
            'next_steps' => [
                'Tambahkan kategori SOP.',
                'Tambahkan upload PDF SOP.',
                'Tambahkan status aktif dan arsip.',
            ],
            'portal_slug' => 'program-kerja-sop',
        ],
        'regulasi' => [
            'title' => 'Regulasi & Artikel',
            'group' => 'Tersier',
            'status' => 'Link Portal',
            'description' => 'Modul kumpulan regulasi terkait perencanaan, anggaran, SAKIP, dan evaluasi.',
            'features' => [
                'Referensi regulasi pada Pojok Baca.',
                'Ruang pengelompokan regulasi.',
                'Arah pencarian dokumen.',
            ],
            'next_steps' => [
                'Tambahkan upload regulasi.',
                'Tambahkan tag dan kategori.',
                'Tambahkan pencarian berdasarkan tahun dan jenis regulasi.',
            ],
            'portal_slug' => 'pojok-baca',
        ],
        'artikel' => [
            'title' => 'Artikel',
            'group' => 'Tersier',
            'status' => 'Link Portal',
            'description' => 'Modul artikel dan bahan bacaan pendukung pelaksanaan IKPA.',
            'features' => [
                'Referensi artikel pada Pojok Baca.',
                'Ruang daftar artikel.',
                'Arah publikasi artikel internal.',
            ],
            'next_steps' => [
                'Tambahkan form artikel.',
                'Tambahkan status draft dan publikasi.',
                'Tambahkan pencarian artikel.',
            ],
            'portal_slug' => 'pojok-baca',
        ],
        'info-pengumuman' => [
            'title' => 'Info & Pengumuman',
            'group' => 'Tersier',
            'status' => 'Dasar',
            'description' => 'Modul papan informasi, jadwal, batas waktu, dan pengumuman bidang program dan anggaran.',
            'features' => [
                'Halaman notifikasi publik sudah tersedia.',
                'Ruang pengumuman internal.',
                'Arah penjadwalan batas waktu.',
            ],
            'next_steps' => [
                'Tambahkan tabel pengumuman.',
                'Tambahkan tanggal mulai dan tanggal selesai.',
                'Tambahkan target penerima pengumuman.',
            ],
            'portal_slug' => 'notifikasi',
        ],
        'lhe-pa' => [
            'title' => 'LHE PA',
            'group' => 'Tersier',
            'status' => 'Perlu Data',
            'description' => 'Modul arsip Laporan Hasil Evaluasi Pengadilan Agama.',
            'features' => [
                'Ruang arsip LHE per satker.',
                'Ruang tindak lanjut hasil evaluasi.',
                'Arah monitoring kelengkapan dokumen.',
            ],
            'next_steps' => [
                'Tambahkan daftar satker PA.',
                'Tambahkan upload LHE per tahun.',
                'Tambahkan status tindak lanjut.',
            ],
            'portal_slug' => 'evaluasi-akip',
        ],
        'upload-tor-kak' => [
            'title' => 'Upload TOR/KAK ABT/Baseline',
            'group' => 'Tersier',
            'status' => 'Perlu Form',
            'description' => 'Modul upload dokumen TOR/KAK untuk ABT dan Baseline.',
            'features' => [
                'Ruang upload dokumen pendukung.',
                'Ruang kategori ABT dan Baseline.',
                'Arah validasi dokumen.',
            ],
            'next_steps' => [
                'Tambahkan tabel dokumen_upload.',
                'Tambahkan validasi tipe file dan ukuran.',
                'Tambahkan status verifikasi dokumen.',
            ],
            'portal_slug' => 'abt',
        ],
        'tupoksi-tim' => [
            'title' => 'Tupoksi & Tim',
            'group' => 'Tersier',
            'status' => 'Link Portal',
            'description' => 'Modul informasi tugas pokok, fungsi, dan tim pelaksana.',
            'features' => [
                'Referensi tugas dan fungsi.',
                'Referensi tim atau squad.',
                'Arah pengelolaan struktur tim.',
            ],
            'next_steps' => [
                'Tambahkan data tim internal.',
                'Tambahkan jabatan dan tugas per anggota.',
                'Tambahkan periode penugasan.',
            ],
            'portal_slug' => 'tugas-dan-fungsi',
        ],
        'biro-humas' => [
            'title' => 'Mahkamah Agung - Biro Humas',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang tautan dan catatan koordinasi dengan Biro Humas Mahkamah Agung.',
            'features' => ['Ruang referensi lembaga.', 'Ruang catatan koordinasi.', 'Arah penyimpanan dokumen terkait.'],
            'next_steps' => ['Tambahkan daftar tautan resmi.', 'Tambahkan catatan koordinasi.', 'Tambahkan arsip dokumen komunikasi.'],
        ],
        'bawas' => [
            'title' => 'Mahkamah Agung - Bawas',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang koordinasi pelaporan dan pengawasan dengan Badan Pengawasan Mahkamah Agung.',
            'features' => ['Ruang referensi Bawas.', 'Ruang catatan pengiriman laporan.', 'Arah rekap triwulan.'],
            'next_steps' => ['Tambahkan log pengiriman laporan.', 'Tambahkan status terkirim per triwulan.', 'Tambahkan arsip bukti email.'],
        ],
        'biro-perencanaan' => [
            'title' => 'Mahkamah Agung - Biro Perencanaan',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang koordinasi data perencanaan, anggaran, dan laporan kinerja.',
            'features' => ['Ruang referensi Biro Perencanaan.', 'Ruang catatan koordinasi.', 'Arah arsip dokumen perencanaan.'],
            'next_steps' => ['Tambahkan daftar dokumen koordinasi.', 'Tambahkan status permintaan data.', 'Tambahkan tanggal batas tindak lanjut.'],
        ],
        'bkn' => [
            'title' => 'Badan Kepegawaian Negara',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang referensi data ASN dan Indeks Profesional ASN.',
            'features' => ['Ruang referensi BKN.', 'Ruang catatan IP ASN.', 'Arah integrasi My ASN.'],
            'next_steps' => ['Tambahkan input rekap IP ASN.', 'Tambahkan sumber data My ASN.', 'Tambahkan riwayat capaian IP ASN.'],
        ],
        'kemenkeu' => [
            'title' => 'Kementerian Keuangan',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang referensi data keuangan, anggaran, IKPA, dan OMSPAN.',
            'features' => ['Ruang referensi Kemenkeu.', 'Ruang catatan IKPA.', 'Arah integrasi SAKTI dan OMSPAN.'],
            'next_steps' => ['Tambahkan input nilai IKPA.', 'Tambahkan sumber data SAKTI/OMSPAN.', 'Tambahkan riwayat capaian per periode.'],
        ],
        'sipp' => [
            'title' => 'SIPP',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang sumber data perkara untuk Panmud Banding dan Panmud Hukum.',
            'features' => ['Ruang catatan data perkara.', 'Ruang sumber realisasi dari SIPP.', 'Arah impor data perkara.'],
            'next_steps' => ['Tambahkan input jumlah perkara masuk dan selesai.', 'Tambahkan data salinan putusan.', 'Siapkan impor CSV jika tersedia.'],
        ],
        'e-semar' => [
            'title' => 'E-SEMAR',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang referensi evaluasi elektronik dan tindak lanjut AKIP.',
            'features' => ['Ruang tautan E-SEMAR.', 'Ruang catatan evaluasi.', 'Arah arsip LHE dan TLHE.'],
            'next_steps' => ['Tambahkan status tindak lanjut.', 'Tambahkan upload LHE/TLHE.', 'Tambahkan catatan reviewer.'],
        ],
        'komdanas' => [
            'title' => 'KOMDANAS',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang referensi data umum dari KOMDANAS.',
            'features' => ['Ruang tautan KOMDANAS.', 'Ruang catatan data dukung.', 'Arah impor data bila tersedia.'],
            'next_steps' => ['Identifikasi data yang diperlukan.', 'Tambahkan mapping indikator.', 'Siapkan format impor.'],
        ],
        'my-asn' => [
            'title' => 'MY ASN',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang sumber data Indeks Profesional ASN.',
            'features' => ['Ruang catatan IP ASN.', 'Ruang sumber data My ASN.', 'Arah input rekap pegawai.'],
            'next_steps' => ['Tambahkan rekap IP ASN.', 'Tambahkan upload bukti data.', 'Hubungkan ke evaluasi Kepegawaian.'],
        ],
        'sakti' => [
            'title' => 'SAKTI',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang sumber data anggaran, DIPA, IKPA, dan IPA.',
            'features' => ['Ruang sumber data SAKTI.', 'Ruang catatan anggaran.', 'Arah impor data keuangan.'],
            'next_steps' => ['Tambahkan input nilai dari SAKTI.', 'Tambahkan upload dokumen ekspor.', 'Hubungkan ke DIPA 01 dan DIPA 04.'],
        ],
        'omspan' => [
            'title' => 'OMSPAN',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang sumber data monitoring pelaksanaan anggaran.',
            'features' => ['Ruang sumber data OMSPAN.', 'Ruang catatan IKPA.', 'Arah impor nilai periode.'],
            'next_steps' => ['Tambahkan input nilai OMSPAN.', 'Tambahkan periode pelaporan.', 'Hubungkan ke capaian Keuangan.'],
        ],
        'satudja' => [
            'title' => 'SATUDJA',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang referensi data dukung SATUDJA.',
            'features' => ['Ruang tautan SATUDJA.', 'Ruang catatan data dukung.', 'Arah mapping indikator.'],
            'next_steps' => ['Identifikasi kebutuhan data.', 'Tambahkan format input.', 'Hubungkan ke capaian terkait.'],
        ],
        'e-bima' => [
            'title' => 'E-BIMA',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang sumber data pelaporan dan monitoring E-BIMA.',
            'features' => ['Ruang sumber data E-BIMA.', 'Ruang catatan capaian.', 'Arah impor data.'],
            'next_steps' => ['Tambahkan input nilai E-BIMA.', 'Tambahkan upload bukti.', 'Hubungkan ke capaian Keuangan/Perencanaan.'],
        ],
        'e-sadewa' => [
            'title' => 'E-SADEWA',
            'group' => 'Korelasi & Koordinasi',
            'status' => 'Referensi',
            'description' => 'Ruang sumber data Indikator Pengelolaan Aset.',
            'features' => ['Ruang sumber data E-SADEWA.', 'Ruang catatan IPA.', 'Arah integrasi aset.'],
            'next_steps' => ['Tambahkan input nilai IPA.', 'Tambahkan sumber data aset.', 'Hubungkan ke evaluasi Keuangan.'],
        ],
    ];
}

function integration_catalog(): array
{
    return [
        'sipp' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'SIPP belum dihubungkan langsung. Halaman ini menjadi bahan koordinasi untuk menentukan data perkara apa saja yang dapat ditarik atau diimpor ke IKPA.',
            'connectable_data' => [
                'Jumlah perkara banding masuk.',
                'Jumlah perkara banding yang diselesaikan tepat waktu.',
                'Tanggal putus dan tanggal upload salinan putusan.',
                'Status pengiriman salinan putusan ke pengadilan pengaju.',
                'Data perkara E-Court dan non E-Court.',
            ],
            'coordination_materials' => [
                'Konfirmasi apakah SIPP menyediakan akses API, ekspor Excel, atau laporan CSV.',
                'Format kolom perkara yang boleh digunakan untuk monitoring IKPA.',
                'Penanggung jawab data SIPP di kepaniteraan.',
                'Periode pengambilan data, misalnya bulanan atau triwulan.',
            ],
            'development_notes' => [
                'Tahap awal cukup memakai upload/impor Excel dari SIPP.',
                'Jika API tersedia, integrasi otomatis dapat dibuat pada tahap berikutnya.',
                'Data dari SIPP akan mendukung role Panmud Banding dan Panmud Hukum.',
            ],
        ],
        'e-semar' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'E-SEMAR belum dihubungkan langsung. Halaman ini dipakai untuk memetakan data evaluasi elektronik, LHE, dan tindak lanjut yang bisa masuk ke IKPA.',
            'connectable_data' => [
                'Status evaluasi AKIP.',
                'Laporan Hasil Evaluasi.',
                'Tindak lanjut hasil evaluasi.',
                'Catatan evaluator.',
            ],
            'coordination_materials' => [
                'Konfirmasi akses data E-SEMAR yang boleh digunakan.',
                'Format dokumen LHE dan TLHE.',
                'Kebutuhan upload dokumen pendukung ke aplikasi IKPA.',
            ],
            'development_notes' => [
                'Tahap awal berupa upload dokumen LHE/TLHE.',
                'Integrasi otomatis menunggu ketersediaan akses resmi.',
            ],
        ],
        'komdanas' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'KOMDANAS belum dihubungkan langsung. Modul ini menampung daftar data dukung yang perlu dikaji sebelum dibuat integrasi.',
            'connectable_data' => [
                'Data umum satker.',
                'Data dukung administrasi.',
                'Rekap informasi kelembagaan yang relevan dengan indikator kinerja.',
            ],
            'coordination_materials' => [
                'Identifikasi data KOMDANAS yang relevan untuk IKPA.',
                'Format ekspor yang tersedia.',
                'Penanggung jawab verifikasi data.',
            ],
            'development_notes' => [
                'Gunakan mapping indikator sebelum membangun form impor.',
                'Integrasi dibuat setelah data yang dibutuhkan disepakati.',
            ],
        ],
        'my-asn' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'MY ASN belum dihubungkan langsung. Halaman ini menjadi bahan koordinasi untuk data Indeks Profesional ASN.',
            'connectable_data' => [
                'Nilai Indeks Profesional ASN.',
                'Rekap IP ASN seluruh pegawai PTA Medan.',
                'Perubahan nilai IP ASN per periode.',
            ],
            'coordination_materials' => [
                'Konfirmasi apakah data MY ASN dapat diekspor.',
                'Format rekap IP ASN yang dipakai Kepegawaian.',
                'Periode pemutakhiran data ASN.',
            ],
            'development_notes' => [
                'Tahap awal memakai input manual atau upload rekap IP ASN.',
                'Integrasi otomatis membutuhkan izin akses data resmi.',
            ],
        ],
        'sakti' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'SAKTI belum dihubungkan langsung. Halaman ini menjelaskan bahan data yang dapat dikaitkan dengan IKPA, anggaran, DIPA, IKPA, IPA, dan perencanaan anggaran.',
            'connectable_data' => [
                'Data DIPA 01 dan DIPA 04 tahun berjalan.',
                'Nilai atau komponen IKPA yang menjadi bahan monitoring.',
                'Data anggaran RKA-KL dan revisi.',
                'Data realisasi anggaran per periode.',
                'Data pendukung Indikator Pengelolaan Aset jika tersedia.',
            ],
            'coordination_materials' => [
                'Konfirmasi format ekspor SAKTI yang dapat digunakan.',
                'Daftar kolom wajib: kode satker, program, kegiatan, output, akun, pagu, realisasi, periode.',
                'Kebijakan akses data SAKTI dan penanggung jawab operator.',
                'Apakah integrasi hanya impor Excel atau dapat memakai API/akses layanan resmi.',
            ],
            'development_notes' => [
                'Tahap awal disarankan memakai upload Excel/CSV hasil ekspor SAKTI.',
                'Data SAKTI dapat menjadi sumber DIPA, RKA, realisasi, IKPA, dan sebagian data aset.',
                'Integrasi otomatis dibuat setelah format data dan izin akses disetujui pimpinan.',
            ],
        ],
        'omspan' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'OMSPAN belum dihubungkan langsung. Modul ini dipakai untuk menyiapkan bahan monitoring pelaksanaan anggaran dan nilai IKPA.',
            'connectable_data' => [
                'Nilai IKPA per periode.',
                'Komponen penilaian pelaksanaan anggaran.',
                'Data monitoring realisasi dan deviasi anggaran.',
            ],
            'coordination_materials' => [
                'Format laporan OMSPAN yang dapat diekspor.',
                'Komponen IKPA yang digunakan dalam evaluasi.',
                'Periode pengambilan data, misalnya bulanan/triwulan.',
            ],
            'development_notes' => [
                'Tahap awal berupa upload laporan OMSPAN.',
                'Data OMSPAN akan mendukung role Keuangan dan Perencanaan.',
            ],
        ],
        'satudja' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'SATUDJA belum dihubungkan langsung. Halaman ini disediakan untuk memetakan data dukung yang relevan dengan indikator kinerja.',
            'connectable_data' => [
                'Data dukung pelaporan.',
                'Informasi yang berkaitan dengan indikator kinerja tertentu.',
                'Dokumen atau rekap yang dapat menjadi bukti realisasi.',
            ],
            'coordination_materials' => [
                'Identifikasi data SATUDJA yang dipakai unit kerja.',
                'Format laporan atau ekspor yang tersedia.',
                'Role pengguna yang membutuhkan data SATUDJA.',
            ],
            'development_notes' => [
                'Tahap awal berupa daftar referensi dan upload dokumen.',
                'Mapping indikator perlu disepakati sebelum integrasi dibuat.',
            ],
        ],
        'e-bima' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'E-BIMA belum dihubungkan langsung. Halaman ini menjadi bahan koordinasi untuk melihat data pelaporan dan monitoring yang dapat masuk ke IKPA.',
            'connectable_data' => [
                'Nilai kinerja perencanaan anggaran.',
                'Data monitoring yang mendukung capaian Keuangan dan Perencanaan.',
                'Rekap pelaporan periodik dari E-BIMA.',
                'Bukti atau laporan yang dapat menjadi dasar realisasi triwulan.',
            ],
            'coordination_materials' => [
                'Konfirmasi jenis data E-BIMA yang boleh digunakan dalam IKPA.',
                'Format ekspor atau laporan E-BIMA.',
                'Penanggung jawab operator E-BIMA.',
                'Frekuensi pemutakhiran data.',
            ],
            'development_notes' => [
                'Tahap awal memakai input manual atau upload file rekap E-BIMA.',
                'Data E-BIMA dapat mendukung role Keuangan dan Perencanaan.',
                'Integrasi otomatis menunggu arahan pimpinan dan ketersediaan akses resmi.',
            ],
        ],
        'e-sadewa' => [
            'status' => 'Rencana Integrasi - Perlu Koordinasi',
            'description' => 'E-SADEWA belum dihubungkan langsung. Halaman ini disiapkan untuk memetakan data aset dan nilai IPA yang dapat mendukung evaluasi Keuangan.',
            'connectable_data' => [
                'Nilai Indikator Pengelolaan Aset.',
                'Data pendukung pengelolaan aset.',
                'Rekap status aset atau laporan aset periodik.',
            ],
            'coordination_materials' => [
                'Konfirmasi format data E-SADEWA yang dapat digunakan.',
                'Kolom wajib untuk monitoring IPA.',
                'Penanggung jawab validasi data aset.',
            ],
            'development_notes' => [
                'Tahap awal berupa input manual nilai IPA dan upload bukti.',
                'Integrasi otomatis dibuat setelah format data aset disepakati.',
            ],
        ],
    ];
}

function module_detail(string $slug): ?array
{
    $catalog = module_catalog();
    if (!isset($catalog[$slug])) {
        return null;
    }

    $module = $catalog[$slug];
    $integrations = integration_catalog();
    if (isset($integrations[$slug])) {
        $module = array_replace($module, $integrations[$slug]);
        $module['is_integration_plan'] = true;
    }

    return $module;
}

function module_is_development(array $module): bool
{
    $status = strtolower((string) ($module['status'] ?? ''));
    return !str_contains($status, 'tersedia') && !str_contains($status, 'aktif');
}

function module_required_materials(array $module): array
{
    if (!empty($module['coordination_materials'])) {
        return $module['coordination_materials'];
    }

    if (!empty($module['required_materials'])) {
        return $module['required_materials'];
    }

    $title = (string) ($module['title'] ?? 'modul');
    return [
        'Dokumen atau format resmi yang akan dipakai untuk modul ' . $title . '.',
        'Contoh data nyata yang sudah pernah digunakan oleh unit kerja.',
        'Daftar kolom/field yang wajib ditampilkan, diinput, atau dicetak.',
        'Penanggung jawab input, verifikasi, dan persetujuan data.',
        'Periode pelaporan yang dipakai, misalnya bulanan, triwulan, atau tahunan.',
    ];
}

function module_development_notes(array $module): array
{
    if (!empty($module['development_notes'])) {
        return $module['development_notes'];
    }

    if (!empty($module['next_steps'])) {
        return $module['next_steps'];
    }

    return [
        'Modul belum memiliki format final.',
        'Isi modul perlu disepakati terlebih dahulu dengan pimpinan dan operator terkait.',
        'Setelah format disetujui, modul dapat dibuat menjadi form input, upload dokumen, tabel monitoring, atau laporan cetak.',
    ];
}

function document_owner(?array $currentUser, bool $canViewAll, int $selectedUserId): array
{
    if (!$currentUser) {
        return [];
    }

    if ($canViewAll && $selectedUserId > 0) {
        $stmt = db()->prepare('SELECT id, username, nama, role, unit FROM users WHERE id = :id');
        $stmt->execute(['id' => $selectedUserId]);
        $owner = $stmt->fetch();
        if ($owner) {
            return $owner;
        }
    }

    return $currentUser;
}

function default_document_meta(array $owner, int $tahun, string $jenis): array
{
    return [
        'tahun' => $tahun,
        'user_id' => (int) ($owner['id'] ?? 0),
        'jenis' => $jenis,
        'no_surat' => '',
        'tanggal_surat' => date('Y-m-d'),
        'lokasi' => 'Medan',
        'pihak1_nama' => (string) ($owner['nama'] ?? ''),
        'pihak1_jabatan' => role_label((string) ($owner['role'] ?? '')),
        'pihak2_nama' => 'Ketua PTA Medan',
        'pihak2_jabatan' => 'Pimpinan',
        'pihak1_ttd' => '',
        'pihak2_ttd' => '',
        'catatan' => '',
    ];
}

function document_meta(array $owner, int $tahun, string $jenis): array
{
    $defaults = default_document_meta($owner, $tahun, $jenis);
    $stmt = db()->prepare(
        'SELECT *
         FROM document_meta
         WHERE tahun = :tahun AND user_id = :user_id AND jenis = :jenis'
    );
    $stmt->execute([
        'tahun' => $tahun,
        'user_id' => (int) $defaults['user_id'],
        'jenis' => $jenis,
    ]);
    $meta = $stmt->fetch();

    return $meta ? array_merge($defaults, $meta) : $defaults;
}

function save_document_meta(array $owner, int $tahun, string $jenis, array $post): void
{
    $defaults = default_document_meta($owner, $tahun, $jenis);
    $payload = [
        'tahun' => $tahun,
        'user_id' => (int) $defaults['user_id'],
        'jenis' => $jenis,
        'no_surat' => trim((string) ($post['no_surat'] ?? '')),
        'tanggal_surat' => trim((string) ($post['tanggal_surat'] ?? $defaults['tanggal_surat'])),
        'lokasi' => trim((string) ($post['lokasi'] ?? $defaults['lokasi'])),
        'pihak1_nama' => trim((string) ($post['pihak1_nama'] ?? $defaults['pihak1_nama'])),
        'pihak1_jabatan' => trim((string) ($post['pihak1_jabatan'] ?? $defaults['pihak1_jabatan'])),
        'pihak2_nama' => trim((string) ($post['pihak2_nama'] ?? $defaults['pihak2_nama'])),
        'pihak2_jabatan' => trim((string) ($post['pihak2_jabatan'] ?? $defaults['pihak2_jabatan'])),
        'pihak1_ttd' => trim((string) ($post['pihak1_ttd'] ?? $defaults['pihak1_ttd'])),
        'pihak2_ttd' => trim((string) ($post['pihak2_ttd'] ?? $defaults['pihak2_ttd'])),
        'catatan' => trim((string) ($post['catatan'] ?? '')),
    ];

    $stmt = db()->prepare(
        'INSERT INTO document_meta
         (tahun, user_id, jenis, no_surat, tanggal_surat, lokasi, pihak1_nama, pihak1_jabatan,
          pihak2_nama, pihak2_jabatan, pihak1_ttd, pihak2_ttd, catatan, updated_at)
         VALUES
         (:tahun, :user_id, :jenis, :no_surat, :tanggal_surat, :lokasi, :pihak1_nama, :pihak1_jabatan,
          :pihak2_nama, :pihak2_jabatan, :pihak1_ttd, :pihak2_ttd, :catatan, CURRENT_TIMESTAMP)
         ON CONFLICT(tahun, user_id, jenis) DO UPDATE SET
          no_surat = excluded.no_surat,
          tanggal_surat = excluded.tanggal_surat,
          lokasi = excluded.lokasi,
          pihak1_nama = excluded.pihak1_nama,
          pihak1_jabatan = excluded.pihak1_jabatan,
          pihak2_nama = excluded.pihak2_nama,
          pihak2_jabatan = excluded.pihak2_jabatan,
          pihak1_ttd = excluded.pihak1_ttd,
          pihak2_ttd = excluded.pihak2_ttd,
          catatan = excluded.catatan,
          updated_at = CURRENT_TIMESTAMP'
    );
    $stmt->execute($payload);
}

function generate_mandatory_targets(int $userId, string $role, int $tahun): void
{
    $mandatoryMap = [
        'PanmudBanding' => [
            ['sasaran' => 'Meningkatnya penyelesaian perkara tingkat banding', 'indikator' => 'Persentase perkara banding yang diselesaikan tepat waktu', 'satuan' => '%', 'tipe_indikator' => 'max', 'sumber_data' => 'SIPP', 'target' => 0],
        ],
        'PanmudHukum' => [
            ['sasaran' => 'Meningkatnya pemanfaatan teknologi informasi dalam penyelesaian perkara', 'indikator' => 'Persentase perkara banding yang menggunakan E-Court', 'satuan' => '%', 'tipe_indikator' => 'max', 'sumber_data' => 'SIPP', 'target' => 0],
        ],
        'KasubagTURT' => [
            ['sasaran' => 'Meningkatnya kualitas layanan sarana dan prasarana', 'indikator' => 'Indeks Kepuasan Masyarakat', 'satuan' => 'Skala', 'tipe_indikator' => 'max', 'sumber_data' => 'Aplikasi Survei Badilag', 'target' => 0],
        ],
        'Kepegawaian' => [
            ['sasaran' => 'Meningkatnya profesionalitas aparatur sipil negara', 'indikator' => 'Indeks Profesionalitas ASN (IP ASN)', 'satuan' => 'Nilai', 'tipe_indikator' => 'max', 'sumber_data' => 'My ASN / SIKEP', 'target' => 0],
        ],
        'Keuangan' => [
            ['sasaran' => 'Meningkatnya kualitas pelaksanaan anggaran dan pengelolaan aset', 'indikator' => 'Nilai Indikator Kinerja Pelaksana Anggaran (IKPA)', 'satuan' => 'Nilai', 'tipe_indikator' => 'max', 'sumber_data' => 'OMSPAN / SAKTI', 'target' => 0],
            ['sasaran' => 'Meningkatnya kualitas pelaksanaan anggaran dan pengelolaan aset', 'indikator' => 'Nilai Indikator Pengelolaan Aset (IPA)', 'satuan' => 'Nilai', 'tipe_indikator' => 'max', 'sumber_data' => 'E-SADEWA / SAKTI', 'target' => 0],
        ],
        'Perencanaan' => [
            ['sasaran' => 'Meningkatnya kualitas perencanaan program dan anggaran', 'indikator' => 'Nilai Kinerja Perencanaan Anggaran', 'satuan' => 'Nilai', 'tipe_indikator' => 'max', 'sumber_data' => 'SAKTI / SMART / E-BIMA', 'target' => 0],
        ],
    ];

    if (!isset($mandatoryMap[$role])) {
        return;
    }

    $existingStmt = db()->prepare(
        'SELECT indikator FROM target_kinerja WHERE tahun = :tahun AND user_id = :user_id AND is_mandatory = 1'
    );
    $existingStmt->execute(['tahun' => $tahun, 'user_id' => $userId]);
    $existing = $existingStmt->fetchAll(PDO::FETCH_COLUMN);

    $uStmt = db()->prepare('SELECT unit FROM users WHERE id = :id');
    $uStmt->execute(['id' => $userId]);
    $unit = (string) $uStmt->fetchColumn();

    $insertStmt = db()->prepare(
        'INSERT INTO target_kinerja 
         (tahun, user_id, unit, sasaran, indikator, satuan, tipe_indikator, sumber_data, target, target_tw1, target_tw2, target_tw3, target_tw4, is_mandatory)
         VALUES 
         (:tahun, :user_id, :unit, :sasaran, :indikator, :satuan, :tipe_indikator, :sumber_data, :target, :target_tw1, :target_tw2, :target_tw3, :target_tw4, 1)'
    );

    foreach ($mandatoryMap[$role] as $item) {
        if (!in_array($item['indikator'], $existing, true)) {
            $t = $item['target'];
            $t_tw = $t / 4;
            $insertStmt->execute([
                'tahun' => $tahun,
                'user_id' => $userId,
                'unit' => $unit,
                'sasaran' => $item['sasaran'],
                'indikator' => $item['indikator'],
                'satuan' => $item['satuan'],
                'tipe_indikator' => $item['tipe_indikator'],
                'sumber_data' => $item['sumber_data'],
                'target' => $t,
                'target_tw1' => $t_tw,
                'target_tw2' => $t_tw,
                'target_tw3' => $t_tw,
                'target_tw4' => $t_tw,
            ]);
        }
    }
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
