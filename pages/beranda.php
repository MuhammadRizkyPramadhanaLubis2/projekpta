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
            <div class="lp-about-card-modern lp-reveal reveal-fade-up" style="--i:1">
                <div class="lp-about-icon-header">
                    <i class="ph-duotone ph-rocket-launch"></i>
                    <h2 class="lp-section-title">Tentang Aplikasi</h2>
                </div>
                <div class="lp-about-content">
                    <?php foreach ($pageData['body'] as $paragraph): ?>
                        <p><?= h((string) $paragraph) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR UTAMA -->
    <!-- SECTION 3: FITUR UTAMA -->
    <?php
    $url_primer   = $isLoggedIn ? 'index.php?page=user&menu=primer'   : 'index.php?page=login';
    $url_sekunder = $isLoggedIn ? 'index.php?page=user&menu=sekunder' : 'index.php?page=login';
    $url_tersier  = $isLoggedIn ? 'index.php?page=user&menu=tersier'  : 'index.php?page=login';
    ?>
    <section id="fitur-utama" class="lp-section lp-bg-light lp-site-green">
        <div class="lp-container">
            <h2 class="lp-section-title lp-reveal reveal-fade-up"  style="text-align: center;">Fitur Utama</h2>
            <div class="lp-featured-grid">
                <!-- Kartu 1 -->
                <div class="lp-featured-card lp-reveal reveal-fade-up" style="--i:1" onclick="window.location.href='<?= $url_primer ?>'">
                    <div class="lp-fc-content">
                        <div class="lp-fc-icon"><i class="ph-duotone ph-diamonds-four"></i></div>
                        <a href="<?= $url_primer ?>" style="text-decoration: none;" onclick="event.preventDefault();">
                            <h3 class="lp-fc-title">Menu Primer</h3>
                        </a>
                        <p class="lp-fc-desc">Akses ke modul prioritas utama dan fungsi kritikal sistem.</p>
                    </div>
                    <div class="lp-fc-arrow"><i class="ph-bold ph-arrow-right"></i></div>
                </div>
                <!-- Kartu 2 -->
                <div class="lp-featured-card lp-reveal reveal-fade-up" style="--i:2" onclick="window.location.href='<?= $url_sekunder ?>'">
                    <div class="lp-fc-content">
                        <div class="lp-fc-icon" style="animation-delay: 0.5s;"><i class="ph-duotone ph-intersect"></i></div>
                        <a href="<?= $url_sekunder ?>" style="text-decoration: none;" onclick="event.preventDefault();">
                            <h3 class="lp-fc-title">Menu Sekunder</h3>
                        </a>
                        <p class="lp-fc-desc">Akses ke layanan pendukung dan manajemen operasional.</p>
                    </div>
                    <div class="lp-fc-arrow"><i class="ph-bold ph-arrow-right"></i></div>
                </div>
                <!-- Kartu 3 -->
                <div class="lp-featured-card lp-reveal reveal-fade-up" style="--i:3" onclick="window.location.href='<?= $url_tersier ?>'">
                    <div class="lp-fc-content">
                        <div class="lp-fc-icon" style="animation-delay: 1s;"><i class="ph-duotone ph-circles-three"></i></div>
                        <a href="<?= $url_tersier ?>" style="text-decoration: none;" onclick="event.preventDefault();">
                            <h3 class="lp-fc-title">Menu Tersier</h3>
                        </a>
                        <p class="lp-fc-desc">Akses ke fitur tambahan dan konfigurasi preferensi.</p>
                    </div>
                    <div class="lp-fc-arrow"><i class="ph-bold ph-arrow-right"></i></div>
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
    <script>
        // Fluid Ambient Background & Glow Cursor (VividMotion style)
        (function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.style.position = 'fixed';
            canvas.style.top = '0';
            canvas.style.left = '0';
            canvas.style.width = '100vw';
            canvas.style.height = '100vh';
            canvas.style.pointerEvents = 'none';
            // Z-index 0 menempatkan canvas DI BELAKANG konten (container z-index: 2)
            canvas.style.zIndex = '0';
            document.body.appendChild(canvas);

            let width, height;
            function resize() {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            }
            window.addEventListener('resize', resize);
            resize();

            // Background ambient blobs (waves)
            const blobs = [
                { x: Math.random() * window.innerWidth, y: Math.random() * window.innerHeight, r: window.innerWidth * 0.6, vx: 0.6, vy: 0.4, color: 'rgba(16, 185, 129, 0.12)' }, // Emerald
                { x: Math.random() * window.innerWidth, y: Math.random() * window.innerHeight, r: window.innerWidth * 0.7, vx: -0.5, vy: 0.7, color: 'rgba(218, 165, 32, 0.08)' }, // Gold
                { x: Math.random() * window.innerWidth, y: Math.random() * window.innerHeight, r: window.innerWidth * 0.5, vx: 0.4, vy: -0.5, color: 'rgba(52, 211, 153, 0.15)' } // Light Green
            ];

            const particles = [];
            const mouse = { x: -100, y: -100 };
            
            // For smooth cursor following
            let currentMouse = { x: -100, y: -100 };
            let isMoving = false;
            let moveTimeout;

            document.addEventListener('mousemove', function(e) {
                mouse.x = e.clientX;
                mouse.y = e.clientY;
                isMoving = true;
                clearTimeout(moveTimeout);
                moveTimeout = setTimeout(() => isMoving = false, 50);
            });

            function animate() {
                // Background color (clears previous frame)
                ctx.globalCompositeOperation = 'source-over';
                ctx.fillStyle = '#022c22'; // Base dark green
                ctx.fillRect(0, 0, width, height);
                
                // Draw ambient blobs (moving waves)
                ctx.globalCompositeOperation = 'screen';
                blobs.forEach(b => {
                    b.x += b.vx;
                    b.y += b.vy;
                    // Bounce off walls gently
                    if (b.x < -b.r * 0.5 || b.x > width + b.r * 0.5) b.vx *= -1;
                    if (b.y < -b.r * 0.5 || b.y > height + b.r * 0.5) b.vy *= -1;

                    const grad = ctx.createRadialGradient(b.x, b.y, 0, b.x, b.y, b.r);
                    grad.addColorStop(0, b.color);
                    grad.addColorStop(1, 'rgba(0,0,0,0)');
                    ctx.fillStyle = grad;
                    ctx.beginPath();
                    ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
                    ctx.fill();
                });

                // Smooth mouse interpolation
                if (mouse.x > 0) {
                    if (currentMouse.x === -100) {
                        currentMouse.x = mouse.x;
                        currentMouse.y = mouse.y;
                    } else {
                        currentMouse.x += (mouse.x - currentMouse.x) * 0.15;
                        currentMouse.y += (mouse.y - currentMouse.y) * 0.15;
                    }
                }

                // Add cursor particle
                if (mouse.x > 0 && isMoving) {
                    particles.push({
                        x: currentMouse.x,
                        y: currentMouse.y,
                        size: 90, // Soft brush size
                        life: 1,
                        vx: (Math.random() - 0.5) * 0.3,
                        vy: (Math.random() - 0.5) * 0.3 - 0.2 // drift slowly up
                    });
                }

                // Draw cursor particles
                for (let i = 0; i < particles.length; i++) {
                    const p = particles[i];
                    p.x += p.vx;
                    p.y += p.vy;
                    p.life -= 0.015; // Fade out speed
                    p.size += 0.8; // Expand slowly

                    if (p.life <= 0) {
                        particles.splice(i, 1);
                        i--;
                        continue;
                    }

                    // Soft glowing radial gradient for cursor
                    const gradient = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.size);
                    gradient.addColorStop(0, `rgba(247, 215, 116, ${p.life * 0.2})`);
                    gradient.addColorStop(0.5, `rgba(247, 215, 116, ${p.life * 0.08})`);
                    gradient.addColorStop(1, `rgba(247, 215, 116, 0)`);

                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                    ctx.fillStyle = gradient;
                    ctx.fill();
                }
                
                requestAnimationFrame(animate);
            }
            animate();
        })();
    </script>
</body>

</html>
