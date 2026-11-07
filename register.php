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
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
        $error = 'Username must be 3-20 characters: letters, numbers or underscore.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($password === '') {
        $error = 'Enter a password.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT email, username FROM `user` WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$email, $username]);
        $existing = $stmt->fetch();

        if ($existing) {
            $error = strcasecmp($existing['email'], $email) === 0
                ? 'That email is already registered.'
                : 'That username is taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO `user` (username, email, password_hash) VALUES (?, ?, ?)');
            $insert->execute([$username, $email, $hash]);
            header('Location: index.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef2f7; min-height: 100vh; display: flex; align-items: center; }
        .auth-card { max-width: 400px; width: 100%; margin: auto; border: 0; border-radius: 12px; box-shadow: 0 8px 30px rgba(20, 40, 80, .12); }
    </style>
</head>
<body>
<div class="card auth-card p-4">
    <h1 class="h4 mb-1">Create an account</h1>
    <p class="text-muted mb-4">Any password works, even one word.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" pattern="[A-Za-z0-9_]{3,20}" minlength="3" maxlength="20" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm" class="form-label">Confirm password</label>
            <input type="password" class="form-control" id="confirm" name="confirm" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <p class="text-center mt-3 mb-0">Already registered? <a href="index.php">Log in</a></p>
</div>

<script>
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) { window.location.reload(); }
    });
</script>
</body>
</html>
