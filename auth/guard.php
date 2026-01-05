<?php
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require __DIR__ . "/../config/database.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: /projectphp/auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: /projectphp/auth/login.php");
    exit;
}

function require_role(array $allowed_roles) {
    global $user;

    if (!in_array($user["role"], $allowed_roles)) {
        http_response_code(403);
        echo "<h1>403 Forbidden</h1><p>Access denied.</p>";
        exit;
    }
}
