<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../conn.php");

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_notification"])) {

    $notification_message = trim($_POST["notification_message"]);
    $notification_type = trim($_POST["notification_type"]);

    $allowed_types = [
        "General",
        "Reminder",
        "Urgent",
        "Information"
    ];

    if ($notification_message == "") {

        $message = "Please enter a notification message.";
        $message_type = "error";

    } elseif (!in_array($notification_type, $allowed_types)) {

        $message = "Invalid notification type.";
        $message_type = "error";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO notifications (message, notification_type)
             VALUES (?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $notification_message,
            $notification_type
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Notification added successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to add notification.";
            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_notification"])) {

    $id = (int) $_POST["id"];

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM notifications WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {

            $message = "Notification deleted successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to delete notification.";
            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}


$notifications = [];

$sql = "
    SELECT id, message, notification_type, created_at
    FROM notifications
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $notifications[] = $row;
    }
}

$total_notifications = count($notifications);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Notifications | Blood Donor System</title>

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

        .notification-form {
            margin-top: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            min-height: 100px;
            resize: vertical;
        }

        .add-btn {
            background: #c62828;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .add-btn:hover {
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

        .notification-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .notification-table th,
        .notification-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .notification-table th {
            background: #c62828;
            color: white;
        }

        .notification-type {
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: bold;
        }

        .general {
            background: #eeeeee;
            color: #424242;
        }

        .reminder {
            background: #fff3e0;
            color: #ef6c00;
        }

        .urgent {
            background: #ffebee;
            color: #c62828;
        }

        .information {
            background: #e3f2fd;
            color: #1565c0;
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

        .notification-count {
            margin-top: 15px;
            padding: 15px;
            background: #fff;
            border-left: 4px solid #c62828;
        }

        .no-data {
            padding: 20px;
            text-align: center;
            color: #777;
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

    <aside class="sidebar">

        <h2>Admin Panel</h2>

        <a href="dashboard.php">Dashboard</a>
        <a href="donors.php">Donors</a>
        <a href="recipients.php">Recipients</a>
        <a href="hospitals.php">Hospitals</a>
        <a href="inventory.php">Inventory</a>
        <a href="requests.php">Requests</a>
        <a href="diseases.php">Diseases</a>
        <a href="notifications.php" class="active">Notifications</a>
        <a href="payments.php">Payments</a>

        <a href="../logout.php">Logout</a>

    </aside>

    <main class="main-panel">

        <h1>Notifications</h1>

        <div class="panel">

            <p>
                Manage important notifications, reminders,
                and alerts for the Blood Donor Management System.
            </p>

            <div class="notification-count">

                <strong>
                    Total Notifications:
                </strong>

                <?php echo $total_notifications; ?>

            </div>

        </div>

        <?php if ($message != ""): ?>

            <div class="alert <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>

        <div class="panel notification-form">

            <h3>Add Notification</h3>

            <form method="POST">

                <div class="form-grid">

                    <div class="form-group">

                        <label for="notification_type">
                            Notification Type
                        </label>

                        <select
                            name="notification_type"
                            id="notification_type"
                            required
                        >

                            <option value="General">
                                General
                            </option>

                            <option value="Reminder">
                                Reminder
                            </option>

                            <option value="Urgent">
                                Urgent
                            </option>

                            <option value="Information">
                                Information
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Date
                        </label>

                        <input
                            type="text"
                            value="<?php echo date('Y-m-d'); ?>"
                            readonly
                        >

                    </div>


                    <div class="form-group full">

                        <label for="notification_message">
                            Notification Message
                        </label>

                        <textarea
                            name="notification_message"
                            id="notification_message"
                            placeholder="Enter notification message"
                            required
                        ></textarea>

                    </div>


                    <div class="form-group full">

                        <button
                            type="submit"
                            name="add_notification"
                            class="add-btn"
                        >
                            Add Notification
                        </button>

                    </div>

                </div>

            </form>

        </div>


        <div class="panel">

            <h3>Notification Records</h3>

            <?php if ($total_notifications > 0): ?>

                <div style="overflow-x:auto;">

                    <table class="notification-table">

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($notifications as $notification): ?>

                            <?php

                            $type = $notification["notification_type"];

                            $type_class = strtolower($type);

                            ?>

                            <tr>

                                <td>
                                    <?php
                                    echo (int)$notification["id"];
                                    ?>
                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $notification["message"]
                                    );
                                    ?>

                                </td>

                                <td>

                                    <span
                                        class="notification-type <?php echo htmlspecialchars($type_class); ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars($type);
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $notification["created_at"]
                                    );
                                    ?>

                                </td>

                                <td>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this notification?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo (int)$notification["id"]; ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_notification"
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

                <div class="no-data">

                    No notifications found.

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>