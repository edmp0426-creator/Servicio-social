<?php
$servername = "mysql"; // This matches the service name in docker-compose.yml
$username = "root";    // Using root user as defined in docker-compose.yml
$password = "root";    // Root password from docker-compose.yml
$dbname = "test-allport"; // Database name from docker-compose.yml

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF8
$conn->set_charset("utf8");
?>