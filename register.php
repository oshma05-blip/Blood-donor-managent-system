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

$name = "";
$email = "";
$phone = "";
$address = "";
$role = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $role = trim($_POST["role"] ?? "");


    
    if (
        empty($name) ||
        empty($email) ||
        empty($phone) ||
        empty($address) ||
        empty($password) ||
        empty($confirm_password) ||
        empty($role)
    ) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    }

    elseif (!preg_match("/^[a-zA-Z ]{2,100}$/", $name)) {

        $message = "Name should contain only letters and spaces.";
        $message_type = "error";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    }

    elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Phone number must contain exactly 10 digits.";
        $message_type = "error";

    }

    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters long.";
        $message_type = "error";

    }

    elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    }

    elseif (!in_array($role, ["donor", "recipient"], true)) {

        $message = "Please select a valid role.";
        $message_type = "error";

    }

    else {

       
        $check_sql = "
            SELECT email
            FROM users
            WHERE email = ?
            LIMIT 1
        ";

        $check_stmt = $conn->prepare($check_sql);

        if (!$check_stmt) {

            $message = "Database error: " . $conn->error;
            $message_type = "error";

        } else {

            $check_stmt->bind_param("s", $email);

            $check_stmt->execute();

            $check_result = $check_stmt->get_result();


            if ($check_result->num_rows > 0) {

                $message = "An account with this email already exists.";
                $message_type = "error";

            } else {

                
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                
                $insert_sql = "
                    INSERT INTO users
                    (
                        full_name,
                        email,
                        password,
                        phone,
                        address,
                        role
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ";


                $insert_stmt = $conn->prepare($insert_sql);


                if (!$insert_stmt) {

                    $message = "Database error: " . $conn->error;
                    $message_type = "error";

                } else {

                    
                    $insert_stmt->bind_param(
                        "ssssss",
                        $name,
                        $email,
                        $hashed_password,
                        $phone,
                        $address,
                        $role
                    );


                    if ($insert_stmt->execute()) {

                        $message =
                            "Registration successful as " .
                            ucfirst($role) .
                            "! You can now login.";

                        $message_type = "success";


                        

                        $name = "";
                        $email = "";
                        $phone = "";
                        $address = "";
                        $role = "";

                    } else {

                        $message =
                            "Registration failed: " .
                            $insert_stmt->error;

                        $message_type = "error";
                    }


                    $insert_stmt->close();
                }
            }


            $check_stmt->close();
        }
    }
}


$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register | Blood Donor System</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }


        
        header {
            background: #c62828;
            color: white;
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .logo {
            font-size: 24px;
            font-weight: bold;
        }


        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 15px;
        }


        nav a:hover {
            text-decoration: underline;
        }


        
        main {
            min-height: calc(100vh - 140px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }


        .register-box {
            width: 100%;
            max-width: 520px;
            background: white;
            padding: 35px;
            border-radius: 10px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.12);
        }


        .register-box h1 {
            text-align: center;
            color: #c62828;
            margin-bottom: 8px;
        }


        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
            font-size: 14px;
        }


    
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }


        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }


        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }


        .form-group {
            margin-bottom: 18px;
        }


        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }


        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
            outline: none;
            font-family: Arial, sans-serif;
        }


        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #c62828;
        }


        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }


        .register-btn {
            width: 100%;
            padding: 13px;
            background: #c62828;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }


        .register-btn:hover {
            background: #a91f1f;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }


        .login-link a {
            color: #c62828;
            text-decoration: none;
            font-weight: bold;
        }


        .login-link a:hover {
            text-decoration: underline;
        }

        footer {
            background: #222;
            color: white;
            text-align: center;
            padding: 18px;
            font-size: 14px;
        }

        @media (max-width: 600px) {

            header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 10px;
            }


            nav a {
                margin: 0 7px;
            }


            .register-box {
                padding: 25px 20px;
            }

        }

    </style>

</head>


<body>

        <div class="logo">
            Blood Donor System
        </div>


        <nav>

            <a href="index.php">
                Home
            </a>

            <a href="login.php">
                Login
            </a>

        </nav>

    </header>

    <main>

        <div class="register-box">

            <h1>
                Create Account
            </h1>


            <p class="subtitle">
                Register for the Blood Donor Management System
            </p>

            <?php if (!empty($message)): ?>

                <div class="message <?php echo $message_type; ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>

            <form
                method="POST"
                action=""
            >
                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php
                            echo htmlspecialchars($name);
                        ?>"
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
                        placeholder="Enter your email"
                        value="<?php
                            echo htmlspecialchars($email);
                        ?>"
                        maxlength="100"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                    </label>


                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="Enter 10 digit phone number"
                        value="<?php
                            echo htmlspecialchars($phone);
                        ?>"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="address">
                        Address
                    </label>


                    <textarea
                        id="address"
                        name="address"
                        placeholder="Enter your address"
                        maxlength="255"
                        required
                    ><?php
                        echo htmlspecialchars($address);
                    ?></textarea>

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        minlength="6"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>


                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Re-enter password"
                        minlength="6"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="role">
                        Register As
                    </label>


                    <select
                        id="role"
                        name="role"
                        required
                    >

                        <option value="">
                            -- Select Role --
                        </option>


                        <option
                            value="donor"
                            <?php
                            if ($role === "donor") {
                                echo "selected";
                            }
                            ?>
                        >
                            Donor
                        </option>


                        <option
                            value="recipient"
                            <?php
                            if ($role === "recipient") {
                                echo "selected";
                            }
                            ?>
                        >
                            Recipient
                        </option>

                    </select>

                </div>

                <button
                    type="submit"
                    class="register-btn"
                >
                    Register
                </button>


            </form>

            <div class="login-link">

                Already have an account?

                <a href="login.php">
                    Login here
                </a>

            </div>

        </div>

    </main>

    <footer>

        &copy;

        <?php echo date("Y"); ?>

        Blood Donor Management System.

        All Rights Reserved.

    </footer>


</body>

</html>