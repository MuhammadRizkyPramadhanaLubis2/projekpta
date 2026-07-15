<?php
declare(strict_types=1);

$satkerId = $_GET['user_id'] ?? null;
$tahun = year_value();
$user = current_user();

if ($user['role'] !== 'Perencanaan') {
    flash('Halaman ini khusus untuk role Perencanaan.', 'error');
    header('Location: index.php?page=beranda');
    exit;
}

if (!$satkerId) {
    $satkers = db()->query('SELECT u.id, u.nama, u.role, u.unit, es.nilai_akhir, es.grade_akhir, es.status, es.lhe_file FROM users u LEFT JOIN evaluasi_sakip es ON u.id = es.satker_id AND es.tahun = ' . (int)$tahun . ' WHERE u.status = "active" AND u.unit != "PTA Medan" AND u.role LIKE "Satker%" GROUP BY u.unit ORDER BY u.nama')->fetchAll();
}



// -------------------------------------------------------------
// Tampilkan Worksheet Evaluasi jika ada user_id yang dipilih
// -------------------------------------------------------------
$satkerName = 'Satuan Kerja';
$stmt = db()->prepare('SELECT unit FROM users WHERE id = :id');
$stmt->execute(['id' => $satkerId]);
$u = $stmt->fetch();
if ($u) {
    $satkerName = $u['unit'];
}

// Data evaluasi â€“ diambil langsung dari file asli
$summary = [
    'title' => 'Lembar Kerja Evaluasi Akuntabilitas Kinerja',
    'subtitle' => $satkerName . ' Tahun ' . $tahun,
    'satker_score' => '100.00',
    'satker_grade' => 'AA',
    'evaluator_score' => '87.80',
    'evaluator_grade' => 'A',
    'component_count' => 4,
    'subcomponent_count' => 11,
];

