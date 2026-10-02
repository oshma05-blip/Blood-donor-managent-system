<?php

session_start();

include("../conn.php");

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {

    header("Location: ../login.php");
    exit();

}

$logged_in_user = $_SESSION["username"]
    ?? $_SESSION["admin_name"]
    ?? $_SESSION["hospital_name"]
    ?? "User";


$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $patient_name = trim($_POST["patient_name"] ?? "");
    $blood_group = trim($_POST["blood_group"] ?? "");
    $units = (int) ($_POST["units"] ?? 0);

    if (
        empty($patient_name) ||
        empty($blood_group) ||
        $units <= 0
    ) {

        $message = "Please fill in all fields correctly.";
        $message_type = "error";

    } elseif (
        !in_array(
            $blood_group,
            [
                "A+",
                "A-",
                "B+",
                "B-",
                "O+",
                "O-",
                "AB+",
                "AB-"
            ]
        )
    ) {

        $message = "Please select a valid blood group.";
        $message_type = "error";

    } else {

        mysqli_begin_transaction($conn);


        try {



            $check_sql = "
                SELECT id, units
                FROM blood_inventory
                WHERE blood_group = ?
                FOR UPDATE
            ";


            $stmt = mysqli_prepare(
                $conn,
                $check_sql
            );


            if (!$stmt) {

                throw new Exception(
                    "Database error."
                );

            }


            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $blood_group
            );


            mysqli_stmt_execute($stmt);


            $result = mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($result) == 0) {

                throw new Exception(
                    "No inventory record found for blood group "
                    . $blood_group
                    . "."
                );

            }


            $inventory = mysqli_fetch_assoc($result);


            $inventory_id =
                $inventory["id"];


            $available_units =
                (int) $inventory["units"];


            if ($available_units < $units) {

                throw new Exception(
                    "Insufficient blood available. Only "
                    . $available_units
                    . " unit(s) of "
                    . $blood_group
                    . " are available."
                );

            }

            $new_units =
                $available_units - $units;


            $update_sql = "
                UPDATE blood_inventory
                SET units = ?
                WHERE id = ?
            ";


            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );


            if (!$update_stmt) {

                throw new Exception(
                    "Unable to update inventory."
                );

            }


            mysqli_stmt_bind_param(
                $update_stmt,
                "ii",
                $new_units,
                $inventory_id
            );


            if (
                !mysqli_stmt_execute(
                    $update_stmt
                )
            ) {

                throw new Exception(
                    "Failed to update blood inventory."
                );

            }

            $insert_sql = "
                INSERT INTO blood_issue
                (
                    patient_name,
                    blood_group,
                    units,
                    issue_date
                )
                VALUES (?, ?, ?, NOW())
            ";


            $insert_stmt = mysqli_prepare(
                $conn,
                $insert_sql
            );


            if (!$insert_stmt) {

                throw new Exception(
                    "Unable to create blood issue record."
                );

            }


            mysqli_stmt_bind_param(
                $insert_stmt,
                "ssi",
                $patient_name,
                $blood_group,
                $units
            );


            if (
                !mysqli_stmt_execute(
                    $insert_stmt
                )
            ) {

                throw new Exception(
                    "Failed to save blood issue record."
                );

            }

            mysqli_commit($conn);


            $message =
                "Blood issued successfully. "
                . $units
                . " unit(s) of "
                . $blood_group
                . " issued to "
                . htmlspecialchars($patient_name)
                . ".";


            $message_type = "success";


            mysqli_stmt_close($insert_stmt);
            mysqli_stmt_close($update_stmt);
            mysqli_stmt_close($stmt);


        } catch (Exception $e) {

            mysqli_rollback($conn);


            $message = $e->getMessage();

            $message_type = "error";

        }

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
        Blood Issue | Blood Donor System
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

        .message {

            padding: 12px 15px;

            margin-bottom: 20px;

            border-radius: 6px;

            font-weight: 500;

        }


        .success {

            background-color: #d4edda;

            color: #155724;

            border: 1px solid #c3e6cb;

        }


        .error {

            background-color: #f8d7da;

            color: #721c24;

            border: 1px solid #f5c6cb;

        }


        .form-card label {

            display: block;

            margin-bottom: 15px;

        }


        .form-card input,
        .form-card select {

            width: 100%;

            padding: 10px;

            margin-top: 6px;

        }


        .button {

            padding: 10px 20px;

            border: none;

            cursor: pointer;

            border-radius: 5px;

        }


        .welcome-user {

            margin-bottom: 20px;

            font-size: 16px;

        }

    </style>

</head>


<body>


<div class="dashboard-shell">


    <aside class="sidebar">


        <h2>
            Hospital Panel
        </h2>


        <a href="dashboard.php">

            Dashboard

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


    </aside>


    <main class="main-panel">


        <h1>
            Blood Issue
        </h1>


        <p class="welcome-user">

            Welcome,
            <strong>
                <?php
                echo htmlspecialchars(
                    $logged_in_user
                );
                ?>
            </strong>

        </p>


        <div class="panel">


            <?php if (!empty($message)): ?>


                <div
                    class="message
                    <?php
                    echo htmlspecialchars(
                        $message_type
                    );
                    ?>"
                >

                    <?php
                    echo $message;
                    ?>

                </div>


            <?php endif; ?>

            <form
                class="form-card"
                method="POST"
                action=""
            >


                <label>

                    Patient Name


                    <input
                        type="text"
                        name="patient_name"
                        placeholder="Enter patient name"
                        maxlength="100"
                        required
                    >

                </label>



                <label>

                    Blood Group


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

                </label>


                <label>

                    Units


                    <input
                        type="number"
                        name="units"
                        min="1"
                        placeholder="Enter number of units"
                        required
                    >

                </label>


                <button
                    class="button primary"
                    type="submit"
                >

                    Issue Blood

                </button>


            </form>


        </div>


    </main>


</div>


</body>

</html>