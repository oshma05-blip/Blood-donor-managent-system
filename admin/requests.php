<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../conn.php");

$pending_query = "SELECT COUNT(*) AS total FROM requests WHERE status = 'Pending'";
$pending_result = mysqli_query($conn, $pending_query);
$pending_data = mysqli_fetch_assoc($pending_result);
$pending_requests = $pending_data['total'];

$approved_query = "SELECT COUNT(*) AS total FROM requests WHERE status = 'Approved'";
$approved_result = mysqli_query($conn, $approved_query);
$approved_data = mysqli_fetch_assoc($approved_result);
$approved_requests = $approved_data['total'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Requests | Blood Donor System</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/responsive.css">

</head>

<body>

<div class="dashboard-shell">

    <aside class="sidebar">

        <h2>Admin Panel</h2>

        <a href="dashboard.php">Dashboard</a>
        <a href="donors.php">Donors</a>
        <a href="recipients.php">Recipients</a>
        <a href="hospitals.php">Hospitals</a>
        <a href="inventory.php">Inventory</a>

        <a href="requests.php" class="active">Requests</a>

        <a href="diseases.php">Diseases</a>
        <a href="notifications.php">Notifications</a>
        <a href="payments.php">Payments</a>

        <a href="../logout.php">Logout</a>

    </aside>


    <main class="main-panel">

        <h1>Requests</h1>

        <div class="panel">

            <h2>Blood Request Overview</h2>


            <div class="request-box">

                <p>

                    <strong>Pending requests:</strong>

                    <?php echo $pending_requests; ?>

                </p>

            </div>


            <div class="request-box">

                <p>

                    <strong>Approved requests:</strong>

                    <?php echo $approved_requests; ?>

                </p>

            </div>

        </div>

        <div class="panel">

            <h2>All Blood Requests</h2>

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>Patient Name</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Request Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $query = "SELECT * FROM requests ORDER BY request_date DESC";

                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {

                        while ($row = mysqli_fetch_assoc($result)) {

                    ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($row['id']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['patient_name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['blood_group']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['units']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['priority']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['status']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['request_date']); ?>
                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td colspan="7">
                                No blood requests found.
                            </td>

                        </tr>

                    <?php

                    }

                    ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

</body>

</html>