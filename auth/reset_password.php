<?php
session_start();
require "../config/database.php";

$success = "";
$error = "";
$emailPrefill = $_GET["email"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $code = trim($_POST["code"]);
    $password = $_POST["password"];

    if ($email === "" || $code === "" || $password === "") {
        $error = "All fields required.";
    } else {

        $stmt = $pdo->prepare("
            SELECT id, reset_expires
            FROM users
            WHERE email = ? AND reset_code = ?
        ");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid code.";
        } elseif (strtotime($user["reset_expires"]) < time()) {
            $error = "Code expired.";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE users
                SET password = ?, reset_code = NULL, reset_expires = NULL
                WHERE id = ?
            ");
            $stmt->execute([$hash, $user["id"]]);

            $success = "Password changed successfully.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<style>
body {
    background: linear-gradient(135deg, #00b4db, #0083b0);
    font-family:"Segoe UI",sans-serif;
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
.success { color:green; }
.error { color:red; }
</style>
</head>
<body>

<div class="card">
<h2>Reset password</h2>

<?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="post">
    <input type="email" name="email" value="<?= htmlspecialchars($emailPrefill) ?>" required>
    <input type="text" name="code" placeholder="6-digit code" required>
    <input type="password" name="password" placeholder="New password" required>
    <button type="submit">Reset password</button>
</form>

<a href="login.php">← Back to login</a>
</div>

</body>
</html>
