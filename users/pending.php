<?php
session_start();
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

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$stmt = $pdo->prepare("
    SELECT id, email
    FROM users
    WHERE status = 'pending'
      AND role = 'patient'
    ORDER BY id DESC
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Patients | MEDCARE HUB</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            font-family: "Segoe UI", sans-serif;
            color: #fff;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 60px 30px;
        }

        h1 {
            margin-bottom: 30px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.08);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        th, td {
            padding: 16px;
            text-align: left;
        }

        th {
            background: rgba(0,0,0,0.3);
        }

        tr:not(:last-child) {
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .btn {
            padding: 8px 14px;
            background: #00e676;
            color: #000;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
        }

        .btn:hover {
            background: #00c853;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #ccc;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .top a {
            color: #fff;
            text-decoration: none;
            background: #e53935;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top">
        <h1>Pending Patient Approvals</h1>
        <a href="../dashboard/index.php">Back</a>
    </div>

    <?php if (count($users) === 0): ?>
        <div class="empty">No pending patients.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Email</th>
                <th>Action</th>
            </tr>

            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u["email"]) ?></td>
                    <td>
                        <form method="post" action="approve.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn">Approve</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
