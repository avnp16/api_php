<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
include("db.php");

// Set the response content type to JSON
header('Content-Type: application/json');

// Initialize an empty response array
$response = array();

// Check the HTTP request method
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    // Create a new item (Create)
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        isset($data["item_name"]) &&
        isset($data["item_gst_desc"]) &&
        isset($data["item_hsn"]) &&
        isset($data["gst_rate"]) &&
        isset($data["item_uqc"]) &&
        isset($data["category"])  // New field
    ) {
        // Extract data from the request
        $itemName = $data["item_name"];
        $itemGstDesc = $data["item_gst_desc"];
        $itemHsn = $data["item_hsn"];
        $gstRate = $data["gst_rate"];
        $itemUqc = $data["item_uqc"];
        $category = $data["category"];  // New field

        // Insert the new item into the item_master table
        $sql = "INSERT INTO item_master (item_name, item_gst_desc, item_hsn, gst_rate, item_uqc, category)
                VALUES ('$itemName', '$itemGstDesc', '$itemHsn', $gstRate, '$itemUqc', '$category')";

        if ($conn->query($sql) === TRUE) {
            $response["message"] = "Item created successfully";
        } else {
            $response["error"] = "Error creating item: " . $conn->error;
        }
    } else {
        $response["error"] = "Invalid data provided";
    }
} elseif ($method === "GET") {
    // Fetch all items (Read)
    $sql = "SELECT * FROM item_master";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $response["data"] = array();

        while ($row = $result->fetch_assoc()) {
            $response["data"][] = $row;
        }
    } else {
        $response["message"] = "No items found";
    }
} elseif ($method === "PUT") {
    // Update an item (Update)
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        isset($data["id"]) &&
        isset($data["item_name"]) &&
        isset($data["item_gst_desc"]) &&
        isset($data["item_hsn"]) &&
        isset($data["gst_rate"]) &&
        isset($data["item_uqc"]) &&
        isset($data["category"])  // New field
    ) {
        // Extract data from the request
        $id = $data["id"];
        $itemName = $data["item_name"];
        $itemGstDesc = $data["item_gst_desc"];
        $itemHsn = $data["item_hsn"];
        $gstRate = $data["gst_rate"];
        $itemUqc = $data["item_uqc"];
        $category = $data["category"];  // New field

        // Update the item in the item_master table
        $sql = "UPDATE item_master SET
                item_name = '$itemName',
                item_gst_desc = '$itemGstDesc',
                item_hsn = '$itemHsn',
                gst_rate = $gstRate,
                item_uqc = '$itemUqc',
                category = '$category'
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            $response["message"] = "Item updated successfully";
        } else {
            $response["error"] = "Error updating item: " . $conn->error;
        }
    } else {
        $response["error"] = "Invalid data provided";
    }
} elseif ($method === "DELETE") {
    // Delete an item (Delete)
    $id = $_GET["id"];

    $sql = "DELETE FROM item_master WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $response["message"] = "Item deleted successfully";
    } else {
        $response["error"] = "Error deleting item: " . $conn->error;
    }
}

// Close the database connection
$conn->close();

// Output the response as JSON
echo json_encode($response);
?>
