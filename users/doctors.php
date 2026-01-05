<?php
require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";

$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.email,
        d.first_name,
        d.last_name,
        d.specialization,
        d.phone
    FROM users u
    JOIN doctors d ON d.user_id = u.id
    WHERE u.role = 'doctor'
    ORDER BY d.last_name, d.first_name
");
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Doctors | MEDCARE HUB</title>
<style>
body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #232526, #414345);
    font-family: "Segoe UI", sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
}
.card {
    width: 90%;
    max-width: 950px;
    background: #fff;
    border-radius: 18px;
    padding: 30px;
}
h1 { text-align: center; }
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 14px;
    border-bottom: 1px solid #eee;
}
th { background: #f5f5f5; }
.actions a, .actions button {
    margin-right: 8px;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 13px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}
.view { background: #00e5ff; color: #000; }
.delete { background: #e53935; color: #fff; }
.back {
    display: block;
    margin-top: 20px;
    text-align: center;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="card">
<h1>All Doctors</h1>

<table>
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Specialization</th>
    <th>Phone</th>
    <th>Actions</th>
</tr>

<?php foreach ($doctors as $d): ?>
<tr>
    <td><?= htmlspecialchars($d["first_name"]." ".$d["last_name"]) ?></td>
    <td><?= htmlspecialchars($d["email"]) ?></td>
    <td><?= htmlspecialchars($d["specialization"]) ?></td>
    <td><?= htmlspecialchars($d["phone"]) ?></td>
    <td class="actions">
        <a class="view" href="doctor_view.php?id=<?= $d["id"] ?>">View</a>

        <form method="post" action="delete_doctor.php"
              style="display:inline;"
              onsubmit="return confirm('Are you sure you want to delete this doctor?');">
            <input type="hidden" name="id" value="<?= $d["id"] ?>">
            <button class="delete" type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<a class="back" href="../dashboard/index.php">← Back</a>
</div>

</body>
</html>
