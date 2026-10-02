<?php

session_start();

include "../conn.php";


if (isset($_SESSION["recipient_id"]) && !empty($_SESSION["recipient_id"])) {

    $recipient_id = (int) $_SESSION["recipient_id"];

} elseif (isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) {


    $recipient_id = (int) $_SESSION["user_id"];

} else {

    header("Location: ../login.php");
    exit();

}


$sql = "SELECT
            id,
            blood_group,
            quantity,
            hospital_name,
            request_date,
            status
        FROM blood_request
        WHERE recipient_id = ?
        ORDER BY request_date DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $recipient_id
);

$stmt->execute();

$result = $stmt->get_result();

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
        Request Status | Blood Donor System
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

        .status-table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .status-table th,
        .status-table td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .status-table th {
            background: #c62828;
            color: white;
        }

        .status-table tr:hover {
            background: #f8f8f8;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #cce5ff;
            color: #004085;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-processing {
            background: #e2e3e5;
            color: #383d41;
        }

        .no-request {
            padding: 30px;
            text-align: center;
            background: #fff;
            border-radius: 8px;
        }

        .no-request h3 {
            margin-bottom: 10px;
        }

        .no-request p {
            color: #666;
        }

        .request-button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            background: #c62828;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .request-button:hover {
            background: #a91f1f;
        }

        .hospital-name {
            font-weight: 500;
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
            Request Blood
        </a>


        <a href="status.php">
            Request Status
        </a>


        <a href="../logout.php">
            Logout
        </a>

    </aside>

    <main class="main-panel">

        <h1>
            Request Status
        </h1>


        <div class="panel">


            <?php if ($result->num_rows > 0): ?>


                <div class="status-table-container">

                    <table class="status-table">

                        <thead>

                            <tr>

                                <th>
                                    Request ID
                                </th>

                                <th>
                                    Blood Group
                                </th>

                                <th>
                                    Quantity
                                </th>

                                <th>
                                    Hospital
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Request Date
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php while ($row = $result->fetch_assoc()): ?>


                                <?php

                                $status = trim($row["status"]);

                                $status_class = "status-processing";

                                switch (strtolower($status)) {

                                    case "pending":
                                        $status_class = "status-pending";
                                        break;

                                    case "approved":
                                        $status_class = "status-approved";
                                        break;

                                    case "completed":
                                        $status_class = "status-completed";
                                        break;

                                    case "rejected":
                                        $status_class = "status-rejected";
                                        break;

                                    case "processing":
                                        $status_class = "status-processing";
                                        break;

                                }

                                ?>


                                <tr>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $row["id"]
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $row["blood_group"]
                                            );

                                            ?>

                                        </strong>

                                    </td>

                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $row["quantity"]
                                        );

                                        ?>

                                        Unit(s)

                                    </td>


                                    <td>

                                        <span class="hospital-name">

                                            <?php

                                            echo htmlspecialchars(
                                                $row["hospital_name"]
                                            );

                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <span
                                            class="status <?php echo $status_class; ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $status
                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?php

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $row["request_date"]
                                            )
                                        );

                                        ?>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="no-request">

                    <h3>
                        No Blood Requests Found
                    </h3>

                    <p>
                        You have not submitted any blood requests yet.
                    </p>


                    <a
                        href="request.php"
                        class="request-button"
                    >
                        Request Blood
                    </a>

                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>
