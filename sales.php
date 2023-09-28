<?php
// Include the database connection file
include("db.php");

// Set the response content type to JSON
header('Content-Type: application/json');

// Initialize an empty response array
$response = array();

// Check the HTTP request method
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    // Create a new GST sales record (Create)
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["customer_name"]) && isset($data["invoice_number"]) && isset($data["date"]) && isset($data["amount"])) {
        $customerName = $data["customer_name"];
        $invoiceNumber = $data["invoice_number"];
        $date = $data["date"];
        $amount = $data["amount"];

        $sql = "INSERT INTO gst_sales (customer_name, invoice_number, date, amount)
                VALUES ('$customerName', '$invoiceNumber', '$date', $amount)";

        if ($conn->query($sql) === TRUE) {
            $response["message"] = "GST sales record created successfully";
        } else {
            $response["error"] = "Error creating GST sales record: " . $conn->error;
        }
    } else {
        $response["error"] = "Invalid data provided";
    }
} elseif ($method === "GET") {
    // Fetch all GST sales records (Read)
    $sql = "SELECT * FROM gst_sales";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $response["data"] = array();

        while ($row = $result->fetch_assoc()) {
            $response["data"][] = $row;
        }
    } else {
        $response["message"] = "No GST sales records found";
    }
} elseif ($method === "PUT") {
    // Update a GST sales record (Update)
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["id"]) && isset($data["customer_name"]) && isset($data["invoice_number"]) && isset($data["date"]) && isset($data["amount"])) {
        $id = $data["id"];
        $customerName = $data["customer_name"];
        $invoiceNumber = $data["invoice_number"];
        $date = $data["date"];
        $amount = $data["amount"];

        $sql = "UPDATE gst_sales SET
                customer_name = '$customerName',
                invoice_number = '$invoiceNumber',
                date = '$date',
                amount = $amount
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            $response["message"] = "GST sales record updated successfully";
        } else {
            $response["error"] = "Error updating GST sales record: " . $conn->error;
        }
    } else {
        $response["error"] = "Invalid data provided";
    }
} elseif ($method === "DELETE") {
    // Delete a GST sales record (Delete)
    $id = $_GET["id"];

    $sql = "DELETE FROM gst_sales WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $response["message"] = "GST sales record deleted successfully";
    } else {
        $response["error"] = "Error deleting GST sales record: " . $conn->error;
    }
}

// Close the connection
$conn->close();

// Output the response as JSON
echo json_encode($response);
?>
