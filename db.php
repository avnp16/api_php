

<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "sales";

// Create a connection to MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
