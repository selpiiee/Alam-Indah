<?php
require __DIR__ . '/includes/bootstrap.php';
if (user())
    redirect('overview.php');
$error = null;
$email = '';
if (is_post()) {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    try {
        $u = row("SELECT * FROM users WHERE LOWER(email)=LOWER(?)", [$email]);
        if (!$u || !password_verify($pass, $u['password_hash']))
            $error = 'Incorrect email or password. Please check and try again.';
        elseif (!$u['is_active'])
            $error = 'This account is inactive. Contact a Super Admin to reactivate it.';
        else {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role'], 'must_change' => (bool) $u['must_change_password']];
            q("UPDATE users SET last_login_at=NOW() WHERE id=?", [$u['id']]);
            $back = $_SESSION['back'] ?? null;
            unset($_SESSION['back']);
            redirect($back && str_starts_with($back, '/') ? $back : 'overview.php');
        }
    } catch (PDOException $ex) {
        $error = 'Cannot connect to the database. Check config/config.php and make sure PostgreSQL is running.';
    }
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign In · Alam Indah</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="<?= url('assets/vendor/phosphor/regular/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/vendor/phosphor/fill/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>

<body data-base="<?= e(BASE) ?>">
    <div class="login">
        <div class="login-card">
            <img src="<?= url('assets/img/logo.svg') ?>" alt="Alam Indah" width="112" height="62">
            <div>
                <h1>Sign in to Continue</h1>
                <p class="sub">Please sign in to start your Inventory Management System</p>
            </div>
            <form method="post" novalidate>
                <?= csrf_field() ?>
                <?php if ($error): ?>    <?= alert('err', 'Sign in failed', e($error)) ?><?php endif; ?>
                <label class="field-ico"><?= ico('envelope-simple') ?><input type="email" name="email"
                        placeholder="Email" value="<?= e($email) ?>" required autofocus autocomplete="username"></label>
                <label class="field-ico"><?= ico('lock-simple') ?><input type="password" id="pw" name="password"
                        placeholder="Password" required autocomplete="current-password"><button type="button"
                        data-toggle-pw="#pw" aria-label="Show password"><?= ico('eye') ?></button></label>
                <button class="btn btn-login" type="submit"><?= ico('sign-in') ?> Sign In</button>
            </form>
            <div class="demo-box"><b style="color:var(--heading)">Demo accounts</b> (password:
                <code>password123</code>)<br>
                Super Admin — rina@alamindah.co.id<br>Admin — budi@alamindah.co.id<br>Staff — siti@alamindah.co.id</div>
        </div>
    </div>
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="toast-wrap"><?php foreach ($_SESSION['flash'] as $f): ?>
                <div class="toast"><?= ico('check-circle', true, 'ok') ?><span><?= $f['m'] ?></span></div>
            <?php endforeach;
        unset($_SESSION['flash']); ?>
        </div><?php endif; ?>
    <script src="<?= url('assets/js/app.js') ?>"></script>
</body>

</html>