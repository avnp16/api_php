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
    // Create a new party record (Create)
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        isset($data["party_name"]) &&
        isset($data["mobile_no"]) &&
        isset($data["category"]) &&
        isset($data["gst_no"]) &&
        isset($data["address"]) &&
        isset($data["city"]) &&   // New field
        isset($data["state"])
    ) {
        $partyName = $data["party_name"];
        $mobileNo = $data["mobile_no"];
        $category = $data["category"];
        $gstNo = $data["gst_no"];
        $address = $data["address"];
        $city = $data["city"];     // New field
        $state = $data["state"];

        $sql = "INSERT INTO party_masters (party_name, mobile_no, category, gst_no, address, city, state)
                VALUES ('$partyName', '$mobileNo', '$category', '$gstNo', '$address', '$city', '$state')";

        if ($conn->query($sql) === TRUE) {
            $response["message"] = "Party record created successfully";
        } else {
            $response["error"] = "Error creating party record: " . $conn->error;
        }
    } else {
        $response["error"] = "Invalid data provided";
    }
} elseif ($method === "GET") {
    // Fetch all party records (Read)
    $sql = "SELECT * FROM party_masters";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $response["data"] = array();

        while ($row = $result->fetch_assoc()) {
            $response["data"][] = $row;
        }
    } else {
        $response["message"] = "No party records found";
    }
} elseif ($method === "PUT") {
    // Update a party record (Update)
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        isset($data["id"]) &&
        isset($data["party_name"]) &&
        isset($data["mobile_no"]) &&
        isset($data["category"]) &&
        isset($data["gst_no"]) &&
        isset($data["address"]) &&
        isset($data["city"]) &&   // New field
        isset($data["state"])
    ) {
        $id = $data["id"];
        $partyName = $data["party_name"];
        $mobileNo = $data["mobile_no"];
        $category = $data["category"];
        $gstNo = $data["gst_no"];
        $address = $data["address"];
        $city = $data["city"];     // New field
        $state = $data["state"];

        $sql = "UPDATE party_masters SET
                party_name = '$partyName',
                mobile_no = '$mobileNo',
                category = '$category',
                gst_no = '$gstNo',
                address = '$address',
                city = '$city',    -- Update the new field
                state = '$state'
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            $response["message"] = "Party record updated successfully";
        } else {
            $response["error"] = "Error updating party record: " . $conn->error;
        }
    } else {
        $response["error"] = "Invalid data provided";
    }
} elseif ($method === "DELETE") {
    // Delete a party record (Delete)
    $id = $_GET["id"];

    $sql = "DELETE FROM party_masters WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $response["message"] = "Party record deleted successfully";
    } else {
        $response["error"] = "Error deleting party record: " . $conn->error;
    }
}

// Close the connection
$conn->close();

// Output the response as JSON
echo json_encode($response);
?>
