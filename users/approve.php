<?php
session_start();

require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";
require "../lib/mailer.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pending.php");
    exit;
}

if (
    !isset($_POST["csrf_token"]) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Security error");
}

$userId = (int)$_POST["user_id"];

$stmt = $pdo->prepare("
    SELECT email, status
    FROM users
    WHERE id = ? AND role = 'patient'
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["status"] !== "pending") {
    header("Location: pending.php");
    exit;
}

$stmt = $pdo->prepare("
    UPDATE users
    SET status = 'active'
    WHERE id = ?
");
$stmt->execute([$userId]);

try {
    $mail = getMailer();
    $mail->addAddress($user["email"]);
    $mail->Subject = "MEDCARE HUB - Account approved";

    $mail->Body = "
        <p>Hello,</p>

        <p>Your MEDCARE HUB account has been <strong>approved by the administrator</strong>.</p>

        <p>You can now log in using the email and password you registered with.</p>

        <p>
            👉 <a href='https://mgherca.daw.ssmr.ro/projectphp/auth/login.php'>
                Login to MEDCARE HUB
            </a>
        </p>

        <p>
            Best regards,<br>
            <strong>MEDCARE HUB Team</strong>
        </p>
    ";

    $mail->isHTML(true);
    $mail->send();

} catch (Exception $e) {
}

header("Location: pending.php");
exit;
