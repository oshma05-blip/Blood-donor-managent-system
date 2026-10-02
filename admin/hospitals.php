<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../conn.php");


$hospitals = [];

$sql = "SELECT id, hospital_name, address, phone, email
        FROM hospitals
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $hospitals[] = $row;
    }
}

$total_hospitals = count($hospitals);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hospitals | Blood Donor System</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/responsive.css">

    <style>

        .hospital-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .hospital-table th,
        .hospital-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .hospital-table th {
            background-color: #c62828;
            color: white;
        }

        .hospital-name {
            font-weight: bold;
            color: #c62828;
        }

        .hospital-count {
            margin-top: 15px;
            padding: 15px;
            background-color: #fff;
            border-left: 4px solid #c62828;
        }

        .no-data {
            padding: 20px;
            text-align: center;
            color: #777;
        }

    </style>

</head>

<body>

<div class="dashboard-shell">

    <aside class="sidebar">

        <h2>Admin Panel</h2>

        <a href="dashboard.php">Dashboard</a>
        <a href="donors.php">Donors</a>
        <a href="recipients.php">Recipients</a>
        <a href="hospitals.php" class="active">Hospitals</a>
        <a href="inventory.php">Inventory</a>
        <a href="requests.php">Requests</a>
        <a href="diseases.php">Diseases</a>
        <a href="notifications.php">Notifications</a>
        <a href="payments.php">Payments</a>

        <a href="../logout.php">Logout</a>

    </aside>

    <main class="main-panel">

        <h1>Hospitals</h1>

        <div class="panel">

            <p>
                View registered hospitals connected to the
                Blood Donor Management System.
            </p>

            <div class="hospital-count">

                <strong>
                    Total Registered Hospitals:
                </strong>

                <?php echo $total_hospitals; ?>

            </div>

        </div>

        <div class="panel">

            <h3>Registered Hospitals</h3>

            <?php if ($total_hospitals > 0): ?>

                <div style="overflow-x:auto;">

                    <table class="hospital-table">

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>Hospital Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Email</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($hospitals as $hospital): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo (int)$hospital["id"];
                                    ?>
                                </td>

                                <td>

                                    <span class="hospital-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $hospital["hospital_name"]
                                        );
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $hospital["address"] ?: "N/A"
                                    );
                                    ?>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $hospital["phone"] ?: "N/A"
                                    );
                                    ?>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $hospital["email"] ?: "N/A"
                                    );
                                    ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="no-data">

                    No hospitals have been registered yet.

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>