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

$stmt = $pdo->query("
    SELECT *
    FROM help_requests
    ORDER BY created_at DESC
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Help Requests | MEDCARE HUB</title>
<style>
body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #141e30, #243b55);
    font-family: "Segoe UI", sans-serif;
    color: #fff;
}
.container {
    max-width: 1000px;
    margin: auto;
    padding: 60px 30px;
}
.card {
    background: rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 30px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}
th {
    background: rgba(0,0,0,0.3);
}
a {
    color: #00e5ff;
    text-decoration: none;
}
.status-new { color: #ffcc80; }
.status-answered { color: #69f0ae; }
.back {
    display: inline-block;
    margin-top: 20px;
    color: #ccc;
}
</style>
</head>
<body>

<div class="container">
<div class="card">
<h1>Help Requests</h1>

<table>
<tr>
    <th>Email</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php foreach ($requests as $r): ?>
<tr>
    <td><?= htmlspecialchars($r["email"]) ?></td>
    <td class="status-<?= $r["status"] ?>">
        <?= ucfirst($r["status"]) ?>
    </td>
    <td><?= $r["created_at"] ?></td>
    <td>
        <a href="reply_help.php?id=<?= $r["id"] ?>">View / Reply</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<a class="back" href="../dashboard/index.php">← Back</a>
</div>
</div>

</body>
</html>
