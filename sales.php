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
    // Create a new GST sales record with items (Create)
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        isset($data["customer_name"]) &&
        isset($data["invoice_number"]) &&
        isset($data["date"]) &&
        isset($data["amount"]) &&
        isset($data["items"])
    ) {
        // Create the bill record in gst_sales table
        $customerName = $data["customer_name"];
        $invoiceNumber = $data["invoice_number"];
        $date = $data["date"];
        $amount = $data["amount"];

        $sql = "INSERT INTO gst_sales (customer_name, invoice_number, date, amount)
                VALUES ('$customerName', '$invoiceNumber', '$date', $amount)";

        if ($conn->query($sql) === TRUE) {
            $billId = $conn->insert_id; // Get the ID of the newly created bill

            // Create item records in bill_items table
            foreach ($data["items"] as $item) {
                $itemName = $item["item_name"];
                $quantity = $item["quantity"];
                $unitPrice = $item["unit_price"];
                $uqc = $item["uqc"];
                $gstRate = $item["gst_rate"];
                $hsnSac = $item["hsn_sac"];

                $sql = "INSERT INTO bill_items (bill_id, item_name, quantity, unit_price, uqc, gst_rate, hsn_sac)
                        VALUES ($billId, '$itemName', $quantity, $unitPrice, '$uqc', $gstRate, '$hsnSac')";

                $conn->query($sql); // Insert each item into bill_items
            }

            $response["message"] = "GST sales record with items created successfully";
        } else {
            $response["error"] = "Error creating GST sales record: " . $conn->error;
        }
    } else {
        $response["error"] = "Invalid data provided";
    }
} elseif ($method === "GET") {
    // Fetch all GST sales records with associated items (Read)
    $sql = "SELECT gst_sales.id AS bill_id, customer_name, invoice_number, date, amount,
                  bill_items.item_name, bill_items.quantity, bill_items.unit_price,
                  bill_items.uqc, bill_items.gst_rate, bill_items.hsn_sac
           FROM gst_sales
           LEFT JOIN bill_items ON gst_sales.id = bill_items.bill_id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $response["data"] = array();

        while ($row = $result->fetch_assoc()) {
            $billId = $row["bill_id"];

            if (!isset($response["data"][$billId])) {
                // Initialize the bill data if it doesn't exist in the response array
                $response["data"][$billId] = array(
                    "bill_id" => $billId,
                    "customer_name" => $row["customer_name"],
                    "invoice_number" => $row["invoice_number"],
                    "date" => $row["date"],
                    "amount" => $row["amount"],
                    "items" => array()
                );
            }

            // Add the item details to the associated bill
            $itemData = array(
                "item_name" => $row["item_name"],
                "quantity" => $row["quantity"],
                "unit_price" => $row["unit_price"],
                "uqc" => $row["uqc"],
                "gst_rate" => $row["gst_rate"],
                "hsn_sac" => $row["hsn_sac"]
            );

            // Append the item data to the bill's items array
            $response["data"][$billId]["items"][] = $itemData;
        }

        // Convert the associative array to a numeric array
        $response["data"] = array_values($response["data"]);
    } else {
        $response["message"] = "No GST sales records found";
    }
} elseif ($method === "PUT") {
    // Update a GST sales record (Update)
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        isset($data["id"]) &&
        isset($data["customer_name"]) &&
        isset($data["invoice_number"]) &&
        isset($data["date"]) &&
        isset($data["amount"])
    ) {
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
