<?php
$host = "127.0.0.1:3306";
$user = "u929965336_wssb";
$pass = "Sutera@23";
$dbname = "u929965336_warisansutera";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
