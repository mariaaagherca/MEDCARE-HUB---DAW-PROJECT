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

if (
    $_SERVER["REQUEST_METHOD"] !== "POST" ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Invalid request");
}

$patientId = (int)$_POST["patient_id"];
$doctorId  = (int)$_POST["doctor_id"];

$pdo->prepare("
    UPDATE patient_assignments
    SET active = 0
    WHERE patient_id = ?
")->execute([$patientId]);

$pdo->prepare("
    INSERT INTO patient_assignments (patient_id, doctor_id, active)
    VALUES (?, ?, 1)
")->execute([$patientId, $doctorId]);

header("Location: patient_view.php?id=" . $patientId);
exit;
