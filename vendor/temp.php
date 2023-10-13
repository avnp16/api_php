<?php

require 'vendor/autoload.php'; // Include PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
include("db.php"); // Replace with your database connection code

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $uploadDir = 'uploads/'; // Directory to store uploaded files
    $fileName = $uploadDir . $_FILES['excel_file']['name'];

    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $fileName)) {
        // Load the Excel file
        $spreadsheet = IOFactory::load($fileName);
        $worksheet = $spreadsheet->getActiveSheet();

        // Define a mapping of expected column names to database fields
        $columnMapping = [
            'GSTIN / UIN' => 'GSTIN / UIN',
            'Party Code' => 'Party Code',
            // Add more columns and mappings here
        ];

        // Iterate through rows and insert data into the MySQL table, starting from the second row
        $rowIterator = $worksheet->getRowIterator();
        $headerRow = $rowIterator->current(); // Get the first row (header)
        $header = [];
        foreach ($headerRow->getCellIterator() as $cell) {
            $header[] = $cell->getValue();
        }
        // Move to the next row
        $rowIterator->next();

        while ($rowIterator->valid()) {
            $row = $rowIterator->current();
            $cellIterator = $row->getCellIterator();
            $data = [];

            foreach ($cellIterator as $cell) {
                try {
                    $column = $header[ord($cell->getColumn()) - ord('A')];
                    $mappedColumn = $columnMapping[$column] ?? null;
                    if ($mappedColumn !== null) {
                        $data[$mappedColumn] = $cell->getValue();
                    }
                } catch (Exception $e) {
                    // Handle the exception or log an error message
                    error_log('Error: ' . $e->getMessage());
                }
            }

            if (count($data) === count(array_keys($columnMapping))) {
                // All expected columns are present, proceed with insertion
                $columns = array_map(function ($column) {
                    return '`' . $column . '`';
                }, array_keys($data));
                $sql = "INSERT INTO webtel2b (" . implode(', ', $columns) . ") VALUES ('" . implode("', '", $data) . "')";
                $conn->query($sql);
            } else {
                // You can add an else block here to handle rows with missing or unmatched columns if needed
            }

            error_log("Data: " . print_r($data, true));
            error_log("SQL: " . $sql);

            // Move to the next row
            $rowIterator->next();
        }

        echo json_encode(['success' => true, 'message' => 'Data uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

// Close the database connection
$conn->close();
?>

// without user_id send data ok of webtel 2b file
