<?php
declare(strict_types=1);

$slug = trim((string) ($_GET['slug'] ?? ''));
$module = module_detail($slug);

if (!$module) {
    http_response_code(404);
    $module = [
        'title' => 'Modul Tidak Ditemukan',
        'group' => 'Aplikasi',
        'status' => 'Tidak tersedia',
        'description' => 'Modul yang diminta belum terdaftar pada katalog aplikasi.',
        'features' => ['Periksa kembali tautan modul dari menu utama.'],
        'next_steps' => ['Daftarkan modul pada katalog aplikasi jika memang diperlukan.'],
    ];
}

$isDevelopment = module_is_development($module);
$requiredMaterials = module_required_materials($module);
$developmentNotes = module_development_notes($module);

render_header((string) $module['title']);
?>
<section class="module-hero panel">
    <div>
        <span class="module-kicker"><?= h((string) $module['group']) ?></span>
        <h2><?= h((string) $module['title']) ?></h2>
        <p><?= h((string) $module['description']) ?></p>
    </div>
    <div class="module-status">
        <span>Status Modul</span>
        <strong><?= h((string) $module['status']) ?></strong>
    </div>
</section>

<?php if ($isDevelopment): ?>
    <section class="panel integration-note">
        <h3>Catatan Pengembangan</h3>
        <p class="muted">
            Halaman ini belum diisi sebagai modul final. Bagian di bawah sengaja hanya berisi catatan
            pengembangan dan bahan yang dibutuhkan agar dapat dikoordinasikan sebelum dibuat menjadi
            fitur resmi.
        </p>
        <ul class="clean-list">
            <?php foreach ($developmentNotes as $note): ?>
                <li><?= h((string) $note) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="module-grid">
        <?php if (!empty($module['connectable_data'])): ?>
            <article class="panel module-card">
                <h3>Data yang Mungkin Dihubungkan</h3>
                <ul class="clean-list">
                    <?php foreach ($module['connectable_data'] as $data): ?>
                        <li><?= h((string) $data) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endif; ?>

        <article class="panel module-card">
            <h3>Bahan yang Dibutuhkan</h3>
            <ul class="clean-list">
                <?php foreach ($requiredMaterials as $material): ?>
                    <li><?= h((string) $material) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
    </section>
<?php else: ?>
    <section class="module-grid">
        <article class="panel module-card">
            <h3>Fungsi Tersedia</h3>
            <ul class="clean-list">
                <?php foreach (($module['features'] ?? []) as $feature): ?>
                    <li><?= h((string) $feature) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="panel module-card">
            <h3>Pengembangan Berikutnya</h3>
            <ul class="clean-list">
                <?php foreach (($module['next_steps'] ?? []) as $step): ?>
                    <li><?= h((string) $step) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
    </section>
<?php endif; ?>

<section class="panel module-actions">
    <div>
        <h3>Aksi Modul</h3>
        <p class="muted">
            <?= $isDevelopment
                ? 'Gunakan halaman ini sebagai bahan koordinasi dengan atasan sebelum isi dan format modul ditetapkan.'
                : 'Halaman ini menjadi titik awal penggunaan dan pengembangan modul.' ?>
        </p>
    </div>
    <div class="toolbar">
        <?php if (!empty($module['internal_page'])): ?>
            <a class="button" href="index.php?page=<?= h((string) $module['internal_page']) ?>">Buka Fitur Terkait</a>
        <?php endif; ?>
        <?php if (!empty($module['portal_slug'])): ?>
            <a class="button secondary" href="index.php?page=portal&slug=<?= h((string) $module['portal_slug']) ?>">Lihat Portal Referensi</a>
        <?php endif; ?>
        <a class="button secondary" href="index.php?page=dashboard">Kembali ke Menu</a>
    </div>
</section>

<?php render_footer(); ?>
