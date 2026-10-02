<?php

session_start();

include "../conn.php";

$message = "";
$message_type = "";

if (isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {

    $user_id = (int) $_SESSION["user_id"];

} else {

    header("Location: ../login.php");
    exit();

}

$user_sql = "SELECT
                id,
                full_name,
                email,
                phone,
                address,
                role
             FROM users
             WHERE id = ?
             LIMIT 1";

$user_stmt = $conn->prepare($user_sql);

if (!$user_stmt) {
    die("Database error: " . $conn->error);
}

$user_stmt->bind_param("i", $user_id);

$user_stmt->execute();

$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {

    session_destroy();

    header("Location: ../login.php");
    exit();
}

$user = $user_result->fetch_assoc();

$user_stmt->close();

$user_role = strtolower(trim($user["role"]));

if (
    $user_role !== "recipient" &&
    $user_role !== "patient" &&
    $user_role !== "receiver"
) {

    die("Access denied. This page is only available to recipients.");

}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $quantity = intval($_POST["quantity"] ?? 0);

    $blood_group = trim(
        $_POST["blood_group"] ?? ""
    );

    $hospital_name = trim(
        $_POST["hospital_name"] ?? ""
    );

    if ($quantity <= 0) {

        $message = "Please enter a valid number of blood units.";
        $message_type = "error";

    } elseif (empty($blood_group)) {

        $message = "Please select a blood group.";
        $message_type = "error";

    } elseif (empty($hospital_name)) {

        $message = "Please enter the hospital name.";
        $message_type = "error";

    } else {

        $status = "Pending";

        $request_sql = "INSERT INTO blood_request
                        (
                            recipient_id,
                            blood_group,
                            quantity,
                            hospital_name,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?)";

        $request_stmt = $conn->prepare($request_sql);

        if (!$request_stmt) {

            $message = "Database error: " . $conn->error;
            $message_type = "error";

        } else {

            $request_stmt->bind_param(
                "isiss",
                $user_id,
                $blood_group,
                $quantity,
                $hospital_name,
                $status
            );

            if ($request_stmt->execute()) {

                $message = "Blood request submitted successfully!";
                $message_type = "success";

                $quantity = "";
                $blood_group = "";
                $hospital_name = "";

            } else {

                $message = "Failed to submit blood request.";
                $message_type = "error";
            }

            $request_stmt->close();
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
        Request Blood | Blood Donor System
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
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .user-info {
            background: #fff5f5;
            border-left: 4px solid #c62828;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
        }

        .user-info h3 {
            margin-bottom: 8px;
            color: #c62828;
        }

        .user-info p {
            margin: 5px 0;
        }

        .form-card label {
            display: block;
            margin-bottom: 18px;
            font-weight: 600;
        }

        .form-card input,
        .form-card select {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .form-card input:focus,
        .form-card select:focus {
            outline: none;
            border-color: #c62828;
        }

        .button {
            border: none;
            cursor: pointer;
        }

    </style>

</head>

<body>

<div class="dashboard-shell">

    <aside class="sidebar">

        <h2>
            Recipient Panel
        </h2>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="register.php">
            Register
        </a>

        <a href="request.php">
            Request
        </a>

        <a href="status.php">
            Status
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </aside>

    <main class="main-panel">

        <h1>
            Request Blood
        </h1>

        <div class="panel">

            <div class="user-info">

                <h3>
                    Recipient Information
                </h3>

                <p>
                    <strong>Name:</strong>
                    <?php
                    echo htmlspecialchars($user["full_name"]);
                    ?>
                </p>

                <p>
                    <strong>Email:</strong>
                    <?php
                    echo htmlspecialchars($user["email"]);
                    ?>
                </p>

                <p>
                    <strong>Phone:</strong>
                    <?php
                    echo htmlspecialchars($user["phone"]);
                    ?>
                </p>

                <p>
                    <strong>Address:</strong>
                    <?php
                    echo htmlspecialchars($user["address"]);
                    ?>
                </p>

            </div>

            <?php if (!empty($message)): ?>

                <div class="message <?php echo $message_type; ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>

            <form
                class="form-card"
                method="POST"
                action="request.php"
            >

                <label>

                    Units Needed

                    <input
                        type="number"
                        name="quantity"
                        min="1"
                        max="20"
                        value="<?php echo htmlspecialchars($quantity ?? ''); ?>"
                        placeholder="Enter number of units"
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

                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>

                    </select>

                </label>

                <label>

                    Hospital Name

                    <input
                        type="text"
                        name="hospital_name"
                        value="<?php echo htmlspecialchars($hospital_name ?? ''); ?>"
                        placeholder="Enter hospital name"
                        maxlength="150"
                        required
                    >

                </label>

                <button
                    class="button primary"
                    type="submit"
                >
                    Submit Blood Request
                </button>

            </form>

        </div>

    </main>

</div>

</body>

</html>
