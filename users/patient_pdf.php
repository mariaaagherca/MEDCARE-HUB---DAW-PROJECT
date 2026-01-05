<?php
error_reporting(0);
ini_set('display_errors', 0);

require "../auth/guard.php";
require_role(["administrator", "doctor"]);
require "../config/database.php";
require "../lib/dompdf/dompdf_config.inc.php";

if (!isset($_GET["id"])) {
    die("Missing patient ID");
}
$stmt = $pdo->prepare("
    INSERT INTO page_visits (user_id, page)
    VALUES (?, ?)
");
$stmt->execute([
    $_SESSION["user_id"] ?? null,
    basename($_SERVER["PHP_SELF"])
]);

$userId = (int)$_GET["id"];

/* pacient */
$stmt = $pdo->prepare("
    SELECT 
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
    die("Patient not found");
}

$firstName = $patient["first_name"] ?? "";
$lastName  = $patient["last_name"] ?? "";
$status    = $patient["status"] ?? "unknown";

$doctorStmt = $pdo->prepare("
    SELECT u.email
    FROM patient_assignments pa
    JOIN users u ON u.id = pa.doctor_id
    WHERE pa.patient_id = ?
      AND pa.active = 1
    LIMIT 1
");
$doctorStmt->execute([$userId]);
$doctor = $doctorStmt->fetch(PDO::FETCH_ASSOC);

$doctorText = $doctor
    ? htmlspecialchars($doctor["email"])
    : "No doctor assigned";

$html = '
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        background: #f4f6f8;
        font-size: 12px;
    }

    .header {
        background: #00b4db;
        color: white;
        padding: 20px;
        text-align: center;
    }

    .header h1 {
        margin: 0;
        font-size: 24px;
    }

    .header p {
        margin: 5px 0 0;
        font-size: 13px;
        opacity: 0.9;
    }

    .card {
        background: #ffffff;
        margin: 25px;
        padding: 25px;
        border-radius: 6px;
    }

    h2 {
        color: #00b4db;
        font-size: 16px;
        margin-bottom: 10px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    td {
        padding: 8px 6px;
        vertical-align: top;
    }

    .label {
        font-weight: bold;
        width: 35%;
        color: #555;
    }

    .value {
        color: #000;
    }

    .status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        color: white;
    }

    .active { background: #4caf50; }
    .pending { background: #ff9800; }
    .inactive { background: #f44336; }

    .footer {
        text-align: center;
        font-size: 10px;
        color: #777;
        margin-top: 30px;
    }
</style>

<div class="header">
    <h1>MEDCARE HUB</h1>
    <p>Patient Medical Profile</p>
</div>

<div class="card">

    <h2>Patient Information</h2>
    <table>
        <tr>
            <td class="label">Full Name</td>
            <td class="value">'.htmlspecialchars(trim($firstName." ".$lastName)).'</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td class="value">'.htmlspecialchars($patient["email"]).'</td>
        </tr>
        <tr>
            <td class="label">CNP</td>
            <td class="value">'.htmlspecialchars($patient["cnp"]).'</td>
        </tr>
        <tr>
            <td class="label">Date of Birth</td>
            <td class="value">'.htmlspecialchars($patient["date_of_birth"]).'</td>
        </tr>
    </table>

    <h2>Contact Details</h2>
    <table>
        <tr>
            <td class="label">Phone</td>
            <td class="value">'.htmlspecialchars($patient["phone"]).'</td>
        </tr>
        <tr>
            <td class="label">Address</td>
            <td class="value">'.htmlspecialchars($patient["address"]).'</td>
        </tr>
    </table>

    <h2>Medical & System Info</h2>
    <table>
        <tr>
            <td class="label">Account Status</td>
            <td class="value">
                <span class="status '.htmlspecialchars(strtolower($status)).'">
                    '.htmlspecialchars(strtoupper($status)).'
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">Assigned Doctor</td>
            <td class="value">'.$doctorText.'</td>
        </tr>
        <tr>
            <td class="label">Registered At</td>
            <td class="value">'.htmlspecialchars($patient["created_at"]).'</td>
        </tr>
    </table>

</div>

<div class="footer">
    Confidential medical document • Generated '.date("Y-m-d H:i").'<br>
    MEDCARE HUB © '.date("Y").'
</div>
';

/* PDF */
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->render();

$filename =
    "Patient_Profile_" .
    preg_replace("/[^a-zA-Z0-9]/", "_", trim($firstName)) . "_" .
    preg_replace("/[^a-zA-Z0-9]/", "_", trim($lastName)) . ".pdf";

$dompdf->stream($filename, ["Attachment" => true]);
exit;
