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

$scheduled_sql = "
    SELECT COUNT(*) AS scheduled_donations
    FROM donation
    WHERE Status = 'Scheduled'
";

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
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --primary: #d62839;
            --primary-dark: #991b2e;
            --primary-light: #fff1f3;
            --primary-soft: #fde3e7;
            --text: #172033;
            --muted: #64748b;
            --white: #ffffff;
            --bg: #f5f7fb;
            --border: #e2e8f0;
            --success: #16a36a;
            --shadow-sm: 0 8px 25px rgba(15, 23, 42, 0.06);
            --shadow: 0 18px 45px rgba(15, 23, 42, 0.09);
            --shadow-lg: 0 30px 70px rgba(15, 23, 42, 0.16);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            padding: 0;
            color: var(--text);
            background: var(--bg);
            font-family: "Inter", "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.6;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font: inherit;
        }

        .container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding-left: 24px;
            padding-right: 24px;
        }

        .hero {
            position: relative;
            min-height: 700px;
            overflow: hidden;
            color: var(--white);
            background:
                radial-gradient(
                    circle at 85% 10%,
                    rgba(255, 255, 255, 0.15),
                    transparent 27%
                ),
                radial-gradient(
                    circle at 10% 90%,
                    rgba(255, 255, 255, 0.08),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #8f1528 0%,
                    #c3263a 48%,
                    #e24a58 100%
                );
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 600px;
            height: 600px;
            top: -360px;
            right: -200px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 50%;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 450px;
            height: 450px;
            left: -240px;
            bottom: -300px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .navbar {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1120px;
            min-height: 76px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            display: inline-flex;
            align-items: center;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            white-space: nowrap;
        }

        .navbar-brand::before {
            content: "+";
            width: 34px;
            height: 34px;
            margin-right: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #fff;
            color: var(--primary);
            font-size: 1.35rem;
            font-weight: 900;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 9px 13px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.87rem;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .nav-link:hover {
            color: #fff;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            left: 13px;
            right: 13px;
            bottom: 3px;
            height: 2px;
            border-radius: 20px;
            background: #fff;
            transform: scaleX(0);
            transition: 0.2s ease;
        }

        .nav-link:hover::after {
            transform: scaleX(1);
        }

        .nav-user {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.87rem;
            padding: 9px 13px;
        }

        .nav-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 20px;
            border-radius: 999px;
            background: #fff;
            color: var(--primary);
            font-size: 0.84rem;
            font-weight: 700;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.14);
            transition: 0.2s ease;
        }

        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
        }

        .menu-button {
            display: none;
            width: 43px;
            height: 39px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            cursor: pointer;
        }

        .menu-icon,
        .menu-icon::before,
        .menu-icon::after {
            display: block;
            width: 19px;
            height: 2px;
            background: #fff;
            transition: 0.2s ease;
        }

        .menu-icon {
            position: relative;
            margin: auto;
        }

        .menu-icon::before,
        .menu-icon::after {
            content: "";
            position: absolute;
            left: 0;
        }

        .menu-icon::before {
            top: -6px;
        }

        .menu-icon::after {
            top: 6px;
        }

        .hero-content {
            position: relative;
            z-index: 5;
            padding-top: 100px;
            padding-bottom: 70px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.75fr);
            align-items: center;
            gap: 70px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 20px;
            padding: 7px 14px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: #ffd9de;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.14rem;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #6ff0aa;
            box-shadow: 0 0 0 5px rgba(111, 240, 170, 0.1);
        }

        .hero h1 {
            max-width: 720px;
            margin: 0 0 22px;
            color: #fff;
            font-size: clamp(2.8rem, 5vw, 4.5rem);
            line-height: 1.05;
            letter-spacing: -0.055em;
            font-weight: 800;
        }

        .hero-lead {
            max-width: 650px;
            margin: 0 0 30px;
            color: rgba(255, 255, 255, 0.75);
            font-size: 1rem;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 13px;
        }

        .hero-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 11px 24px;
            border-radius: 999px;
            font-size: 0.87rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .hero-button:hover {
            transform: translateY(-2px);
        }

        .hero-button-primary {
            border: 1px solid #fff;
            background: #fff;
            color: var(--primary);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }

        .hero-button-secondary {
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.07);
            color: #fff;
        }

        .hero-button-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .quick-access {
            width: 100%;
            max-width: 390px;
            margin-left: auto;
            padding: 30px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            box-shadow:
                0 25px 60px rgba(60, 0, 10, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
        }

        .quick-access h3 {
            margin: 0 0 20px;
            color: #fff;
            font-size: 1.25rem;
        }

        .quick-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .quick-list li {
            margin-bottom: 10px;
        }

        .quick-list li:last-child {
            margin-bottom: 0;
        }

        .quick-list a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 54px;
            padding: 0 17px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.07);
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.83rem;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .quick-list a::after {
            content: "→";
            color: #ffd6dc;
            transition: 0.2s ease;
        }

        .quick-list a:hover {
            transform: translateX(4px);
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.25);
        }

        .quick-list a:hover::after {
            transform: translateX(4px);
        }

        main {
            position: relative;
            z-index: 3;
        }

        .main-content {
            padding-top: 80px;
            padding-bottom: 90px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 50px;
        }

        .feature-card {
            position: relative;
            min-height: 190px;
            height: 100%;
            padding: 28px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #fff;
            box-shadow: var(--shadow-sm);
            transition: 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: #f0c4ca;
            box-shadow: var(--shadow);
        }

        .feature-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(
                90deg,
                var(--primary),
                #ed6974
            );
        }

        .feature-card::after {
            content: "";
            position: absolute;
            right: -45px;
            bottom: -45px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: var(--primary-light);
        }

        .feature-card h3 {
            position: relative;
            z-index: 2;
            margin: 0 0 12px;
            color: var(--text);
            font-size: 1rem;
        }

        .feature-card p {
            position: relative;
            z-index: 2;
            margin: 0;
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.75;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 50px;
        }

        .stat-card {
            position: relative;
            min-height: 150px;
            height: 100%;
            padding: 28px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #fff;
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }

        .stat-card::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-light);
        }

        .stat-card h2 {
            position: relative;
            z-index: 2;
            margin: 0 0 5px;
            color: var(--primary);
            font-size: 2.5rem;
            line-height: 1;
        }

        .stat-card p {
            position: relative;
            z-index: 2;
            margin: 0;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .professional-section {
            position: relative;
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            align-items: center;
            gap: 50px;
            padding: 45px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 26px;
            background:
                radial-gradient(
                    circle at 100% 0%,
                    #fff0f2 0%,
                    transparent 32%
                ),
                #fff;
            box-shadow: var(--shadow);
        }

        .professional-section::before {
            content: "";
            position: absolute;
            right: -110px;
            top: -110px;
            width: 250px;
            height: 250px;
            border: 1px solid #f5d9dd;
            border-radius: 50%;
        }

        .professional-content,
        .mini-stats {
            position: relative;
            z-index: 2;
        }

        .section-label {
            margin: 0 0 10px;
            color: var(--primary);
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.1rem;
            text-transform: uppercase;
        }

        .professional-section h2 {
            max-width: 650px;
            margin: 0 0 18px;
            color: var(--text);
            font-size: clamp(1.9rem, 4vw, 2.7rem);
            line-height: 1.12;
            letter-spacing: -0.045em;
        }

        .professional-section p {
            max-width: 650px;
            margin: 0;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.85;
        }

        .mini-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .mini-stat {
            padding: 17px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fafbfc;
            text-align: center;
            transition: 0.2s ease;
        }

        .mini-stat:hover {
            transform: translateY(-3px);
            border-color: #f1c7cd;
            background: var(--primary-light);
        }

        .mini-stat strong {
            display: block;
            margin-bottom: 3px;
            color: var(--text);
            font-size: 1.05rem;
        }

        .mini-stat span {
            color: var(--muted);
            font-size: 0.7rem;
        }

        .footer {
            width: 100%;
            padding: 30px 20px;
            border-top: 1px solid #1e293b;
            background: #0f172a;
            color: rgba(255, 255, 255, 0.5);
            text-align: center;
        }

        .footer p {
            margin: 0;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.76rem;
        }

        @media (max-width: 1050px) {

            .hero-grid {
                grid-template-columns: 1.2fr 0.8fr;
                gap: 40px;
            }

            .hero h1 {
                font-size: clamp(2.7rem, 5vw, 4rem);
            }

            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 850px) {

            .navbar {
                min-height: 70px;
                padding-top: 14px;
                padding-bottom: 14px;
                flex-wrap: wrap;
            }

            .menu-button {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .navbar-menu {
                display: none;
                width: 100%;
                padding-top: 15px;
                flex-direction: column;
                align-items: flex-start;
                gap: 0;
            }

            .navbar-menu.show {
                display: flex;
            }

            .navbar-menu .nav-link,
            .navbar-menu .nav-user {
                width: 100%;
                padding: 9px 0;
            }

            .navbar-menu .nav-button {
                margin-top: 7px;
            }

            .hero-content {
                padding-top: 65px;
            }

            .hero-grid {
                grid-template-columns: 1fr;
                gap: 45px;
            }

            .hero-copy {
                text-align: center;
            }

            .hero h1,
            .hero-lead {
                margin-left: auto;
                margin-right: auto;
            }

            .eyebrow {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-buttons {
                justify-content: center;
            }

            .quick-access {
                max-width: 600px;
                margin: 0 auto;
            }

            .professional-section {
                grid-template-columns: 1fr;
                gap: 30px;
            }

        }

        @media (max-width: 767px) {

            .container {
                padding-left: 18px;
                padding-right: 18px;
            }

            .navbar {
                padding-left: 18px;
                padding-right: 18px;
            }

            .hero {
                min-height: auto;
            }

            .hero-content {
                padding-top: 50px;
                padding-bottom: 50px;
            }

            .hero h1 {
                font-size: clamp(2.3rem, 9vw, 3.3rem);
            }

            .hero-lead {
                font-size: 0.9rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-button {
                width: 100%;
            }

            .quick-access {
                padding: 24px;
            }

            .main-content {
                padding-top: 55px;
                padding-bottom: 60px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .professional-section {
                padding: 28px;
            }

            .professional-section h2 {
                font-size: 1.9rem;
            }

        }

        @media (max-width: 480px) {

            .container {
                padding-left: 15px;
                padding-right: 15px;
            }

            .navbar {
                padding-left: 15px;
                padding-right: 15px;
            }

            .navbar-brand {
                font-size: 0.95rem;
            }

            .navbar-brand::before {
                width: 30px;
                height: 30px;
                font-size: 1.1rem;
            }

            .hero h1 {
                font-size: 2.25rem;
            }

            .quick-access {
                padding: 20px;
                border-radius: 20px;
            }

            .quick-list a {
                min-height: 50px;
                padding: 0 14px;
                font-size: 0.78rem;
            }

            .professional-section {
                padding: 22px;
            }

            .mini-stats {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>

<header class="hero">

    <nav class="navbar">

        <a
            class="navbar-brand"
            href="index.php"
        >
            BloodDonorSystem
        </a>

        <button
            class="menu-button"
            type="button"
            id="menuButton"
            aria-label="Open menu"
        >
            <span class="menu-icon"></span>
        </button>

        <div
            class="navbar-menu"
            id="navMenu"
        >

            <a
                class="nav-link"
                href="index.php"
            >
                Home
            </a>

            <a
                class="nav-link"
                href="about.php"
            >
                About
            </a>

            <a
                class="nav-link"
                href="contact.php"
            >
                Contact
            </a>

            <?php if (!empty($logged_in_user)): ?>

                <span class="nav-user">
                    Welcome,
                    <strong>
                        <?php
                        echo htmlspecialchars($logged_in_user);
                        ?>
                    </strong>
                </span>

                <a
                    class="nav-button"
                    href="logout.php"
                >
                    Logout
                </a>

            <?php else: ?>

                <a
                    class="nav-button"
                    href="login.php"
                >
                    Login
                </a>

            <?php endif; ?>

        </div>

    </nav>


    <div class="container hero-content">

        <div class="hero-grid">

            <div class="hero-copy">

                <p class="eyebrow">
                    Life-saving coordination
                </p>

                <h1>
                    Modern infrastructure for blood
                    donation, distribution, and care.
                </h1>

                <p class="hero-lead">
                    Coordinate donors, recipients, hospitals,
                    and administrators through one secure
                    and intelligent platform.
                </p>

                <div class="hero-buttons">

                    <a
                        class="hero-button hero-button-primary"
                        href="login.php"
                    >
                        Get Started
                    </a>

                    <a
                        class="hero-button hero-button-secondary"
                        href="about.php"
                    >
                        Learn More
                    </a>

                </div>

            </div>


            <div>

                <div class="quick-access">

                    <h3>
                        Quick Access
                    </h3>

                    <ul class="quick-list">

                        <li>
                            <a href="donor/register.php">
                                Register as donor
                            </a>
                        </li>

                        <li>
                            <a href="recipient/register.php">
                                Register as recipient
                            </a>
                        </li>

                        <li>
                            <a href="hospital/dashboard.php">
                                Hospital dashboard
                            </a>
                        </li>

                        <li>
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


<main>

    <div class="container main-content">

        <section class="feature-grid">

            <div class="feature-card">

                <h3>
                    Donors
                </h3>

                <p>
                    Track eligibility, appointments,
                    and donation history with confidence.
                </p>

            </div>


            <div class="feature-card">

                <h3>
                    Recipients
                </h3>

                <p>
                    Submit blood support requests
                    and monitor their progress.
                </p>

            </div>


            <div class="feature-card">

                <h3>
                    Hospitals
                </h3>

                <p>
                    Manage inventory, urgent requests,
                    and blood issuance workflows.
                </p>

            </div>


            <div class="feature-card">

                <h3>
                    Admin
                </h3>

                <p>
                    Monitor operations, notifications,
                    disease screening, and payments.
                </p>

            </div>

        </section>


        <section class="stats-grid">

            <div class="stat-card">

                <h2>
                    <?php echo $total_donors; ?>
                </h2>

                <p>
                    Registered Donors
                </p>

            </div>


            <div class="stat-card">

                <h2>
                    <?php echo $total_donations; ?>
                </h2>

                <p>
                    Donation Records
                </p>

            </div>


            <div class="stat-card">

                <h2>
                    <?php echo $scheduled_donations; ?>
                </h2>

                <p>
                    Scheduled Donations
                </p>

            </div>

        </section>


        <section class="professional-section">

            <div class="professional-content">

                <p class="section-label">
                    Professional by design
                </p>

                <h2>
                    A robust blood management
                    ecosystem for healthcare teams.
                </h2>

                <p>
                    The Blood Donor System is designed to
                    improve coordination between donors,
                    recipients, hospitals, and administrators.
                    It supports daily operations while helping
                    organize urgent blood-related requests.
                </p>

            </div>


            <div class="mini-stats">

                <div class="mini-stat">

                    <strong>
                        <?php echo $total_donors; ?>
                    </strong>

                    <span>
                        Registered Donors
                    </span>

                </div>


                <div class="mini-stat">

                    <strong>
                        <?php echo $total_donations; ?>
                    </strong>

                    <span>
                        Donations
                    </span>

                </div>


                <div class="mini-stat">

                    <strong>
                        Multi-role
                    </strong>

                    <span>
                        Access
                    </span>

                </div>


                <div class="mini-stat">

                    <strong>
                        Secure
                    </strong>

                    <span>
                        Data Flow
                    </span>

                </div>

            </div>

        </section>

    </div>

</main>


<footer class="footer">

    <p>

        &copy;

        <?php echo $current_year; ?>

        BloodDonorSystem.

        All rights reserved.

    </p>

</footer>


<script>

    const menuButton = document.getElementById("menuButton");
    const navMenu = document.getElementById("navMenu");

    menuButton.addEventListener("click", function () {
        navMenu.classList.toggle("show");
    });

</script>


</body>

</html>

<?php
$conn->close();
?>
