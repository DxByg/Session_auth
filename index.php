<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'db.php';

$error = '';
$success = isset($_GET['registered']) ? 'Account created. You can log in now.' : '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM `user` WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['login_time'] = time();
            $_SESSION['session_id'] = session_id();
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Incorrect username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef2f7; min-height: 100vh; display: flex; align-items: center; }
        .auth-card { max-width: 400px; width: 100%; margin: auto; border: 0; border-radius: 12px; box-shadow: 0 8px 30px rgba(20, 40, 80, .12); }
    </style>
</head>
<body>
<div class="card auth-card p-4">
    <h1 class="h4 mb-1">Log in</h1>
    <p class="text-muted mb-4">Use your username or email.</p>

    <?php if ($success): ?>
        <div class="alert alert-success py-2"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="login" class="form-label">Username or email</label>
            <input type="text" class="form-control" id="login" name="login" value="<?= htmlspecialchars($login) ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Log in</button>
    </form>

    <p class="text-center mt-3 mb-0">No account? <a href="register.php">Register</a></p>
</div>

<script>
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) { window.location.reload(); }
    });
</script>
</body>
</html>
