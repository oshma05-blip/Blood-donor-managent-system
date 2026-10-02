<?php

session_start();

include "../conn.php";

if (!isset($_SESSION["donor_id"]) || empty($_SESSION["donor_id"])) {
    header("Location: ../login.php");
    exit();
}

$donor_id = (int) $_SESSION["donor_id"];

$stmt = $conn->prepare("
    SELECT
        Donor_ID,
        Full_Name,
        Gender,
        DOB,
        Blood_Group,
        Address,
        Phone,
        Email,
        Availability
    FROM donor
    WHERE Donor_ID = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $donor_id);
$stmt->execute();

$result = $stmt->get_result();

$donor = $result->fetch_assoc();

if (!$donor) {
    session_unset();
    session_destroy();

    header("Location: ../login.php");
    exit();
}

$donor_name = $donor["Full_Name"] ?? "Donor";
$blood_group = $donor["Blood_Group"] ?? "Not Available";
$availability = $donor["Availability"] ?? "Not Available";

$stmt->close();

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Donor Dashboard | Blood Donor System
    </title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .dashboard-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #b30000;
            padding: 25px 15px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 23px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: white;
            color: #b30000;
        }

        .main-panel {
            margin-left: 240px;
            width: calc(100% - 240px);
            padding: 35px;
        }

        .welcome-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .welcome-box h1 {
            color: #b30000;
            margin-bottom: 8px;
            font-size: 28px;
        }

        .welcome-box p {
            color: #666;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #b30000;
        }

        .dashboard-card h3 {
            color: #555;
            margin-bottom: 12px;
            font-size: 17px;
        }

        .dashboard-card p {
            color: #b30000;
            font-size: 28px;
            font-weight: bold;
        }

        .profile-panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .profile-panel h2 {
            color: #b30000;
            margin-bottom: 20px;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #ddd;
            gap: 20px;
        }

        .profile-row:last-child {
            border-bottom: none;
        }

        .profile-label {
            font-weight: bold;
            color: #555;
        }

        .profile-value {
            color: #333;
            text-align: right;
        }

        .availability {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            background: #d4edda;
            color: #155724;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 11px 20px;
            background: #b30000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }

        .button:hover {
            background: #8f0000;
        }

        @media (max-width: 800px) {

            .sidebar {
                width: 200px;
            }

            .main-panel {
                margin-left: 200px;
                width: calc(100% - 200px);
                padding: 20px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .dashboard-shell {
                display: block;
            }

            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }

            .main-panel {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .profile-row {
                display: block;
            }

            .profile-value {
                text-align: left;
                margin-top: 5px;
            }

        }

    </style>

</head>

<body>

<div class="dashboard-shell">

    <aside class="sidebar">

        <h2>
            Donor Panel
        </h2>

        <a
            href="dashboard.php"
            class="active"
        >
            Dashboard
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="requests.php">
            Blood Requests
        </a>

        <a href="notifications.php">
            Notifications
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </aside>

    <main class="main-panel">

        <div class="welcome-box">

            <h1>
                Welcome, <?php echo htmlspecialchars($donor_name); ?>
            </h1>

            <p>
                Welcome to your Blood Donor Management System dashboard.
            </p>

        </div>

        <div class="dashboard-cards">

            <div class="dashboard-card">

                <h3>
                    Blood Group
                </h3>

                <p>
                    <?php echo htmlspecialchars($blood_group); ?>
                </p>

            </div>

            <div class="dashboard-card">

                <h3>
                    Availability
                </h3>

                <p>
                    <?php echo htmlspecialchars($availability); ?>
                </p>

            </div>

        </div>

        <div class="profile-panel">

            <h2>
                Donor Information
            </h2>

            <div class="profile-row">

                <span class="profile-label">
                    Donor ID
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["Donor_ID"]); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Full Name
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["Full_Name"]); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Gender
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["Gender"] ?? "Not Available"); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Date of Birth
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["DOB"] ?? "Not Available"); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Blood Group
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["Blood_Group"] ?? "Not Available"); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Address
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["Address"] ?? "Not Available"); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Phone
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["Phone"] ?? "Not Available"); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Email
                </span>

                <span class="profile-value">
                    <?php echo htmlspecialchars($donor["Email"] ?? "Not Available"); ?>
                </span>

            </div>

            <div class="profile-row">

                <span class="profile-label">
                    Availability
                </span>

                <span class="profile-value">

                    <span class="availability">
                        <?php echo htmlspecialchars($availability); ?>
                    </span>

                </span>

            </div>

            <a
                href="profile.php"
                class="button"
            >
                Edit Profile
            </a>

        </div>

    </main>

</div>

</body>

</html>