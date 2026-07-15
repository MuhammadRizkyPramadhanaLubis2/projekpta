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
            flash('File LHE berhasil diunggah.');
        }
        header('Location: index.php?page=evaluasi&tahun=' . $tahun);
        exit;
    } elseif (($_POST['action'] ?? '') === 'save_eval') {
        $sid = (int)($_POST['satker_id'] ?? 0);
        if ($sid > 0) {
            $jawaban = $_POST['eval_jawaban'] ?? [];
            $nilai = $_POST['eval_nilai'] ?? [];
            $catatan = $_POST['eval_notes'] ?? [];
            $criteriaNotes = $_POST['criteria_notes'] ?? [];

            $totalScore = 0.0;
            $dataNilai = [];

            foreach ($jawaban as $code => $ans) {
                $sc = (float)($nilai[$code] ?? 0);
                $totalScore += $sc;
                $dataNilai[$code] = [
                    'jawaban' => $ans,
                    'nilai' => $sc,
                    'catatan' => $catatan[$code] ?? '',
                    'kriteria' => $criteriaNotes[$code] ?? []
                ];
            }

            $grade = 'E';
            if ($totalScore >= 90) $grade = 'AA';
            elseif ($totalScore >= 80) $grade = 'A';
            elseif ($totalScore >= 70) $grade = 'BB';
            elseif ($totalScore >= 60) $grade = 'B';
            elseif ($totalScore >= 50) $grade = 'CC';
            elseif ($totalScore >= 30) $grade = 'C';
            elseif ($totalScore >= 10) $grade = 'D';

            $jsonData = json_encode($dataNilai);

            $stmt = db()->prepare('SELECT id FROM evaluasi_sakip WHERE tahun = :t AND satker_id = :sid');
            $stmt->execute(['t' => $tahun, 'sid' => $sid]);
            if ($stmt->fetch()) {
                $upd = db()->prepare('UPDATE evaluasi_sakip SET nilai_akhir = :n, grade_akhir = :g, data_nilai = :dn, evaluator_id = :eid, status = "Evaluasi Selesai", updated_at = CURRENT_TIMESTAMP WHERE tahun = :t AND satker_id = :sid');
                $upd->execute(['n' => $totalScore, 'g' => $grade, 'dn' => $jsonData, 'eid' => $user['id'], 't' => $tahun, 'sid' => $sid]);
            } else {
                $ins = db()->prepare('INSERT INTO evaluasi_sakip (tahun, satker_id, evaluator_id, nilai_akhir, grade_akhir, data_nilai, status) VALUES (:t, :sid, :eid, :n, :g, :dn, "Evaluasi Selesai")');
                $ins->execute(['t' => $tahun, 'sid' => $sid, 'eid' => $user['id'], 'n' => $totalScore, 'g' => $grade, 'dn' => $jsonData]);
            }
            flash('Data evaluasi berhasil disimpan.');
        }
        header('Location: index.php?page=evaluasi&user_id=' . $sid . '&tahun=' . $tahun);
        exit;
    }
}


