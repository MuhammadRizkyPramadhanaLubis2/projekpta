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
        flash('Berhasil masuk sebagai ' . $user['nama']);
        redirect('dashboard');
    }

    $error = 'Username atau password salah.';
}

render_header('Masuk');
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap');

.login-card-nature {
    width: 100%;
    max-width: 850px;
    min-height: 480px;
    background: url('assets/paluhakim.png');
    background-size: cover;
    background-position: center;
    border-radius: 35px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    position: relative;
    z-index: 10;
    margin: 40px;
    display: flex;
    overflow: hidden;
}

/* Gradient overlay on the card so the white text is readable */
.login-card-nature::before {
    content: '';
    position: absolute;
    inset: 0;
    /* Dark gradient on the left for the form, fading slightly on the right */
    background: linear-gradient(90deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.4) 60%, rgba(0, 0, 0, 0.2) 100%);
    z-index: 1;
}



.nature-left {
    flex: 1;
    padding: 60px 40px 60px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.nature-left h1 {
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 30px;
    color: #fff;
    text-align: center;
    margin-right: 20px;
}

.nature-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
    max-width: 320px;
}

.nature-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.nature-input-group i.input-icon {
    position: absolute;
    left: 18px;
    color: rgba(255,255,255,0.9);
    font-size: 1.2rem;
}

.password-toggle {
    position: absolute;
    right: 18px;
    color: rgba(255,255,255,0.6);
    font-size: 1.2rem;
    cursor: pointer;
    transition: color 0.2s;
    background: none;
    border: none;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: rgba(255,255,255,1);
}

.nature-form input {
    width: 100%;
    padding: 14px 20px 14px 50px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255,255,255,0.7);
    border-radius: 50px;
    color: #fff;
    font-size: 0.95rem;
    outline: none;
    transition: all 0.2s;
    box-sizing: border-box;
}

.nature-form input::placeholder {
    color: rgba(255,255,255,0.8);
}

.nature-form input:focus {
    border-color: #fff;
    background: rgba(255, 255, 255, 0.25);
}

.nature-links {
    margin-top: 4px;
    margin-bottom: 8px;
    padding-left: 15px;
}

.nature-links a {
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    font-size: 0.85rem;
}

.nature-links a:hover {
    text-decoration: underline;
}

.nature-btn {
    background: #f8fafc;
    color: #5b9e32;
    border: none;
    padding: 14px 20px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    transition: transform 0.2s, background 0.2s;
    margin-top: 8px;
}

.nature-btn:hover {
    transform: translateY(-2px);
    background: #ffffff;
}

.nature-footer {
    margin-top: 24px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.9);
    text-align: center;
}

.nature-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 40px;
    position: relative;
    z-index: 2;
}

.nature-right h2 {
    font-family: 'Great Vibes', cursive;
    font-size: 3.8rem;
    color: #fff;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.15);
    margin: 0;
    line-height: 1.2;
    text-align: right;
    width: 100%;
}

.nature-right .back-text {
    font-family: 'Great Vibes', cursive;
    font-size: 3.8rem;
    color: #fff;
    text-shadow: 1px 1px 4px rgba(0,0,0,0.15);
    margin: 0;
    line-height: 1.2;
    text-align: right;
    width: 100%;
    margin-right: 40px;
}

.login-error {
    background: rgba(255, 0, 0, 0.15);
    color: #fff;
    border: 1px solid rgba(255, 0, 0, 0.3);
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    width: 100%;
    max-width: 320px;
}

/* Pastikan tombol kembali berada di atas segalanya */
.login-back-btn {
    position: absolute;
    top: 24px;
    left: 24px;
    z-index: 100;
}

@media (max-width: 768px) {
    .login-card-nature {
        flex-direction: column;
        border-radius: 20px;
    }
    .nature-right {
        display: none; /* Hide the text/leaves on mobile for better fit */
    }
    .nature-left {
        padding: 40px 30px;
        align-items: center;
    }
    .nature-left h1 {
        margin-right: 0;
    }
}
</style>

<a href="index.php?page=beranda" class="login-back-btn">
    <i class="ph ph-arrow-left"></i> Kembali ke Beranda
</a>

<div class="login-card-nature">
    <div class="nature-left">
        <h1>Masuk</h1>
        
        <?php if ($error): ?>
            <div class="login-error"><?= h($error) ?></div>
        <?php endif; ?>
        
        <form method="post" class="nature-form">
            <div class="nature-input-group">
                <i class="ph ph-envelope input-icon"></i>
                <input type="text" name="username" placeholder="Username" autocomplete="username" required autofocus>
            </div>
            
            <div class="nature-input-group">
                <i class="ph ph-lock-key input-icon"></i>
                <input type="password" id="password-input" name="password" placeholder="Password" autocomplete="current-password" required>
                <button type="button" class="password-toggle" id="toggle-password" title="Tampilkan Password">
                    <i class="ph ph-eye"></i>
                </button>
            </div>
            
            <div class="nature-links">
                <a href="#">Forgot Password?</a>
            </div>
            
            <button type="submit" class="nature-btn">Masuk</button>
        </form>
        
        <div class="nature-footer">
            Contoh akun: <strong>admin / admin123</strong>
        </div>
    </div>
    
    <div class="nature-right">
        <h2>Welcome</h2>
        <div class="back-text">Back</div>
    </div>
</div>

<?php render_footer(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('#toggle-password');
    const passwordInput = document.querySelector('#password-input');
    const toggleIcon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'text') {
            toggleIcon.classList.remove('ph-eye');
            toggleIcon.classList.add('ph-eye-slash');
            this.setAttribute('title', 'Sembunyikan Password');
        } else {
            toggleIcon.classList.remove('ph-eye-slash');
            toggleIcon.classList.add('ph-eye');
            this.setAttribute('title', 'Tampilkan Password');
        }
    });
});
</script>
