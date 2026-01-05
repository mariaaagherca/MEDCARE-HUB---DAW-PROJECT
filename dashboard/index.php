<?php
session_start();
require "../config/database.php";

require "../analytics/log_visit.php";

$stmt = $pdo->prepare("
    INSERT INTO page_visits (user_id, page)
    VALUES (?, ?)
");
$stmt->execute([
    $_SESSION["user_id"] ?? null,
    basename($_SERVER["PHP_SELF"])
]);


if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../auth/logout.php");
    exit;
}

$role = $user["role"];
$welcomeMessage = "";



if ($role === "administrator") {
    $welcomeMessage = "You are logged in as <strong>Administrator</strong>";
}

if ($role === "doctor") {
    $stmt = $pdo->prepare("
        SELECT first_name, last_name
        FROM doctors
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION["user_id"]]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doctor) {
        $welcomeMessage = "Welcome, <strong>Dr. " .
            htmlspecialchars($doctor["first_name"] . " " . $doctor["last_name"]) .
            "</strong>";
    } else {
        $welcomeMessage = "Welcome, Doctor";
    }
}

if ($role === "patient") {
    $stmt = $pdo->prepare("
        SELECT first_name
        FROM patients
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION["user_id"]]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        $welcomeMessage = "Welcome, <strong>" .
            htmlspecialchars($patient["first_name"]) .
            "</strong>";
    } else {
        $welcomeMessage = "Welcome";
    }

    // pacientul trebuie sa isi completeze profilul
    if (!$patient) {
        header("Location: ../patients/complete_profile.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | MEDCARE HUB</title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #141e30, #243b55);
            font-family: "Segoe UI", sans-serif;
            color: #fff;
        }

        .navbar {
            padding: 20px 40px;
            background: rgba(0,0,0,0.35);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            margin: 0;
            font-size: 22px;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            background: #e53935;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
        }

        .container {
            padding: 50px;
            max-width: 1100px;
            margin: auto;
        }

        .welcome {
            font-size: 17px;
            margin-bottom: 35px;
            color: #e0f7fa;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
        }

        .card {
            background: rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.35);
            transition: transform 0.2s, background 0.2s;
        }

        .card:hover {
            transform: translateY(-6px);
            background: rgba(255,255,255,0.14);
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .card p {
            color: #ddd;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .card a {
            display: inline-block;
            color: #00e5ff;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>MEDCARE HUB</h1>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="container">
    <h2>Dashboard</h2>
    <div class="welcome"><?= $welcomeMessage ?></div>

    <div class="grid">

        <?php if ($role === "administrator"): ?>
            <div class="card">
                <h3>Pending Patients</h3>
                <p>Approve newly registered patient accounts.</p>
                <a href="../users/pending.php">Open</a>
            </div>

            <div class="card">
                <h3>Manage Doctors</h3>
                <p>Create doctor accounts.</p>
                <a href="../users/create_doctor.php">Open</a>
            </div>

            <div class="card">
                <h3>All Doctors</h3>
                <p>View all registered doctors.</p>
                <a href="../users/doctors.php">Open</a>
            </div>

            <div class="card">
                <h3>All Patients</h3>
                <p>View and manage all patients.</p>
                <a href="../users/patients.php">Open</a>
            </div>

            <div class="card">
                <h3>Help Requests</h3>
                <p>View and answer user support messages.</p>
                <a href="../help/admin_help.php">Open</a>
            </div>

            <div class="card">
                <h3>Analytics</h3>
                <p>Website visits and activity.</p>
                <a href="../admin/analytics.php">Open</a>
            </div>

            <div class="card">
                <h3>External medical data</h3>
                <p>Covid 19 Informations</p>
                <a href="../analytics/external_data.php">Open</a>
            </div>


        <?php endif; ?>

        <?php if ($role === "doctor"): ?>
            <div class="card">
                <h3>My Patients</h3>
                <p>View patients assigned to you.</p>
                <a href="../patients/patients_list.php">Open</a>
            </div>
        <?php endif; ?>

        <?php if ($role === "patient"): ?>
            <div class="card">
                <h3>My Profile</h3>
                <p>View and update your personal data.</p>
                <a href="../patients/profile.php">Open</a>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