if (!$satkerId) {
    // Tampilkan Dashboard Evaluasi SAKIP (Daftar Satuan Kerja)
    $satkers = db()->query('SELECT u.id, u.nama, u.role, u.unit, es.nilai_akhir, es.grade_akhir, es.status, es.lhe_file FROM users u LEFT JOIN evaluasi_sakip es ON u.id = es.satker_id AND es.tahun = ' . (int)$tahun . ' WHERE u.status = "active" AND u.unit != "PTA Medan" AND u.role LIKE "Satker%" GROUP BY u.unit ORDER BY u.nama')->fetchAll();
    render_header('Evaluasi SAKIP');
    ?>
    <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Evaluasi (Semar Dashboard)</h2>
            
            <form method="get" class="toolbar" style="margin: 0;">
                <input type="hidden" name="page" value="evaluasi">
                <select name="tahun" onchange="this.form.submit()">
                    <?php for($y=2020; $y<=2030; $y++): ?>
                        <option value="<?= $y ?>" <?= $y === $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="button primary">Cari</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr style="background: var(--bg-secondary); color: var(--text-primary); text-align: center;">
                            <th>No</th>
                            <th style="text-align: left;">Satuan Kerja</th>
                            <th>Pelaksanaan</th>
                            <th>Tahun Penilaian</th>
                            <th>Nilai Mandiri</th>
                            <th>Nilai Evaluasi</th>
                            <th>Status</th>
                            <th>Unggah LHE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$satkers): ?>
                            <tr><td colspan="8" style="text-align: center;">Belum ada data Satuan Kerja.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($satkers as $index => $satker): ?>
                            <tr>
                                <td style="text-align: center;"><?= $index + 1 ?></td>
                                <td>
                                    <strong><a href="index.php?page=evaluasi&user_id=<?= h((string)$satker['id']) ?>" style="color: var(--primary); text-decoration: none;"><?= h((string)$satker['unit']) ?></a></strong>
                                    <br>
                                    <small><a href="index.php?page=evaluasi&user_id=<?= h((string)$satker['id']) ?>" style="color: #3b82f6;">(Klik untuk menuju instrumen)</a></small>
                                    <br><br>
                                    <span style="background: #0ea5e9; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">Laporan Tindaklanjut Tahun <?= $tahun - 1 ?></span>
                                </td>
                                <td style="text-align: center; font-size: 0.9em; color: var(--text-muted);">
                                    01 April <?= $tahun ?><br>s.d<br>30 April <?= $tahun ?>
                                </td>
                                <td style="text-align: center;"><?= $tahun ?></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">AA<br>ðŸ˜Š</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;"><?= $satker['grade_akhir'] ? h($satker['grade_akhir']) : '-' ?><br></td>
                                <td style="text-align: center; color: var(--text-muted);"><?= $satker['status'] ? h($satker['status']) : 'Belum Dievaluasi' ?></td>
                                <td style="text-align: center;">
                                    <?php if ($satker['lhe_file']): ?>
                                        <a href="data/uploads/<?= h($satker['lhe_file']) ?>" target="_blank" class="button secondary" style="padding: 2px 8px; font-size: 0.75rem; margin-bottom: 5px;">Lihat LHE</a>
                                    <?php endif; ?>
                                    <form method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 5px; align-items: center;">
                                        <input type="hidden" name="action" value="upload_lhe">
                                        <input type="hidden" name="satker_id" value="<?= h((string)$satker['id']) ?>">
                                        <input type="file" name="lhe_file" style="max-width: 200px; font-size: 0.8rem;" required>
                                        <button type="submit" class="button primary" style="padding: 4px 12px; font-size: 0.85rem;">Unggah</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php
    render_footer();
    exit;
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

$evalStmt = db()->prepare('SELECT * FROM evaluasi_sakip WHERE tahun = :t AND satker_id = :sid');
$evalStmt->execute(['t' => $tahun, 'sid' => $satkerId]);
$evalData = $evalStmt->fetch();
$dn = $evalData && $evalData['data_nilai'] ? json_decode($evalData['data_nilai'], true) : [];

