<?php
require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: patients.php");
    exit;
}

if (
    !isset($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Security error.");
}

if (!isset($_POST["patient_id"])) {
    die("Missing patient ID.");
}

$patientId = (int)$_POST["patient_id"];

$stmt = $pdo->prepare("
    DELETE FROM patient_assignments
    WHERE patient_id = ?
");
$stmt->execute([$patientId]);

$stmt = $pdo->prepare("
    DELETE FROM patients
    WHERE user_id = ?
");
$stmt->execute([$patientId]);

$stmt = $pdo->prepare("
    DELETE FROM users
    WHERE id = ? AND role = 'patient'
");
$stmt->execute([$patientId]);

$_SESSION["flash_success"] = "Patient deleted successfully.";

header("Location: patients.php");
exit;
