<?php
$conn = new mysqli("localhost", "root", "", "blood_donor_system");
if(!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>