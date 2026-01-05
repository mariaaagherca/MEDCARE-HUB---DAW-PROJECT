<?php
require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";
$stmt = $pdo->prepare("
    INSERT INTO page_visits (user_id, page)
    VALUES (?, ?)
");
$stmt->execute([
    $_SESSION["user_id"] ?? null,
    basename($_SERVER["PHP_SELF"])
]);

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$patientsStmt = $pdo->prepare("
    SELECT id, email
    FROM users
    WHERE role = 'patient'
      AND status = 'active'
    ORDER BY email
");
$patientsStmt->execute();
$patients = $patientsStmt->fetchAll(PDO::FETCH_ASSOC);

$doctorsStmt = $pdo->prepare("
    SELECT id, email
    FROM users
    WHERE role = 'doctor'
      AND status = 'active'
    ORDER BY email
");
$doctorsStmt->execute();
$doctors = $doctorsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid CSRF token");
    }

    $patientId = (int)$_POST["patient_id"];
    $doctorId  = (int)$_POST["doctor_id"];

    $pdo->prepare("
        UPDATE patient_assignments
        SET active = 0
        WHERE patient_id = ?
    ")->execute([$patientId]);

    $pdo->prepare("
        INSERT INTO patient_assignments (patient_id, doctor_id, active)
        VALUES (?, ?, 1)
    ")->execute([$patientId, $doctorId]);

    header("Location: patients.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Patient | MEDCARE HUB</title>
</head>
<body>

<h2>Assign Patient to Doctor</h2>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">

    <label>Patient:</label><br>
    <select name="patient_id" required>
        <?php foreach ($patients as $p): ?>
            <option value="<?= $p["id"] ?>">
                <?= htmlspecialchars($p["email"]) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Doctor:</label><br>
    <select name="doctor_id" required>
        <?php foreach ($doctors as $d): ?>
            <option value="<?= $d["id"] ?>">
                <?= htmlspecialchars($d["email"]) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <button type="submit">Assign</button>
</form>

<br>
<a href="patients.php">← Back</a>

</body>
</html>
