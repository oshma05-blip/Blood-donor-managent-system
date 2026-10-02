<?php

session_start();

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "blood_donor_system"
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";
$message_type = "";

$full_name = "";
$email = "";
$user_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $user_message = trim($_POST["message"] ?? "");

    if (empty($full_name) || empty($email) || empty($user_message)) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($full_name) < 2) {

        $message = "Please enter a valid full name.";
        $message_type = "error";

    } elseif (strlen($user_message) < 5) {

        $message = "Please enter a longer message.";
        $message_type = "error";

    } else {


        $status = "unread";

        $sql = "INSERT INTO contact_messages
                (full_name, email, message, status)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $message = "Database error: " . $conn->error;
            $message_type = "error";

        } else {

            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $user_message,
                $status
            );

            if ($stmt->execute()) {

                $message = "Thank you! Your message has been sent successfully.";
                $message_type = "success";

                $full_name = "";
                $email = "";
                $user_message = "";

            } else {

                $message = "Unable to send your message. Please try again.";
                $message_type = "error";
            }

            $stmt->close();
        }
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Contact Us | Blood Donor Management System
    </title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f7f7f7;
            color: #333;
        }


        header {
            background: #b30000;
            color: white;
            padding: 18px 7%;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        nav {
            display: flex;
            align-items: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 25px;
            font-size: 15px;
        }

        nav a:hover {
            text-decoration: underline;
        }


        .page-title {
            background: white;
            text-align: center;
            padding: 50px 20px 40px;

            border-bottom: 1px solid #eee;
        }

        .page-title h1 {
            color: #b30000;
            font-size: 36px;
            margin-bottom: 12px;
        }

        .page-title p {
            color: #666;
            font-size: 16px;
        }


        .container {
            width: 90%;
            max-width: 1100px;
            margin: 45px auto;
        }

        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
        }

        .contact-info {
            background: #b30000;
            color: white;
            padding: 35px;
            border-radius: 10px;
        }

        .contact-info h2 {
            font-size: 26px;
            margin-bottom: 15px;
        }

        .contact-info > p {
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .info-box {
            margin-bottom: 25px;
        }

        .info-box h3 {
            font-size: 17px;
            margin-bottom: 7px;
        }

        .info-box p {
            font-size: 15px;
            line-height: 1.5;
        }

        .contact-form {
            background: white;
            padding: 35px;
            border-radius: 10px;

            box-shadow:
                0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .contact-form h2 {
            color: #b30000;
            font-size: 26px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 13px;

            border: 1px solid #ccc;
            border-radius: 5px;

            font-size: 15px;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #b30000;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;

            background: #b30000;
            color: white;

            border: none;
            border-radius: 5px;

            padding: 14px;

            font-size: 16px;
            font-weight: bold;

            cursor: pointer;
        }

        .submit-btn:hover {
            background: #8f0000;
        }

        .message {
            padding: 14px;
            margin-bottom: 20px;

            border-radius: 5px;

            font-size: 14px;
        }

        .success {
            background: #e7f7ec;
            color: #176b2c;
            border: 1px solid #b7dfc1;
        }

        .error {
            background: #ffe8e8;
            color: #a00000;
            border: 1px solid #efb5b5;
        }

            background: #222; {
            color: white;

            text-align: center;

            padding: 22px;

            margin-top: 60px;
        }

        footer p {
            font-size: 14px;
        }


        @media (max-width: 768px) {

            header {
                flex-direction: column;
                gap: 15px;
            }

            nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            nav a {
                margin: 5px 8px;
            }

            .contact-wrapper {
                grid-template-columns: 1fr;
            }

            .page-title h1 {
                font-size: 30px;
            }
        }

    </style>

</head>

<body>


<header>

    <div class="logo">
        Blood Donor System
    </div>

    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="about.php">
            About Us
        </a>

        <a href="contact.php">
            Contact
        </a>

        <a href="login.php">
            Login
        </a>

        <a href="register.php">
            Register
        </a>

    </nav>

</header>


<section class="page-title">

    <h1>
        Contact Us
    </h1>

    <p>
        Have a question or need help?
        Send us a message.
    </p>

</section>

<div class="container">

    <div class="contact-wrapper">


        <div class="contact-info">

            <h2>
                Get In Touch
            </h2>

            <p>
                If you have any questions, suggestions,
                or need assistance regarding blood donation
                and blood requests, feel free to contact us.
            </p>


            <div class="info-box">

                <h3>
                    📍 Address
                </h3>

                <p>
                    Kathmandu, Nepal
                </p>

            </div>


            <div class="info-box">

                <h3>
                    📞 Phone
                </h3>

                <p>
                    +977 9800000000
                </p>

            </div>


            <div class="info-box">

                <h3>
                    ✉ Email
                </h3>

                <p>
                    support@blooddonorsystem.com
                </p>

            </div>


            <div class="info-box">

                <h3>
                    🕒 Working Hours
                </h3>

                <p>
                    Sunday - Friday<br>
                    10:00 AM - 5:00 PM
                </p>

            </div>

        </div>


        <div class="contact-form">

            <h2>
                Send Us a Message
            </h2>


            <?php if (!empty($message)): ?>

                <div class="message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="contact.php"
            >

                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php echo htmlspecialchars($full_name); ?>"
                        placeholder="Enter your full name"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="Enter your email address"
                        maxlength="150"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="message">
                        Message
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        placeholder="Write your message here..."
                        maxlength="5000"
                        required
                    ><?php echo htmlspecialchars($user_message); ?></textarea>

                </div>


                <button
                    type="submit"
                    class="submit-btn"
                >
                    Send Message
                </button>

            </form>

        </div>

    </div>

</div>

<footer>

    <p>
        &copy; <?php echo date("Y"); ?>
        Blood Donor Management System.
        All Rights Reserved.
    </p>

</footer>


</body>

</html>
