<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../conn.php");

$message = "";
$message_type = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_stock"])) {

    $blood_group = trim($_POST["blood_group"]);
    $units = (int) $_POST["units"];

    $allowed_groups = [
        "A+", "A-", "B+", "B-",
        "O+", "O-", "AB+", "AB-"
    ];

    if (!in_array($blood_group, $allowed_groups)) {

        $message = "Invalid blood group.";
        $message_type = "error";

    } elseif ($units <= 0) {

        $message = "Please enter a valid number of units.";
        $message_type = "error";

    } else {


        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM blood_inventory WHERE blood_group = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $blood_group);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);
            $inventory_id = (int) $row["id"];


            $update = mysqli_prepare(
                $conn,
                "UPDATE blood_inventory
                 SET units = units + ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ii",
                $units,
                $inventory_id
            );

            if (mysqli_stmt_execute($update)) {

                $message = "Blood stock updated successfully.";
                $message_type = "success";

            } else {

                $message = "Failed to update blood stock.";
                $message_type = "error";
            }

            mysqli_stmt_close($update);

        } else {

            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO blood_inventory
                 (blood_group, units)
                 VALUES (?, ?)"
            );

            mysqli_stmt_bind_param(
                $insert,
                "si",
                $blood_group,
                $units
            );

            if (mysqli_stmt_execute($insert)) {

                $message = "Blood stock added successfully.";
                $message_type = "success";

            } else {

                $message = "Failed to add blood stock.";
                $message_type = "error";
            }

            mysqli_stmt_close($insert);
        }

        mysqli_stmt_close($stmt);
    }
}

$total_units = 0;

$sql = "
    SELECT COALESCE(SUM(units), 0) AS total
    FROM blood_inventory
";

$result = mysqli_query($conn, $sql);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $total_units = (int) $row["total"];
}

$inventory = [];

$sql = "
    SELECT id, blood_group, units
    FROM blood_inventory
    ORDER BY FIELD(
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

    <title>Admin Inventory | Blood Donor System</title>

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

        .inventory-summary {
            margin-top: 15px;
            padding: 20px;
            background: #fff;
            border-left: 5px solid #c62828;
            border-radius: 6px;
        }

        .inventory-summary h2 {
            margin: 0;
            color: #c62828;
            font-size: 30px;
        }

        .inventory-summary p {
            margin-top: 5px;
        }

        .inventory-form {
            margin-top: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-group select,
        .form-group input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .add-btn {
            background: #c62828;
            color: white;
            border: none;
            padding: 11px 20px;
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

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .inventory-table th {
            background: #c62828;
            color: white;
        }

        .blood-group {
            font-weight: bold;
            color: #c62828;
        }

        .units {
            font-weight: bold;
        }

        .stock-low {
            color: #d32f2f;
        }

        .stock-medium {
            color: #ef6c00;
        }

        .stock-good {
            color: #2e7d32;
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

        <a href="inventory.php" class="active">Inventory</a>

        <a href="requests.php">Requests</a>

        <a href="diseases.php">Diseases</a>

        <a href="notifications.php">Notifications</a>

        <a href="payments.php">Payments</a>

        <a href="../logout.php">Logout</a>

    </aside>

    <main class="main-panel">

        <h1>Inventory</h1>

        <div class="panel">

            <div class="inventory-summary">

                <h2>
                    <?php echo $total_units; ?>
                </h2>

                <p>
                    Total Blood Units Available
                </p>

            </div>

        </div>

        <?php if ($message != ""): ?>

            <div class="alert <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>

        <div class="panel inventory-form">

            <h3>Add Blood Stock</h3>

            <form method="POST">

                <div class="form-grid">

                    <div class="form-group">

                        <label for="blood_group">
                            Blood Group
                        </label>

                        <select
                            name="blood_group"
                            id="blood_group"
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

                    </div>


                    <div class="form-group">

                        <label for="units">
                            Units
                        </label>

                        <input
                            type="number"
                            name="units"
                            id="units"
                            min="1"
                            placeholder="Enter units"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <button
                            type="submit"
                            name="add_stock"
                            class="add-btn"
                        >
                            Add Stock
                        </button>

                    </div>

                </div>

            </form>

        </div>
        
        <div class="panel">

            <h3>Current Blood Inventory</h3>

            <?php if (count($inventory) > 0): ?>

                <div style="overflow-x:auto;">

                    <table class="inventory-table">

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Blood Group</th>
                                <th>Available Units</th>
                                <th>Stock Status</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($inventory as $item): ?>

                            <?php

                            $units = (int) $item["units"];

                            if ($units == 0) {

                                $status = "Out of Stock";
                                $status_class = "stock-low";

                            } elseif ($units <= 5) {

                                $status = "Low Stock";
                                $status_class = "stock-low";

                            } elseif ($units <= 10) {

                                $status = "Medium Stock";
                                $status_class = "stock-medium";

                            } else {

                                $status = "Good Stock";
                                $status_class = "stock-good";
                            }

                            ?>

                            <tr>

                                <td>
                                    <?php
                                    echo (int) $item["id"];
                                    ?>
                                </td>

                                <td>

                                    <span class="blood-group">

                                        <?php
                                        echo htmlspecialchars(
                                            $item["blood_group"]
                                        );
                                        ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="units">

                                        <?php
                                        echo $units;
                                        ?>

                                        units

                                    </span>

                                </td>

                                <td>

                                    <span
                                        class="<?php echo $status_class; ?>"
                                    >

                                        <?php
                                        echo $status;
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

                    No blood inventory records found.

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>