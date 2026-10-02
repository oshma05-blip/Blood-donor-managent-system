<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../conn.php");

$donors = [];

$sql = "
    SELECT id, full_name, blood_group
    FROM donors
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $donors[] = $row;

    }
}


$total_donors = count($donors);

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
        Donors | Blood Donor System
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


    <style>

        .donor-table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 20px;

        }


        .donor-table th,
        .donor-table td {

            padding: 12px;

            border-bottom: 1px solid #ddd;

            text-align: left;

        }


        .donor-table th {

            background-color: #c62828;

            color: white;

        }


        .blood-group {

            display: inline-block;

            padding: 6px 10px;

            background-color: #ffebee;

            color: #c62828;

            border-radius: 5px;

            font-weight: bold;

        }


        .donor-count {

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


        <h2>
            Admin Panel
        </h2>


        <a href="dashboard.php">
            Dashboard
        </a>


        <a
            class="active"
            href="donors.php"
        >
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
            Donors
        </h1>


        <div class="panel">


            <p>

                View registered blood donors and their blood groups.

            </p>


            <div class="donor-count">


                <strong>

                    Total Registered Donors:

                </strong>


                <?php

                echo $total_donors;

                ?>


            </div>


        </div>


        <div class="panel">


            <h3>
                Registered Donors
            </h3>


            <?php if ($total_donors > 0): ?>


                <div style="overflow-x:auto;">


                    <table class="donor-table">


                        <thead>


                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Donor Name
                                </th>

                                <th>
                                    Blood Group
                                </th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php foreach ($donors as $donor): ?>


                            <tr>


                                <td>

                                    <?php

                                    echo (int)$donor["id"];

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $donor["full_name"]
                                    );

                                    ?>

                                </td>


                                <td>


                                    <span class="blood-group">


                                        <?php

                                        echo htmlspecialchars(
                                            $donor["blood_group"]
                                        );

                                        ?>


                                    </span>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <div class="no-data">

                    No donors have been registered yet.

                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>
