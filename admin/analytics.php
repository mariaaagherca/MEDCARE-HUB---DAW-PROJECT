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

$total = $pdo->query("SELECT COUNT(*) FROM page_visits")->fetchColumn();

$today = $pdo->query("
    SELECT COUNT(*) 
    FROM page_visits
    WHERE DATE(visited_at) = CURDATE()
")->fetchColumn();

$topPages = $pdo->query("
    SELECT page, COUNT(*) cnt
    FROM page_visits
    GROUP BY page
    ORDER BY cnt DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$chartLabels = [];
$chartData = [];

foreach ($topPages as $p) {
    $chartLabels[] = $p["page"];
    $chartData[] = $p["cnt"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Analytics | MEDCARE HUB</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
}
.card {
    width: 700px;
    background: rgba(255,255,255,0.08);
    border-radius: 18px;
    padding: 40px;
}
h1 {
    text-align: center;
    margin-bottom: 25px;
}
.stat {
    margin-bottom: 15px;
    font-size: 18px;
}
ul {
    padding-left: 20px;
}
.btn {
    display: block;
    margin-top: 25px;
    padding: 14px;
    text-align: center;
    background: #00e5ff;
    color: #000;
    font-weight: 700;
    border-radius: 10px;
    text-decoration: none;
}
.btn:hover {
    background: #00bcd4;
}
.back {
    display: block;
    margin-top: 15px;
    text-align: center;
    color: #ccc;
    text-decoration: none;
}
canvas {
    margin-top: 20px;
}
</style>
</head>
<body>

<div class="card">
    <h1>📊 Website Analytics</h1>

    <div class="stat">📈 <strong>Total visits:</strong> <?= $total ?></div>
    <div class="stat">📅 <strong>Visits today:</strong> <?= $today ?></div>

    <h3> Top accessed pages</h3>
    <ul>
        <?php foreach ($topPages as $p): ?>
            <li>
                <?= htmlspecialchars($p["page"]) ?> 
                (<?= $p["cnt"] ?> visits)
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>📈 Visits chart</h3>
    <canvas id="pagesChart" height="120"></canvas>

    <a class="btn" href="../admin/admin_reports_pdf.php">
        📄 Download detailed PDF report
    </a>

    <a class="back" href="../dashboard/index.php">← Back to dashboard</a>
</div>

<script>
const ctx = document.getElementById('pagesChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Number of visits',
            data: <?= json_encode($chartData) ?>,
            backgroundColor: '#00e5ff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>