$summary = [
    'title' => 'Lembar Kerja Evaluasi Akuntabilitas Kinerja',
    'subtitle' => $satkerName . ' Tahun ' . $tahun,
    'satker_score' => '100.00',
    'satker_grade' => 'AA',
    'evaluator_score' => $evalData ? number_format((float)$evalData['nilai_akhir'], 2) : '0.00',
    'evaluator_grade' => $evalData ? $evalData['grade_akhir'] : '-',
    'component_count' => 4,
    'subcomponent_count' => 12,
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
                'weight' => '6',
                'satker_answer' => 'AA',
                'satker_score' => '6',
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
                'weight' => '9',
                'satker_answer' => 'AA',
                'satker_score' => '9',
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
                'weight' => '15',
                'satker_answer' => 'AA',
                'satker_score' => '15',
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
                'weight' => '3',
                'satker_answer' => 'AA',
                'satker_score' => '3',
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
                'weight' => '5',
                'satker_answer' => 'AA',
                'satker_score' => '5',
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
                'weight' => '12.5',
                'satker_answer' => 'AA',
                'satker_score' => '12.5',
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



render_header('Evaluasi - ' . h($satkerName));
?>

<div style="margin-bottom: 15px;">
    <a href="index.php?page=evaluasi" class="button secondary">â† Kembali ke Daftar Satker</a>
</div>

<section class="panel">
    <strong><?= h($summary['title']) ?></strong>
    <p><?= h($summary['subtitle']) ?></p>
    <div style="display: flex; gap: 20px; margin-top: 10px;">
        <div>
            <small class="muted">Nilai Satker</small>
            <div style="font-size: 1.5rem; font-weight: bold;"><?= h($summary['satker_score']) ?> (<?= h($summary['satker_grade']) ?>)</div>
        </div>
        <div>
            <small class="muted">Nilai Evaluator</small>
            <div style="font-size: 1.5rem; font-weight: bold;"><?= h($summary['evaluator_score']) ?> (<?= h($summary['evaluator_grade']) ?>)</div>
        </div>
    </div>
</section>

<form method="POST" action="index.php?page=evaluasi" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_eval">
    <input type="hidden" name="satker_id" value="<?= h((string)$satkerId) ?>">
    <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0;">Lembar Evaluasi</h2>
            <button type="submit" class="button primary">Simpan Evaluasi</button>
        </div>
        
        <div class="table-wrap">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 5%;">No</th>
                            <th rowspan="2" style="width: 35%;">Komponen</th>
                            <th rowspan="2" style="width: 8%;">Bobot</th>
                            <th colspan="2" style="width: 22%;">Evaluator</th>
                            <th rowspan="2" style="width: 15%;">Catatan</th>
                            <th rowspan="2" style="width: 15%;">Dokumen / Bukti</th>
                        </tr>
                        <tr>
                            <th>Jawaban</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sections as $section): ?>
                            <!-- Komponen Utama -->
                            <tr style="background: var(--bg-tertiary);">
                                <td><strong><?= h($section['code']) ?></strong></td>
                                <td colspan="6"><strong><?= h(strtoupper($section['title'])) ?></strong></td>
                            </tr>

                            <?php foreach ($section['subsections'] as $sub): ?>
                                <!-- Subkomponen -->
                                <tr style="background: var(--bg-secondary);">
                                    <td><?= h($sub['code']) ?></td>
                                    <td><strong><?= h($sub['title']) ?></strong></td>
                                    <td style="text-align: center;"><?= h($sub['weight']) ?></td>
                                    <?php 
                                        $ans = $dn[$sub['code']]['jawaban'] ?? $sub['evaluator_answer'];
                                        $sc = $dn[$sub['code']]['nilai'] ?? $sub['evaluator_score'];
                                        $nt = $dn[$sub['code']]['catatan'] ?? $sub['notes'];
                                    ?>
                                    <td>
                                        <?php $options = ['AA', 'A', 'BB', 'B', 'CC', 'C', 'D', 'E']; ?>
                                        <select name="eval_jawaban[<?= h($sub['code']) ?>]" 
                                                class="eval-jawaban-select" 
                                                data-bobot="<?= h($sub['weight']) ?>" 
                                                data-target="eval_nilai_<?= h(str_replace('.', '_', $sub['code'])) ?>">
                                            <?php foreach ($options as $opt): ?>
                                                <option value="<?= $opt ?>" <?= $opt === $ans ? 'selected' : '' ?>><?= $opt ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="number" step="0.01" 
                                               name="eval_nilai[<?= h($sub['code']) ?>]" 
                                               id="eval_nilai_<?= h(str_replace('.', '_', $sub['code'])) ?>" 
                                               value="<?= h((string)$sc) ?>" 
                                               readonly 
                                               style="width: 80px; text-align: center; background: transparent; border: none; font-weight: bold;">
                                    </td>
                                    <td>
                                        <textarea name="eval_notes[<?= h($sub['code']) ?>]" rows="2" placeholder="Catatan evaluator..."><?= h($nt) ?></textarea>
                                    </td>
                                    <td></td>
                                </tr>
                                
                                <!-- Header Kriteria -->
                                <tr>
                                    <td colspan="7" style="font-weight: bold; background: var(--bg-tertiary); color: var(--text-primary);"><small>KRITERIA PENILAIAN</small></td>
                                </tr>

                                <?php foreach ($sub['criteria'] as $index => $criterion): ?>
                                    <!-- Kriteria Individual -->
                                    <tr>
                                        <td style="text-align: right; padding-right: 15px;"><?= $index + 1 ?></td>
                                        <td colspan="4">
                                            <?= h($criterion['text']) ?>
                                        </td>
                                        <?php
                                            $cn = $dn[$sub['code']]['kriteria'][$index] ?? $criterion['note'];
                                        ?>
                                        <td>
                                            <textarea name="criteria_notes[<?= h($sub['code']) ?>][<?= $index ?>]" rows="2" placeholder="Catatan kriteria..."><?= h($cn) ?></textarea>
                                        </td>
                                        <td>
                                            <div style="font-size: 0.85em; margin-bottom: 5px;" class="muted">
                                                Data awal: <strong><?= h(implode(', ', $criterion['evidence'])) ?></strong>
                                            </div>
                                            <input type="file" name="evidence_file[<?= h($sub['code']) ?>][<?= $index ?>]" multiple>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>

                            <!-- Subtotal Per Komponen -->
                            <tr style="background: var(--bg-tertiary); font-weight: bold;">
                                <td colspan="2" style="text-align: right;">SUB TOTAL</td>
                                <td style="text-align: center;"><?= h($section['weight']) ?></td>
                                <td colspan="2" style="text-align: center;"><?= h($section['evaluator']) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background: var(--bg-secondary);">
                            <td colspan="3" style="text-align: right;">Total Bobot / Evaluator Score:</td>
                            <td colspan="2" style="text-align: center; font-size: 1.2em;"><?= h($summary['evaluator_score']) ?></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr style="font-weight: bold; background: var(--bg-secondary);">
                            <td colspan="3" style="text-align: right;">Nilai Akhir (Grade):</td>
                            <td colspan="2" style="text-align: center; font-size: 1.2em; color: var(--accent);">
                                <?= h($summary['evaluator_grade']) ?>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>
