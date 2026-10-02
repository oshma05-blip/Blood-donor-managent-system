<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../conn.php';

if (empty($_SESSION['donor_id'])) {
    header('Location: ../login.php');
    exit;
}

$donor_id = (int) $_SESSION['donor_id'];

$stmt = $conn->prepare(
    'SELECT Donor_ID, Full_Name, Gender, DOB, Blood_Group, Address, Phone, Email, Availability
     FROM donor
     WHERE Donor_ID = ?
     LIMIT 1'
);

if (!$stmt) {
    error_log('Database prepare error: ' . $conn->error);
    http_response_code(500);
    exit('A database error occurred.');
}

$stmt->bind_param('i', $donor_id);

if (!$stmt->execute()) {
    error_log('Database query error: ' . $stmt->error);
    http_response_code(500);
    exit('A database error occurred.');
}

$result = $stmt->get_result();
$donor = $result->fetch_assoc();

$stmt->close();

if (!$donor) {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$conn->close();

function escape($value): string
{
    return htmlspecialchars((string) ($value ?? 'Not Available'), ENT_QUOTES, 'UTF-8');
}

$donor_name = $donor['Full_Name'] ?: 'Donor';
$blood_group = $donor['Blood_Group'] ?: 'Not Available';
$availability = $donor['Availability'] ?: 'Not Available';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard | Blood Donor System</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: #f5f6fa;
            color: #252a34;
            font-family: Arial, sans-serif;
        }

        .dashboard-shell {
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 250px;
            padding: 28px 16px;
            background: #a91022;
        }

        .sidebar h2 {
            margin-bottom: 32px;
            color: #fff;
            text-align: center;
            font-size: 23px;
        }

        .sidebar a {
            display: block;
            margin-bottom: 8px;
            padding: 13px 15px;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #fff;
            color: #a91022;
        }

        .main-panel {
            width: calc(100% - 250px);
            max-width: 1400px;
            margin-left: 250px;
            padding: 36px;
        }

        .welcome-box,
        .dashboard-card,
        .profile-panel {
            border: 1px solid #ececf0;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 5px 18px rgb(30 35 50 / 5%);
        }

        .welcome-box {
            margin-bottom: 24px;
            padding: 28px;
        }

        .welcome-box h1 {
            margin-bottom: 8px;
            color: #a91022;
            font-size: 28px;
        }

        .welcome-box p {
            color: #687080;
            line-height: 1.5;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .dashboard-card {
            padding: 24px;
            border-left: 5px solid #a91022;
        }

        .dashboard-card h2 {
            margin-bottom: 12px;
            color: #687080;
            font-size: 16px;
            font-weight: 600;
        }

        .dashboard-card p {
            color: #a91022;
            font-size: 28px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .profile-panel {
            padding: 26px;
        }

        .profile-panel h2 {
            margin-bottom: 12px;
            color: #a91022;
            font-size: 21px;
        }

        .profile-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #ececf0;
        }

        .profile-row:last-of-type {
            border-bottom: 0;
        }

        .profile-label {
            color: #687080;
            font-weight: 700;
        }

        .profile-value {
            text-align: right;
            overflow-wrap: anywhere;
        }

        .availability {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: #e4f5e9;
            color: #206b37;
            font-size: 14px;
            font-weight: 700;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 18px;
            border-radius: 7px;
            background: #a91022;
            color: #fff;
            text-decoration: none;
            transition: background 0.2s;
        }

        .button:hover {
            background: #820c1a;
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 210px;
            }

            .main-panel {
                width: calc(100% - 210px);
                margin-left: 210px;
                padding: 24px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                position: static;
                width: 100%;
                padding: 20px 16px;
            }

            .sidebar h2 {
                margin-bottom: 16px;
            }

            .main-panel {
                width: 100%;
                margin-left: 0;
                padding: 18px;
            }

            .welcome-box,
            .profile-panel {
                padding: 20px;
            }

            .profile-row {
                display: block;
            }

            .profile-value {
                margin-top: 6px;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-shell">
        <aside class="sidebar">
            <h2>Donor Panel</h2>
            <nav aria-label="Donor navigation">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="requests.php">Blood Requests</a>
                <a href="notifications.php">Notifications</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-panel">
            <section class="welcome-box">
                <h1>Welcome, <?= escape($donor_name) ?></h1>
                <p>Welcome to your Blood Donor Management System dashboard.</p>
            </section>

            <section class="dashboard-cards" aria-label="Donor summary">
                <article class="dashboard-card">
                    <h2>Blood Group</h2>
                    <p><?= escape($blood_group) ?></p>
                </article>

                <article class="dashboard-card">
                    <h2>Availability</h2>
                    <p><?= escape($availability) ?></p>
                </article>
            </section>

            <section class="profile-panel">
                <h2>Donor Information</h2>

                <?php
                $profile_fields = [
                    'Donor ID' => $donor['Donor_ID'],
                    'Full Name' => $donor['Full_Name'],
                    'Gender' => $donor['Gender'],
                    'Date of Birth' => $donor['DOB'],
                    'Blood Group' => $donor['Blood_Group'],
                    'Address' => $donor['Address'],
                    'Phone' => $donor['Phone'],
                    'Email' => $donor['Email'],
                ];
                ?>

                <?php foreach ($profile_fields as $label => $value): ?>
                    <div class="profile-row">
                        <span class="profile-label"><?= escape($label) ?></span>
                        <span class="profile-value"><?= escape($value) ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="profile-row">
                    <span class="profile-label">Availability</span>
                    <span class="profile-value">
                        <span class="availability"><?= escape($availability) ?></span>
                    </span>
                </div>

                <a href="profile.php" class="button">Edit Profile</a>
            </section>
        </main>
    </div>
</body>
</html>
