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

if (!isset($_GET["id"])) {
    header("Location: admin_help.php");
    exit;
}

$id = (int)$_GET["id"];

$stmt = $pdo->prepare("SELECT * FROM help_requests WHERE id = ?");
$stmt->execute([$id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    header("Location: admin_help.php");
    exit;
}

/* stări */
$isAnswered   = ($request["status"] === "answered");
$justAnswered = false;

$success = "";
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$isAnswered) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $error = "Security error.";
    } else {

        $response = trim($_POST["response"]);

        if ($response === "") {
            $error = "Response cannot be empty.";
        } else {
            try {
                $mail = getMailer();
                $mail->isHTML(true);
                $mail->addAddress($request["email"]);
                $mail->Subject = "MEDCARE HUB - Support Team Response";

                $mail->Body = "
                    <p>Hello,</p>

                    <p>Thank you for contacting <strong>MEDCARE HUB</strong>.</p>

                    <p><strong>Your message:</strong></p>
                    <blockquote style='background:#f5f5f5;padding:10px;border-left:4px solid #00e5ff;'>
                        " . nl2br(htmlspecialchars($request["message"])) . "
                    </blockquote>

                    <p><strong>Our response:</strong></p>
                    <p>
                        " . nl2br(htmlspecialchars($response)) . "
                    </p>

                    <p>Best regards,<br>
                    <strong>MEDCARE HUB Support Team</strong></p>
                ";

                $mail->send();

                $stmt = $pdo->prepare("
                    UPDATE help_requests
                    SET response = ?, status = 'answered'
                    WHERE id = ?
                ");
                $stmt->execute([$response, $id]);

                $success       = "Response sent successfully.";
                $justAnswered  = true;
                $isAnswered    = false; 
                
                $request["response"] = $response;

            } catch (Exception $e) {
                $error = "Mail error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reply Help | MEDCARE HUB</title>
<style>
body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #141e30, #243b55);
    font-family: "Segoe UI", sans-serif;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
}
.card {
    width: 600px;
    background: rgba(255,255,255,0.08);
    border-radius: 18px;
    padding: 40px;
}
textarea {
    width: 100%;
    min-height: 150px;
    border-radius: 10px;
    padding: 12px;
    border: none;
}
button {
    margin-top: 15px;
    width: 100%;
    padding: 14px;
    background: #00e5ff;
    border: none;
    border-radius: 10px;
    font-weight: 700;
}
.success { color: #69f0ae; margin-bottom: 15px; text-align:center; }
.error   { color: #ff8a80; margin-bottom: 15px; text-align:center; }
.warning { color: #ffd54f; margin-bottom: 15px; text-align:center; }
.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #ccc;
    text-decoration: none;
}
.section {
    margin-top: 25px;
    padding: 15px;
    background: rgba(0,0,0,0.25);
    border-radius: 12px;
}
.section h3 {
    margin-top: 0;
    color: #00e5ff;
}
</style>
</head>
<body>

<div class="card">
<h1>Help Request</h1>

<p><strong>Email:</strong> <?= htmlspecialchars($request["email"]) ?></p>
<p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($request["message"])) ?></p>

<?php if ($isAnswered && !$justAnswered): ?>
    <div class="warning">⚠ This request has already been answered.</div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($request["response"])): ?>
    <div class="section">
        <h3>Response</h3>
        <p><?= nl2br(htmlspecialchars($request["response"])) ?></p>
    </div>
<?php endif; ?>



<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!$isAnswered && !$justAnswered): ?>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">
    <textarea name="response" placeholder="Write your response..." required></textarea>
    <button type="submit">Send Response</button>
</form>
<?php endif; ?>

<a class="back" href="admin_help.php">← Back</a>
</div>

</body>
</html>
