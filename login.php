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

    // Prepare a statement to select the user by username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);

    // Execute the statement
    if ($stmt->execute()) {
        // Fetch user data
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            http_response_code(401); // Unauthorized
            echo json_encode(['error' => 'User not found']);
        } else {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                $user_id = $user['user_id']; // Get the user_id

                // Generate a new JWT token
                $token = (new Builder())
                    ->set('user_id', $user_id)
                    ->sign(new Sha256(), $secretKey)
                    ->getToken();

                http_response_code(200); // Success
                echo json_encode(['message' => 'Login successful', 'user_id' => $user_id, 'token' => (string) $token]);
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode(['error' => 'Invalid password']);
            }
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database query error']);
    }

    // Close the statement
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}

// Close the database connection (optional, as it will be included in db.php)
$conn->close();
?>
