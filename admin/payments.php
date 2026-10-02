<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../conn.php");

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_payment"])) {

    $payer_name = trim($_POST["payer_name"]);
    $amount = (float) $_POST["amount"];
    $payment_method = trim($_POST["payment_method"]);
    $status = trim($_POST["status"]);

    $allowed_methods = [
        "Cash",
        "Bank Transfer",
        "eSewa",
        "Khalti",
        "Card"
    ];

    $allowed_status = [
        "Pending",
        "Paid",
        "Cancelled"
    ];

    if ($payer_name == "") {

        $message = "Please enter the payer name.";
        $message_type = "error";

    } elseif ($amount <= 0) {

        $message = "Please enter a valid payment amount.";
        $message_type = "error";

    } elseif (!in_array($payment_method, $allowed_methods)) {

        $message = "Invalid payment method.";
        $message_type = "error";

    } elseif (!in_array($status, $allowed_status)) {

        $message = "Invalid payment status.";
        $message_type = "error";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO payments
            (payer_name, amount, payment_method, status)
            VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sdss",
            $payer_name,
            $amount,
            $payment_method,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Payment record added successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to add payment record.";
            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_payment"])) {

    $id = (int) $_POST["id"];

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM payments WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {

            $message = "Payment deleted successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to delete payment.";
            $message_type = "error";
        }

        mysqli_stmt_close($stmt);
    }
}

$payments = [];

$sql = "
    SELECT
        id,
        payer_name,
        amount,
        payment_method,
        status,
        payment_date
    FROM payments
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $payments[] = $row;
    }
}


$total_payments = 0;
$pending_payments = 0;
$paid_payments = 0;
$total_amount = 0;
$pending_amount = 0;

foreach ($payments as $payment) {

    $amount = (float) $payment["amount"];

    $total_payments++;
    $total_amount += $amount;

    if ($payment["status"] == "Pending") {

        $pending_payments++;
        $pending_amount += $amount;
    }

    if ($payment["status"] == "Paid") {

        $paid_payments++;
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

    <title>Payments | Blood Donor System</title>

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

        .payment-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .payment-card {
            padding: 20px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #c62828;
        }

        .payment-card h2 {
            margin: 0;
            color: #c62828;
        }

        .payment-card p {
            margin-top: 5px;
        }

        .payment-form {
            margin-top: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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
        .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
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

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .payment-table th,
        .payment-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .payment-table th {
            background: #c62828;
            color: white;
        }

        .status {
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: bold;
        }

        .paid {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .pending {
            background: #fff3e0;
            color: #ef6c00;
        }

        .cancelled {
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

        .no-data {
            padding: 20px;
            text-align: center;
            color: #777;
        }

        @media (max-width: 900px) {

            .payment-stats {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .payment-stats,
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
        <a href="notifications.php">Notifications</a>
        <a href="payments.php" class="active">Payments</a>

        <a href="../logout.php">Logout</a>

    </aside>


    <main class="main-panel">

        <h1>Payments</h1>

        <?php if ($message != ""): ?>

            <div class="alert <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>

    

        <div class="payment-stats">

            <div class="payment-card">

                <h2>
                    <?php echo $total_payments; ?>
                </h2>

                <p>Total Payments</p>

            </div>


            <div class="payment-card">

                <h2>
                    <?php echo $paid_payments; ?>
                </h2>

                <p>Paid Payments</p>

            </div>


            <div class="payment-card">

                <h2>
                    <?php echo $pending_payments; ?>
                </h2>

                <p>Pending Payments</p>

            </div>


            <div class="payment-card">

                <h2>
                    Rs. <?php echo number_format($total_amount, 2); ?>
                </h2>

                <p>Total Amount</p>

            </div>

        </div>


        <div class="panel payment-form">

            <h3>Add Payment</h3>

            <form method="POST">

                <div class="form-grid">


                    <div class="form-group">

                        <label for="payer_name">
                            Payer Name
                        </label>

                        <input
                            type="text"
                            id="payer_name"
                            name="payer_name"
                            placeholder="Enter payer name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="amount">
                            Amount (Rs.)
                        </label>

                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            min="1"
                            step="0.01"
                            placeholder="Enter amount"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="payment_method">
                            Payment Method
                        </label>

                        <select
                            id="payment_method"
                            name="payment_method"
                            required
                        >

                            <option value="">
                                Select Payment Method
                            </option>

                            <option value="Cash">
                                Cash
                            </option>

                            <option value="Bank Transfer">
                                Bank Transfer
                            </option>

                            <option value="eSewa">
                                eSewa
                            </option>

                            <option value="Khalti">
                                Khalti
                            </option>

                            <option value="Card">
                                Card
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="status">
                            Payment Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                        >

                            <option value="Pending">
                                Pending
                            </option>

                            <option value="Paid">
                                Paid
                            </option>

                            <option value="Cancelled">
                                Cancelled
                            </option>

                        </select>

                    </div>


                    <div class="form-group full">

                        <button
                            type="submit"
                            name="add_payment"
                            class="add-btn"
                        >
                            Add Payment
                        </button>

                    </div>

                </div>

            </form>

        </div>



        <div class="panel">

            <h3>Payment Records</h3>

            <?php if ($total_payments > 0): ?>

                <div style="overflow-x:auto;">

                    <table class="payment-table">

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Payer Name</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($payments as $payment): ?>

                            <?php

                            $status = $payment["status"];

                            $status_class = strtolower($status);

                            ?>

                            <tr>

                                <td>
                                    <?php
                                    echo (int)$payment["id"];
                                    ?>
                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment["payer_name"]
                                    );
                                    ?>

                                </td>

                                <td>

                                    Rs.
                                    <?php
                                    echo number_format(
                                        (float)$payment["amount"],
                                        2
                                    );
                                    ?>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment["payment_method"]
                                    );
                                    ?>

                                </td>

                                <td>

                                    <span
                                        class="status <?php echo htmlspecialchars($status_class); ?>"
                                    >

                                        <?php
                                        echo htmlspecialchars($status);
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment["payment_date"]
                                    );
                                    ?>

                                </td>

                                <td>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this payment?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo (int)$payment["id"]; ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_payment"
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

                    No payment records found.

                </div>

            <?php endif; ?>

        </div>


    </main>

</div>

</body>

</html>