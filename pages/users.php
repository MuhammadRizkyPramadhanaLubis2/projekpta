<?php
declare(strict_types=1);

require_permission('manage_users');

$currentUser = current_user();
$roles = role_options();
$units = ['PTA Medan', 'Satker PA'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');
    $id = (int) ($_POST['id'] ?? 0);
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = trim((string) ($_POST['password'] ?? ''));
    $nama = trim((string) ($_POST['nama'] ?? ''));
    $role = (string) ($_POST['role'] ?? '');
    $unit = trim((string) ($_POST['unit'] ?? ''));
    $status = (string) ($_POST['status'] ?? 'active');

    if (!isset($roles[$role])) {
        flash('Role pengguna tidak valid.', 'error');
        redirect('users');
    }

    if (!in_array($unit, $units, true)) {
        flash('Unit pengguna tidak valid.', 'error');
        redirect('users');
    }

    if (!in_array($status, ['active', 'inactive'], true)) {
        $status = 'active';
    }

    if ($id === (int) ($currentUser['id'] ?? 0) && $status === 'inactive') {
        flash('Anda tidak dapat menonaktifkan akun yang sedang digunakan.', 'error');
        redirect('users');
    }

    if ($action === 'create') {
        if ($username === '' || $password === '' || $nama === '') {
            flash('Username, password, dan nama wajib diisi.', 'error');
            redirect('users');
        }

        try {
            $stmt = db()->prepare(
                'INSERT INTO users (username, password, nama, role, unit, status)
                 VALUES (:username, :password, :nama, :role, :unit, :status)'
            );
            $stmt->execute([
                'username' => $username,
                'password' => $password,
                'nama' => $nama,
                'role' => $role,
                'unit' => $unit,
                'status' => $status,
            ]);
            flash('Pengguna baru berhasil ditambahkan.');
        } catch (PDOException $e) {
            flash('Username sudah digunakan. Pilih username lain.', 'error');
        }

        redirect('users');
    }

    if ($action === 'update' && $id > 0) {
        if ($nama === '') {
            flash('Nama pengguna wajib diisi.', 'error');
            redirect('users');
        }

        $stmt = db()->prepare(
            'UPDATE users
             SET nama = :nama, role = :role, unit = :unit, status = :status
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'nama' => $nama,
            'role' => $role,
            'unit' => $unit,
            'status' => $status,
        ]);

        if ($password !== '') {
            $passwordStmt = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
            $passwordStmt->execute(['id' => $id, 'password' => $password]);
        }

        flash('Data pengguna berhasil diperbarui.');
        redirect('users');
    }
}

$users = db()
    ->query('SELECT id, username, nama, role, unit, status, created_at FROM users ORDER BY unit, role, nama')
    ->fetchAll();

render_header('Manajemen Pengguna');
?>
<section class="panel summary-panel">
    <div>
        <h2>Manajemen Pengguna</h2>
        <p class="muted">Kelola user berdasarkan pembagian pengguna pada konsep APKIN RPA: PTA Medan dan Satker PA.</p>
    </div>
    <ul class="task-list">
        <li>Admin dan Perencanaan dapat menambah serta memperbarui akun.</li>
        <li>Status nonaktif mencegah user melakukan login.</li>
        <li>Role menentukan kewenangan input, evaluasi, cetak, dan monitoring.</li>
    </ul>
</section>

<section class="panel" style="margin-bottom:24px">
    <h2>Tambah Pengguna</h2>
    <form method="post" class="user-form-grid">
        <input type="hidden" name="action" value="create">
        <label>
            Username
            <input name="username" required>
        </label>
        <label>
            Password Awal
            <input name="password" required>
        </label>
        <label>
            Nama
            <input name="nama" required>
        </label>
        <label>
            Role
            <select name="role">
                <?php foreach ($roles as $role => $label): ?>
                    <option value="<?= h($role) ?>"><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Unit
            <select name="unit">
                <?php foreach ($units as $unit): ?>
                    <option value="<?= h($unit) ?>"><?= h($unit) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </label>
        <button type="submit">Tambah Pengguna</button>
    </form>
</section>

<section class="panel">
    <h2>Daftar Pengguna</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Username</th>
                <th>Nama</th>
                <th>Role</th>
                <th>Unit</th>
                <th>Status</th>
                <th>Reset Password</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= h((string) $user['id']) ?>">
                        <input type="hidden" name="username" value="<?= h((string) $user['username']) ?>">
                        <td>
                            <strong><?= h((string) $user['username']) ?></strong>
                            <?php if ((int) $user['id'] === (int) ($currentUser['id'] ?? 0)): ?>
                                <span class="small-badge">Akun Anda</span>
                            <?php endif; ?>
                        </td>
                        <td><input name="nama" value="<?= h((string) $user['nama']) ?>" required></td>
                        <td>
                            <select name="role">
                                <?php foreach ($roles as $role => $label): ?>
                                    <option value="<?= h($role) ?>" <?= $role === $user['role'] ? 'selected' : '' ?>>
                                        <?= h($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="unit">
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= h($unit) ?>" <?= $unit === $user['unit'] ? 'selected' : '' ?>>
                                        <?= h($unit) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="status">
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </td>
                        <td><input name="password" placeholder="Kosongkan jika tetap"></td>
                        <td><button type="submit" class="secondary">Simpan</button></td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
