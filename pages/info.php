<?php
declare(strict_types=1);

$title = trim((string) ($_GET['title'] ?? 'Informasi Modul'));
render_header($title);
?>
<section class="panel">
    <h2><?= h($title) ?></h2>
    <p>
        Modul ini adalah bagian dari konsep IKPA. Fungsinya bisa dikembangkan
        untuk upload dokumen, cetak PDF, integrasi data, atau tampilan laporan sesuai kebutuhan.
    </p>
    <a class="button secondary" href="index.php?page=dashboard">Kembali ke Menu</a>
</section>
<?php render_footer(); ?>
