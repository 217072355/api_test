<?php
header('Content-Type: application/json'); // Ensure response is in JSON format

// Database connection (replace with your actual database connection logic)
$conn = new mysqli("localhost", "sibekodumisani", "dspwd", "kendal");

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

//threshold for the number of incidents to require inspection
$systemThreshold = 5;

// SQL query to retrieve systems with the highest number of incidents in the last month
$queryHighestIncidentsSystems = "
    SELECT system_name, COUNT(*) AS incident_count
    FROM Incidents
    WHERE timestamp >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY system_name
    ORDER BY incident_count DESC
";

// Execute the query and handle errors
$resultHighestIncidentsSystems = $conn->query($queryHighestIncidentsSystems);
if (!$resultHighestIncidentsSystems) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error executing query: " . $conn->error
    ]);
    exit();
}

//response data
$systems = [];
if ($resultHighestIncidentsSystems->num_rows > 0) {
    while ($row = $resultHighestIncidentsSystems->fetch_assoc()) {
        // Ensure system_name is not empty
        if (!empty($row['system_name'])) {
            // Check if an inspection is required based on the threshold
            $row['inspection_required'] = $row['incident_count'] > $systemThreshold ? 'Yes' : 'No';
            $systems[] = $row; // Add the system data to the response
        }
    }

    // Return the result as JSON
    echo json_encode([
        "status" => "success",
        "data" => $systems
    ]);
} else {
    // No systems found
    http_response_code(404); // Not Found
    echo json_encode([
        "status" => "error",
        "message" => "No incidents found in the last month"
    ]);
}

?>
