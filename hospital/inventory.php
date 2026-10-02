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


$logged_in_user =
    $_SESSION["username"]
    ?? $_SESSION["admin_name"]
    ?? $_SESSION["hospital_name"]
    ?? "Hospital User";


$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $blood_group = trim($_POST["blood_group"]);
    $units = (int) $_POST["units"];


    if (empty($blood_group) || $units <= 0) {

        $message = "Please enter a valid blood group and number of units.";

    } else {

        $check_sql = "
            SELECT id
            FROM blood_inventory
            WHERE blood_group = ?
        ";

        $stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $blood_group
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);


        if (mysqli_num_rows($result) > 0) {


            $row = mysqli_fetch_assoc($result);

            $inventory_id = $row["id"];


            $update_sql = "
                UPDATE blood_inventory
                SET units = units + ?
                WHERE id = ?
            ";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "ii",
                $units,
                $inventory_id
            );


            if (mysqli_stmt_execute($update_stmt)) {

                $message =
                    $blood_group .
                    " inventory updated successfully.";

            } else {

                $message =
                    "Error updating inventory.";
            }


            mysqli_stmt_close($update_stmt);

        } else {

    
        
            $insert_sql = "
                INSERT INTO blood_inventory
                (blood_group, units)
                VALUES (?, ?)
            ";

            $insert_stmt = mysqli_prepare(
                $conn,
                $insert_sql
            );

            mysqli_stmt_bind_param(
                $insert_stmt,
                "si",
                $blood_group,
                $units
            );


            if (mysqli_stmt_execute($insert_stmt)) {

                $message =
                    $blood_group .
                    " added to inventory successfully.";

            } else {

                $message =
                    "Error adding blood inventory.";
            }


            mysqli_stmt_close($insert_stmt);
        }


        mysqli_stmt_close($stmt);
    }
}


$inventory = [];


$sql = "
    SELECT id, blood_group, units
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

        $inventory[] = $row;
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
        Hospital Inventory | Blood Donor System
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

        .inventory-card {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(180px, 1fr)
            );
            gap: 20px;
            margin-top: 20px;
        }


        .blood-card {
            padding: 20px;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #ddd;
            text-align: center;
        }


        .blood-card h2 {
            color: #c62828;
            margin-bottom: 10px;
        }


        .blood-card .units {
            font-size: 28px;
            font-weight: bold;
        }


        .blood-card p {
            margin: 5px 0 0;
        }


        .inventory-form {
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
        }


        .inventory-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
        }


        .inventory-form select,
        .inventory-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }


        .inventory-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: #c62828;
            color: white;
            cursor: pointer;
        }


        .message {
            padding: 12px;
            margin-bottom: 20px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 5px;
        }


        .user-info {
            padding: 10px 15px;
            margin-bottom: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            font-weight: 600;
        }

    </style>

</head>


<body>


<div class="dashboard-shell">


    <aside class="sidebar">


        <h2>
            Hospital Panel
        </h2>


        

        <div class="user-info">

            Welcome,
            <?php
            echo htmlspecialchars($logged_in_user);
            ?>

        </div>


        <a href="dashboard.php">
            Dashboard
        </a>


        <a
            class="active"
            href="inventory.php"
        >
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


    </aside>



    <main class="main-panel">


        <h1>
            Blood Inventory
        </h1>


        <?php if (!empty($message)): ?>

            <div class="message">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>



        <div class="panel inventory-form">


            <h3>
                Add Blood Units
            </h3>


            <form
                method="POST"
                action=""
            >


                <label>
                    Blood Group
                </label>


                <select
                    name="blood_group"
                    required
                >

                    <option value="">
                        Select Blood Group
                    </option>

                    <option value="A+">
                        A+
                    </option>

                    <option value="A-">
                        A-
                    </option>

                    <option value="B+">
                        B+
                    </option>

                    <option value="B-">
                        B-
                    </option>

                    <option value="O+">
                        O+
                    </option>

                    <option value="O-">
                        O-
                    </option>

                    <option value="AB+">
                        AB+
                    </option>

                    <option value="AB-">
                        AB-
                    </option>

                </select>



                <label>
                    Number of Units
                </label>


                <input
                    type="number"
                    name="units"
                    min="1"
                    placeholder="Enter units"
                    required
                >


                <button type="submit">
                    Add Blood
                </button>


            </form>


        </div>


        <div class="panel">


            <h3>
                Current Blood Inventory
            </h3>


            <div class="inventory-card">


                <?php if (count($inventory) > 0): ?>


                    <?php foreach ($inventory as $item): ?>


                        <div class="blood-card">


                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $item["blood_group"]
                                );
                                ?>

                            </h2>


                            <div class="units">

                                <?php
                                echo (int) $item["units"];
                                ?>

                            </div>


                            <p>
                                Available Units
                            </p>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <p>
                        No blood inventory available.
                    </p>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


</body>

</html>