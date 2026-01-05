<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . "/../config/database.php";

$page = basename($_SERVER["PHP_SELF"]);
$userId = $_SESSION["user_id"] ?? null;
$ip = $_SERVER["REMOTE_ADDR"] ?? null;

$stmt = $pdo->prepare("
    INSERT INTO page_visits (page, user_id, ip_address)
    VALUES (?, ?, ?)
");
$stmt->execute([$page, $userId, $ip]);
