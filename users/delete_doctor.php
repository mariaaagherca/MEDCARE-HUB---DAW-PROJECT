<?php
require "../auth/guard.php";
require_role(["administrator"]);
require "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: doctors.php");
    exit;
}

$doctorId = (int)$_POST["id"];

try {
    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM patient_assignments WHERE doctor_id = ?")
        ->execute([$doctorId]);

    $pdo->prepare("DELETE FROM doctors WHERE user_id = ?")
        ->execute([$doctorId]);

    $pdo->prepare("DELETE FROM users WHERE id = ?")
        ->execute([$doctorId]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}

header("Location: doctors.php");
exit;
