<?php
session_start();
require "../config/database.php";
$stmt = $pdo->prepare("
    INSERT INTO page_visits (user_id, page)
    VALUES (?, ?)
");
$stmt->execute([
    $_SESSION["user_id"] ?? null,
    basename($_SERVER["PHP_SELF"])
]);

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        empty($_POST["csrf_token"]) ||
        empty($_SESSION["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $error = "Security error. Please refresh the page.";
    } elseif (empty($_POST["g-recaptcha-response"])) {
        $error = "Captcha missing.";
    } else {

        $captcha = $_POST["g-recaptcha-response"];
        $secret = "6LdG_DMsAAAAAJTDEDWuFIT81vH_WdpeBpa5z-kE";

        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$captcha}"
        );

        $captchaSuccess = json_decode($verify, true);

        if (empty($captchaSuccess["success"])) {
            $error = "Captcha verification failed.";
        } else {

            $email = trim($_POST["email"]);
            $message = trim($_POST["message"]);

            if ($email === "" || $message === "") {
                $error = "All fields are required.";
            } else {

                $stmt = $pdo->prepare("
                    INSERT INTO help_requests (email, message, status, created_at)
                    VALUES (?, ?, 'pending', NOW())
                ");
                $stmt->execute([$email, $message]);

                $success = "Your message has been sent successfully.";
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Need Help? | MEDCARE HUB</title>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #00b4db, #0083b0);
    font-family: "Segoe UI", sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
}
.card {
    width: 420px;
    background: #fff;
    border-radius: 16px;
    padding: 35px;
}
input, textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 14px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    padding: 14px;
    background: #0083b0;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
}
.success { color: green; margin-bottom: 10px; }
.error { color: red; margin-bottom: 10px; }
.back {
    display: block;
    margin-top: 15px;
    text-align: center;
    color: #0083b0;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="card">
<h2>Need Help?</h2>

<?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">
    <input type="email" name="email" placeholder="Your email address" required>
    <textarea name="message" placeholder="Describe your problem..." required></textarea>

        <div class="g-recaptcha" data-sitekey="6LdG_DMsAAAAABTrgZ3rCrnBksAI-5sGQXJfnPYy"></div>

    <button type="submit">Send message</button>
</form>

<a class="back" href="javascript:history.back()">← Back</a>
</div>

</body>
</html>
