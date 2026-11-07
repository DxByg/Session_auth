<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'db.php';

$stmt = $pdo->prepare('SELECT id, username, email, created_at FROM `user` WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit;
}

$sid = session_id();
$loginTime = $_SESSION['login_time'] ?? time();
$browser = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef2f7; min-height: 100vh; }
        .panel { border: 0; border-radius: 12px; box-shadow: 0 8px 30px rgba(20, 40, 80, .12); }
        .explore-card { border: 0; border-radius: 12px; box-shadow: 0 4px 16px rgba(20, 40, 80, .08); cursor: pointer; height: 100%; }
        .explore-card:hover { box-shadow: 0 6px 22px rgba(20, 40, 80, .18); }
        code { word-break: break-all; }
        .mini { font-size: .9rem; margin: 0; }
        .ticker { overflow: hidden; white-space: nowrap; margin-top: 1.5rem; pointer-events: none; user-select: none; }
        .ticker-track { display: inline-block; padding-left: 100%; animation: ticker-move 22s linear infinite; color: #4a5a70; font-size: 1.05rem; }
        @keyframes ticker-move { from { transform: translateX(0); } to { transform: translateX(-100%); } }
        @media (prefers-reduced-motion: reduce) { .ticker-track { animation-duration: 60s; } }
        .view { display: none; }
        .view.active { display: block; }
    </style>
</head>
<body>
<nav class="navbar bg-white shadow-sm mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1">SessionAuthSystem Dashboard</span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Log out</a>
    </div>
</nav>

<div class="container pb-5">

    <div class="view active" id="view-main">
        <div class="card panel p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h4 mb-1">Welcome, <?= htmlspecialchars($user['username']) ?></h1>
                    <p class="text-muted mb-0"><?= ("This page is protected and only visible to logged-in users") ?></p>
                </div>
                <span class="badge text-bg-success">Active</span>
            </div>
        </div>

        <h2 class="h6 text-muted mb-3">Explore</h2>
        <div class="row g-3">
            
            <div class="col-md-4">
                <div class="card explore-card p-3" data-open="view-status">
                    <h3 class="h6">Account status</h3>
                    <p class="mini text-muted">Status: Active</p>
                    <p class="mini text-muted">Login state: Logged in</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card explore-card p-3" data-open="view-details">
                    <h3 class="h6">User details</h3>
                    <p class="mini text-muted">ID: <?= (int)$user['id'] ?></p>
                    <p class="mini text-muted">Username: <?= htmlspecialchars($user['username']) ?></p>
                    <p class="mini text-muted">Joined: <?= htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card explore-card p-3" data-open="view-session">
                    <h3 class="h6">Active session ID</h3>
                    <p class="mini text-muted">Current Session ID: <?= htmlspecialchars(substr($sid, 0, 8)) ?>...</p>
                    <p class="mini text-muted">Logged in: <?= htmlspecialchars(date('g:i A', $loginTime)) ?></p>
                </div>
            </div>
        </div>

        <div class="ticker" aria-label="This is a demo dashboard. You can log out and log back in to see session regeneration in action.">
            <span class="ticker-track">This is a demo dashboard. You can log out and log back in to see session regeneration in action.</span>
        </div>
    </div>

    <div class="view" id="view-details">
        <button class="btn btn-link px-0 mb-2" data-open="view-main">&larr; Back</button>
        <div class="card panel p-4">
            <h2 class="h5 mb-3">User details</h2>
            <table class="table mb-0">
                <tbody>
                    <tr><th style="width: 35%;">User ID</th><td><?= (int)$user['id'] ?></td></tr>
                    <tr><th>Username</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
                    <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
                    <tr><th>Member since</th><td><?= htmlspecialchars(date('F j, Y g:i A', strtotime($user['created_at']))) ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="view" id="view-status">
        <button class="btn btn-link px-0 mb-2" data-open="view-main">&larr; Back</button>
        <div class="card panel p-4">
            <h2 class="h5 mb-3">Account status</h2>
            <table class="table mb-0">
                <tbody>
                    <tr><th style="width: 35%;">Status</th><td><span class="badge text-bg-success">Active</span></td></tr>
                    <tr><th>Login state</th><td>Logged in</td></tr>
                    <tr><th>Password storage</th><td>Hashed with password_hash()</td></tr>
                    <tr><th>Session protection</th><td>ID regenerated at login</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="view" id="view-session">
        <button class="btn btn-link px-0 mb-2" data-open="view-main">&larr; Back</button>
        <div class="card panel p-4">
            <h2 class="h5 mb-3">Active session</h2>
            <table class="table mb-0">
                <tbody>
                    <tr><th style="width: 35%;">Session ID</th><td><code><?= htmlspecialchars($sid) ?></code></td></tr>
                    <tr><th>Logged in at</th><td><?= htmlspecialchars(date('F j, Y g:i:s A', $loginTime)) ?></td></tr>
                    <tr><th>IP address</th><td><?= htmlspecialchars($ip) ?></td></tr>
                    <tr><th>Browser</th><td><?= htmlspecialchars($browser) ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('[data-open]').forEach(function (el) {
        el.addEventListener('click', function () {
            document.querySelectorAll('.view').forEach(function (v) { v.classList.remove('active'); });
            document.getElementById(el.dataset.open).classList.add('active');
            window.scrollTo(0, 0);
        });
    });

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) { window.location.reload(); }
    });
</script>
</body>
</html>