</form>

<?php if (isset($flash)): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: '<?= h($flash['type']) === 'error' ? 'error' : 'success' ?>',
                title: <?= json_encode($flash['message']) ?>,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        });
    </script>
<?php endif; ?>

<script>

// Script untuk kalkulasi otomatis nilai berdasarkan jawaban
const multiplierMap = {
    'AA': 1.0,
    'A': 0.9,
    'BB': 0.8,
    'B': 0.7,
    'CC': 0.6,
    'C': 0.5,
    'D': 0.3,
    'E': 0.0
};

function calculateScore(selectEl) {
    const bobot = parseFloat(selectEl.dataset.bobot) || 0;
    const answer = selectEl.value;
    const multiplier = multiplierMap[answer] || 0;
    const targetId = selectEl.dataset.target;
    const targetInput = document.getElementById(targetId);
    
    if (targetInput) {
        const score = bobot * multiplier;
        // Format to 2 decimal places if needed, but remove trailing zeros
        targetInput.value = parseFloat(score.toFixed(2));
    }
}

document.querySelectorAll('.eval-jawaban-select').forEach(select => {
    select.addEventListener('change', function() {
        calculateScore(this);
        // Optional: Trigger recalculation of the total/subtotal scores here if needed
    });
});
</script>

<?php render_footer(); ?>
