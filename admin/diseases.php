<?php

session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}


include("../conn.php");


$message = "";
$message_type = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_disease"])) {

    $disease_name = trim($_POST["disease_name"]);
    $description = trim($_POST["description"]);
    $eligibility_status = $_POST["eligibility_status"];

    $allowed_status = [
        "Eligible",
        "Temporarily Ineligible",
        "Permanently Ineligible"
    ];


    if ($disease_name == "") {

        $message = "Please enter the disease name.";
        $message_type = "error";

    } elseif (!in_array($eligibility_status, $allowed_status)) {

        $message = "Invalid eligibility status.";
        $message_type = "error";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO diseases
            (disease_name, description, eligibility_status)
            VALUES (?, ?, ?)"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $disease_name,
            $description,
            $eligibility_status
        );


        if (mysqli_stmt_execute($stmt)) {

            $message = "Disease record added successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to add disease record.";
            $message_type = "error";

        }


        mysqli_stmt_close($stmt);
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_disease"])) {

    $id = (int) $_POST["id"];


    if ($id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM diseases WHERE id = ?"
        );


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );


        if (mysqli_stmt_execute($stmt)) {

            $message = "Disease record deleted successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to delete disease record.";
            $message_type = "error";

        }


        mysqli_stmt_close($stmt);
    }
}

3tw
$diseases = [];


$sql = "
    SELECT
        id,
        disease_name,
        description,
        eligibility_status,
        created_at
    FROM diseases
    ORDER BY id DESC
";


$result = mysqli_query($conn, $sql);


if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $diseases[] = $row;

    }
}


$total_diseases = count($diseases);


$eligible_count = 0;

$temp_ineligible_count = 0;

$permanent_ineligible_count = 0;


