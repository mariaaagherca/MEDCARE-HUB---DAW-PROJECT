<?php
require "../auth/guard.php";
require_role(["patient"]);
require "../config/database.php";
$stmt = $pdo->prepare("
    INSERT INTO page_visits (user_id, page)
    VALUES (?, ?)
");
$stmt->execute([
    $_SESSION["user_id"] ?? null,
    basename($_SERVER["PHP_SELF"])
]);

$userId = (int)$_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
$stmt->execute([$userId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    header("Location: ../dashboard/index.php");
    exit;
}

$message = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim($_POST["first_name"]);
    $last = trim($_POST["last_name"]);
    $cnp = trim($_POST["cnp"]);
    $dob = $_POST["date_of_birth"];
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    $ok = true;

    if ($first === "" || $last === "" || $address === "") $ok = false;
    if (!preg_match("/^[0-9]{13}$/", $cnp)) $ok = false;
    if ($dob === "") $ok = false;

    if (!$ok) {
        $message = "Please complete all fields correctly.";
        $type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO patients (user_id, first_name, last_name, cnp, date_of_birth, phone, address)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $first, $last, $cnp, $dob, $phone, $address]);

            $message = "Profile saved successfully.";
            $type = "success";

            header("Location: ../dashboard/index.php");
            exit;
        } catch (PDOException $e) {
            $message = "Error saving profile (CNP might already exist).";
            $type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Profile | MEDCARE HUB</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            font-family: "Segoe UI", sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .card {
            width: 100%;
            max-width: 520px;
            background: rgba(255,255,255,0.08);
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.45);
        }

        h1 {
            margin: 0 0 8px;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #cfd8dc;
            margin-bottom: 25px;
        }

        input, textarea {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            border: none;
            margin-bottom: 12px;
            font-size: 14px;
            outline: none;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #00e5ff;
            color: #000;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            margin-top: 8px;
        }

        button:hover {
            background: #00bcd4;
        }

        .msg {
            margin-bottom: 14px;
            font-size: 14px;
            text-align: center;
            font-weight: 600;
        }

        .success { color: #69f0ae; }
        .error { color: #ff8a80; }

        .hint {
            text-align: center;
            color: #cfd8dc;
            margin-top: 15px;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>Complete Your Profile</h1>
    <div class="subtitle">This is required before using MEDCARE HUB.</div>

    <?php if ($message): ?>
        <div class="msg <?= $type ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="first_name" placeholder="First name" required>
        <input type="text" name="last_name" placeholder="Last name" required>
        <input type="text" name="cnp" placeholder="CNP (13 digits)" required>
        <input type="date" name="date_of_birth" required>
        <input type="text" name="phone" placeholder="Phone (optional)">
        <textarea name="address" placeholder="Address" required></textarea>

        <button type="submit">Save Profile</button>
    </form>

    <div class="hint">After saving, you will be redirected to your dashboard.</div>
</div>

</body>
</html>
