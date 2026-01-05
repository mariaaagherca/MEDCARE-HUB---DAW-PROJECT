<?php
error_reporting(0);
ini_set('display_errors', 0);

require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";

require "../lib/dompdf/dompdf_config.inc.php";

$total = $pdo->query("SELECT COUNT(*) FROM page_visits")->fetchColumn();

$today = $pdo->query("
    SELECT COUNT(*)
    FROM page_visits
    WHERE DATE(visited_at) = CURDATE()
")->fetchColumn();

$visits = $pdo->query("
    SELECT 
        pv.page,
        pv.visited_at,
        u.email
    FROM page_visits pv
    LEFT JOIN users u ON u.id = pv.user_id
    ORDER BY pv.visited_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$html = '
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { text-align: center; color: #243b55; }
    h3 { margin-top: 30px; }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 6px;
    }
    th {
        background: #f0f0f0;
    }
</style>

<h1>MEDCARE HUB - Website Analytics Report</h1>

<p><strong>Total visits:</strong> ' . $total . '</p>
<p><strong>Visits today:</strong> ' . $today . '</p>

<h3>Detailed visits</h3>

<table>
<tr>
    <th>User</th>
    <th>Page</th>
    <th>Date & Time</th>
</tr>';

foreach ($visits as $v) {
    $user = $v["email"] ? htmlspecialchars($v["email"]) : "Guest";
    $html .= '
    <tr>
        <td>' . $user . '</td>
        <td>' . htmlspecialchars($v["page"]) . '</td>
        <td>' . htmlspecialchars($v["visited_at"]) . '</td>
    </tr>';
}

$html .= '
</table>

<p style="margin-top:40px;">
    Generated on ' . date("Y-m-d H:i") . '<br>
    MEDCARE HUB
</p>
';

$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->render();

$dompdf->stream(
    "Website_Analytics_Report_" . date("Y-m-d") . ".pdf",
    ["Attachment" => true]
);

exit;
