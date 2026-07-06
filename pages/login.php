<?php
declare(strict_types=1);

if (current_user()) {
    redirect('dashboard');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = trim((string) ($_POST['password'] ?? ''));

    $stmt = db()->prepare(
        'SELECT id, username, nama, role, unit, status
         FROM users
         WHERE username = :username AND password = :password AND status = "active"'
    );
    $stmt->execute([
        'username' => $username,
        'password' => $password,
    ]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        redirect('dashboard');
    }

    $error = 'Username atau password salah.';
}

render_header('Login');
?>
<a href="index.php?page=beranda" class="login-back-btn">
    <i class="ph ph-arrow-left"></i> Kembali ke Beranda
</a>
<section class="login-card">
    <h1>APLIKASI IKPA</h1>
    <p>Masuk sesuai role pengguna PTA Medan.</p>

    <?php if ($error): ?>
        <div class="alert error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-grid">
        <label>
            Username
            <input name="username" autocomplete="username" required autofocus>
        </label>
        <label>
            Password
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button type="submit">Masuk</button>
    </form>
    <p class="muted">Contoh akun: admin / admin123</p>
</section>
<?php render_footer(); ?>