foreach ($diseases as $disease) {

    if ($disease["eligibility_status"] == "Eligible") {

        $eligible_count++;

    }


    if ($disease["eligibility_status"] == "Temporarily Ineligible") {

        $temp_ineligible_count++;

    }


    if ($disease["eligibility_status"] == "Permanently Ineligible") {

        $permanent_ineligible_count++;

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
        Diseases | Blood Donor System
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

        .disease-form {

            margin-top: 20px;

        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    2,
                    1fr
                );

            gap: 15px;

        }


        .form-group {

            display: flex;

            flex-direction: column;

        }


        .form-group.full {

            grid-column: 1 / -1;

        }


        .form-group label {

            margin-bottom: 6px;

            font-weight: 600;

        }


        .form-group input,
        .form-group textarea,
        .form-group select {

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;

        }


        .form-group textarea {

            resize: vertical;

            min-height: 90px;

        }


        .btn {

            background: #c62828;

            color: white;

            border: none;

            padding: 10px 18px;

            border-radius: 6px;

            cursor: pointer;

            font-weight: 600;

        }


        .btn:hover {

            background: #a91f1f;

        }


        .alert {

            padding: 12px;

            margin: 15px 0;

            border-radius: 6px;

        }


        .alert.success {

            background: #e8f5e9;

            color: #2e7d32;

        }


        .alert.error {

            background: #ffebee;

            color: #c62828;

        }


        .stats {

            margin-top: 20px;

        }


        .disease-table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 20px;

        }


        .disease-table th,
        .disease-table td {

            padding: 12px;

            border-bottom: 1px solid #ddd;

            text-align: left;

        }


        .disease-table th {

            background: #c62828;

            color: white;

        }


        .status {

            padding: 5px 9px;

            border-radius: 5px;

            font-size: 13px;

            font-weight: 600;

        }


        .status.eligible {

            background: #e8f5e9;

            color: #2e7d32;

        }


        .status.temporary {

            background: #fff3e0;

            color: #ef6c00;

        }


        .status.permanent {

            background: #ffebee;

            color: #c62828;

        }


        .delete-btn {

            background: #d32f2f;

            color: white;

            border: none;

            padding: 7px 12px;

            border-radius: 5px;

            cursor: pointer;

        }


        .delete-btn:hover {

            background: #b71c1c;

        }


        @media (max-width: 700px) {

            .form-grid {

                grid-template-columns: 1fr;

            }


            .form-group.full {

                grid-column: auto;

            }


            .disease-table {

                font-size: 13px;

            }

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


        <a
            class="active"
            href="diseases.php"
        >
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
            Diseases
        </h1>


        <div class="panel">

            <p>
                Review screening records and eligibility flags.
            </p>

        </div>


        <?php if ($message != ""): ?>


            <div class="alert <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>


        <?php endif; ?>

        <div class="stats">


            <div class="stat-box">

                <h3>
                    <?php
                    echo $total_diseases;
                    ?>
                </h3>

                <p>
                    Total Diseases
                </p>

            </div>


            <div class="stat-box">

                <h3>
                    <?php
                    echo $eligible_count;
                    ?>
                </h3>

                <p>
                    Eligible
                </p>

            </div>


            <div class="stat-box">

                <h3>
                    <?php
                    echo $temp_ineligible_count;
                    ?>
                </h3>

                <p>
                    Temporarily Ineligible
                </p>

            </div>


            <div class="stat-box">

                <h3>
                    <?php
                    echo $permanent_ineligible_count;
                    ?>
                </h3>

                <p>
                    Permanently Ineligible
                </p>

            </div>


        </div>


        <div class="panel disease-form">


            <h3>
                Add Disease / Screening Record
            </h3>


            <form method="POST">


                <div class="form-grid">


                    <div class="form-group">


                        <label for="disease_name">

                            Disease Name

                        </label>


                        <input
                            type="text"
                            id="disease_name"
                            name="disease_name"
                            placeholder="Enter disease name"
                            required
                        >


                    </div>


                    <div class="form-group">


                        <label for="eligibility_status">

                            Eligibility Status

                        </label>


                        <select
                            id="eligibility_status"
                            name="eligibility_status"
                            required
                        >


                            <option value="Eligible">

                                Eligible

                            </option>


                            <option value="Temporarily Ineligible">

                                Temporarily Ineligible

                            </option>


                            <option value="Permanently Ineligible">

                                Permanently Ineligible

                            </option>


                        </select>


                    </div>


                    <div class="form-group full">


                        <label for="description">

                            Description

                        </label>


                        <textarea
                            id="description"
                            name="description"
                            placeholder="Enter disease description or screening information"
                        ></textarea>


                    </div>


                    <div class="form-group full">


                        <button
                            type="submit"
                            name="add_disease"
                            class="btn"
                        >

                            Add Disease

                        </button>


                    </div>


                </div>


            </form>


        </div>

        <div class="panel">


            <h3>
                Disease Screening Records
            </h3>


            <?php if (count($diseases) > 0): ?>


                <div style="overflow-x:auto;">


                    <table class="disease-table">


                        <thead>


                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Disease
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Eligibility
                                </th>

                                <th>
                                    Date Added
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php foreach ($diseases as $disease): ?>


                            <tr>


                                <td>

                                    <?php
                                    echo (int)$disease["id"];
                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $disease["disease_name"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $disease["description"]
                                        ?: "No description"
                                    );

                                    ?>

                                </td>


                                <td>


                                    <?php


                                    $status_class = "eligible";


                                    if (
                                        $disease["eligibility_status"]
                                        == "Temporarily Ineligible"
                                    ) {

                                        $status_class = "temporary";

                                    }


                                    if (
                                        $disease["eligibility_status"]
                                        == "Permanently Ineligible"
                                    ) {

                                        $status_class = "permanent";

                                    }


                                    ?>


                                    <span
                                        class="status <?php echo $status_class; ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $disease["eligibility_status"]
                                        );

                                        ?>

                                    </span>


                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $disease["created_at"]
                                    );

                                    ?>

                                </td>


                                <td>


                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this disease record?');"
                                    >


                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo (int)$disease["id"]; ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="delete_disease"
                                            class="delete-btn"
                                        >

                                            Delete

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <p>

                    No disease records found.

                </p>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>
