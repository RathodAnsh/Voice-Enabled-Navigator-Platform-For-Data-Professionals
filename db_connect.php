<?php
$servername = "localhost";  // or your database server name
$username = "root"; // your database username
$password = "";              // leave empty if no password is required
$database = "data"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>