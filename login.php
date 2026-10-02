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
$email = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    $email = trim($_POST["email"] ?? "");
    $user_password = $_POST["password"] ?? "";


    if (empty($email) || empty($user_password)) {

        $message = "Please enter your email and password.";
        $message_type = "error";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    }

    else {

        $sql = "
            SELECT
                id,
                full_name,
                email,
                password,
                phone,
                address,
                role
            FROM users
            WHERE email = ?
            LIMIT 1
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $message = "Database error: " . $conn->error;
            $message_type = "error";

        }

        else {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows == 1) {

                $user = $result->fetch_assoc();


                if (password_verify(
                    $user_password,
                    $user["password"]
                )) {

                    $_SESSION["logged_in"] = true;

                    $_SESSION["user_id"] = $user["id"];

                    $_SESSION["username"] = $user["full_name"];

                    $_SESSION["email"] = $user["email"];

                    $_SESSION["phone"] = $user["phone"];

                    $_SESSION["address"] = $user["address"];

                    $_SESSION["role"] = $user["role"];


                    $role = strtolower(
                        trim($user["role"])
                    );


                    if (
                        $role == "donor" ||
                        $role == "blood donor"
                    ) {

                        header(
                            "Location: donor/dashboard.php"
                        );

                        exit();
                    }


                    elseif (
                        $role == "recipient" ||
                        $role == "patient" ||
                        $role == "receiver"
                    ) {

                        header(
                            "Location: recipient/dashboard.php"
                        );

                        exit();
                    }

                    elseif (
                        $role == "admin" ||
                        $role == "administrator"
                    ) {

                        $_SESSION["admin_id"] = $user["id"];

                        $_SESSION["admin_name"] =
                            $user["full_name"];

                        $_SESSION["admin_email"] =
                            $user["email"];


                        header(
                            "Location: admin/index.php"
                        );

                        exit();
                    }

                    else {

                        $message =
                            "Invalid user role: " .
                            htmlspecialchars(
                                $user["role"]
                            );

                        $message_type = "error";

                        session_unset();

                        session_destroy();
                    }

                }

                else {

                    $message =
                        "Incorrect password.";

                    $message_type = "error";
                }

            }

            else {

                $message =
                    "No account found with this email.";

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login | Blood Donor System
    </title>


    <style>

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

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

        .login-box {

            width: 100%;

            max-width: 450px;

            background: white;

            padding: 35px;

            border-radius: 10px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.12);

        }


        .login-box h1 {

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

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            font-size: 14px;

        }


        .form-group input {

            width: 100%;

            padding: 13px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-size: 15px;

            outline: none;

        }


        .form-group input:focus {

            border-color: #c62828;

        }

        .login-btn {

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


        .login-btn:hover {

            background: #a91f1f;

        }

        .register-link {

            text-align: center;

            margin-top: 20px;

            font-size: 14px;

        }


        .register-link a {

            color: #c62828;

            text-decoration: none;

            font-weight: bold;

        }


        .register-link a:hover {

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


            .login-box {

                padding: 25px 20px;

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

            <a href="register.php">
                Register
            </a>

        </nav>

    </header>


    <main>

        <div class="login-box">


            <h1>
                Login
            </h1>


            <p class="subtitle">

                Access your Blood Donor Management System account

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
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">

                        Password

                    </label>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="login-btn"
                >

                    Login

                </button>


            </form>

            <div class="register-link">

                Don't have an account?

                <a href="register.php">

                    Create an account

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
