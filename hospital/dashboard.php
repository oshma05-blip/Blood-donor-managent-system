<?php

session_start();

include("../conn.php");


if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    header("Location: ../login.php");
    exit();

}



$logged_in_user = $_SESSION["username"]
    ?? $_SESSION["admin_name"]
    ?? $_SESSION["hospital_name"]
    ?? "Hospital User";



$total_units = 0;

$sql = "SELECT COALESCE(SUM(units), 0) AS total_units
        FROM blood_inventory";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $total_units = (int)$row['total_units'];

}


$critical_requests = 0;

$sql = "SELECT COUNT(*) AS total
        FROM requests
        WHERE status = 'Pending'";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $critical_requests = (int)$row['total'];

}


$issued_today = 0;

$sql = "SELECT COALESCE(SUM(units), 0) AS total
        FROM blood_issue
        WHERE DATE(issue_date) = CURDATE()";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $issued_today = (int)$row['total'];

}


$inventory = [];

$sql = "SELECT blood_group, units
        FROM blood_inventory
        ORDER BY units ASC
        LIMIT 3";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $inventory[] = $row;

    }

}


$max_inventory = 20;



$today_operations = [];

$sql = "SELECT patient_name, blood_group, units
        FROM blood_issue
        WHERE DATE(issue_date) = CURDATE()
        ORDER BY issue_date DESC
        LIMIT 10";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $today_operations[] = $row;

    }

}


$urgent_requests = [];

$sql = "SELECT patient_name, blood_group, units, status
        FROM requests
        WHERE status = 'Pending'
        ORDER BY id DESC
        LIMIT 5";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $urgent_requests[] = $row;

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
        Hospital Dashboard | Blood Donor System
    </title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >


    <link
        rel="stylesheet"
        href="../css/responsive.css"
    >

</head>


<body class="dashboard-body">


