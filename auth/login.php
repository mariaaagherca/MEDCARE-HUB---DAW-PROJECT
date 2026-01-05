<?php 
session_start();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

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

    if (
            !isset($_POST["csrf_token"]) ||
            !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
        ) {
            $message = "Invalid request.";
            $type = "error";
        } else {

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

            $stmt = $pdo->prepare("SELECT id, password, status, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user["password"])) {
                $message = "Invalid email or password.";
                $type = "error";
            } elseif ($user["status"] !== "active") {
                $message = "Your account is not active.";
                $type = "warning";
            }
             else {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["role"] = $user["role"];

                header("Location: ../dashboard/index.php");
                exit;
            }
        }
    }}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEDCARE HUB | Login</title>

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

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #0083b0;
            color: white;
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

        .error {
            color: #c62828;
        }

        .warning {
            color: #ef6c00;
        }

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
    <h1>MEDCARE HUB</h1>
    <p class="subtitle">Secure medical access</p>

    <?php if ($message): ?>
        <div class="msg <?= $type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">
        <input type="email" name="email" placeholder="Email address" required>
        <input type="password" name="password" placeholder="Password" required>

        <div class="g-recaptcha" data-sitekey="6LdG_DMsAAAAABTrgZ3rCrnBksAI-5sGQXJfnPYy"></div>
        <br>
        <button type="submit">Login</button>
    </form>

    <div class="footer">
        <a href="forgot_password.php">Forgot password?</a>
        <br> <br>
        Don’t have an account?
        <a href="register.php">Create one</a>
        <div class="footer" style="margin-top: 10px;">
            Do you need help?
            <a href="../help/help.php">Contact support</a>
        </div>
    </div>
</div>

</body>
</html>
