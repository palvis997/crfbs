<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "crfbs_db";

// Create database connection using MySQLi
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>