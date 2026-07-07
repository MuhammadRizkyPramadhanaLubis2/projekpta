<?php
declare(strict_types=1);

if (!defined('PENYUSUNAN_ANGGARAN_EMBEDDED')) {
    render_header('Penyusunan Anggaran');
}

$budgetStages = [
    [
        'number' => '01',
        'label' => 'Angka Dasar',
        'title' => 'Baseline',
        'description' => 'Kebutuhan anggaran untuk mempertahankan kegiatan dan layanan yang sedang berjalan.',
        'slug' => 'baseline',
    ],
    [
        'number' => '02',
        'label' => 'Ancar-ancar',
        'title' => 'Pagu Indikatif',
        'description' => 'Batas tertinggi anggaran sementara sebagai ancar-ancar penyusunan RKA-K/L.',
        'slug' => 'pagu-indikatif',
    ],
    [
        'number' => '03',
        'label' => 'Alokasi Final',
        'title' => 'Pagu Definitif',
        'description' => 'Alokasi anggaran final yang menjadi dasar RKA-K/L dan pengesahan DIPA.',
        'slug' => 'pagu-definitif',
    ],
    [
        'number' => '04',
        'label' => 'Anggaran Biaya Tambahan',
        'title' => 'ABT',
        'description' => 'Tambahan alokasi di luar pagu untuk kebutuhan mendesak sepanjang tahun berjalan.',
        'slug' => 'abt',
    ],
];
?>
<style>
    .shell > .topbar {
        display: none;
    }

    .budget-page {
        --budget-bg: #eef3ef;
        --budget-paper: #ffffff;
        --budget-ink: #11201a;
        --budget-muted: #5d6d65;
        --budget-faint: #94a19a;
        --budget-line: #e7ece9;
        --budget-green: #1f7a4d;
        --budget-green-deep: #155f3b;
        --budget-green-lite: #3fae74;
        --budget-green-soft: #edf5f0;
        --budget-green-mid: #d3e5db;
        position: relative;
        overflow: hidden;
        margin: -40px;
        min-height: calc(100vh - 80px);
        color: var(--budget-ink);
        background: var(--budget-bg);
    }

    .public-shell-mode .budget-page {
        margin: 0;
    }

    .budget-page::before {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 455px;
        background:
            linear-gradient(90deg, rgba(2, 21, 14, .96), rgba(15, 51, 36, .74), rgba(2, 21, 14, .88)),
            url('assets/gedung2.webp') center/cover;
        pointer-events: none;
    }

    .budget-page::after {
        content: "";
        position: absolute;
        inset: 0 0 auto;
        height: 455px;
        background:
            linear-gradient(180deg, transparent 74%, var(--budget-bg)),
            url('assets/batik_sumut.png') center/320px;
        opacity: .16;
        mix-blend-mode: soft-light;
        pointer-events: none;
    }

    .budget-wrap {
        position: relative;
        z-index: 2;
        width: min(912px, 100%);
        margin: 0 auto;
        padding: 0 28px;
    }

    .budget-hero {
        min-height: 385px;
        padding: 84px 0 118px;
        text-align: center;
        color: #fff;
    }

    .budget-eyebrow {
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        background: rgba(255, 255, 255, .10);
        color: #d7f7e4;
        padding: 7px 16px;
        box-shadow: 0 10px 24px -18px rgba(0, 0, 0, .7);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2.4px;
        text-transform: uppercase;
    }

    .budget-eyebrow::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #9ee6bb;
        box-shadow: 0 0 0 4px rgba(158, 230, 187, .18);
        margin-right: 9px;
    }

    .budget-hero h1 {
        margin: 22px 0 0;
        padding-bottom: .14em;
        color: #fff;
        font-size: clamp(42px, 6vw, 58px);
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1.16;
    }

    .budget-hero p {
        max-width: 52ch;
        margin: 16px auto 0;
        color: rgba(255, 255, 255, .90);
        font-size: 16px;
        line-height: 1.65;
    }

    .budget-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-top: -72px;
    }

    .budget-card {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 210px;
        border: 1px solid var(--budget-line);
        border-radius: 18px;
        background: linear-gradient(180deg, #fff, #fcfefd);
        color: var(--budget-ink);
        padding: 28px 30px 26px;
        text-decoration: none;
        box-shadow: 0 1px 0 rgba(255, 255, 255, .7) inset, 0 24px 55px -36px rgba(7, 32, 22, .55);
        transition: .2s ease;
    }

    .budget-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--budget-green), var(--budget-green-lite));
        transform: scaleX(0);
        transform-origin: left;
        transition: .28s ease;
    }

    .budget-card::after {
        content: "";
        position: absolute;
        top: -48px;
        right: -48px;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(31, 122, 77, .12), rgba(63, 174, 116, .05) 45%, transparent 72%);
        pointer-events: none;
    }

    .budget-card:hover {
        transform: translateY(-4px);
        border-color: var(--budget-green-mid);
        box-shadow: 0 26px 50px -30px rgba(21, 95, 59, .45);
        color: var(--budget-ink);
    }

    .budget-card:hover::before {
        transform: scaleX(1);
    }

    .budget-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .budget-badge {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, var(--budget-green), #2f9160);
        color: #fff;
        box-shadow: 0 10px 20px -10px rgba(31, 122, 77, .7), 0 1px 0 rgba(255, 255, 255, .3) inset;
        font-size: 14px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .budget-label {
        margin-top: 20px;
        color: var(--budget-faint);
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 1.4px;
        text-transform: uppercase;
    }

    .budget-card h3 {
        margin: 5px 0 0;
        color: var(--budget-ink);
        font-size: 21px;
        font-weight: 800;
        letter-spacing: 0;
    }

    .budget-card p {
        margin: 9px 0 0;
        color: var(--budget-muted);
        font-size: 13.5px;
        line-height: 1.6;
    }

    .budget-go {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: auto;
        padding-top: 16px;
        border-top: 1px solid var(--budget-line);
        color: var(--budget-green);
        font-size: 12.5px;
        font-weight: 800;
        transition: .18s ease;
    }

    .budget-card:hover .budget-go {
        gap: 10px;
    }

    .budget-band {
        position: relative;
        overflow: hidden;
        margin-top: 24px;
        border: 1px solid var(--budget-green-mid);
        border-radius: 20px;
        background: linear-gradient(160deg, #ffffff, #eef6f1);
        padding: 44px 30px;
        text-align: center;
        box-shadow: 0 24px 50px -38px rgba(21, 95, 59, .5);
    }

    .budget-band::after {
        content: "";
        position: absolute;
        top: -160px;
        right: -80px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(63, 174, 116, .16), transparent 70%);
        pointer-events: none;
    }

    .budget-band h2 {
        position: relative;
        margin: 0;
        color: var(--budget-ink);
        font-size: 20px;
        font-weight: 800;
        letter-spacing: 0;
    }

    .budget-band p {
        position: relative;
        max-width: 46ch;
        margin: 8px auto 0;
        color: var(--budget-muted);
        font-size: 13.5px;
        line-height: 1.65;
    }

    .budget-band a {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 22px;
        border-radius: 12px;
        background: linear-gradient(145deg, var(--budget-green), var(--budget-green-deep));
        color: #fff;
        padding: 13px 26px;
        text-decoration: none;
        box-shadow: 0 14px 26px -12px rgba(21, 95, 59, .8), 0 1px 0 rgba(255, 255, 255, .25) inset;
        font-size: 13px;
        font-weight: 800;
        transition: .18s ease;
    }

    .budget-band a:hover {
        transform: translateY(-2px);
        color: #fff;
        box-shadow: 0 20px 34px -12px rgba(21, 95, 59, .85);
    }

    .budget-footer-note {
        padding: 30px 0 48px;
        color: var(--budget-faint);
        text-align: center;
        font-size: 12px;
        line-height: 2;
    }

    .budget-contact {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        color: var(--budget-muted);
        margin-bottom: 4px;
    }

    .budget-contact span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .budget-contact i {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--budget-green-mid);
    }

    .budget-footer-note b {
        color: var(--budget-muted);
        font-weight: 700;
    }

    @media (max-width: 980px) {
        .budget-page {
            margin: -24px;
        }

        .public-shell-mode .budget-page {
            margin: 0;
        }
    }

    @media (max-width: 640px) {
        .budget-wrap {
            padding: 0 18px;
        }

        .budget-grid {
            grid-template-columns: 1fr;
            margin-top: -56px;
        }
    }
