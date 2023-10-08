<?php
// Enable error reporting and display errors (for development/debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration
include('db.php');

// Include the lcobucci/jwt library
require __DIR__ . '/vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

// Your secret key for JWT
$secretKey = 'your-secret-key'; // Replace with your actual secret key

try {
    // Read the incoming JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if ($data === null || !isset($data['username']) || !isset($data['password'])) {
        throw new Exception("Invalid JSON data received.");
    }

    // Extract username and password from the JSON data
    $username = $data['username'];
    $password = $data['password'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Username already exists']);
    } else {
        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Generate a new JWT token
            $token = (new Builder())
                ->set('user_id', $user_id)
                ->sign(new Sha256(), $secretKey)
                ->getToken();

            http_response_code(200); // Success
            echo json_encode(['message' => 'Registration successful', 'user_id' => $user_id, 'token' => (string) $token]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    // Close the statements and the database connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
?>
