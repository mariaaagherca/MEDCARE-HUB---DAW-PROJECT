<?php
require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";

if (!isset($_GET["id"])) {
    header("Location: doctors.php");
    exit;
}

$doctorId = (int)$_GET["id"];

$stmt = $pdo->prepare("
    SELECT d.first_name, d.last_name, u.email, d.specialization, d.phone
    FROM doctors d
    JOIN users u ON u.id = d.user_id
    WHERE u.id = ?
");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    header("Location: doctors.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.first_name, p.last_name, u.email
    FROM patient_assignments pa
    JOIN patients p ON p.user_id = pa.patient_id
    JOIN users u ON u.id = p.user_id
    WHERE pa.doctor_id = ?
      AND pa.active = 1
    ORDER BY p.last_name, p.first_name
");
$stmt->execute([$doctorId]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Details | MEDCARE HUB</title>

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
    margin-top: 0;
    margin-bottom: 25px;
}

.section {
    margin-bottom: 30px;
}

.section h3 {
    margin-bottom: 15px;
    color: #00e5ff;
    font-size: 18px;
}

.row {
    margin-bottom: 10px;
    font-size: 15px;
}

.label {
    font-weight: 600;
    color: #e0f7fa;
}

.patients {
    list-style: none;
    padding: 0;
    margin: 0;
}

.patients li {
    background: rgba(255,255,255,0.08);
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 10px;
}

.empty {
    color: #ccc;
    font-style: italic;
}

.back {
    display: block;
    margin-top: 30px;
    text-align: center;
    text-decoration: none;
    color: #fff;
    background: rgba(255,255,255,0.15);
    padding: 12px;
    border-radius: 10px;
    transition: background 0.2s;
}

.back:hover {
    background: rgba(255,255,255,0.25);
}
</style>
</head>
<body>

<div class="card">

    <h1>Dr. <?= htmlspecialchars($doctor["first_name"] . " " . $doctor["last_name"]) ?></h1>

    <div class="section">
        <h3>Doctor Information</h3>

        <div class="row"><span class="label">Email:</span> <?= htmlspecialchars($doctor["email"]) ?></div>
        <div class="row"><span class="label">Specialization:</span> <?= htmlspecialchars($doctor["specialization"]) ?></div>
        <div class="row"><span class="label">Phone:</span> <?= htmlspecialchars($doctor["phone"]) ?></div>
    </div>

    <div class="section">
        <h3>Assigned Patients</h3>

        <?php if (!$patients): ?>
            <div class="empty">No patients assigned to this doctor.</div>
        <?php else: ?>
            <ul class="patients">
                <?php foreach ($patients as $p): ?>
                    <li>
                        <?= htmlspecialchars($p["first_name"] . " " . $p["last_name"]) ?><br>
                        <small><?= htmlspecialchars($p["email"]) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    

    <a href="doctor_pdf.php?id=<?= $doctorId ?>" target="_blank">
        <button style="
            width:100%;
            padding:14px;
            background:#00e5ff;
            border:none;
            border-radius:10px;
            font-weight:700;
            cursor:pointer;
        ">
            📄 Download doctor profile (PDF)
        </button>
    </a>

    <a class="back" href="doctors.php">← Back to doctors list</a>

</div>

</body>
</html>
