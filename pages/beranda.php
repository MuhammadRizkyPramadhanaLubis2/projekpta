<?php
declare(strict_types=1);

$pageData = site_page('beranda');

// Check login status
$isLoggedIn = current_user() !== null;
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>document.documentElement.classList.add('js');</script>
    <title><?= h((string) $pageData['title']) ?> - PTA Medan</title>
    <!-- Assets -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/app.css?v=<?= time() ?>">
</head>

<body class="lp-body" style="overflow: hidden; height: 100vh;">

    <!-- TOP-BAR -->
    <nav class="lp-topbar">
        <div class="lp-container lp-topbar-inner">
            <div class="lp-logo-area">
                <img src="assets/logo_pta.png" alt="Logo PTA Medan" class="lp-logo">
                <span class="lp-brand">IKPA</span>
            </div>
            <div class="lp-nav-menu">
                <a href="#hero">Beranda</a>
                <a href="#about">Tentang</a>
                <a href="#fitur-utama">Fitur</a>
                <a href="#sisa-fitur">Layanan</a>
                <a href="#notifikasi">Informasi</a>
                <a href="#peta">Lokasi</a>
                <?php if ($isLoggedIn): ?>
                    <a href="index.php?page=dashboard" class="lp-btn lp-btn-primary">Dashboard <i
                            class="ph-bold ph-arrow-right"></i></a>
                    <a href="index.php?page=logout" class="lp-btn lp-btn-outline" style="margin-left:10px;">Keluar</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="lp-btn lp-btn-primary">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="lp-main-scroller">
    <!-- HERO -->
    <header id="hero" class="lp-hero">
        <video class="lp-hero-video lp-parallax" data-speed="0.25" autoplay muted loop playsinline preload="auto" poster="assets/paluhakim.png">
            <source src="assets/video palu.mp4" type="video/mp4">
        </video>
        <div class="lp-hero-overlay"></div>
        <canvas id="hero-particles" class="lp-particles"></canvas>

        <div class="lp-container lp-hero-content">
            <div class="lp-hero-text">
                <h1 class="lp-hero-title lp-reveal reveal-blur"  style="--i:1"><?= h((string) $pageData['title']) ?></h1>
                <p class="lp-hero-subtitle lp-reveal reveal-blur"  style="--i:2"><?= h((string) $pageData['subtitle']) ?></p>
                <p class="lp-hero-lead lp-reveal reveal-blur"  style="--i:3"><?= h((string) $pageData['lead']) ?></p>

                <div class="lp-hero-action lp-reveal reveal-blur"  style="margin-top: 32px; --i:4;">
                    <?php if ($isLoggedIn): ?>
                        <a href="index.php?page=dashboard" class="lp-btn lp-btn-primary"
                            style="padding: 16px 32px; font-size: 1.1rem;">Ke Dashboard <i
                                class="ph-bold ph-arrow-right"></i></a>
                    <?php else: ?>
                        <a href="index.php?page=login" class="lp-btn lp-btn-primary"
                            style="padding: 16px 32px; font-size: 1.1rem;">Masuk ke Sistem <i
                                class="ph-bold ph-sign-in"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- TENTANG -->
    <section id="about" class="lp-section lp-site-green lp-about-section">
        <div class="lp-container">
            <div class="lp-about-grid lp-about-card">
                <div class="lp-about-image lp-reveal reveal-slide-left"  style="--i:1">
                    <img src="assets/gedung1.webp" alt="Gedung PTA Medan" class="lp-parallax" data-speed="0.1">
                </div>
                <div class="lp-about-text lp-reveal reveal-slide-right"  style="--i:2">
                    <h2 class="lp-section-title">Tentang Aplikasi</h2>
                    <div class="lp-about-content">
                        <?php foreach ($pageData['body'] as $paragraph): ?>
                            <p><?= h((string) $paragraph) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR UTAMA -->
    <!-- SECTION 3: FITUR UTAMA -->
    <section id="fitur-utama" class="lp-section lp-bg-light lp-site-green">
        <div class="lp-container">
            <h2 class="lp-section-title lp-reveal reveal-fade-up"  style="text-align: center;">Fitur Utama</h2>
            <div class="lp-featured-grid">
                <!-- Kartu 1 -->
                <div class="lp-featured-card lp-reveal reveal-fade-up"  style="--i:1">
                    <div class="lp-fc-icon float-icon"><i class="ph-duotone ph-chart-line-up"></i></div>
                    <a href="index.php?page=portal&slug=penyusunan-anggaran" style="text-decoration: none;">
                        <h3 class="lp-fc-title">Penyusunan Anggaran</h3>
                    </a>
                    <div class="lp-fc-chips">
                        <a href="index.php?page=portal&slug=baseline" class="lp-chip">Baseline</a>
                        <a href="index.php?page=portal&slug=pagu-indikatif" class="lp-chip">Pagu Indikatif</a>
                        <a href="index.php?page=portal&slug=pagu-definitif" class="lp-chip">Pagu Definitif</a>
                        <a href="index.php?page=portal&slug=abt" class="lp-chip">ABT</a>
                    </div>
                </div>
                <!-- Kartu 2 -->
                <div class="lp-featured-card lp-reveal reveal-fade-up"  style="--i:2">
                    <div class="lp-fc-icon float-icon" style="animation-delay: 0.5s;"><i class="ph-duotone ph-shield-check"></i></div>
                    <a href="index.php?page=portal&slug=sakip" style="text-decoration: none;">
                        <h3 class="lp-fc-title">SAKIP</h3>
                    </a>
                    <div class="lp-fc-chips">
                        <a href="index.php?page=portal&slug=sakip-pta-medan" class="lp-chip">SAKIP PTA Medan</a>
                        <a href="index.php?page=portal&slug=sakip-pa" class="lp-chip">SAKIP PA</a>
                    </div>
                </div>
                <!-- Kartu 3 -->
                <div class="lp-featured-card lp-reveal reveal-fade-up"  style="--i:3">
                    <div class="lp-fc-icon float-icon" style="animation-delay: 1s;"><i class="ph-duotone ph-users-three"></i></div>
                    <a href="index.php?page=portal&slug=tugas-dan-fungsi" style="text-decoration: none;">
                        <h3 class="lp-fc-title">Tugas dan Fungsi</h3>
                    </a>
                    <div class="lp-fc-chips">
                        <a href="index.php?page=portal&slug=squad" class="lp-chip">Squad</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: SISA FITUR -->
    <section id="sisa-fitur" class="lp-section lp-site-green">
        <div class="lp-container">
            <h2 class="lp-section-title lp-reveal reveal-fade-up"  style="text-align: center;">Layanan Lainnya</h2>
            <div class="lp-rows-container">
                <a href="index.php?page=portal&slug=revisi" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:1">
                    <div class="lp-row-icon float-icon"><i class="ph-duotone ph-arrows-clockwise"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">Revisi</div>
                        <div class="lp-row-desc">Modul pengelolaan revisi anggaran secara terpadu.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
                <a href="index.php?page=portal&slug=program-kerja-sop" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:2">
                    <div class="lp-row-icon"><i class="ph-duotone ph-clipboard-text"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">Program Kerja & SOP</div>
                        <div class="lp-row-desc">Dokumen Standar Operasional Prosedur dan rencana kerja.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
                <a href="index.php?page=portal&slug=hibah" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:3">
                    <div class="lp-row-icon"><i class="ph-duotone ph-handshake"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">Hibah</div>
                        <div class="lp-row-desc">Informasi dan pelaporan terkait pengelolaan hibah.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
                <a href="index.php?page=portal&slug=manajemen-risiko" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:4">
                    <div class="lp-row-icon"><i class="ph-duotone ph-warning-octagon"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">Manajemen Risiko</div>
                        <div class="lp-row-desc">Pemetaan dan mitigasi risiko operasional lembaga.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
                <a href="index.php?page=portal&slug=monev-kinerja" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:5">
                    <div class="lp-row-icon float-icon" style="animation-delay: 0.2s;"><i class="ph-duotone ph-chart-bar"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">Evaluasi AKIP</div>
                        <div class="lp-row-desc">Penilaian dan evaluasi Akuntabilitas Kinerja Instansi.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
                <a href="index.php?page=portal&slug=e-monev-bappenas" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:6">
                    <div class="lp-row-icon"><i class="ph-duotone ph-monitor-play"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">e-Monev Bappenas</div>
                        <div class="lp-row-desc">Sistem monitoring dan evaluasi terintegrasi Bappenas.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
                <a href="index.php?page=portal&slug=capaian-kinerja" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:7">
                    <div class="lp-row-icon float-icon" style="animation-delay: 0.4s;"><i class="ph-duotone ph-check-circle"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">Monev Capaian Kinerja</div>
                        <div class="lp-row-desc">Pemantauan target dan realisasi kinerja secara berkala.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
                <a href="index.php?page=portal&slug=penyelesaian-perkara" class="lp-row-item lp-reveal reveal-fade-up"  style="--i:8">
                    <div class="lp-row-icon float-icon" style="animation-delay: 0.6s;"><i class="ph-duotone ph-scales"></i></div>
                    <div class="lp-row-content">
                        <div class="lp-row-title">Pojok Baca</div>
                        <div class="lp-row-desc">Kumpulan literatur dan regulasi terkait peradilan.</div>
                    </div>
                    <i class="ph-bold ph-caret-right lp-row-arrow"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- SECTION 5: NOTIFIKASI / INFORMASI KINERJA -->
    <section id="notifikasi" class="lp-section lp-site-green">
        <div class="lp-container">
            <div class="lp-section-header lp-reveal reveal-fade-up" 
                style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 class="lp-section-title" style="margin-bottom: 8px;">Informasi Kinerja</h2>
                    <p class="lp-text-muted">Direktori website pemerintah dan peradilan.</p>
                </div>
                <a href="index.php?page=portal&slug=notifikasi" class="lp-btn lp-btn-outline">Lihat Semua <i
                        class="ph-bold ph-arrow-right"></i></a>
            </div>

            <div class="lp-news-layout">
                <a href="index.php?page=portal&slug=notifikasi" class="lp-news-featured lp-reveal reveal-scale-up" style="--i:1; text-decoration: none; color: inherit; display: flex; flex-direction: column;">
                    <div class="lp-news-img-placeholder float-icon" style="background-image: url('assets/gedung2.webp'); background-size: cover; background-position: center; border-radius: 20px 20px 0 0;">
                    </div>
                    <div class="lp-news-content">
                        <div class="lp-news-date">Pusat Informasi</div>
                        <h3 class="lp-news-title">Portal Informasi Kinerja (IFKIN)</h3>
                        <p class="lp-news-desc">Rujukan cepat menuju sumber informasi kinerja, kebijakan, pembinaan, dan
                            pengawasan lembaga peradilan.</p>
                    </div>
                </a>
                <div class="lp-news-list">
                    <!-- TODO: Ganti dengan tautan/berita dinamis jika data sudah tersedia -->
                    <a href="https://mahkamahagung.go.id" target="_blank" class="lp-news-item lp-reveal reveal-scale-up"  style="--i:2">
                        <div class="lp-news-date">Situs Eksternal</div>
                        <h4 class="lp-news-title">Mahkamah Agung RI</h4>
                    </a>
                    <a href="https://badilag.mahkamahagung.go.id" target="_blank" class="lp-news-item lp-reveal reveal-scale-up"  style="--i:3">
                        <div class="lp-news-date">Situs Eksternal</div>
                        <h4 class="lp-news-title">Ditjen Badilag MA RI</h4>
                    </a>
                    <a href="https://bawas.mahkamahagung.go.id" target="_blank" class="lp-news-item lp-reveal reveal-scale-up"  style="--i:4">
                        <div class="lp-news-date">Situs Eksternal</div>
                        <h4 class="lp-news-title">Badan Pengawasan MA RI</h4>
                    </a>
                    <a href="https://menpan.go.id" target="_blank" class="lp-news-item lp-reveal reveal-scale-up"  style="--i:5">
                        <div class="lp-news-date">Situs Eksternal</div>
                        <h4 class="lp-news-title">Kementerian PANRB</h4>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php
    $lokasi_peradilan = [
        // Koordinat spesifik gedung — bersumber dari situs resmi .go.id & Google Maps
        ['nama' => 'PTA Medan', 'jenis' => 'PTA', 'alamat' => 'Jl. Kapten Sumarsono No. 12, Helvetia Timur, Medan Helvetia, Kota Medan 20124', 'lat' => 3.6315, 'lng' => 98.6462],
        ['nama' => 'PA Medan (IA)', 'jenis' => 'PA', 'alamat' => 'Jl. Sisingamangaraja Km 8,8 No. 198, Medan Amplas', 'lat' => 3.5186, 'lng' => 98.7188],
        ['nama' => 'PA Lubuk Pakam (IA)', 'jenis' => 'PA', 'alamat' => 'Jl. Mahoni No. 3, Kompleks Pemkab Deli Serdang', 'lat' => 3.5521, 'lng' => 98.8559],
        ['nama' => 'PA Binjai', 'jenis' => 'PA', 'alamat' => 'Jl. Sultan Hasanuddin No. 24, Binjai Kota', 'lat' => 3.5979, 'lng' => 98.4804],
        ['nama' => 'PA Stabat (IB)', 'jenis' => 'PA', 'alamat' => 'Jl. Proklamasi No. 46, Kwala Bingai, Stabat, Langkat', 'lat' => 3.7333, 'lng' => 98.4500],
        ['nama' => 'PA Tanjung Balai', 'jenis' => 'PA', 'alamat' => 'Jl. Jend. Sudirman KM. 5.5, Sjambi, Datuk Bandar, Tanjungbalai', 'lat' => 2.9667, 'lng' => 99.8000],
        ['nama' => 'PA Kisaran (IA)', 'jenis' => 'PA', 'alamat' => 'Jl. Jend. Ahmad Yani No. 73, Sendang Sari, Kisaran Barat, Asahan', 'lat' => 2.9845, 'lng' => 99.6158],
        ['nama' => 'PA Tebing Tinggi', 'jenis' => 'PA', 'alamat' => 'Jl. Rumah Sakit Umum No. 7, Tebing Tinggi', 'lat' => 3.3250, 'lng' => 99.1417],
        ['nama' => 'PA Pematang Siantar', 'jenis' => 'PA', 'alamat' => 'Jl. Sisingamangaraja–Pasar Baru No. 47, Nagahuta, Pematang Siantar', 'lat' => 2.9537, 'lng' => 99.0502],
        ['nama' => 'PA Simalungun', 'jenis' => 'PA', 'alamat' => 'Jl. Asahan Km 3,5, Nagori Pamatang Simalungun, Kec. Siantar', 'lat' => 2.9400, 'lng' => 99.0700],
        ['nama' => 'PA Sidikalang', 'jenis' => 'PA', 'alamat' => 'Jl. RSU No. 16, Batangberuh, Sidikalang, Dairi', 'lat' => 2.7380, 'lng' => 98.3150],
        ['nama' => 'PA Kabanjahe', 'jenis' => 'PA', 'alamat' => 'Jl. Letjen Jamin Ginting, Kabanjahe, Karo', 'lat' => 3.1000, 'lng' => 98.4900],
        ['nama' => 'PA Sei Rampah', 'jenis' => 'PA', 'alamat' => 'Jl. Negara (Medan-Tebing Tinggi), Desa Firdaus, Sei Rampah, Serdang Bedagai', 'lat' => 3.4575, 'lng' => 99.1478],
        ['nama' => 'PA Balige', 'jenis' => 'PA', 'alamat' => 'Jl. Siborong-borong – Parapat, Balige, Toba', 'lat' => 2.3364, 'lng' => 99.0628],
        ['nama' => 'PA Tarutung', 'jenis' => 'PA', 'alamat' => 'Jl. Raja Johannes Hutabarat No. 51, Siraja Hutagalung, Siatas Barita, Tapanuli Utara', 'lat' => 1.9965, 'lng' => 98.9764],
        ['nama' => 'PA Pandan', 'jenis' => 'PA', 'alamat' => 'Jl. D.I. Pandjaitan No. 4, Sibuluan Indah, Pandan, Tapanuli Tengah', 'lat' => 1.6830, 'lng' => 98.8270],
        ['nama' => 'PA Sibolga', 'jenis' => 'PA', 'alamat' => 'Jl. Perintis Kemerdekaan No. 1, Pasar Belakang, Sibolga Kota', 'lat' => 1.7410, 'lng' => 98.7758],
        ['nama' => 'PA Padangsidimpuan', 'jenis' => 'PA', 'alamat' => 'Kota Padangsidimpuan', 'lat' => 1.3786, 'lng' => 99.2722],
        ['nama' => 'PA Kota Padangsidimpuan', 'jenis' => 'PA', 'alamat' => 'Jl. H.T. Rizal Nurdin Km 7, Salambue, Padangsidimpuan Tenggara', 'lat' => 1.3786, 'lng' => 99.2722],
        ['nama' => 'PA Panyabungan', 'jenis' => 'PA', 'alamat' => 'Panyabungan, Kab. Mandailing Natal', 'lat' => 0.8400, 'lng' => 99.5550],
        ['nama' => 'PA Sibuhuan', 'jenis' => 'PA', 'alamat' => 'Jl. Ki Hajar Dewantara, Pasar Sibuhuan, Barumun, Padang Lawas', 'lat' => 1.2335, 'lng' => 99.7891],
        ['nama' => 'PA Rantauprapat (IB)', 'jenis' => 'PA', 'alamat' => 'Jl. SM. Raja, Komplek Asrama Haji No. 4, Rantauprapat, Labuhanbatu', 'lat' => 2.1023, 'lng' => 99.8247],
        ['nama' => 'PA Gunungsitoli', 'jenis' => 'PA', 'alamat' => 'Jl. Pancasila No. 29, Gunungsitoli, Nias', 'lat' => 1.2861, 'lng' => 97.6169],
    ];

    // Konstanta Bounding Box — Full Sumatera (matching SVG)
    $LAT_TOP = 6.6563341199999995;
    $LAT_BOTTOM = -6.83691892;
    $LNG_LEFT = 94.409464224;
    $LNG_RIGHT = 107.208899776;
    ?>

    <!-- SECTION 6: PETA LOKASI -->
    <section id="peta" class="lp-section lp-map-section" style="padding: 80px 0;">
        <div class="lp-container">
            <h2 class="lp-section-title lp-reveal reveal-fade-up"  style="text-align: center; margin-bottom: 40px;">Jaringan
                Peradilan</h2>

            <div class="lp-map-discover lp-reveal reveal-scale-up" >
                <!-- Kiri: Area Visual Peta -->
                <div class="lp-discover-visual" id="map-container">
                    <img src="assets/peta_sumut.svg?v=9" alt="Peta Sumatera" class="lp-discover-bg">

                    <?php foreach ($lokasi_peradilan as $idx => $lokasi):
                        $left_pct = (($lokasi['lng'] - $LNG_LEFT) / ($LNG_RIGHT - $LNG_LEFT)) * 100;
                        $top_pct = (($LAT_TOP - $lokasi['lat']) / ($LAT_TOP - $LAT_BOTTOM)) * 100;
                        $is_pta = ($lokasi['jenis'] === 'PTA');
                        ?>
                        <div class="lp-discover-marker lp-reveal reveal-scale-up"  class="<?= $is_pta ? 'is-pta' : '' ?>"
                            style="--i:<?= $idx + 1 ?>; left: <?= $left_pct ?>%; top: <?= $top_pct ?>%;" data-idx="<?= $idx ?>"
                            onmouseenter="selectLocation(<?= $idx ?>)" onclick="selectLocation(<?= $idx ?>)">
                            <div class="lp-marker-dot"></div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Hover Info Card -->
                    <div id="map-info-card" class="lp-map-info-card">
                        <div class="info-card-header">
                            <span id="info-jenis" class="info-badge">PA</span>
                            <h4 id="info-nama">Nama PA</h4>
                        </div>
                        <p id="info-alamat">Alamat</p>
                    </div>
                </div>

                <!-- Kanan: Sidebar Daftar -->
                <div class="lp-discover-sidebar">
                    <div class="lp-sidebar-header">
                        <h3>Daftar Lokasi</h3>
                        <p>Wilayah Hukum Sumatera Utara</p>
                    </div>
                    <div class="lp-sidebar-list" id="sidebar-list-container">
                        <?php foreach ($lokasi_peradilan as $idx => $lokasi):
                            $is_pta = ($lokasi['jenis'] === 'PTA');
                            ?>
                            <div class="lp-loc-item <?= $is_pta ? 'item-pta' : '' ?>" id="loc-item-<?= $idx ?>"
                                onclick="selectLocation(<?= $idx ?>)">
                                <div class="lp-loc-icon">
                                    <i class="<?= $is_pta ? 'ph-fill' : 'ph-duotone' ?> ph-map-pin"></i>
                                </div>
                                <div class="lp-loc-text">
                                    <strong><?= htmlspecialchars($lokasi['nama']) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const lokasiData = <?= json_encode($lokasi_peradilan) ?>;

        function selectLocation(idx) {
            // Hapus status active
            document.querySelectorAll('.lp-discover-marker').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.lp-loc-item').forEach(el => el.classList.remove('active'));

            // Set active pada elemen yang sesuai
            const marker = document.querySelector(`.lp-discover-marker[data-idx="${idx}"]`);
            const item = document.getElementById(`loc-item-${idx}`);

            if (marker) marker.classList.add('active');
            if (item) {
                item.classList.add('active');
                // Scroll daftar jika diperlukan
                const container = document.getElementById('sidebar-list-container');
                const itemTop = item.offsetTop;
                const itemBottom = itemTop + item.offsetHeight;
                const containerTop = container.scrollTop;
                const containerBottom = containerTop + container.offsetHeight;

                if (itemTop < containerTop || itemBottom > containerBottom) {
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }

            // Perbarui Info Card
            const card = document.getElementById('map-info-card');
            const data = lokasiData[idx];
            document.getElementById('info-nama').textContent = data.nama;
            document.getElementById('info-alamat').textContent = data.alamat;
            document.getElementById('info-jenis').textContent = data.jenis;

            if (marker) {
                card.style.display = 'block';
                card.style.left = marker.style.left;
                card.style.top = marker.style.top;
            }
        }
    </script>

    <footer class="lp-footer">
        <div class="lp-container">
            <p>&copy; <?= date('Y') ?> Pengadilan Tinggi Agama Medan. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    </main>

    <script src="assets/landing.js"></script>
</body>

</html>
