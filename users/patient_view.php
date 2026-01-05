<?php
require "../auth/guard.php";
require_role(["administrator", "doctor"]);
require "../config/database.php";
require "../analytics/log_visit.php";

$role = $_SESSION["role"];

$backUrl = ($role === "administrator")
    ? "../users/patients.php"
    : "../patients/patients_list.php";

if (!isset($_GET["id"])) {
    header("Location: $backUrl");
    exit;
}

$userId = (int)$_GET["id"];

$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.email,
        u.status,
        u.created_at,
        p.first_name,
        p.last_name,
        p.cnp,
        p.date_of_birth,
        p.phone,
        p.address
    FROM users u
    LEFT JOIN patients p ON p.user_id = u.id
    WHERE u.id = ? AND u.role = 'patient'
");
$stmt->execute([$userId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: $backUrl");
    exit;
}

$assignedDoctorStmt = $pdo->prepare("
    SELECT u.id, u.email
    FROM patient_assignments pa
    JOIN users u ON u.id = pa.doctor_id
    WHERE pa.patient_id = ?
      AND pa.active = 1
");
$assignedDoctorStmt->execute([$userId]);
$assignedDoctor = $assignedDoctorStmt->fetch(PDO::FETCH_ASSOC);

$doctors = [];
if ($role === "administrator") {
    $doctorsStmt = $pdo->query("
        SELECT id, email
        FROM users
        WHERE role = 'doctor'
        ORDER BY email
    ");
    $doctors = $doctorsStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Profile | MEDCARE HUB</title>

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
    padding: 30px;
}
.card {
    width: 100%;
    max-width: 700px;
    background: rgba(255,255,255,0.08);
    border-radius: 18px;
    padding: 40px;
    box-shadow: 0 25px 70px rgba(0,0,0,0.45);
}
h1 {
    text-align: center;
    margin-bottom: 25px;
}
.row {
    margin-bottom: 12px;
    font-size: 15px;
}
.label {
    color: #00e5ff;
    font-weight: 600;
}
hr {
    margin: 30px 0;
    border-color: rgba(255,255,255,0.2);
}
select, button {
    padding: 10px;
    border-radius: 6px;
    border: none;
    font-size: 14px;
}
button {
    cursor: pointer;
}
.btn-main {
    background: #00e5ff;
}
.btn-danger {
    background: #e53935;
    color: #fff;
}
.back {
    display: block;
    margin-top: 30px;
    text-align: center;
    color: #ccc;
    text-decoration: none;
}
.actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
</style>
</head>
<body>

<div class="card">
<h1>Patient Profile</h1>

<div class="row"><span class="label">Email:</span> <?= htmlspecialchars($patient["email"]) ?></div>
<div class="row"><span class="label">Name:</span>
    <?= htmlspecialchars($patient["first_name"] . " " . $patient["last_name"]) ?>
</div>
<div class="row"><span class="label">CNP:</span> <?= htmlspecialchars($patient["cnp"]) ?></div>
<div class="row"><span class="label">Birth date:</span> <?= htmlspecialchars($patient["date_of_birth"]) ?></div>
<div class="row"><span class="label">Phone:</span> <?= htmlspecialchars($patient["phone"]) ?></div>
<div class="row"><span class="label">Address:</span> <?= htmlspecialchars($patient["address"]) ?></div>
<div class="row"><span class="label">Status:</span> <?= htmlspecialchars($patient["status"]) ?></div>
<div class="row"><span class="label">Registered:</span> <?= htmlspecialchars($patient["created_at"]) ?></div>

<?php if ($role === "administrator"): ?>
<hr>

<div class="row">
    <span class="label">Assigned Doctor:</span>
    <?= $assignedDoctor ? htmlspecialchars($assignedDoctor["email"]) : "Not assigned" ?>
</div>

<form method="post" action="assign_doctor_inline.php">
    <input type="hidden" name="patient_id" value="<?= $userId ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">

    <div class="row">
        <span class="label">Assign / Change doctor:</span><br><br>
        <select name="doctor_id" required>
            <?php foreach ($doctors as $d): ?>
                <option value="<?= $d["id"] ?>">
                    <?= htmlspecialchars($d["email"]) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="actions">
        <button type="submit" class="btn-main">Save</button>

        <a href="patient_pdf.php?id=<?= $userId ?>" target="_blank">
            <button type="button" class="btn-main">
                Download patient profile (PDF)
            </button>
        </a>
    </div>
</form>

<hr>

<form method="post" action="delete_patient.php"
      onsubmit="return confirm('Are you sure you want to delete this patient? This action is irreversible!');">

    <input type="hidden" name="patient_id" value="<?= $userId ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">

    <button type="submit" class="btn-danger">
        Delete patient
    </button>
</form>

<?php endif; ?>

<a class="back" href="<?= $backUrl ?>">← Back to patients</a>
</div>

</body>
</html>
