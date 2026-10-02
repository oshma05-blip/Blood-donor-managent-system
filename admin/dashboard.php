<?php

session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}


include("../conn.php");



$donors_count = 0;

$sql = "SELECT COUNT(*) AS total FROM donors";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $donors_count = (int)$row["total"];
}



$recipients_count = 0;

$sql = "SELECT COUNT(*) AS total FROM recipients";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $recipients_count = (int)$row["total"];
}


$hospitals_count = 0;

$sql = "SELECT COUNT(*) AS total FROM hospitals";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $hospitals_count = (int)$row["total"];
}


$inventory_units = 0;

$sql = "
    SELECT COALESCE(SUM(units), 0) AS total
    FROM blood_inventory
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $inventory_units = (int)$row["total"];
}


$pending_requests = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM requests
    WHERE status = 'Pending'
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $pending_requests = (int)$row["total"];
}



$inventory_data = [];

$sql = "
    SELECT blood_group, units
    FROM blood_inventory
    ORDER BY
        FIELD(
            blood_group,
            'A+',
            'A-',
            'B+',
            'B-',
            'O+',
            'O-',
            'AB+',
            'AB-'
        )
";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $inventory_data[] = $row;
    }
}

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
        Admin Dashboard | Blood Donor System
    </title>


    <!-- Main CSS -->

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <!-- Dashboard CSS -->

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >


    <!-- Responsive CSS -->

    <link
        rel="stylesheet"
        href="../css/responsive.css"
    >


    <!-- Charts JavaScript -->

    <script
        defer
        src="../js/charts.js"
    ></script>


    <style>



        .admin-stats {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(200px, 1fr)
                );

            gap: 20px;

            margin-bottom: 25px;
        }


        .admin-stat {

            background: white;

            padding: 25px;

            border-radius: 10px;

            border: 1px solid #ddd;

            text-align: center;
        }


        .admin-stat h3 {

            margin: 0;

            font-size: 35px;

            color: #c62828;
        }


        .admin-stat p {

            margin-top: 8px;

            color: #555;
        }



        .overview-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(150px, 1fr)
                );

            gap: 15px;

            margin-top: 20px;
        }


        .blood-overview {

            padding: 20px;

            background: #f8f8f8;

            border-radius: 8px;

            text-align: center;
        }


        .blood-overview strong {

            display: block;

            font-size: 25px;

            color: #c62828;
        }


        .blood-overview span {

            display: block;

            margin-top: 5px;
        }



        .system-summary {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(200px, 1fr)
                );

            gap: 20px;

            margin-top: 25px;
        }


        .summary-box {

            padding: 18px;

            border-radius: 8px;

            background: #fff5f5;

            border: 1px solid #eee;
        }


        .summary-box strong {

            font-size: 24px;

            color: #c62828;
        }


    </style>

</head>


<body>


<div class="dashboard-shell">



    <aside class="sidebar">


        <h2>
            Admin Panel
        </h2>


        <a
            class="active"
            href="dashboard.php"
        >
            Dashboard
        </a>


        <a href="donors.php">
            Donors
        </a>


        <a href="recipients.php">
            Recipients
        </a>


        <a href="hospitals.php">
            Hospitals
        </a>



        <a href="inventory.php">
            Inventory
        </a>



        <a href="requests.php">
            Requests
        </a>


        <a href="diseases.php">
            Diseases
        </a>


        <a href="notifications.php">
            Notifications
        </a>


        <a href="payments.php">
            Payments
        </a>

        <a href="../logout.php">
            Logout
        </a>


    </aside>

    <main class="main-panel">


        <h1>
            Admin Dashboard
        </h1>


        <div class="admin-stats">

            <div class="admin-stat">

                <h3
                    data-count="<?php
                        echo $donors_count;
                    ?>"
                >

                    <?php
                    echo $donors_count;
                    ?>

                </h3>


                <p>
                    Donors
                </p>

            </div>

            <div class="admin-stat">

                <h3
                    data-count="<?php
                        echo $recipients_count;
                    ?>"
                >

                    <?php
                    echo $recipients_count;
                    ?>

                </h3>


                <p>
                    Recipients
                </p>

            </div>

            <div class="admin-stat">

                <h3
                    data-count="<?php
                        echo $hospitals_count;
                    ?>"
                >

                    <?php
                    echo $hospitals_count;
                    ?>

                </h3>


                <p>
                    Hospitals
                </p>

            </div>


        </div>

        <div class="panel">


            <h3>
                System Overview
            </h3>


            <div class="system-summary">


                <div class="summary-box">

                    <strong>

                        <?php
                        echo $inventory_units;
                        ?>

                    </strong>


                    <p>
                        Total Blood Units
                    </p>

                </div>

                <div class="summary-box">

                    <strong>

                        <?php
                        echo $pending_requests;
                        ?>

                    </strong>


                    <p>
                        Pending Requests
                    </p>

                </div>

                <div class="summary-box">

                    <strong>

                        <?php
                        echo $donors_count;
                        ?>

                    </strong>


                    <p>
                        Registered Donors
                    </p>

                </div>


            </div>


            

            <h4 style="margin-top: 30px;">

                Blood Inventory Overview

            </h4>


            <div class="overview-grid">


                <?php if (count($inventory_data) > 0): ?>


                    <?php foreach ($inventory_data as $item): ?>


                        <div class="blood-overview">


                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $item["blood_group"]
                                );

                                ?>

                            </strong>


                            <span>

                                <?php

                                echo (int)$item["units"];

                                ?>

                                Units

                            </span>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <p>

                        No blood inventory data available.

                    </p>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


</body>

</html>
