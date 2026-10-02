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


$current_year = date("Y");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Blood Donor System</title>



    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <script
        defer
        src="js/app.js">
    </script>

</head>


<body>


<header class="hero py-4">

    <nav
        class="navbar navbar-expand-lg navbar-dark container px-3 px-lg-4"
    >


        <a
            class="navbar-brand fw-bold fs-4"
            href="index.php"
        >
            BloodDonorSystem
        </a>



        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navMenu"
        >

            <span class="navbar-toggler-icon"></span>

        </button>



        <div
            class="collapse navbar-collapse"
            id="navMenu"
        >

            <ul class="navbar-nav ms-auto gap-2">

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php"
                    >
                        Home
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="about.php"
                    >
                        About
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="contact.php"
                    >
                        Contact
                    </a>

                </li>


                <?php if (!empty($logged_in_user)): ?>


                    <li class="nav-item d-flex align-items-center">

                        <span class="nav-link text-white">

                            Welcome,
                            <strong>
                                <?php
                                echo htmlspecialchars($logged_in_user);
                                ?>
                            </strong>

                        </span>

                    </li>


                    <li class="nav-item">

                        <a
                            class="btn btn-light btn-sm fw-semibold"
                            href="logout.php"
                        >
                            Logout
                        </a>

                    </li>

                <?php else: ?>


                    <li class="nav-item">

                        <a
                            class="btn btn-light btn-sm fw-semibold"
                            href="login.php"
                        >
                            Login
                        </a>

                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </nav>



    <div class="container py-5 py-lg-6">

        <div class="row align-items-center g-5">



            <div class="col-lg-7">

                <p class="eyebrow mb-3">
                    Life-saving coordination
                </p>


                <h1 class="display-5 fw-bold mb-3">

                    Modern infrastructure for blood
                    donation, distribution, and care.

                </h1>


                <p class="lead text-white-50 mb-4">

                    Coordinate donors, recipients, hospitals,
                    and administrators through one secure
                    and intelligent platform.

                </p>


                <div class="d-flex flex-wrap gap-3">

                    <a
                        class="btn btn-light fw-semibold px-4"
                        href="login.php"
                    >
                        Get Started
                    </a>


                    <a
                        class="btn btn-outline-light fw-semibold px-4"
                        href="about.php"
                    >
                        Learn More
                    </a>

                </div>

            </div>


            <div class="col-lg-5">

                <div
                    class="card border-0 shadow-lg rounded-4 p-4"
                >

                    <h3 class="fw-semibold mb-3">
                        Quick Access
                    </h3>


                    <ul
                        class="list-group list-group-flush"
                    >

                        <li
                            class="list-group-item px-0"
                        >

                            <a href="donor/register.php">
                                Register as donor
                            </a>

                        </li>


                        <li
                            class="list-group-item px-0"
                        >

                            <a href="recipient/register.php">
                                Register as recipient
                            </a>

                        </li>


                        <li
                            class="list-group-item px-0"
                        >

                            <a href="hospital/dashboard.php">
                                Hospital dashboard
                            </a>

                        </li>


                        <li
                            class="list-group-item px-0"
                        >

                            <a href="admin/dashboard.php">
                                Admin dashboard
                            </a>

                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </div>

</header>


<main class="container py-5">



    <section class="row g-4 mb-5">

        <div class="col-md-6 col-xl-3">

            <div
                class="card h-100 border-0 shadow-sm rounded-4 p-4"
            >

                <h5 class="fw-semibold">
                    Donors
                </h5>

                <p class="text-muted mb-0">

                    Track eligibility, appointments,
                    and donation history with confidence.

                </p>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div
                class="card h-100 border-0 shadow-sm rounded-4 p-4"
            >

                <h5 class="fw-semibold">
                    Recipients
                </h5>

                <p class="text-muted mb-0">

                    Submit blood support requests
                    and monitor their progress.

                </p>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div
                class="card h-100 border-0 shadow-sm rounded-4 p-4"
            >

                <h5 class="fw-semibold">
                    Hospitals
                </h5>

                <p class="text-muted mb-0">

                    Manage inventory, urgent requests,
                    and blood issuance workflows.

                </p>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div
                class="card h-100 border-0 shadow-sm rounded-4 p-4"
            >

                <h5 class="fw-semibold">
                    Admin
                </h5>

                <p class="text-muted mb-0">

                    Monitor operations, notifications,
                    disease screening, and payments.

                </p>

            </div>

        </div>

    </section>


    <section class="row g-4 mb-5">

        <div class="col-md-4">

            <div
                class="card border-0 shadow-sm rounded-4 p-4 text-center h-100"
            >

                <h2 class="text-danger fw-bold">
                    <?php echo $total_donors; ?>
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
                    <?php echo $total_donations; ?>
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
                    <?php echo $scheduled_donations; ?>
                </h2>

                <p class="text-muted mb-0">
                    Scheduled Donations
                </p>

            </div>

        </div>

    </section>


    <section
        class="row align-items-center g-4 bg-white p-4 p-lg-5 rounded-4 shadow-sm"
    >

        <div class="col-lg-7">

            <p class="text-danger fw-semibold mb-2">
                Professional by design
            </p>

            <h2 class="fw-bold mb-3">

                A robust blood management ecosystem
                for healthcare teams.

            </h2>

            <p class="text-muted">

                The Blood Donor System is designed to
                improve coordination between donors,
                recipients, hospitals, and administrators.
                It supports daily operations while helping
                organize urgent blood-related requests.

            </p>

        </div>


        <div class="col-lg-5">

            <div class="row g-3">

                <div class="col-6">

                    <div
                        class="card border-0 bg-light rounded-4 p-3 text-center"
                    >

                        <strong>
                            <?php echo $total_donors; ?>
                        </strong>

                        <div class="small text-muted">
                            Registered Donors
                        </div>

                    </div>

                </div>


                <div class="col-6">

                    <div class="col-6">

    <div class="card border-0 bg-light rounded-4 p-3 text-center">

        <strong>
            <?php echo $total_donations; ?>
        </strong>

        <div class="small text-muted">
            Donations
        </div>

    </div>

</div>


                        <div class="small text-muted">
                            Donations
                        </div>

                    </div>

                </div>


                <div class="col-6">

                    <div
                        class="card border-0 bg-light rounded-4 p-3 text-center"
                    >

                        <strong>
                            Multi-role
                        </strong>

                        <div class="small text-muted">
                            Access
                        </div>

                    </div>

                </div>


                <div class="col-6">

                    <div
                        class="card border-0 bg-light rounded-4 p-3 text-center"
                    >

                        <strong>
                            Secure
                        </strong>

                        <div class="small text-muted">
                            Data Flow
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>

<footer class="footer py-4">

    <p class="mb-0 text-center">

        &copy;

        <span id="year">
            <?php echo $current_year; ?>
        </span>

        BloodDonorSystem.

        All rights reserved.

    </p>

</footer>


</body>

</html>


<?php

$conn->close();

?>