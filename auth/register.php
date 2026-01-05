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


$message = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["g-recaptcha-response"])) {
        $message = "Please confirm you are not a robot.";
        $type = "error";
    } else {
        $captcha = $_POST["g-recaptcha-response"];
        $secret = "6LdG_DMsAAAAAJTDEDWuFIT81vH_WdpeBpa5z-kE";

        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha"
        );
        $response = json_decode($verify);

        if (!$response->success) {
            $message = "Captcha verification failed.";
            $type = "error";
        } else {

            $email = trim($_POST["email"]);
            $password = $_POST["password"];

            if ($email === "" || $password === "") {
                $message = "All fields are required.";
                $type = "error";
            } else {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);

                if ($stmt->fetch()) {
                    $message = "Email already registered.";
                    $type = "error";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO users (email, password, role, status)
                        VALUES (?, ?, 'patient', 'pending')
                    ");
                    $stmt->execute([$email, $hash]);

                    $message = "Account created. Await admin approval.";
                    $type = "success";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | MEDCARE HUB</title>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
body {
    margin: 0;
    height: 100vh;
    background: linear-gradient(135deg, #00b4db, #0083b0);
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: "Segoe UI", sans-serif;
}
.card {
    background: #fff;
    width: 380px;
    padding: 35px;
    border-radius: 16px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    text-align: center;
}
input {
    width: 100%;
    padding: 14px;
    margin-bottom: 15px;
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
    font-size: 15px;
    cursor: pointer;
}
button:hover {
    background: #006f96;
}
.msg {
    margin-bottom: 15px;
    font-size: 14px;
}
.error { color: #c62828; }
.success { color: #2e7d32; }
.footer {
    margin-top: 20px;
    font-size: 13px;
}
.footer a {
    color: #0083b0;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="card">
    <h1>Register</h1>

    <?php if ($message): ?>
        <div class="msg <?= $type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email address" required>
        <input type="password" name="password" placeholder="Password" required>

        <div class="g-recaptcha" data-sitekey="6LdG_DMsAAAAABTrgZ3rCrnBksAI-5sGQXJfnPYy"></div>

        <button type="submit">Create account</button>
    </form>

    <div class="footer">
        Already have an account?
        <a href="login.php">Login</a>
        <div class="footer" style="margin-top: 10px;">
            Do you need help?
            <a href="../help/help.php">Contact support</a>
        </div>

    </div>
</div>

</body>
</html>
