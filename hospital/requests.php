<?php
include("../conn.php");

$message = "";
$message_type = "";

$create_table = "CREATE TABLE IF NOT EXISTS recipient (
    Recipient_ID INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Gender VARCHAR(20) NOT NULL,
    DOB DATE NOT NULL,
    Blood_Group VARCHAR(10) NOT NULL,
    Address VARCHAR(255) NOT NULL,
    Phone VARCHAR(20) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

mysqli_query($conn, $create_table);


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name   = trim($_POST["full_name"] ?? "");
    $gender      = trim($_POST["gender"] ?? "");
    $dob         = trim($_POST["dob"] ?? "");
    $blood_group = trim($_POST["blood_group"] ?? "");
    $address     = trim($_POST["address"] ?? "");
    $phone       = trim($_POST["phone"] ?? "");
    $email       = trim($_POST["email"] ?? "");
    $password    = $_POST["password"] ?? "";

    
    if (
        empty($full_name) ||
        empty($gender) ||
        empty($dob) ||
        empty($blood_group) ||
        empty($address) ||
        empty($phone) ||
        empty($email) ||
        empty($password)
    ) {

        $message = "Please fill in all fields.";
        $message_type = "danger";

    } elseif (!preg_match("/^[A-Za-z ]+$/", $full_name)) {

        $message = "Full name should contain letters only.";
        $message_type = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "danger";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Phone number must contain exactly 10 digits.";
        $message_type = "danger";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "danger";

    } else {

        
        $check_sql = "SELECT Recipient_ID FROM recipient WHERE Email = ?";

        $check_stmt = $conn->prepare($check_sql);

        if ($check_stmt) {

            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {

                $message = "This email is already registered.";
                $message_type = "danger";

            } else {

                
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                

                $insert_sql = "INSERT INTO recipient
                    (Full_Name, Gender, DOB, Blood_Group, Address, Phone, Email, Password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $insert_stmt = $conn->prepare($insert_sql);

                if ($insert_stmt) {

                    $insert_stmt->bind_param(
                        "ssssssss",
                        $full_name,
                        $gender,
                        $dob,
                        $blood_group,
                        $address,
                        $phone,
                        $email,
                        $hashed_password
                    );

                    if ($insert_stmt->execute()) {

                        $message = "Recipient registration successful.";
                        $message_type = "success";

                        
                        $full_name = "";
                        $gender = "";
                        $dob = "";
                        $blood_group = "";
                        $address = "";
                        $phone = "";
                        $email = "";

                    } else {

                        $message = "Error while registering recipient.";
                        $message_type = "danger";
                    }

                    $insert_stmt->close();

                } else {

                    $message = "Database error: " . $conn->error;
                    $message_type = "danger";
                }
            }

            $check_stmt->close();

        } else {

            $message = "Database error: " . $conn->error;
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Recipient Registration | Blood Donor System</title>

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

        .header {
            background: #b30000;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin-bottom: 5px;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .container h2 {
            text-align: center;
            color: #b30000;
            margin-bottom: 25px;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .danger {
            background: #f8d7da;
            color: #721c24;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #b30000;
        }

        textarea {
            height: 90px;
            resize: vertical;
        }

        .row {
            display: flex;
            gap: 20px;
        }

        .row .form-group {
            width: 50%;
        }

        .btn {
            width: 100%;
            padding: 13px;
            background: #b30000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            background: #8f0000;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #b30000;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {

            .container {
                width: 95%;
                padding: 20px;
            }

            .row {
                display: block;
            }

            .row .form-group {
                width: 100%;
            }
        }

    </style>

</head>

<body>

    <div class="header">

        <h1>Blood Donor Management System</h1>

        <p>Recipient Registration</p>

    </div>


    <div class="container">

        <h2>Recipient Registration Form</h2>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              action=""
              onsubmit="return validateForm();">



            <div class="form-group">

                <label for="full_name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                    required
                >

            </div>
            

            <div class="row">

                <div class="form-group">

                    <label for="gender">
                        Gender
                    </label>

                    <select id="gender"
                            name="gender"
                            required>

                        <option value="">Select Gender</option>

                        <option value="Male"
                            <?php
                            if (($gender ?? '') == "Male")
                                echo "selected";
                            ?>>
                            Male
                        </option>

                        <option value="Female"
                            <?php
                            if (($gender ?? '') == "Female")
                                echo "selected";
                            ?>>
                            Female
                        </option>

                        <option value="Other"
                            <?php
                            if (($gender ?? '') == "Other")
                                echo "selected";
                            ?>>
                            Other
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="dob">
                        Date of Birth
                    </label>

                    <input
                        type="date"
                        id="dob"
                        name="dob"
                        value="<?php echo htmlspecialchars($dob ?? ''); ?>"
                        required
                    >

                </div>

            </div>

            <div class="form-group">

                <label for="blood_group">
                    Required Blood Group
                </label>

                <select id="blood_group"
                        name="blood_group"
                        required>

                    <option value="">
                        Select Blood Group
                    </option>

                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>

                </select>

            </div>


            <div class="form-group">

                <label for="address">
                    Address
                </label>

                <textarea
                    id="address"
                    name="address"
                    placeholder="Enter your address"
                    required><?php echo htmlspecialchars($address ?? ''); ?></textarea>

            </div>



            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter 10 digit phone number"
                    maxlength="10"
                    value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
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
                    placeholder="Enter password"
                    minlength="6"
                    required
                >

            </div>

            <button type="submit"
                    class="btn">

                Register as Recipient

            </button>

        </form>


        <a href="../index.php" class="back">
            ← Back to Home
        </a>

    </div>


    <script>

        function validateForm() {

            const name =
                document.getElementById("full_name").value.trim();

            const phone =
                document.getElementById("phone").value.trim();

            const password =
                document.getElementById("password").value;


            if (!/^[A-Za-z ]+$/.test(name)) {

                alert("Please enter a valid name using letters only.");

                return false;
            }

            if (!/^[0-9]{10}$/.test(phone)) {

                alert("Phone number must contain exactly 10 digits.");

                return false;
            }

            if (password.length < 6) {

                alert("Password must be at least 6 characters.");

                return false;
            }


            return true;
        }

    </script>

</body>

</html>