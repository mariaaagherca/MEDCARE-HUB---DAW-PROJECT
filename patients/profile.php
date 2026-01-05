<?php
require "../auth/guard.php";
require_role(["patient"]);
require "../config/database.php";

$userId = (int)$_SESSION["user_id"];
$stmt = $pdo->prepare("
    INSERT INTO page_visits (user_id, page)
    VALUES (?, ?)
");
$stmt->execute([
    $_SESSION["user_id"] ?? null,
    basename($_SERVER["PHP_SELF"])
]);

/* pacientul */
$stmt = $pdo->prepare("
    SELECT *
    FROM patients
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: complete_profile.php");
    exit;
}

$doctorStmt = $pdo->prepare("
    SELECT d.first_name, d.last_name, u.email
    FROM doctors d
    JOIN patient_assignments pa ON d.user_id = pa.doctor_id
    JOIN users u ON u.id = d.user_id
    WHERE pa.patient_id = ?
      AND pa.active = 1
");
$doctorStmt->execute([$patient["user_id"]]);
$doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);

$message = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim($_POST["first_name"]);
    $last = trim($_POST["last_name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    if ($first === "" || $last === "" || $address === "") {
        $message = "All required fields must be completed.";
        $type = "error";
    } else {
        $stmt = $pdo->prepare("
            UPDATE patients
            SET first_name = ?, last_name = ?, phone = ?, address = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$first, $last, $phone, $address, $userId]);

        $message = "Profile updated successfully.";
        $type = "success";

        $stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
        $stmt->execute([$userId]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | MEDCARE HUB</title>
<style>
body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #1c1c1c, #434343);
    font-family: "Segoe UI", sans-serif;
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 30px;
}
.card {
    width: 100%;
    max-width: 540px;
    background: rgba(255,255,255,0.08);
    border-radius: 18px;
    padding: 40px;
}
.section {
    margin-top: 25px;
    padding: 15px;
    background: rgba(255,255,255,0.06);
    border-radius: 12px;
}
.section h3 { color: #00e5ff; }
input, textarea {
    width: 100%;
    padding: 13px;
    border-radius: 10px;
    border: none;
    margin-bottom: 12px;
}
input[readonly] { background: rgba(255,255,255,0.15); color: #ccc; }
button {
    width: 100%;
    padding: 14px;
    border-radius: 10px;
    background: #00e5ff;
    font-weight: 700;
}
.back {
    display: block;
    text-align: center;
    margin-top: 18px;
    color: #ccc;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="card">
<h1>My Profile</h1>

<?php if ($message): ?>
<div class="<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post">
<input type="text" value="<?= htmlspecialchars($patient["cnp"]) ?>" readonly>
<input type="date" value="<?= htmlspecialchars($patient["date_of_birth"]) ?>" readonly>

<input type="text" name="first_name" value="<?= htmlspecialchars($patient["first_name"]) ?>" required>
<input type="text" name="last_name" value="<?= htmlspecialchars($patient["last_name"]) ?>" required>
<input type="text" name="phone" value="<?= htmlspecialchars($patient["phone"]) ?>">
<textarea name="address" required><?= htmlspecialchars($patient["address"]) ?></textarea>

<button type="submit">Save Changes</button>
</form>

<div class="section">
<h3>Assigned Doctor</h3>

<?php if ($doctor): ?>
<p>
<strong>Dr. <?= htmlspecialchars($doctor["first_name"] . " " . $doctor["last_name"]) ?></strong><br>
Email: <?= htmlspecialchars($doctor["email"]) ?>
</p>
<?php else: ?>
<p>No doctor assigned yet.</p>
<?php endif; ?>
</div>

<a class="back" href="../dashboard/index.php">← Back to dashboard</a>
</div>

</body>
</html>
