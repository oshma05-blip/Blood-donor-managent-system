<?php
session_start();

include("../conn.php");

if (!isset($_SESSION['recipient_id'])) {
    header("Location: login.php");
    exit();
}

$recipient_id = $_SESSION['recipient_id'];

$requests = [];
$message = "";

$create_table = "
CREATE TABLE IF NOT EXISTS blood_request (
    Request_ID INT AUTO_INCREMENT PRIMARY KEY,
    Recipient_ID INT NOT NULL,
    Full_Name VARCHAR(100) NOT NULL,
    Blood_Group VARCHAR(10) NOT NULL,
    Required_Units INT NOT NULL DEFAULT 1,
    Hospital VARCHAR(150) NOT NULL,
    Address VARCHAR(255) NOT NULL,
    Phone VARCHAR(20) NOT NULL,
    Request_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status VARCHAR(30) DEFAULT 'Pending'
) ENGINE=InnoDB
";

if (!mysqli_query($conn, $create_table)) {
    die("Database Error: " . mysqli_error($conn));
}

$sql = "
    SELECT
        Request_ID,
        Full_Name,
        Blood_Group,
        Required_Units,
        Hospital,
        Address,
        Phone,
        Request_Date,
        Status
    FROM blood_request
    WHERE Recipient_ID = ?
    ORDER BY Request_Date DESC
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    mysqli_stmt_bind_param($stmt, "i", $recipient_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }

    mysqli_stmt_close($stmt);

} else {
    $message = "Unable to load request status.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Request Status | Blood Donor Management System</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: #c62828;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .logout {
            background: white;
            color: #c62828;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 5px;
            font-weight: bold;
        }

        .logout:hover {
            background: #eeeeee;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .title {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .title h2 {
            color: #c62828;
            margin-bottom: 8px;
        }

        .title p {
            color: #666;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            background: #c62828;
            color: white;
            padding: 13px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #fafafa;
        }

        .pending {
            color: #ff9800;
            font-weight: bold;
        }

        .approved {
            color: #2e7d32;
            font-weight: bold;
        }

        .rejected {
            color: #d32f2f;
            font-weight: bold;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #777;
            font-size: 18px;
        }

        .buttons {
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 5px;
            margin-right: 10px;
            font-weight: bold;
        }

        .btn-red {
            background: #c62828;
            color: white;
        }

        .btn-red:hover {
            background: #a51f1f;
        }

        .btn-gray {
            background: #555;
            color: white;
        }

        .btn-gray:hover {
            background: #333;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

    <div class="header">

        <h1>Blood Donor Management System</h1>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>


    <div class="container">

        <div class="title">

            <h2>Blood Request Status</h2>

            <p>
                View the current status of your blood requests.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="error">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <div class="table-box">

            <?php if (count($requests) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Blood Group</th>

                            <th>Units</th>

                            <th>Hospital</th>

                            <th>Address</th>

                            <th>Phone</th>

                            <th>Date</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($requests as $request): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Request_ID']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Full_Name']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Blood_Group']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Required_Units']
                                    );
                                ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Hospital']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Address']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Phone']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $request['Request_Date']
                                    );
                                    ?>
                                </td>

                                <td>

                                    <?php

                                    $status = $request['Status'];

                                    $class = '';

                                    if ($status == 'Pending') {
                                        $class = 'pending';
                                    } elseif ($status == 'Approved') {
                                        $class = 'approved';
                                    } elseif ($status == 'Rejected') {
                                        $class = 'rejected';
                                    }

                                    ?>

                                    <span class="<?php echo $class; ?>">
                                        <?php
                                        echo htmlspecialchars($status);
                                        ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="no-data">

                    No blood requests found.

                </div>

            <?php endif; ?>

        </div>


        <div class="buttons">

            <a href="request.php" class="btn btn-red">
                Make New Request
            </a>

            <a href="dashboard.php" class="btn btn-gray">
                Dashboard
            </a>

        </div>

    </div>

</body>

</html>