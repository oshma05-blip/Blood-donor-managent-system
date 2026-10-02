<?php
session_start();

include "../conn.php";


if (!isset($_SESSION["donor_id"])) {
    header("Location: ../login.php");
    exit();
}

$donor_id = (int) $_SESSION["donor_id"];

$message = "";
$message_type = "";


$check_sql = "
    SELECT Donor_ID, Full_Name
    FROM donor
    WHERE Donor_ID = ?
";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    die("Database error: " . $conn->error);
}

$check_stmt->bind_param("i", $donor_id);
$check_stmt->execute();

$check_result = $check_stmt->get_result();

$donor = $check_result->fetch_assoc();

$check_stmt->close();


if (!$donor) {

    $message = "Your donor profile was not found. Please complete your donor profile first.";
    $message_type = "danger";

} else {

    $donor_name = $donor["Full_Name"];


    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $donation_date = trim($_POST["donation_date"] ?? "");
        $hospital = trim($_POST["hospital"] ?? "");
        $status = "Pending";


        if (empty($donation_date) || empty($hospital)) {

            $message = "Please fill in all required fields.";
            $message_type = "danger";

        } else {


            $date_object = DateTime::createFromFormat(
                "Y-m-d",
                $donation_date
            );

            if (
                !$date_object ||
                $date_object->format("Y-m-d") !== $donation_date
            ) {

                $message = "Please enter a valid donation date.";
                $message_type = "danger";

            } else {



                $insert_sql = "
                    INSERT INTO donation
                    (
                        Donor_ID,
                        Donation_Date,
                        Hospital,
                        Status
                    )
                    VALUES (?, ?, ?, ?)
                ";

                $insert_stmt = $conn->prepare($insert_sql);

                if (!$insert_stmt) {

                    $message = "Database error: " . $conn->error;
                    $message_type = "danger";

                } else {

                    $insert_stmt->bind_param(
                        "isss",
                        $donor_id,
                        $donation_date,
                        $hospital,
                        $status
                    );


                    if ($insert_stmt->execute()) {

                        $message = "Donation scheduled successfully.";
                        $message_type = "success";

                    } else {

                        $message = "Unable to schedule donation: "
                                 . $insert_stmt->error;

                        $message_type = "danger";
                    }

                    $insert_stmt->close();
                }
            }
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
        Schedule Donation | Blood Donor System
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

        .donation-form-container {
            max-width: 700px;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #b30000;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            background: #b30000;
            color: #ffffff;
            cursor: pointer;
            text-decoration: none;
        }

        .button:hover {
            background: #8f0000;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .donor-info {
            background: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #b30000;
            margin-bottom: 25px;
        }

    </style>

</head>


<body>

<div class="dashboard-shell">


    <aside class="sidebar">

        <h2>Donor Panel</h2>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="donation.php" class="active">
            Donation
        </a>

        <a href="history.php">
            History
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </aside>


    <main class="main-panel">

        <h1>
            Schedule Blood Donation
        </h1>


        <div class="donation-form-container">


            <?php if (!empty($message)): ?>

                <div class="alert <?php echo $message_type; ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($donor): ?>


                <div class="donor-info">

                    <strong>
                        Donor:
                    </strong>

                    <?php
                    echo htmlspecialchars($donor_name);
                    ?>

                </div>


                <form
                    method="POST"
                    action=""
                >


                    <div class="form-group">

                        <label for="donation_date">
                            Donation Date
                        </label>

                        <input
                            type="date"
                            id="donation_date"
                            name="donation_date"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label for="hospital">
                            Hospital
                        </label>

                        <input
                            type="text"
                            id="hospital"
                            name="hospital"
                            placeholder="Enter hospital name"
                            maxlength="150"
                            required
                        >

                    </div>



                    <button
                        type="submit"
                        class="button"
                    >
                        Schedule Donation
                    </button>


                </form>


            <?php else: ?>


                <div class="alert danger">

                    Your donor profile does not exist.

                    Please register as a donor before
                    scheduling a donation.

                </div>


                <a
                    href="profile.php"
                    class="button"
                >
                    Create Donor Profile
                </a>


            <?php endif; ?>


        </div>

    </main>

</div>

</body>

</html>

<?php

$conn->close();

?>