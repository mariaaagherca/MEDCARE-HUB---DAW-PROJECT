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

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$role = $_SESSION["role"];
$userId = $_SESSION["user_id"];

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
        WHERE u.role = 'patient'
          AND u.status = 'active'
          AND pa.doctor_id = ?
          AND pa.active = 1
        ORDER BY u.created_at DESC
    ");
    $stmt->execute([$userId]);
}

$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Patients | MEDCARE HUB</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1f4037, #99f2c8);
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
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        th {
            background: #f5f5f5;
        }

        .status {
            font-weight: bold;
        }

        .active {
            color: #2e7d32;
        }

        .pending {
            color: #f57c00;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="card">
    <h1>All Patients</h1>

    <table>
        <tr>
            <th>Patient</th>
            <th>Registered At</th>

            <?php if ($role === "administrator"): ?>
                <th>Status</th>
                <th>Actions</th>
            <?php endif; ?>
        </tr>

        <?php foreach ($patients as $p): ?>
            <tr>
                <td>
                    <a href="patient_view.php?id=<?= $p['id'] ?>">
                        <?= htmlspecialchars($p["email"]) ?>
                    </a>
                </td>

                <td><?= htmlspecialchars($p["created_at"]) ?></td>

                <?php if ($role === "administrator"): ?>
                    <td class="status <?= $p["status"] ?>">
                        <?= ucfirst($p["status"]) ?>
                    </td>

                    <td>
                        <?php if ($p["status"] === "active"): ?>
                            <form method="post" action="deactivate_user.php" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $p["id"] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION["csrf_token"] ?>">
                                <button type="submit"
                                        onclick="return confirm('Deactivate this account?')">
                                    Deactivate
                                </button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <a class="back" href="../dashboard/index.php">← Back to dashboard</a>
</div>

</body>
</html>
