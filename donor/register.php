<?php
session_start();

include "../conn.php";

/* =========================================================
   CHECK LOGIN
   ========================================================= */

if (!isset($_SESSION["donor_id"])) {
    header("Location: ../login.php");
    exit();
}

$donor_id = (int) $_SESSION["donor_id"];

$message = "";
$message_type = "";


/* =========================================================
   CREATE DONOR TABLE IF NOT EXISTS
   ========================================================= */

$donor_table = "
CREATE TABLE IF NOT EXISTS donor (
    Donor_ID INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Gender VARCHAR(20),
    DOB DATE,
    Blood_Group VARCHAR(20),
    Address VARCHAR(255),
    Phone VARCHAR(20),
    Email VARCHAR(100),
    Availability VARCHAR(20) DEFAULT 'Available',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
";

if (!$conn->query($donor_table)) {
    die("Error creating donor table: " . $conn->error);
}


/* =========================================================
   CREATE DONATION TABLE IF NOT EXISTS
   ========================================================= */

$donation_table = "
CREATE TABLE IF NOT EXISTS donation (
    Donation_ID INT AUTO_INCREMENT PRIMARY KEY,
    Donor_ID INT NOT NULL,
    Donation_Date DATE NOT NULL,
    Hospital VARCHAR(150) NOT NULL,
    Status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT donation_donor_fk
    FOREIGN KEY (Donor_ID)
    REFERENCES donor(Donor_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
";

if (!$conn->query($donation_table)) {
    die("Error creating donation table: " . $conn->error);
}


/* =========================================================
   CHECK Donation_Date COLUMN
   ========================================================= */

$column_check = $conn->query("
    SHOW COLUMNS FROM donation LIKE 'Donation_Date'
");

if ($column_check->num_rows == 0) {

    $alter_sql = "
        ALTER TABLE donation
        ADD COLUMN Donation_Date DATE NULL
    ";

    if (!$conn->query($alter_sql)) {
        die("Unable to add Donation_Date: " . $conn->error);
    }
}


/* =========================================================
   CHECK EXISTING DONOR PROFILE
   ========================================================= */

$check_sql = "
    SELECT *
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

$existing_donor = $check_result->fetch_assoc();

$check_stmt->close();


/* =========================================================
   FORM SUBMISSION
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $dob = trim($_POST["dob"] ?? "");
    $blood_group = trim($_POST["blood_group"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $availability = trim($_POST["availability"] ?? "Available");


    /* =====================================================
       VALIDATION
       ===================================================== */

    if (
        empty($full_name) ||
        empty($gender) ||
        empty($dob) ||
        empty($blood_group) ||
        empty($address) ||
        empty($phone) ||
        empty($email)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "danger";

    } elseif (!preg_match("/^[0-9+\-\s]{7,20}$/", $phone)) {

        $message = "Please enter a valid phone number.";
        $message_type = "danger";

    } else {


        /* =================================================
           UPDATE EXISTING DONOR
           ================================================= */

        if ($existing_donor) {

            $update_sql = "
                UPDATE donor
                SET
                    Full_Name = ?,
                    Gender = ?,
                    DOB = ?,
                    Blood_Group = ?,
                    Address = ?,
                    Phone = ?,
                    Email = ?,
                    Availability = ?
                WHERE Donor_ID = ?
            ";

            $update_stmt = $conn->prepare($update_sql);

            if (!$update_stmt) {
                die("Database error: " . $conn->error);
            }

            $update_stmt->bind_param(
                "ssssssssi",
                $full_name,
                $gender,
                $dob,
                $blood_group,
                $address,
                $phone,
                $email,
                $availability,
                $donor_id
            );

            if ($update_stmt->execute()) {

                $message = "Donor profile updated successfully.";
                $message_type = "success";

            } else {

                $message = "Unable to update donor profile.";
                $message_type = "danger";
            }

            $update_stmt->close();


        } else {


            /* =================================================
               INSERT NEW DONOR
               ================================================= */

            /*
             * IMPORTANT:
             * If the logged-in donor ID does not already exist,
             * we insert the donor using that exact ID.
             */

            $insert_sql = "
                INSERT INTO donor
                (
                    Donor_ID,
                    Full_Name,
                    Gender,
                    DOB,
                    Blood_Group,
                    Address,
                    Phone,
                    Email,
                    Availability
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $insert_stmt = $conn->prepare($insert_sql);

            if (!$insert_stmt) {
                die("Database error: " . $conn->error);
            }

            $insert_stmt->bind_param(
                "issssssss",
                $donor_id,
                $full_name,
                $gender,
                $dob,
                $blood_group,
                $address,
                $phone,
                $email,
                $availability
            );

            if ($insert_stmt->execute()) {

                $message = "Donor registration completed successfully.";
                $message_type = "success";

                $existing_donor = true;

            } else {

                $message = "Unable to register donor: "
                         . $insert_stmt->error;

                $message_type = "danger";
            }

            $insert_stmt->close();
        }
    }
}


/* =========================================================
   GET DONOR DATA AGAIN
   ========================================================= */

$data_sql = "
    SELECT *
    FROM donor
    WHERE Donor_ID = ?
";

$data_stmt = $conn->prepare($data_sql);

$data_stmt->bind_param("i", $donor_id);

$data_stmt->execute();

$data_result = $data_stmt->get_result();

$donor_data = $data_result->fetch_assoc();

$data_stmt->close();

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
        Donor Registration | Blood Donor System
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

        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 90px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #b30000;
        }

        .button {
            padding: 12px 22px;
            border: none;
            border-radius: 5px;
            background: #b30000;
            color: #ffffff;
            cursor: pointer;
            font-size: 15px;
        }

        .button:hover {
            background: #8f0000;
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
        }

        .alert.danger {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

        }

    </style>

</head>


<body>

<div class="dashboard-shell">


    <!-- SIDEBAR -->

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

        <a href="history.php">
            History
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-panel">

        <h1>
            Donor Registration
        </h1>


        <div class="form-container">


            <?php if (!empty($message)): ?>

                <div class="alert <?php echo $message_type; ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST" action="">


                <div class="form-grid">


                    <!-- FULL NAME -->

                    <div class="form-group">

                        <label for="full_name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="<?php
                            echo htmlspecialchars(
                                $donor_data["Full_Name"] ?? ""
                            );
                            ?>"
                            required
                        >

                    </div>


                    <!-- GENDER -->

                    <div class="form-group">

                        <label for="gender">
                            Gender
                        </label>

                        <select
                            id="gender"
                            name="gender"
                            required
                        >

                            <option value="">
                                Select Gender
                            </option>

                            <option
                                value="Male"
                                <?php
                                if (
                                    ($donor_data["Gender"] ?? "")
                                    == "Male"
                                ) echo "selected";
                                ?>
                            >
                                Male
                            </option>

                            <option
                                value="Female"
                                <?php
                                if (
                                    ($donor_data["Gender"] ?? "")
                                    == "Female"
                                ) echo "selected";
                                ?>
                            >
                                Female
                            </option>

                            <option
                                value="Other"
                                <?php
                                if (
                                    ($donor_data["Gender"] ?? "")
                                    == "Other"
                                ) echo "selected";
                                ?>
                            >
                                Other
                            </option>

                        </select>

                    </div>


                    <!-- DATE OF BIRTH -->

                    <div class="form-group">

                        <label for="dob">
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            id="dob"
                            name="dob"
                            value="<?php
                            echo htmlspecialchars(
                                $donor_data["DOB"] ?? ""
                            );
                            ?>"
                            required
                        >

                    </div>


                    <!-- BLOOD GROUP -->

                    <div class="form-group">

                        <label for="blood_group">
                            Blood Group
                        </label>

                        <select
                            id="blood_group"
                            name="blood_group"
                            required
                        >

                            <option value="">
                                Select Blood Group
                            </option>

                            <?php

                            $blood_groups = [
                                "A+",
                                "A-",
                                "B+",
                                "B-",
                                "AB+",
                                "AB-",
                                "O+",
                                "O-"
                            ];

                            foreach ($blood_groups as $group):

                            ?>

                                <option
                                    value="<?php
                                    echo $group;
                                    ?>"
                                    <?php

                                    if (
                                        ($donor_data[
                                            "Blood_Group"
                                        ] ?? "")
                                        == $group
                                    ) {
                                        echo "selected";
                                    }

                                    ?>
                                >

                                    <?php
                                    echo $group;
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- PHONE -->

                    <div class="form-group">

                        <label for="phone">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="<?php
                            echo htmlspecialchars(
                                $donor_data["Phone"] ?? ""
                            );
                            ?>"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php
                            echo htmlspecialchars(
                                $donor_data["Email"] ?? ""
                            );
                            ?>"
                            required
                        >

                    </div>


                    <!-- ADDRESS -->

                    <div class="form-group full">

                        <label for="address">
                            Address
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            required
                        ><?php
                        echo htmlspecialchars(
                            $donor_data["Address"] ?? ""
                        );
                        ?></textarea>

                    </div>


                    <!-- AVAILABILITY -->

                    <div class="form-group">

                        <label for="availability">
                            Availability
                        </label>

                        <select
                            id="availability"
                            name="availability"
                        >

                            <option
                                value="Available"
                                <?php
                                if (
                                    ($donor_data[
                                        "Availability"
                                    ] ?? "Available")
                                    == "Available"
                                ) echo "selected";
                                ?>
                            >
                                Available
                            </option>

                            <option
                                value="Unavailable"
                                <?php
                                if (
                                    ($donor_data[
                                        "Availability"
                                    ] ?? "")
                                    == "Unavailable"
                                ) echo "selected";
                                ?>
                            >
                                Unavailable
                            </option>

                        </select>

                    </div>


                    <!-- SUBMIT -->

                    <div class="form-group">

                        <label>
                            &nbsp;
                        </label>

                        <button
                            type="submit"
                            class="button"
                        >

                            <?php
                            echo $donor_data
                                ? "Update Donor Profile"
                                : "Register as Donor";
                            ?>

                        </button>

                    </div>


                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>

<?php

$conn->close();

?>