</style>

<div class="budget-page">
    <div class="budget-wrap">
        <section class="budget-hero">
            <div class="budget-eyebrow">Empat Tahap Perencanaan</div>
            <h1>Penyusunan Anggaran</h1>
            <p>Rangkaian tahap perencanaan anggaran satuan kerja - dari angka dasar hingga anggaran biaya tambahan. Pilih tahap untuk membuka dokumennya.</p>
        </section>

        <section class="budget-grid" aria-label="Tahap penyusunan anggaran">
            <?php foreach ($budgetStages as $stage): ?>
                <a class="budget-card" href="index.php?page=portal&amp;slug=<?= h($stage['slug']) ?>">
                    <div class="budget-card-head">
                        <span class="budget-badge"><?= h($stage['number']) ?></span>
                    </div>
                    <div class="budget-label"><?= h($stage['label']) ?></div>
                    <h3><?= h($stage['title']) ?></h3>
                    <p><?= h($stage['description']) ?></p>
                    <span class="budget-go">Lihat dokumen <span>&rarr;</span></span>
                </a>
            <?php endforeach; ?>
        </section>

        <section class="budget-band">
            <h2>Arsip Lengkap Dokumen Anggaran</h2>
            <p>Seluruh berkas keempat tahap - termasuk rekap se-Sumatera Utara - tersimpan lengkap dan siap diunduh.</p>
            <a href="#">Buka Folder Drive <span>&rarr;</span></a>
        </section>

        <div class="budget-footer-note">
            <div class="budget-contact">
                <span><i></i>www.pta-medan.go.id</span>
                <span><i></i>perencanaanptamedan@gmail.com</span>
                <span><i></i>0812-6540-0490</span>
            </div>
            &copy; 2025 <b>Pengadilan Tinggi Agama Medan</b> - Sub Bagian Rencana Program &amp; Anggaran
        </div>
    </div>
</div>
<?php
if (!defined('PENYUSUNAN_ANGGARAN_EMBEDDED')) {
    render_footer();
}
?>
        text-shadow: 0 18px 38px rgba(0, 0, 0, .38);
