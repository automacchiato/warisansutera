<?php
$host = "127.0.0.1:3306";
$user = "u647109978_wssb";
$pass = "Macchiato98@";
$dbname = "u647109978_wssb";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
