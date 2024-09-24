<?php
// Set the response to JSON format
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "sibekodumisani", "dspwd", "kendal");

// Check the connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate the required fields
if (!isset($data['building_name']) || !isset($data['inspection_date']) || !isset($data['start_time']) || !isset($data['end_time'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error",
        "message" => "All fields are required (building_name, inspection_date, start_time, end_time)."
    ]);
    exit();
}

// Prepare SQL query to insert the data into the inspections table
$stmt = $conn->prepare("INSERT INTO inspections (building_name, inspection_date, start_time, end_time) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $data['building_name'], $data['inspection_date'], $data['start_time'], $data['end_time']);

// Execute the query
if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode([
        "status" => "success",
        "message" => "Inspection scheduled successfully!"
    ]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "message" => "Failed to schedule inspection: " . $stmt->error
    ]);
}

?>
