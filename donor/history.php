<?php
session_start();

include "../conn.php";

/* =========================================================
   CHECK DONOR LOGIN
   ========================================================= */

if (!isset($_SESSION["donor_id"])) {
    header("Location: ../login.php");
    exit();
}

$donor_id = $_SESSION["donor_id"];


/* =========================================================
   CREATE DONOR TABLE IF IT DOES NOT EXIST
   ========================================================= */

$create_donor_table = "
CREATE TABLE IF NOT EXISTS donor (
    Donor_ID INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Gender VARCHAR(20),
    DOB DATE,
    Blood_Group VARCHAR(10),
    Address VARCHAR(255),
    Phone VARCHAR(20),
    Email VARCHAR(100),
    Availability VARCHAR(20) DEFAULT 'Available',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($create_donor_table)) {
    die("Error creating donor table: " . $conn->error);
}


/* =========================================================
   CREATE DONATION TABLE IF IT DOES NOT EXIST
   ========================================================= */

$create_donation_table = "
CREATE TABLE IF NOT EXISTS donation (
    Donation_ID INT AUTO_INCREMENT PRIMARY KEY,
    Donor_ID INT NOT NULL,
    Donation_Date DATE NOT NULL,
    Hospital VARCHAR(150) NOT NULL,
    Status VARCHAR(50) DEFAULT 'Pending',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (Donor_ID)
    REFERENCES donor(Donor_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)";

if (!$conn->query($create_donation_table)) {
    die("Error creating donation table: " . $conn->error);
}


/* =========================================================
   GET DONOR INFORMATION
   ========================================================= */

$name_sql = "
    SELECT Full_Name
    FROM donor
    WHERE Donor_ID = ?
";

$name_stmt = $conn->prepare($name_sql);

if (!$name_stmt) {
    die("Database error: " . $conn->error);
}

$name_stmt->bind_param("i", $donor_id);
$name_stmt->execute();

$name_result = $name_stmt->get_result();

$donor = $name_result->fetch_assoc();

$donor_name = $donor["Full_Name"] ?? "Donor";

$name_stmt->close();


/* =========================================================
   GET DONATION HISTORY
   ========================================================= */

$sql = "
    SELECT
        Donation_ID,
        Donation_Date,
        Hospital,
        Status,
        Created_At
    FROM donation
    WHERE Donor_ID = ?
    ORDER BY Donation_Date DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $donor_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Donation History | Blood Donor System</title>

    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="../css/dashboard.css">

    <link rel="stylesheet" href="../css/responsive.css">

    <style>

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }

        .donation-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .donation-table th {
            background: #b30000;
            color: white;
            padding: 14px;
            text-align: left;
        }

        .donation-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #ddd;
        }

        .donation-table tr:hover {
            background: #f9f9f9;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .status.Pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.Approved {
            background: #d4edda;
            color: #155724;
        }

        .status.Completed {
            background: #cce5ff;
            color: #004085;
        }

        .status.Rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-message p {
            margin-bottom: 20px;
            color: #666;
        }

        .button.primary {
            display: inline-block;
            padding: 10px 18px;
            background: #b30000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .button.primary:hover {
            background: #8f0000;
        }

    </style>

</head>


<body>

<div class="dashboard-shell">


    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar">

        <h2>Donor Panel</h2>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="donation.php">
            Donation
        </a>

        <a href="history.php" class="active">
            History
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->

    <main class="main-panel">

        <h1>Donation History</h1>


        <div class="panel">

            <p>
                Donation history of
                <strong>
                    <?php echo htmlspecialchars($donor_name); ?>
                </strong>
            </p>


            <?php if ($result->num_rows > 0): ?>


                <div class="table-container">

                    <table class="donation-table">

                        <thead>

                            <tr>

                                <th>Donation ID</th>

                                <th>Donation Date</th>

                                <th>Hospital</th>

                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["Donation_ID"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php

                                    echo htmlspecialchars(
                                        date(
                                            "Y-m-d",
                                            strtotime(
                                                $row["Donation_Date"]
                                            )
                                        )
                                    );

                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["Hospital"]
                                    );
                                    ?>
                                </td>


                                <td>

                                    <span class="status
                                        <?php
                                        echo htmlspecialchars(
                                            $row["Status"]
                                        );
                                        ?>
                                    ">

                                        <?php
                                        echo htmlspecialchars(
                                            $row["Status"]
                                        );
                                        ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="empty-message">

                    <p>
                        No donation history found.
                    </p>

                    <a
                        href="donation.php"
                        class="button primary"
                    >
                        Schedule Your First Donation
                    </a>

                </div>


            <?php endif; ?>


        </div>

    </main>

</div>


<?php

$stmt->close();

$conn->close();

?>

</body>

</html>