$sections = [
    [
        'code' => '1',
        'title' => 'Perencanaan Kinerja',
        'weight' => '30.00',
        'evaluator' => '24.60',
        'subsections' => [
            [
                'code' => '1.a',
                'title' => 'Dokumen Perencanaan Kinerja telah tersedia',
                'weight' => '6',
                'satker_answer' => 'AA',
                'satker_score' => '6',
                'evaluator_answer' => 'A',
                'evaluator_score' => '5.4',
                'notes' => 'Mayoritas dokumen sudah sesuai. Tambahan penyempurnaan utama: perjanjian kinerja, RKA/RPA, dan penguatan rencana aksi.',
                'criteria' => [
                    ['text' => 'Terdapat pedoman teknis perencanaan kinerja.', 'evidence' => ['Pedoman teknis perencanaan kinerja'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Terdapat dokumen perencanaan kinerja jangka panjang.', 'evidence' => ['Dokumen perencanaan kinerja jangka panjang'], 'note' => 'Sudah sesuai berdasarkan blueprint Mahkamah Agung.'],
                    ['text' => 'Terdapat dokumen perencanaan kinerja jangka menengah.', 'evidence' => ['RENSTRA'], 'note' => 'Perlu ditambahkan perjanjian kinerja.'],
                    ['text' => 'Terdapat dokumen perencanaan kinerja jangka pendek.', 'evidence' => ['Rencana Kinerja Tahunan'], 'note' => 'Perlu diperkuat dengan rencana aksi.'],
                    ['text' => 'Terdapat dokumen perencanaan aktivitas yang mendukung kinerja.', 'evidence' => ['Dokumen RKT & Rencana Aksi Kinerja'], 'note' => 'Perlu tambahan RKA, DIPA, dan RPA.'],
                    ['text' => 'Terdapat dokumen perencanaan anggaran yang mendukung kinerja.', 'evidence' => ['Dokumen perencanaan anggaran'], 'note' => 'Sesuai.'],
                ],
            ],
            [
                'code' => '1.b',
                'title' => 'Dokumen Perencanaan Kinerja telah memenuhi standar yang baik',
                'weight' => '9',
                'satker_answer' => 'AA',
                'satker_score' => '9',
                'evaluator_answer' => 'BB',
                'evaluator_score' => '7.2',
                'notes' => 'Kualitas rumusan sudah baik, namun evaluator meminta penguatan pohon kinerja, IKU, dan sinkronisasi sasaranâ€“outcomeâ€“output.',
                'criteria' => [
                    ['text' => 'Dokumen Perencanaan Kinerja telah diformalkan.', 'evidence' => ['Dokumen perencanaan kinerja telah diformalkan'], 'note' => 'Sudah baik.'],
                    ['text' => 'Dokumen Perencanaan Kinerja telah dipublikasikan tepat waktu.', 'evidence' => ['Dokumen perencanaan dipublikasikan tepat waktu'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Dokumen Perencanaan Kinerja telah menggambarkan kebutuhan atas kinerja sebenarnya yang perlu dicapai.', 'evidence' => ['Dokumen perencanaan kinerja menggambarkan kebutuhan kinerja'], 'note' => 'Perlu dimasukkan pohon kinerja PA Binjai.'],
                    ['text' => 'Kualitas rumusan hasil (tujuan/sasaran) telah jelas menggambarkan kondisi kinerja yang akan dicapai.', 'evidence' => ['Rumusan hasil / tujuan / sasaran'], 'note' => 'Sudah baik, namun perlu validasi ulang sasaran di setiap satuan kerja.'],
                    ['text' => 'Ukuran keberhasilan (indikator kinerja) telah memenuhi kriteria SMART.', 'evidence' => ['Dokumen IKU memenuhi kriteria SMART'], 'note' => 'Sudah sesuai dengan IKU MARI.'],
                    ['text' => 'IKU menggambarkan kondisi kinerja utama yang berkelanjutan (sustainable).', 'evidence' => ['Dokumen IKU yang sustainable'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Target yang ditetapkan dalam Perencanaan Kinerja dapat dicapai (achievable), menantang, dan realistis.', 'evidence' => ['Target kinerja yang achievable'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Setiap dokumen perencanaan menggambarkan hubungan yang berkesinambungan/cascading.', 'evidence' => ['Pohon kinerja dan cascading PK Ketua, wakil, hakim, kesekretariatan, kepaniteraan'], 'note' => 'Sudah ada.'],
                    ['text' => 'Perencanaan kinerja memberikan informasi hubungan kinerja, strategi, kebijakan, dan aktivitas lintas bidang.', 'evidence' => ['Perencanaan kinerja yang menjelaskan hubungan kinerja'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Setiap unit/satuan kerja merumuskan dan menetapkan Perencanaan Kinerja.', 'evidence' => ['Perencanaan kinerja satker'], 'note' => 'Sudah ada.'],
                    ['text' => 'Setiap pegawai merumuskan dan menetapkan Perencanaan Kinerja.', 'evidence' => ['Sasaran Kinerja Pegawai'], 'note' => 'Sudah sesuai.'],
                ],
            ],
            [
                'code' => '1.c',
                'title' => 'Perencanaan Kinerja telah dimanfaatkan untuk mewujudkan hasil yang berkesinambungan',
                'weight' => '15',
                'satker_answer' => 'AA',
                'satker_score' => '15',
                'evaluator_answer' => 'BB',
                'evaluator_score' => '12',
                'notes' => 'Pemanfaatan dokumen sudah kuat, namun evaluator meminta tambahan bukti analisis perbaikan, monitoring berkala, dan evidence tingkat pegawai.',
                'criteria' => [
                    ['text' => 'Anggaran yang ditetapkan telah mengacu pada kinerja yang ingin dicapai.', 'evidence' => ['Dokumen DIPA, matriks pendanaan, dan RKA satker'], 'note' => 'Perlu ditambahkan rencana penggunaan anggaran per tahun.'],
                    ['text' => 'Aktivitas yang dilaksanakan telah mendukung kinerja yang ingin dicapai.', 'evidence' => ['SOP pada masing-masing indikator yang mendukung kinerja'], 'note' => 'Perlu disandingkan dengan DIPA, RKA, PK, dan renaksi.'],
                    ['text' => 'Target yang ditetapkan telah dicapai dengan baik atau setidaknya on the right track.', 'evidence' => ['Dokumen capaian kinerja dan capaian anggaran secara berkala'], 'note' => 'Sudah baik.'],
                    ['text' => 'Rencana aksi kinerja berjalan dinamis karena capaian dipantau berkala.', 'evidence' => ['Dokumen capaian kinerja berkala pada aplikasi Komdanas dan SAKTI'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Terdapat perbaikan/penyempurnaan dokumen perencanaan dari hasil analisis perbaikan sebelumnya.', 'evidence' => ['Dokumen reviu RKT tahun 2023, 2024, dan 2025'], 'note' => 'Sebaiknya hasil analisis perbaikan ditampilkan eksplisit.'],
                    ['text' => 'Terdapat perbaikan dokumen perencanaan dalam mewujudkan kondisi/hasil yang lebih baik.', 'evidence' => ['Dokumen reviu RKT'], 'note' => 'Sesuai.'],
                    ['text' => 'Setiap unit/satuan kerja memahami dan peduli untuk mencapai kinerja yang telah direncanakan.', 'evidence' => ['Laporan perkara, penyerapan anggaran, monev Bappenas, realisasi anggaran, pengukuran perjanjian kinerja'], 'note' => 'Sesuai.'],
                    ['text' => 'Setiap pegawai memahami dan peduli untuk mencapai kinerja yang telah direncanakan.', 'evidence' => ['Pengukuran perjanjian kinerja bulanan, undangan, daftar hadir, rakor bulanan, realisasi anggaran'], 'note' => 'Perlu tambahan PCK bulanan sebagai evidence.'],
                ],
            ],
        ],
    ],
    [
        'code' => '2',
        'title' => 'Pengukuran Kinerja',
        'weight' => '30.00',
        'evaluator' => '25.50',
        'subsections' => [
            [
                'code' => '2.a',
                'title' => 'Pengukuran Kinerja telah dilakukan',
                'weight' => '10',
                'satker_answer' => 'AA',
                'satker_score' => '10',
                'evaluator_answer' => 'BB',
                'evaluator_score' => '4.8',
                'notes' => 'Dasar pengukuran tersedia, tetapi evaluator menekankan inovasi atau aplikasi pengumpulan data serta analisa pengukuran pada LKjIP.',
                'criteria' => [
                    ['text' => 'Terdapat pedoman teknis pengukuran kinerja dan pengumpulan data.', 'evidence' => ['Dokumen teknis pengukuran kinerja'], 'note' => 'Sesuai.'],
                    ['text' => 'Terdapat definisi operasional yang jelas atas kinerja dan cara mengukur indikator.', 'evidence' => ['Dokumen IKU dan penjelasannya'], 'note' => 'Tambahkan analisa pengukuran pada LKjIP sebagai evidence.'],
                    ['text' => 'Terdapat mekanisme yang jelas terhadap pengumpulan data kinerja yang dapat diandalkan.', 'evidence' => ['Dokumen SOP pengumpulan data kinerja'], 'note' => 'Jika memungkinkan, tambahkan inovasi/aplikasi pengumpulan kinerja.'],
                ],
            ],
            [
                'code' => '2.b',
                'title' => 'Pengukuran Kinerja telah menjadi kebutuhan dalam mewujudkan kinerja secara efektif, efisien, berjenjang, dan berkelanjutan',
                'weight' => '10',
                'satker_answer' => 'AA',
                'satker_score' => '10',
                'evaluator_answer' => 'BB',
                'evaluator_score' => '7.2',
                'notes' => 'Praktik pengukuran sudah rutin, relevan, dan berjenjang. Penguatan utama ada pada aplikasi pendukung dan integrasi monitoring lintas level.',
                'criteria' => [
                    ['text' => 'Pimpinan terlibat sebagai decision maker dalam mengukur capaian kinerja.', 'evidence' => ['Dokumen monev capaian kinerja secara berkala'], 'note' => 'Sesuai.'],
                    ['text' => 'Data kinerja yang dikumpulkan relevan untuk mengukur capaian kinerja yang diharapkan.', 'evidence' => ['Dokumen capaian kinerja'], 'note' => 'Sesuai.'],
                    ['text' => 'Data kinerja yang dikumpulkan mendukung capaian kinerja yang diharapkan.', 'evidence' => ['Data pendukung pengukuran capaian kinerja'], 'note' => 'Sesuai.'],
                    ['text' => 'Pengukuran kinerja telah dilakukan secara berkala.', 'evidence' => ['Dokumen capaian kinerja Komdanas bulanan dan triwulan'], 'note' => 'Sesuai.'],
                    ['text' => 'Setiap level organisasi memantau pengukuran capaian kinerja unit di bawahnya secara berjenjang.', 'evidence' => ['Monev Bappenas, capaian kinerja Komdanas, laporan perkara, SKP pegawai yang telah dinilai atasan'], 'note' => 'Sesuai.'],
                    ['text' => 'Pengumpulan data kinerja telah memanfaatkan teknologi informasi.', 'evidence' => ['Penggunaan aplikasi KOMDANAS, e-Monev Bappenas, SPAN, dan SIPP untuk entry data'], 'note' => 'Sesuai, namun akan lebih kuat jika ada inovasi khusus.'],
                    ['text' => 'Pengukuran capaian kinerja telah memanfaatkan teknologi informasi.', 'evidence' => ['Penggunaan aplikasi KOMDANAS, e-Monev Bappenas, SPAN, dan SIPP untuk pengukuran'], 'note' => 'Sesuai.'],
                ],
            ],
            [
                'code' => '2.c',
                'title' => 'Pengukuran Kinerja telah dijadikan dasar reward, punishment, dan penyesuaian strategi',
                'weight' => '10',
                'satker_answer' => 'AA',
                'satker_score' => '10',
                'evaluator_answer' => 'A',
                'evaluator_score' => '13.5',
                'notes' => 'Nilai kuat. Penyempurnaan diarahkan pada dokumen refocusing, penempatan jabatan, efisiensi anggaran, serta bukti pemahaman pegawai.',
                'criteria' => [
                    ['text' => 'Pengukuran Kinerja menjadi dasar penyesuaian tunjangan kinerja/penghasilan.', 'evidence' => ['Dokumen PKP'], 'note' => 'Tambahkan piagam atau prestasi kinerja satker/pegawai bila ada.'],
                    ['text' => 'Pengukuran Kinerja menjadi dasar penempatan/penghapusan jabatan struktural maupun fungsional.', 'evidence' => ['Dokumen Baperjakat, SK pegawai yang mendapat promosi'], 'note' => 'Sesuai.'],
                    ['text' => 'Pengukuran Kinerja mempengaruhi penyesuaian (refocusing) organisasi.', 'evidence' => ['Standar dokumen refocusing'], 'note' => 'Perlu dokumen RKAKL berisi data adjustment.'],
                    ['text' => 'Pengukuran kinerja mempengaruhi penyesuaian strategi dalam mencapai kinerja.', 'evidence' => ['Dokumen rapat intern, reviu SOP, dokumen RTM'], 'note' => 'Sesuai.'],
                    ['text' => 'Pengukuran kinerja mempengaruhi penyesuaian kebijakan dalam mencapai kinerja.', 'evidence' => ['Dokumen rapat intern tindak lanjut, reviu SOP, dokumen RTM'], 'note' => 'Sesuai.'],
                    ['text' => 'Pengukuran kinerja mempengaruhi penyesuaian aktivitas dalam mencapai kinerja.', 'evidence' => ['Kebijakan pusat dan hasil reviu revisi anggaran'], 'note' => 'Sesuai.'],
                    ['text' => 'Pengukuran kinerja mempengaruhi penyesuaian anggaran.', 'evidence' => ['Kebijakan pusat dan hasil reviu revisi anggaran'], 'note' => 'Sesuai.'],
                    ['text' => 'Terdapat efisiensi atas penggunaan anggaran dalam mencapai kinerja.', 'evidence' => ['Dokumen rapat capaian kinerja berkala, undangan, daftar hadir, notulen'], 'note' => 'Sesuai.'],
                    ['text' => 'Setiap unit/satuan kerja memahami hasil pengukuran kinerja.', 'evidence' => ['Dokumen capaian rapat kinerja secara berkala'], 'note' => 'Perlu ditambah SKP dan PCK.'],
                    ['text' => 'Setiap pegawai memahami hasil pengukuran kinerja.', 'evidence' => ['Dokumen rapat capaian kinerja berkala, undangan, daftar hadir, notulen'], 'note' => 'Perlu tambahan hasil wawancara pegawai yang difungsikan.'],
                ],
            ],
        ],
    ],
    [
        'code' => '3',
        'title' => 'Pelaporan Kinerja',
        'weight' => '15.00',
        'evaluator' => '13.95',
        'subsections' => [
            [
                'code' => '3.a',
                'title' => 'Terdapat Dokumen Laporan yang menggambarkan kinerja',
                'weight' => '10.5',
                'satker_answer' => 'AA',
                'satker_score' => '10.5',
                'evaluator_answer' => 'BB',
                'evaluator_score' => '2.4',
                'notes' => 'Basis dokumen sudah baik dan lengkap. Evaluator menilai publikasi, reviu, dan formalisasi sudah tersedia dengan baik.',
                'criteria' => [
                    ['text' => 'Dokumen Laporan Kinerja telah disusun.', 'evidence' => ['Dokumen LKjIP 2023'], 'note' => 'Sudah baik.'],
                    ['text' => 'Dokumen Laporan Kinerja telah disusun secara berkala.', 'evidence' => ['Dokumen LKjIP 2022 dan 2023'], 'note' => 'Sudah baik.'],
                    ['text' => 'Dokumen Laporan Kinerja telah diformalkan.', 'evidence' => ['Dokumen LKjIP 2023 yang telah diformalkan'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Dokumen Laporan Kinerja telah direviu.', 'evidence' => ['Dokumen reviu LKjIP 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Dokumen Laporan Kinerja telah dipublikasikan.', 'evidence' => ['Dokumen laporan kinerja telah dipublikasikan'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Dokumen Laporan Kinerja telah disampaikan tepat waktu.', 'evidence' => ['Dokumen laporan kinerja tepat waktu'], 'note' => 'Sudah sesuai.'],
                ],
            ],
            [
                'code' => '3.b',
                'title' => 'Dokumen Laporan Kinerja telah memenuhi standar menggambarkan kualitas capaian',
                'weight' => '4.5',
                'satker_answer' => 'AA',
                'satker_score' => '4.5',
                'evaluator_answer' => 'A',
                'evaluator_score' => '4.05',
                'notes' => 'Laporan kuat secara standar dan isi. Perlu dijaga konsistensi narasi perbandingan tahunan serta benchmark kinerja.',
                'criteria' => [
                    ['text' => 'Dokumen Laporan Kinerja disusun secara berkualitas sesuai standar.', 'evidence' => ['Dokumen LKjIP sesuai PermenPAN RB No. 53 Tahun 2014'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan mengungkap seluruh informasi tentang pencapaian kinerja.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan menginfokan perbandingan realisasi kinerja dengan target tahunan.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan menginfokan perbandingan realisasi kinerja dengan target jangka menengah.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan menginfokan perbandingan realisasi kinerja dengan tahun-tahun sebelumnya.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan menginfokan perbandingan realisasi kinerja di level nasional/internasional.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan menginfokan kualitas capaian kinerja beserta upaya/hambatan.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan menginfokan efisiensi penggunaan sumber daya.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Laporan menginfokan upaya perbaikan/penyempurnaan kinerja ke depan.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                ],
            ],
            [
                'code' => '3.c',
                'title' => 'Pelaporan Kinerja telah memberikan dampak besar dalam penyesuaian strategi/kebijakan berikutnya',
                'weight' => '7.5',
                'satker_answer' => 'AA',
                'satker_score' => '7.5',
                'evaluator_answer' => 'AA',
                'evaluator_score' => '7.5',
                'notes' => 'Subkomponen paling kuat. Laporan dinilai sudah digunakan sebagai referensi pimpinan, evaluasi capaian, penganggaran, dan budaya kinerja.',
                'criteria' => [
                    ['text' => 'Informasi dalam laporan kinerja selalu menjadi perhatian utama pimpinan.', 'evidence' => ['Capaian kinerja dan dokumen rapat kinerja'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Penyajian informasi dalam laporan kinerja menjadi kepedulian seluruh pegawai.', 'evidence' => ['Capaian kinerja, rapat, pengukuran kinerja, SOP pengumpulan data'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Informasi dalam laporan kinerja berkala digunakan dalam penyesuaian aktivitas.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Informasi dalam laporan kinerja berkala digunakan dalam penyesuaian penggunaan anggaran.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Informasi dalam laporan kinerja digunakan dalam evaluasi keberhasilan kinerja.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Informasi dalam laporan kinerja digunakan dalam penyesuaian perencanaan berikutnya.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Sudah sesuai.'],
                    ['text' => 'Informasi dalam laporan kinerja mempengaruhi perubahan budaya kinerja organisasi.', 'evidence' => ['Dokumen LKjIP Tahun 2023'], 'note' => 'Perlu ditambahkan notula evaluasi rapat awal dan akhir tahun.'],
                ],
            ],
        ],
    ],
    [
        'code' => '4',
        'title' => 'Evaluasi Akuntabilitas Kinerja Internal',
        'weight' => '25.00',
        'evaluator' => '23.75',
        'subsections' => [
            [
                'code' => '4.a',
                'title' => 'Evaluasi Akuntabilitas Kinerja Internal telah dilaksanakan',
                'weight' => '10',
                'satker_answer' => 'AA',
                'satker_score' => '10',
                'evaluator_answer' => 'A',
                'evaluator_score' => '4.5',
                'notes' => 'Pelaksanaan LHE AKIP dinilai matang. Pedoman, Hawasbid, dan tindak lanjut sudah terlihat jelas.',
                'criteria' => [
                    ['text' => 'Terdapat pedoman teknis Evaluasi Akuntabilitas Kinerja Internal.', 'evidence' => ['LHE AKIP, pedoman Evaluasi MA, dan PermenPAN RB 88 Tahun 2021'], 'note' => 'Sudah mantap.'],
                    ['text' => 'Evaluasi Akuntabilitas Kinerja Internal telah dilaksanakan pada seluruh unit kerja/perangkat daerah.', 'evidence' => ['LHE AKIP, dokumen Hawasbid, TLHP'], 'note' => 'Sudah baik.'],
                    ['text' => 'Evaluasi Akuntabilitas Kinerja Internal dilaksanakan secara berjenjang.', 'evidence' => ['LHE AKIP, dokumen Hawasbid, TLHP'], 'note' => 'Sudah baik.'],
                ],
            ],
            [
                'code' => '4.b',
                'title' => 'Evaluasi Akuntabilitas Kinerja Internal telah dilaksanakan secara berkualitas dengan sumber daya yang memadai',
                'weight' => '7.5',
                'satker_answer' => 'AA',
                'satker_score' => '7.5',
                'evaluator_answer' => 'A',
                'evaluator_score' => '6.75',
                'notes' => 'Kualitas tim dan dokumen sudah sangat baik. Poin plus terlihat pada SDM bersertifikat, pemanfaatan TI, dan kedalaman evaluasi.',
                'criteria' => [
                    ['text' => 'Evaluasi Akuntabilitas Kinerja Internal dilaksanakan sesuai standar.', 'evidence' => ['SK pedoman Evaluasi, SK tim reviu IKU, PK PA Binjai, dan LHE'], 'note' => 'Sudah baik.'],
                    ['text' => 'Evaluasi dilaksanakan oleh SDM yang memadai.', 'evidence' => ['LHE AKIP, SK pedoman Evaluasi, SDM bersertifikat SAKIP'], 'note' => 'Sudah baik.'],
                    ['text' => 'Evaluasi dilaksanakan dengan pendalaman yang memadai.', 'evidence' => ['LHE AKIP'], 'note' => 'Sudah baik.'],
                    ['text' => 'Evaluasi dilaksanakan pada seluruh unit kerja/perangkat daerah.', 'evidence' => ['LHE AKIP'], 'note' => 'Sudah baik.'],
                    ['text' => 'Evaluasi memanfaatkan Teknologi Informasi (Aplikasi).', 'evidence' => ['Pemanfaatan TI dalam Evaluasi'], 'note' => 'Sudah baik.'],
                ],
            ],
            [
                'code' => '4.c',
                'title' => 'Implementasi SAKIP meningkat karena evaluasi internal memberi dampak nyata',
                'weight' => '7.5',
                'satker_answer' => 'AA',
                'satker_score' => '7.5',
                'evaluator_answer' => 'AA',
                'evaluator_score' => '12.5',
                'notes' => 'Dampak evaluasi internal sangat kuat. Rekomendasi ditindaklanjuti dan menjadi dasar perbaikan akuntabilitas serta efisiensi kinerja.',
                'criteria' => [
                    ['text' => 'Seluruh rekomendasi hasil evaluasi akuntabilitas kinerja internal telah ditindaklanjuti.', 'evidence' => ['Dokumen TLHP, LHE, reviu IKU, reviu Renstra, e-SAKIP reviu, SAKIP Komdanas'], 'note' => 'Perlu tambahan notula rapat pembahasan evaluasi untuk tindak lanjut LHE.'],
                    ['text' => 'Terjadi peningkatan implementasi SAKIP melalui tindak lanjut rekomendasi.', 'evidence' => ['Dokumen LHE, pengukuran kinerja, TLHP, Hawasbid, reviu renstra, PK 2023'], 'note' => 'Sudah baik.'],
                    ['text' => 'Hasil evaluasi dimanfaatkan untuk perbaikan dan peningkatan akuntabilitas kinerja.', 'evidence' => ['Dokumen evaluasi akuntabilitas kinerja'], 'note' => 'Sudah baik.'],
                    ['text' => 'Hasil evaluasi dimanfaatkan dalam mendukung efektivitas dan efisiensi kinerja.', 'evidence' => ['Dokumen evaluasi akuntabilitas kinerja'], 'note' => 'Sudah baik.'],
                    ['text' => 'Terjadi perbaikan dan peningkatan kinerja dengan memanfaatkan hasil evaluasi internal.', 'evidence' => ['Perbaikan akuntabilitas kinerja'], 'note' => 'Sudah baik.'],
                ],
            ],
        ],
    ],
];

$user = current_user();
if (!$user || $user['role'] !== 'Perencanaan') {
    http_response_code(403);
    render_header('Akses Ditolak');
    echo '<section class="panel"><h2>Akses Ditolak</h2><p class="muted">Halaman ini khusus untuk role Perencanaan.</p><a class="button secondary" href="index.php?page=dashboard">Kembali ke Menu</a></section>';
    render_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'upload_lhe') {
        $sid = (int)($_POST['satker_id'] ?? 0);
        if ($sid > 0 && isset($_FILES['lhe_file']) && $_FILES['lhe_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/data/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext = pathinfo($_FILES['lhe_file']['name'], PATHINFO_EXTENSION);
            $filename = 'lhe_' . $tahun . '_' . $sid . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['lhe_file']['tmp_name'], $uploadDir . $filename);

            $stmt = db()->prepare('SELECT id FROM evaluasi_sakip WHERE tahun = :t AND satker_id = :sid');
            $stmt->execute(['t' => $tahun, 'sid' => $sid]);
            if ($stmt->fetch()) {
                $upd = db()->prepare('UPDATE evaluasi_sakip SET lhe_file = :f, updated_at = CURRENT_TIMESTAMP WHERE tahun = :t AND satker_id = :sid');
                $upd->execute(['f' => $filename, 't' => $tahun, 'sid' => $sid]);
            } else {
                $ins = db()->prepare('INSERT INTO evaluasi_sakip (tahun, satker_id, evaluator_id, status, lhe_file) VALUES (:t, :sid, :eid, "Evaluasi", :f)');
                $ins->execute(['t' => $tahun, 'sid' => $sid, 'eid' => $user['id'], 'f' => $filename]);
            }
            $flash = ['type' => 'success', 'message' => 'File LHE berhasil diunggah.'];
        }
    } elseif (($_POST['action'] ?? '') === 'save_eval_ajax') {
        $sid = (int)($_POST['satker_id'] ?? 0);
        $type = $_POST['type'] ?? '';
        $code = $_POST['code'] ?? '';
        
        if ($sid > 0 && $code) {
            $stmt = db()->prepare('SELECT data_nilai FROM evaluasi_sakip WHERE tahun = :t AND satker_id = :sid');
            $stmt->execute(['t' => $tahun, 'sid' => $sid]);
            $row = $stmt->fetch();
            $dn = $row && $row['data_nilai'] ? json_decode($row['data_nilai'], true) : [];
            
            if (!isset($dn[$code])) {
                $dn[$code] = ['jawaban' => '', 'nilai' => 0, 'catatan' => '', 'rekomendasi' => '', 'kriteria' => []];
            }
            
            if ($type === 'jawaban') {
                $dn[$code]['jawaban'] = $_POST['value'];
                $dn[$code]['nilai'] = (float)$_POST['score'];
            } elseif ($type === 'catatan') {
                $dn[$code]['catatan'] = $_POST['value'];
            } elseif ($type === 'rekomendasi') {
                $dn[$code]['rekomendasi'] = $_POST['value'];
            }
            
            // Recalculate total
            $totalScore = 0.0;
            foreach ($dn as $c => $data) {
                $totalScore += (float)($data['nilai'] ?? 0);
            }
            
            $grade = 'E';
            if ($totalScore >= 90) $grade = 'AA';
            elseif ($totalScore >= 80) $grade = 'A';
            elseif ($totalScore >= 70) $grade = 'BB';
            elseif ($totalScore >= 60) $grade = 'B';
            elseif ($totalScore >= 50) $grade = 'CC';
            elseif ($totalScore >= 30) $grade = 'C';
            elseif ($totalScore >= 10) $grade = 'D';
            
            $jsonData = json_encode($dn);
            
            if ($row) {
                $upd = db()->prepare('UPDATE evaluasi_sakip SET nilai_akhir = :n, grade_akhir = :g, data_nilai = :dn, evaluator_id = :eid, updated_at = CURRENT_TIMESTAMP WHERE tahun = :t AND satker_id = :sid');
                $upd->execute(['n' => $totalScore, 'g' => $grade, 'dn' => $jsonData, 'eid' => $user['id'], 't' => $tahun, 'sid' => $sid]);
            } else {
                $ins = db()->prepare('INSERT INTO evaluasi_sakip (tahun, satker_id, evaluator_id, nilai_akhir, grade_akhir, data_nilai, status) VALUES (:t, :sid, :eid, :n, :g, :dn, "Evaluasi")');
                $ins->execute(['t' => $tahun, 'sid' => $sid, 'eid' => $user['id'], 'n' => $totalScore, 'g' => $grade, 'dn' => $jsonData]);
            }
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'total_score' => $totalScore, 'grade' => $grade]);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error']);
        exit;
    }
}



// Fetch data_nilai from DB to populate answers
$dn = [];
if ($satkerId) {
    $stmt = db()->prepare('SELECT data_nilai FROM evaluasi_sakip WHERE tahun = :t AND satker_id = :sid');
    $stmt->execute(['t' => $tahun, 'sid' => $satkerId]);
    $row = $stmt->fetch();
    if ($row && $row['data_nilai']) {
        $dn = json_decode($row['data_nilai'], true);
    }
}

define('HIDE_PAGE_TOPBAR', true);
render_header('Evaluasi SAKIP');
?>
<link rel="stylesheet" href="assets/evaluasi-akip.css" />
<div class="evaluasi-wrapper">

<?php if (!$satkerId): ?>
<header class="primer-hero" style="padding-bottom: 80px;">
  <div class="primer-hero__inner">
    <div class="primer-hero__copy">
      <span class="hero-kicker">Evaluasi SAKIP · Dashboard</span>
      <h1>Dashboard Evaluasi Kinerja SAKIP</h1>
      <p>Pantau perkembangan dan tindak lanjut evaluasi akuntabilitas kinerja setiap Satuan Kerja.</p>
    </div>
  </div>
</header>
<main class="primer-shell">
    <section class="worksheet-card">
        <div class="worksheet-header">
            <div>
                <span class="section-eyebrow">Daftar Satker</span>
                <h2>Pilih Satuan Kerja untuk Evaluasi</h2>
            </div>
            <div class="worksheet-totals">
                <form method="get" style="display:flex; gap:10px; align-items:center;">
                    <input type="hidden" name="page" value="evaluasi-akip">
                    <select name="tahun" onchange="this.form.submit()" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border);">
                        <?php for($y=2020; $y<=2030; $y++): ?>
                            <option value="<?= $y ?>" <?= $y === $tahun ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background: var(--bg-secondary); color: var(--text-primary);">
                    <tr>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border);">No</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border);">Satuan Kerja</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border);">Tahun Penilaian</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border);">Nilai Evaluasi</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border);">Status</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border);">Unggah LHE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$satkers): ?>
                        <tr><td colspan="5" style="padding: 12px; text-align: center;">Belum ada data Satuan Kerja.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($satkers as $index => $satker): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px;"><?= $index + 1 ?></td>
                            <td style="padding: 12px;">
                                <strong><a href="index.php?page=evaluasi-akip&user_id=<?= h((string)$satker['id']) ?>" style="color: var(--primary); text-decoration: none;"><?= h((string)$satker['unit']) ?></a></strong>
                                <br>
                                <small><a href="index.php?page=evaluasi-akip&user_id=<?= h((string)$satker['id']) ?>" style="color: #3b82f6;">(Klik untuk menuju instrumen)</a></small>
                            </td>
                            <td style="padding: 12px;"><?= $tahun ?></td>
                            <td style="padding: 12px; color: #10b981; font-weight: bold; font-size: 1.2rem;"><?= $satker['grade_akhir'] ? h($satker['grade_akhir']) : '-' ?></td>
                            <td style="padding: 12px; color: var(--text-muted);"><?= $satker['status'] ? h($satker['status']) : 'Belum Dievaluasi' ?></td>
                            <td style="padding: 12px;">
                                <?php if ($satker['lhe_file']): ?>
                                    <a href="data/uploads/<?= h($satker['lhe_file']) ?>" target="_blank" style="display:inline-block; margin-bottom: 5px; color: var(--primary);">Lihat Dokumen</a>
                                <?php endif; ?>
                                <form method="post" enctype="multipart/form-data" style="display: flex; gap: 5px; align-items: center;">
                                    <input type="hidden" name="action" value="upload_lhe">
                                    <input type="hidden" name="satker_id" value="<?= h((string)$satker['id']) ?>">
                                    <input type="file" name="lhe_file" accept=".pdf,.doc,.docx" required style="font-size: 0.8rem; width: 150px;">
                                    <button type="submit" style="padding: 4px 8px; font-size: 0.8rem; border-radius: 4px; border: 1px solid var(--primary); background: var(--primary); color: white; cursor: pointer;">Upload</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php else: ?>

<header class="primer-hero">
  <div class="primer-hero__inner">
    <div class="primer-hero__copy">
      <span class="hero-kicker">Evaluasi SAKIP · Worksheet</span>
      <h1>Lembar Kerja Evaluasi Akuntabilitas Kinerja</h1>
      <p><?= h($satker['unit'] ?? '') ?> Tahun <?= $tahun ?> · Review dan lengkapi evaluasi beserta catatan dan rekomendasi.</p>
    </div>
    <div class="hero-scoreboard">
      <div class="hero-scorecard hero-scorecard--primary"><span>Nilai Evaluator</span><strong><?= number_format(floatval($summary['evaluator_score'] ?? 0), 2) ?></strong><em>Predikat <?= h($summary['evaluator_grade'] ?? 'E') ?></em></div>
      <div class="hero-scorecard"><span>Nilai Satker</span><strong><?= number_format(floatval($summary['satker_score'] ?? 0), 2) ?></strong><em>Predikat <?= h($summary['satker_grade'] ?? 'E') ?></em></div>
    </div>
  </div>
</header>
<main class="primer-shell">
  <section class="overview-grid">
    <article class="overview-card"><span class="overview-label">Komponen</span><strong>4</strong><p>Perencanaan, Pengukuran, Pelaporan, Evaluasi Internal.</p></article>
    <article class="overview-card"><span class="overview-label">Subkomponen</span><strong><?= count($sections) ?></strong><p>Subkomponen terstruktur sesuai LHE asli.</p></article>
    <article class="overview-card"><span class="overview-label">Status</span><strong><?= h($satker['status'] ?? 'Draft') ?></strong><p>Kondisi evaluasi saat ini.</p></article>
    <article class="overview-card">
        <span class="overview-label">Kembali</span>
        <strong><a href="index.php?page=evaluasi-akip" style="color: var(--primary-dark); text-decoration: none;">Dashboard</a></strong>
        <p>Kembali ke daftar Satker.</p>
    </article>
  </section>

  <section class="worksheet-card">
    <div class="worksheet-header">
      <div>
        <span class="section-eyebrow">Lembar Evaluasi</span>
        <h2>Struktur penilaian Kinerja SAKIP</h2>
        <p>Klik ganda (double-click) pada kolom Catatan atau Rekomendasi untuk mengedit. Nilai akan otomatis tersimpan.</p>
      </div>
      <div class="worksheet-totals">
        <div><span>Total Satker</span><strong><?= number_format(floatval($summary['satker_score'] ?? 0), 2) ?></strong></div>
        <div><span>Total Evaluator</span><strong><?= number_format(floatval($summary['evaluator_score'] ?? 0), 2) ?></strong></div>
      </div>
    </div>

    <?php foreach ($sections as $section): ?>
    <section class="component-panel">
      <div class="component-banner">
        <div><span class="component-badge">Komponen <?= h($section['code']) ?></span><h3><?= h($section['title']) ?></h3></div>
        <div class="component-summary"><div><span>Bobot</span><strong><?= number_format((float)$section['weight'], 2) ?></strong></div><div><span>Subtotal Evaluator</span><strong><?= number_format((float)$section['evaluator'], 2) ?></strong></div></div>
      </div>
      
      <?php foreach ($section['subsections'] as $sub): ?>
      <?php 
          $ans = $dn[$sub['code']]['jawaban'] ?? $sub['evaluator_answer'];
          $sc = $dn[$sub['code']]['nilai'] ?? $sub['evaluator_score'];
          $nt = $dn[$sub['code']]['catatan'] ?? $sub['notes'];
          $rek = $dn[$sub['code']]['rekomendasi'] ?? '';
      ?>
      <article class="sheet-block">
        <div class="sheet-table sheet-table--head">
          <div>No</div><div>Komponen</div><div>Bobot</div><div>Evaluator<br><small>Jawaban</small></div><div>Evaluator<br><small>Nilai</small></div><div>Catatan</div><div>Rekomendasi</div><div>Dokumen</div>
        </div>
        <div class="sheet-table sheet-table--body">
          <div class="cell-code"><?= h($sub['code']) ?></div>
          <div class="cell-title"><h4><?= h($sub['title']) ?></h4></div>
          <div class="cell-number"><?= h($sub['weight']) ?></div>
          <div class="cell-answer cell-answer--evaluator">
              <?php $options = ['AA', 'A', 'BB', 'B', 'CC', 'C', 'D', 'E']; ?>
              <select name="eval_jawaban[<?= h($sub['code']) ?>]" 
                      class="eval-jawaban-select" 
                      data-code="<?= h($sub['code']) ?>"
                      data-bobot="<?= h($sub['weight']) ?>" 
                      data-target="eval_nilai_<?= h(str_replace('.', '_', $sub['code'])) ?>"
                      style="width: 100%; border: none; background: transparent; font-weight: bold; color: inherit; text-align: center; appearance: none; cursor: pointer;">
                  <?php foreach ($options as $opt): ?>
                      <option value="<?= $opt ?>" <?= $opt === $ans ? 'selected' : '' ?>><?= $opt ?></option>
                  <?php endforeach; ?>
              </select>
          </div>
          <div class="cell-number cell-number--accent">
              <input type="number" step="0.01" 
                     id="eval_nilai_<?= h(str_replace('.', '_', $sub['code'])) ?>" 
                     value="<?= h((string)$sc) ?>" 
                     readonly 
                     style="width: 100%; text-align: center; background: transparent; border: none; font-weight: bold; color: inherit;">
          </div>
          <div class="cell-note" ondblclick="editField(this, '<?= h($sub['code']) ?>', 'catatan')" style="cursor: pointer;" title="Double-click to edit">
              <div class="display-text"><?= nl2br(h($nt)) ?: '<i style="color:#ccc;">(kosong)</i>' ?></div>
              <textarea style="display:none; width: 100%; height: 80px;" rows="3"><?= h($nt) ?></textarea>
          </div>
          <div class="cell-note" ondblclick="editField(this, '<?= h($sub['code']) ?>', 'rekomendasi')" style="cursor: pointer;" title="Double-click to edit">
              <div class="display-text"><?= nl2br(h($rek)) ?: '<i style="color:#ccc;">(kosong)</i>' ?></div>
              <textarea style="display:none; width: 100%; height: 80px;" rows="3"><?= h($rek) ?></textarea>
          </div>
          <div style="font-size: 0.8rem; color: var(--muted); text-align: center;">-</div>
        </div>
        
        <?php if (!empty($sub['criteria'])): ?>
        <div class="criteria-card">
          <div class="criteria-card__header">Kriteria</div>
          <div class="criteria-table criteria-table--head"><div>No</div><div>Uraian Kriteria</div><div>Dokumen / Evidence</div><div>Catatan Singkat</div></div>
          <?php foreach ($sub['criteria'] as $index => $criterion): ?>
          <?php $cn = $dn[$sub['code']]['kriteria'][$index] ?? $criterion['note']; ?>
          <div class="criteria-table criteria-table--row">
              <div class="criteria-no"><?= $index + 1 ?></div>
              <div class="criteria-text"><?= h($criterion['text']) ?></div>
              <div class="criteria-evidence">
                  <?php foreach ($criterion['evidence'] as $ev): ?>
                      <span class="evidence-chip"><?= h($ev) ?></span>
                  <?php endforeach; ?>
              </div>
              <div class="criteria-note"><?= h($cn) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </section>
    <?php endforeach; ?>
    
  </section>
</main>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Logic to calculate score based on weight and option multiplier
function calculateScore(selectEl) {
    const bobot = parseFloat(selectEl.dataset.bobot) || 0;
    const val = selectEl.value;
    let multiplier = 0;
    switch(val) {
        case 'AA': multiplier = 1.0; break;
        case 'A':  multiplier = 0.9; break;
        case 'BB': multiplier = 0.8; break;
        case 'B':  multiplier = 0.7; break;
        case 'CC': multiplier = 0.6; break;
        case 'C':  multiplier = 0.5; break;
        case 'D':  multiplier = 0.4; break;
        case 'E':  multiplier = 0.0; break;
    }
    const targetId = selectEl.dataset.target;
    const score = bobot * multiplier;
    document.getElementById(targetId).value = score.toFixed(2);
}

function editField(container, code, type) {
    const displayDiv = container.querySelector('.display-text');
    const textarea = container.querySelector('textarea');
    if (!displayDiv || !textarea) return;
    
    displayDiv.style.display = 'none';
    textarea.style.display = 'block';
    textarea.focus();
    
    // Auto-save on blur
    textarea.onblur = function() {
        const value = this.value;
        if(value.trim() === '') {
            displayDiv.innerHTML = '<i style="color:#ccc;">(kosong)</i>';
        } else {
            displayDiv.innerHTML = value.replace(/\n/g, '<br>');
        }
        this.style.display = 'none';
        displayDiv.style.display = 'block';
        
        saveAjax(type, code, value, 0);
    };
}

function saveAjax(type, code, value, score) {
    const formData = new FormData();
    formData.append('action', 'save_eval_ajax');
    formData.append('satker_id', '<?= h((string)$satkerId) ?>');
    formData.append('type', type);
    formData.append('code', code);
    formData.append('value', value);
    if (type === 'jawaban') {
        formData.append('score', score);
    }
    
    fetch('index.php?page=evaluasi-akip', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan otomatis',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
            setTimeout(() => window.location.reload(), 1500); 
        }
    });
}

document.querySelectorAll('.eval-jawaban-select').forEach(select => {
    select.addEventListener('change', function() {
        calculateScore(this);
        const code = this.dataset.code;
        const targetId = this.dataset.target;
        const targetInput = document.getElementById(targetId);
        saveAjax('jawaban', code, this.value, targetInput.value);
    });
});
</script>

<?php render_footer(); ?>
