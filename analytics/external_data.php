<?php
require "../auth/guard.php";
require_role(["administrator", "doctor"]);

/* date dintr o sursa externa - API public */
$json = file_get_contents("https://disease.sh/v3/covid-19/all");
$data = json_decode($json, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>External Medical Data</title>
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
    width: 500px;
    background: rgba(255,255,255,0.08);
    border-radius: 18px;
    padding: 40px;
}
h1 { text-align: center; }
.stat { margin-bottom: 12px; font-size: 16px; }
.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #ccc;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="card">
<h1>External Medical Data</h1>

<div class="stat">🌍 Total cases: <strong><?= number_format($data["cases"]) ?></strong></div>
<div class="stat">⚠️ Total deaths: <strong><?= number_format($data["deaths"]) ?></strong></div>
<div class="stat">💊 Recovered: <strong><?= number_format($data["recovered"]) ?></strong></div>
<div class="stat">📅 Updated: <strong><?= date("d-m-Y H:i", $data["updated"]/1000) ?></strong></div>

<a class="back" href="../dashboard/index.php">← Back</a>
</div>

</body>
</html>
