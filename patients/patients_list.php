<?php
require "../auth/guard.php";
require_role(["administrator", "doctor"]);
require "../config/database.php";
require "../analytics/log_visit.php";
$stmt = $pdo->prepare("
    INSERT INTO page_visits (user_id, page)
    VALUES (?, ?)
");
$stmt->execute([
    $_SESSION["user_id"] ?? null,
    basename($_SERVER["PHP_SELF"])
]);

$role = $_SESSION["role"];

if ($role === "administrator") {

    $stmt = $pdo->prepare("
        SELECT id, email, status, created_at
        FROM users
        WHERE role = 'patient'
        ORDER BY created_at DESC
    ");
    $stmt->execute();

} else {
    $stmt = $pdo->prepare("
        SELECT u.id, u.email, u.created_at
        FROM users u
        JOIN patient_assignments pa ON pa.patient_id = u.id
        WHERE pa.doctor_id = ?
          AND pa.active = 1
          AND u.status = 'active'
        ORDER BY u.created_at DESC
    ");
    $stmt->execute([$_SESSION["user_id"]]);
}

$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $role === "administrator" ? "All Patients" : "My Patients" ?> | MEDCARE HUB</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1c1c1c, #434343);
            font-family: "Segoe UI", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            width: 90%;
            max-width: 900px;
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f5f5f5;
        }
        a {
            color: #1976d2;
            text-decoration: none;
        }
        .back {
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="card">
    <h1><?= $role === "administrator" ? "All Patients" : "My Patients" ?></h1>

    <table>
        <tr>
            <th>Email</th>
            <th>Registered At</th>
        </tr>

        <?php foreach ($patients as $p): ?>
            <tr>
                <td>
                    <a href="../users/patient_view.php?id=<?= $p["id"] ?>">
                        <?= htmlspecialchars($p["email"]) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($p["created_at"]) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a class="back" href="../dashboard/index.php">← Back to dashboard</a>
</div>

</body>
</html>
