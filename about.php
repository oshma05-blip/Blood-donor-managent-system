<?php

session_start();

include "conn.php";

$logged_in_user =
    $_SESSION["username"]
    ?? $_SESSION["donor_name"]
    ?? $_SESSION["admin_name"]
    ?? $_SESSION["hospital_name"]
    ?? "";

$donor_sql = "SELECT COUNT(*) AS total_donors FROM donor";
$donor_result = $conn->query($donor_sql);

$total_donors = 0;

if ($donor_result) {
    $donor_data = $donor_result->fetch_assoc();
    $total_donors = $donor_data["total_donors"];
}

$donation_sql = "SELECT COUNT(*) AS total_donations FROM donation";
$donation_result = $conn->query($donation_sql);

$total_donations = 0;

if ($donation_result) {
    $donation_data = $donation_result->fetch_assoc();
    $total_donations = $donation_data["total_donations"];
}


$scheduled_sql = "SELECT COUNT(*) AS scheduled_donations
                  FROM donation
                  WHERE Status = 'Scheduled'";

$scheduled_result = $conn->query($scheduled_sql);

$scheduled_donations = 0;

if ($scheduled_result) {
    $scheduled_data = $scheduled_result->fetch_assoc();
    $scheduled_donations = $scheduled_data["scheduled_donations"];
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
        About | Blood Donor System
    </title>



    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <script
        defer
        src="js/app.js"
    ></script>

</head>


<body>



    <header class="page-header py-3">

        <nav class="navbar navbar-expand-lg navbar-dark container">


            <a
                class="navbar-brand fw-bold"
                href="index.php"
            >
                BloodDonorSystem
            </a>


            <div class="ms-auto d-flex align-items-center">


                <a
                    class="nav-link d-inline text-white me-3"
                    href="index.php"
                >
                    Home
                </a>


                <a
                    class="nav-link d-inline text-white me-3"
                    href="about.php"
                >
                    About
                </a>


                <a
                    class="nav-link d-inline text-white me-3"
                    href="contact.php"
                >
                    Contact
                </a>


                <?php if (!empty($logged_in_user)): ?>


                    <span class="text-white me-3 fw-semibold">

                        Welcome,
                        <?php
                        echo htmlspecialchars($logged_in_user);
                        ?>

                    </span>


                    <a
                        class="btn btn-light btn-sm fw-semibold"
                        href="logout.php"
                    >
                        Logout
                    </a>


                <?php else: ?>


                    <a
                        class="btn btn-light btn-sm fw-semibold"
                        href="login.php"
                    >
                        Login
                    </a>

                <?php endif; ?>


            </div>

        </nav>

    </header>



    <main class="container py-5">


        <section class="row align-items-center g-5 mb-5">


            <div class="col-lg-7">


                <p class="text-danger fw-semibold mb-2">
                    About the platform
                </p>


                <h1 class="display-6 fw-bold mb-3">

                    A professional ecosystem designed
                    for life-supporting operations.

                </h1>


                <p class="text-muted lead">

                    The Blood Donor System brings donors,
                    recipients, hospitals, and administrators
                    together into a unified workflow that is
                    dependable, transparent, and efficient.

                </p>


            </div>



            <div class="col-lg-5">


                <div class="card border-0 shadow-sm rounded-4 p-4">


                    <h5 class="fw-semibold">
                        What it supports
                    </h5>


                    <ul class="mb-0 text-muted">


                        <li>
                            Donor registration and history
                        </li>


                        <li>
                            Recipient requests and tracking
                        </li>


                        <li>
                            Hospital inventory and issue management
                        </li>


                        <li>
                            Administrative oversight and reporting
                        </li>


                    </ul>


                </div>


            </div>


        </section>



        <section class="row g-4 mb-5">


            <div class="col-md-4">


                <div
                    class="card border-0 shadow-sm rounded-4 p-4 text-center h-100"
                >


                    <h2 class="text-danger fw-bold">

                        <?php
                        echo (int) $total_donors;
                        ?>

                    </h2>


                    <p class="text-muted mb-0">
                        Registered Donors
                    </p>


                </div>


            </div>



            <div class="col-md-4">


                <div
                    class="card border-0 shadow-sm rounded-4 p-4 text-center h-100"
                >


                    <h2 class="text-danger fw-bold">

                        <?php
                        echo (int) $total_donations;
                        ?>

                    </h2>


                    <p class="text-muted mb-0">
                        Donation Records
                    </p>


                </div>


            </div>



            <div class="col-md-4">


                <div
                    class="card border-0 shadow-sm rounded-4 p-4 text-center h-100"
                >


                    <h2 class="text-danger fw-bold">

                        <?php
                        echo (int) $scheduled_donations;
                        ?>

                    </h2>


                    <p class="text-muted mb-0">
                        Scheduled Donations
                    </p>


                </div>


            </div>


        </section>



        <section class="row g-4">


            <div class="col-md-6">


                <div
                    class="card border-0 shadow-sm rounded-4 p-4 h-100"
                >


                    <h5 class="fw-semibold">
                        Reliable Coordination
                    </h5>


                    <p class="text-muted mb-0">

                        Support emergency requests and day-to-day
                        blood donation management through an organized
                        digital platform.

                    </p>


                </div>


            </div>



            <div class="col-md-6">


                <div
                    class="card border-0 shadow-sm rounded-4 p-4 h-100"
                >


                    <h5 class="fw-semibold">
                        Transparent Workflows
                    </h5>


                    <p class="text-muted mb-0">

                        Track donor information, donation appointments,
                        requests, and history through a clean and
                        professional user experience.

                    </p>


                </div>


            </div>



            <div class="col-md-6">


                <div
                    class="card border-0 shadow-sm rounded-4 p-4 h-100"
                >


                    <h5 class="fw-semibold">
                        Donor Management
                    </h5>


                    <p class="text-muted mb-0">

                        Donors can register, log in, manage their profile,
                        schedule donations, and view their donation history.

                    </p>


                </div>


            </div>



            <div class="col-md-6">


                <div
                    class="card border-0 shadow-sm rounded-4 p-4 h-100"
                >


                    <h5 class="fw-semibold">
                        Secure Database
                    </h5>


                    <p class="text-muted mb-0">

                        Donor and donation information is stored and
                        managed through a MySQL database connected
                        with the PHP application.

                    </p>


                </div>


            </div>


        </section>


    </main>


    <footer class="footer py-4">

        <p class="mb-0 text-center">

            &copy;

            <span id="year">
                <?php echo date("Y"); ?>
            </span>

            BloodDonorSystem.

        </p>

    </footer>



</body>

</html>


<?php

$conn->close();

?>