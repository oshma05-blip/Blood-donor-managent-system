<?php
session_start();

include "../conn.php";

// Check if donor is logged in
if (!isset($_SESSION["donor_id"])) {
    header("Location: ../login.php");
    exit();
}

$donor_id = $_SESSION["donor_id"];

$message = "";
$message_type = "";

// --------------------------------------------------
// UPDATE PROFILE
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $dob = $_POST["dob"] ?? "";
    $blood_group = trim($_POST["blood_group"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if (
        empty($full_name) ||
        empty($gender) ||
        empty($dob) ||
        empty($blood_group) ||
        empty($address) ||
        empty($phone) ||
        empty($email)
    ) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        // Check whether email is already used by another donor
        $check_sql = "SELECT Donor_ID
                      FROM donor
                      WHERE Email = ?
                      AND Donor_ID != ?";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $donor_id);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $message = "This email address is already registered.";
            $message_type = "error";

        } else {

            // Update donor profile
            $update_sql = "UPDATE donor
                           SET Full_Name = ?,
                               GENDER = ?,
                               DOB = ?,
                               Blood_Group = ?,
                               Address = ?,
                               Phone = ?,
                               Email = ?
                           WHERE Donor_ID = ?";

            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "sssssssi",
                $full_name,
                $gender,
                $dob,
                $blood_group,
                $address,
                $phone,
                $email,
                $donor_id
            );

            if ($update_stmt->execute()) {

                // Update session information
                $_SESSION["donor_name"] = $full_name;
                $_SESSION["donor_email"] = $email;
                $_SESSION["blood_group"] = $blood_group;

                $message = "Profile updated successfully!";
                $message_type = "success";

            } else {

                $message = "Unable to update profile.";
                $message_type = "error";
            }

            $update_stmt->close();
        }

        $check_stmt->close();
    }
}


// --------------------------------------------------
// GET DONOR PROFILE
// --------------------------------------------------

$sql = "SELECT
            Donor_ID,
            Full_Name,
            GENDER,
            DOB,
            Blood_Group,
            Address,
            Phone,
            Email,
            Donation_Date,
            Eligibility_Status
        FROM donor
        WHERE Donor_ID = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    session_unset();
    session_destroy();

    header("Location: ../login.php");
    exit();
}

$donor = $result->fetch_assoc();

$stmt->close();


// --------------------------------------------------
// FORMAT LAST DONATION DATE
// --------------------------------------------------

$last_donation = "No donation recorded";

if (!empty($donor["Donation_Date"])) {

    $last_donation = date(
        "Y-m-d",
        strtotime($donor["Donation_Date"])
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Donor Profile | Blood Donor System</title>

    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="../css/dashboard.css">

    <link rel="stylesheet" href="../css/responsive.css">

</head>

<body>

<div class="dashboard-shell">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>Donor Panel</h2>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="profile.php" class="active">
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

        <h1>My Profile</h1>


        <div class="panel">

            <?php if (!empty($message)): ?>

                <div class="<?php
                    echo ($message_type == "success")
                        ? "success-message"
                        : "error-message";
                ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <!-- PROFILE INFORMATION -->

            <h2>Personal Information</h2>

            <form method="POST" action="profile.php" class="form-card">


                <!-- Full Name -->

                <label>

                    Full Name

                    <input
                        type="text"
                        name="full_name"
                        value="<?php
                            echo htmlspecialchars($donor["Full_Name"]);
                        ?>"
                        required
                    >

                </label>


                <!-- Gender -->

                <label>

                    Gender

                    <select name="gender" required>

                        <option value="">
                            Select Gender
                        </option>

                        <option value="Male"
                            <?php
                            if ($donor["GENDER"] == "Male")
                                echo "selected";
                            ?>>
                            Male
                        </option>

                        <option value="Female"
                            <?php
                            if ($donor["GENDER"] == "Female")
                                echo "selected";
                            ?>>
                            Female
                        </option>

                        <option value="Other"
                            <?php
                            if ($donor["GENDER"] == "Other")
                                echo "selected";
                            ?>>
                            Other
                        </option>

                    </select>

                </label>


                <!-- Date of Birth -->

                <label>

                    Date of Birth

                    <input
                        type="date"
                        name="dob"
                        value="<?php
                            echo htmlspecialchars($donor["DOB"]);
                        ?>"
                        required
                    >

                </label>


                <!-- Blood Group -->

                <label>

                    Blood Group

                    <select name="blood_group" required>

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

                        foreach ($blood_groups as $group) {

                            $selected =
                                ($donor["Blood_Group"] == $group)
                                ? "selected"
                                : "";

                            echo "<option value=\"$group\" $selected>
                                    $group
                                  </option>";
                        }

                        ?>

                    </select>

                </label>


                <!-- Address -->

                <label>

                    Address

                    <input
                        type="text"
                        name="address"
                        value="<?php
                            echo htmlspecialchars($donor["Address"]);
                        ?>"
                        required
                    >

                </label>


                <!-- Phone -->

                <label>

                    Phone

                    <input
                        type="text"
                        name="phone"
                        value="<?php
                            echo htmlspecialchars($donor["Phone"]);
                        ?>"
                        required
                    >

                </label>


                <!-- Email -->

                <label>

                    Email

                    <input
                        type="email"
                        name="email"
                        value="<?php
                            echo htmlspecialchars($donor["Email"]);
                        ?>"
                        required
                    >

                </label>


                <!-- Donor ID -->

                <label>

                    Donor ID

                    <input
                        type="text"
                        value="<?php
                            echo htmlspecialchars($donor["Donor_ID"]);
                        ?>"
                        readonly
                    >

                </label>


                <!-- Eligibility -->

                <label>

                    Eligibility Status

                    <input
                        type="text"
                        value="<?php
                            echo htmlspecialchars(
                                $donor["Eligibility_Status"]
                            );
                        ?>"
                        readonly
                    >

                </label>


                <!-- Last Donation -->

                <label>

                    Last Donation

                    <input
                        type="text"
                        value="<?php
                            echo htmlspecialchars($last_donation);
                        ?>"
                        readonly
                    >

                </label>


                <button
                    type="submit"
                    class="button primary"
                >
                    Update Profile
                </button>

            </form>

        </div>

    </main>

</div>

</body>

</html>

<?php
$conn->close();
?>