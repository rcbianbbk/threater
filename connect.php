<?php
$servername = "sql313.infinityfree.com";
$username = "if0_42714572";
$password = "bibekpathak18";
$dbname = "if0_42714572_theater_app";

// Connection banaune
$conn = new mysqli($servername, $username, $password, $dbname);

// Check garne
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>