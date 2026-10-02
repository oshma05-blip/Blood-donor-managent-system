<?php
session_start();

include "../conn.php";


$recipient_name = "Recipient";


$total_requests = 0;
$pending_requests = 0;
$approved_requests = 0;
$completed_requests = 0;

$sql = "SHOW TABLES LIKE 'blood_request'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {


    $sql = "SELECT COUNT(*) AS total FROM blood_request";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $total_requests = $row['total'];
    }


    $sql = "SELECT COUNT(*) AS total
            FROM blood_request
            WHERE Status = 'Pending'";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $pending_requests = $row['total'];
    }

    
    $sql = "SELECT COUNT(*) AS total
            FROM blood_request
            WHERE Status = 'Approved'";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $approved_requests = $row['total'];
    }

    
    $sql = "SELECT COUNT(*) AS total
            FROM blood_request
            WHERE Status = 'Completed'";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $completed_requests = $row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recipient Dashboard | Blood Donor System</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/responsive.css">

    <style>
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 25px;
        }

        .dashboard-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .dashboard-card h3 {
            margin-bottom: 10px;
            color: #555;
        }

        .dashboard-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #c62828;
        }

        .welcome-panel {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .quick-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .quick-links a {
            text-decoration: none;
            background: #c62828;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
        }

        .quick-links a:hover {
            background: #a91f1f;
        }

        @media (max-width: 900px) {
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="dashboard-shell">

    
    <aside class="sidebar">

        <h2>Recipient Panel</h2>

        <a href="dashboard.php">Dashboard</a>

        <a href="request.php">Request</a>

        <a href="status.php">Status</a>

        <a href="../logout.php">Logout</a>

    </aside>


    <main class="main-panel">

        <h1>Recipient Dashboard</h1>


        <div class="welcome-panel">

            <h2>Welcome, <?php echo htmlspecialchars($recipient_name); ?>!</h2>

            <p>
                Create a request for blood units and track its progress
                through your recipient dashboard.
            </p>

            <div class="quick-links">

                <a href="request.php">
                    Request Blood
                </a>

                <a href="status.php">
                    Check Request Status
                </a>

                
            </div>

        </div>


    
        <div class="dashboard-cards">

            <div class="dashboard-card">

                <h3>Total Requests</h3>

                <div class="number">
                    <?php echo $total_requests; ?>
                </div>

            </div>


            <div class="dashboard-card">

                <h3>Pending</h3>

                <div class="number">
                    <?php echo $pending_requests; ?>
                </div>

            </div>


            <div class="dashboard-card">

                <h3>Approved</h3>

                <div class="number">
                    <?php echo $approved_requests; ?>
                </div>

            </div>


            <div class="dashboard-card">

                <h3>Completed</h3>

                <div class="number">
                    <?php echo $completed_requests; ?>
                </div>

            </div>

        </div>


        <div class="panel" style="margin-top:25px;">

            <h2>Blood Request Information</h2>

            <p>
                You can submit a blood request by selecting the required
                blood group, number of units, hospital and required date.
            </p>

            <p>
                After submitting your request, you can use the
                <strong>Status</strong> section to monitor your request.
            </p>

        </div>

    </main>

</div>

</body>
</html>