<div class="dashboard-shell">


    <aside class="sidebar">


        <div class="brand-block">


            <div class="brand-icon">

                +

            </div>


            <div>

                <h2>
                    Hospital Portal
                </h2>

                <p>
                    Blood Management
                </p>

            </div>


        </div>



        <nav class="sidebar-nav">


            <a
                class="active"
                href="dashboard.php"
            >

                Overview

            </a>


            <a href="inventory.php">

                Inventory

            </a>


            <a href="requests.php">

                Requests

            </a>


            <a href="bloodissue.php">

                Blood Issue

            </a>


            <a href="../logout.php">

                Logout

            </a>


        </nav>



        <div class="sidebar-footer">


            <p>
                Logged in as
            </p>


            <strong>

                <?php

                echo htmlspecialchars(
                    $logged_in_user
                );

                ?>

            </strong>


            <p>
                System status
            </p>


            <strong>
                Live • 24/7 monitoring
            </strong>


        </div>


    </aside>



    <main class="main-panel">



        <header class="dashboard-header">


            <div>


                <p class="eyebrow">
                    Operational Overview
                </p>


                <h1>
                    Hospital Dashboard
                </h1>


                <p>

                    Welcome,

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $logged_in_user
                        );

                        ?>

                    </strong>

                    — Monitor inventory, urgent requests,
                    and blood issue activity from one
                    intelligent workspace.

                </p>


            </div>



            <div class="header-actions">


                <a
                    class="btn btn-outline-secondary btn-sm"
                    href="inventory.php"
                >

                    Export Report

                </a>


                <a
                    class="btn btn-danger btn-sm"
                    href="bloodissue.php"
                >

                    Issue Blood

                </a>


            </div>


        </header>


        <section class="stats">


            <div class="stat-box gradient-blue">


                <span class="stat-label">

                    Available Units

                </span>


                <strong>

                    <?php

                    echo $total_units;

                    ?>

                </strong>


                <p>

                    Across all blood groups

                </p>


            </div>


            <div class="stat-box gradient-red">


                <span class="stat-label">

                    Critical Requests

                </span>


                <strong>

                    <?php

                    echo $critical_requests;

                    ?>

                </strong>


                <p>

                    Pending requests

                </p>


            </div>

            <div class="stat-box gradient-green">


                <span class="stat-label">

                    Issued Today

                </span>


                <strong>

                    <?php

                    echo $issued_today;

                    ?>

                </strong>


                <p>

                    Units processed today

                </p>


            </div>


        </section>

        <section class="content-grid">


            <div class="panel">


                <div class="panel-header">


                    <h3>

                        Critical Inventory

                    </h3>


                    <a href="inventory.php">

                        View all

                    </a>


                </div>



                <div class="inventory-list">


                    <?php if (count($inventory) > 0): ?>


                        <?php foreach ($inventory as $item): ?>


                            <?php

                            $units =
                                (int)$item['units'];


                            $percentage =
                                ($units / $max_inventory) * 100;


                            if ($percentage > 100) {

                                $percentage = 100;

                            }

                            ?>


                            <div class="inventory-row">


                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $item['blood_group']
                                    );

                                    ?>

                                </strong>



                                <div class="progress">


                                    <div
                                        style="width: <?php echo $percentage; ?>%"
                                    ></div>


                                </div>



                                <span>

                                    <?php

                                    echo $units;

                                    ?>

                                    units

                                </span>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <p>

                            No inventory data available.

                        </p>


                    <?php endif; ?>


                </div>


            </div>

            <div class="panel">


                <div class="panel-header">


                    <h3>

                        Urgent Requests

                    </h3>


                    <a href="requests.php">

                        Manage

                    </a>


                </div>



                <ul class="request-list">


                    <?php if (count($urgent_requests) > 0): ?>


                        <?php foreach (
                            $urgent_requests
                            as $request
                        ): ?>


                            <li>


                                <div>


                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $request['patient_name']
                                        );

                                        ?>

                                    </strong>


                                    <p>


                                        <?php

                                        echo htmlspecialchars(
                                            $request['blood_group']
                                        );

                                        ?>


                                        •


                                        <?php

                                        echo (int)
                                            $request['units'];

                                        ?>

                                        units


                                    </p>


                                </div>



                                <span class="status-chip urgent">


                                    <?php

                                    echo htmlspecialchars(
                                        $request['status']
                                    );

                                    ?>


                                </span>


                            </li>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <li>


                            <div>


                                <strong>

                                    No pending requests

                                </strong>


                                <p>

                                    All requests are handled.

                                </p>


                            </div>


                        </li>


                    <?php endif; ?>


                </ul>


            </div>


        </section>

        <section class="panel">


            <div class="panel-header">


                <h3>

                    Today's Operations

                </h3>


                <a href="bloodissue.php">

                    Create Issue

                </a>


            </div>



            <div class="table-responsive">


                <table
                    class="data-table table align-middle"
                >


                    <thead>


                        <tr>


                            <th>

                                Patient

                            </th>


                            <th>

                                Group

                            </th>


                            <th>

                                Units

                            </th>


                            <th>

                                Status

                            </th>


                        </tr>


                    </thead>



                    <tbody>


                        <?php if (
                            count($today_operations) > 0
                        ): ?>


                            <?php foreach (
                                $today_operations
                                as $operation
                            ): ?>


                                <tr>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $operation[
                                                'patient_name'
                                            ]
                                        );

                                        ?>

                                    </td>



                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $operation[
                                                'blood_group'
                                            ]
                                        );

                                        ?>

                                    </td>



                                    <td>

                                        <?php

                                        echo (int)
                                            $operation[
                                                'units'
                                            ];

                                        ?>

                                    </td>



                                    <td>


                                        <span
                                            class="status-chip normal"
                                        >

                                            Completed

                                        </span>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <tr>


                                <td
                                    colspan="4"
                                    class="text-center"
                                >

                                    No blood issue records
                                    found for today.

                                </td>


                            </tr>


                        <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


    </main>


</div>


</body>

</html>