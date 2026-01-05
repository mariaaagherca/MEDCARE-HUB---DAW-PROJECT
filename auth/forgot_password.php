<?php
session_start();
require "../config/database.php";
require "../lib/mailer.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);

    if ($email === "") {
        $error = "Email required.";
    } else {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            $code = random_int(100000, 999999);
            $expires = date("Y-m-d H:i:s", time() + 900);

            $stmt = $pdo->prepare("
                UPDATE users
                SET reset_code = ?, reset_expires = ?
                WHERE id = ?
            ");
            $stmt->execute([$code, $expires, $user["id"]]);

            try {
                $mail = getMailer();
                $mail->addAddress($email);
                $mail->Subject = "MEDCARE HUB - Password Reset";
                $mail->isHTML(true);
                $mail->Body = "
                    <p>Hello,</p>
                    <p>Your password reset code is:</p>
                    <h2>$code</h2>
                    <p>This code is valid for 15 minutes.</p>
                    <p>MEDCARE HUB Team</p>
                ";
                $mail->send();

                header("Location: reset_password.php?email=" . urlencode($email));
                exit;

            } catch (Exception $e) {
                $error = "Email sending failed.";
            }

        } else {
            $error = "Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<style>
body {
    background: linear-gradient(135deg, #00b4db, #0083b0);
    font-family: "Segoe UI", sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.card {
    background:#fff;
    padding:35px;
    width:360px;
    border-radius:16px;
}
input, button {
    width:100%;
    padding:12px;
    margin-bottom:12px;
}
button {
    background:#0083b0;
    color:#fff;
    border:none;
    border-radius:8px;
}
.error { color:red; }
</style>
</head>
<body>

<div class="card">
<h2>Forgot password</h2>

<?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="post">
    <input type="email" name="email" placeholder="Your email" required>
    <button type="submit">Send reset code</button>
</form>

<a href="login.php">← Back to login</a>
</div>

</body>
</html>
