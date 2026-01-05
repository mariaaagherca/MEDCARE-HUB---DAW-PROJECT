<?php
require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";
require "../lib/mailer.php";

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
    $email = trim($_POST["email"]);
    $plainPassword = $_POST["password"];
    $password = password_hash($plainPassword, PASSWORD_DEFAULT);

    $first = trim($_POST["first_name"]);
    $last = trim($_POST["last_name"]);
    $spec = trim($_POST["specialization"]);
    $phone = trim($_POST["phone"]);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO users (email, password, role, status)
            VALUES (?, ?, 'doctor', 'active')
        ");
        $stmt->execute([$email, $password]);

        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO doctors (user_id, first_name, last_name, specialization, phone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $first, $last, $spec, $phone]);

        /* EMAIL */
        $mail = getMailer();
        $mail->addAddress($email);
        $mail->Subject = "Your MEDCARE HUB Doctor Account";

        $mail->Body = "
            <p>Hello Dr. <strong>{$first} {$last}</strong>,</p>

            <p>An administrator from <strong>MEDCARE HUB</strong> has created a doctor account for you.</p>

            <p><strong>Account details:</strong></p>
            <ul>
                <li>Email: {$email}</li>
                <li>Password: <strong>{$plainPassword}</strong></li>
                <li>Specialization: {$spec}</li>
            </ul>

            <p>You can now log in using your credentials and access the MEDCARE HUB platform.</p>

        <p>
            👉 <a href='https://mgherca.daw.ssmr.ro/projectphp/auth/login.php'>
                Login to MEDCARE HUB
            </a>
        </p>

            <p>For security reasons, we recommend changing your password after first login.</p>

            <br>
            <p>Best regards,<br>
            <strong>MEDCARE HUB Administration</strong></p>
        ";

        $mail->isHTML(true);
        $mail->send();

        $pdo->commit();

        $message = "Doctor created successfully and email sent.";
        $type = "success";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error creating doctor or sending email.";
        $type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Doctor | MEDCARE HUB</title>

<style>
body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #232526, #414345);
    font-family: "Segoe UI", sans-serif;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
}
.card {
    background: rgba(255,255,255,0.08);
    padding: 40px;
    width: 420px;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.4);
    text-align: center;
}
h1 { margin-bottom: 8px; }
.subtitle { color: #ccc; margin-bottom: 20px; }
input {
    width: 100%;
    padding: 13px;
    margin-bottom: 12px;
    border-radius: 8px;
    border: none;
}
button {
    width: 100%;
    padding: 14px;
    background: #00e5ff;
    color: #000;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    margin-top: 10px;
}
.msg { margin-bottom: 15px; }
.success { color: #69f0ae; }
.error { color: #ff8a80; }
.back {
    margin-top: 20px;
    display: block;
    color: #fff;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="card">
<h1>Create Doctor</h1>
<p class="subtitle">Administrator panel</p>

<?php if ($message): ?>
    <div class="msg <?= $type ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="email" name="email" placeholder="Doctor email" required>
    <input type="password" name="password" placeholder="Password" required>

    <input type="text" name="first_name" placeholder="First name" required>
    <input type="text" name="last_name" placeholder="Last name" required>
    <input type="text" name="specialization" placeholder="Specialization" required>
    <input type="text" name="phone" placeholder="Phone number" required>

    <button type="submit">Create Doctor</button>
</form>

<a class="back" href="../dashboard/index.php">← Back to dashboard</a>
</div>

</body>
</html>
