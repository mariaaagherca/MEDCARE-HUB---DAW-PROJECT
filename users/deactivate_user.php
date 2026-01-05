<?php
require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../dashboard/index.php");
    exit;
}

if (
    !isset($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Invalid CSRF token");
}

if (!isset($_POST["user_id"])) {
    header("Location: ../dashboard/index.php");
    exit;
}

$userId = (int)$_POST["user_id"];

if ($userId === (int)$_SESSION["user_id"]) {
    die("You cannot deactivate your own account.");
}

$stmt = $pdo->prepare("
    UPDATE users
    SET status = 'disabled'
    WHERE id = ?
");
$stmt->execute([$userId]);

header("Location: " . $_SERVER["HTTP_REFERER"]);
exit;
