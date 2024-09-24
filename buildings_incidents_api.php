<?php
header('Content-Type: application/json'); // Ensure response is in JSON format

// Database connection
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

// Set the threshold for incident count to require inspection
$buildingThreshold = 4; 

// SQL query to retrieve the building with the highest number of incidents in the last month
$queryHighestIncidentsBuilding = "
    SELECT location, COUNT(*) AS incident_count
    FROM Incidents
    WHERE timestamp >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY location
    ORDER BY incident_count DESC
";

// Execute the query and handle errors
$resultHighestIncidentsBuilding = $conn->query($queryHighestIncidentsBuilding);
if (!$resultHighestIncidentsBuilding) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "message" => "Error executing query: " . $conn->error
    ]);
    exit();
}

// Prepare the response
$buildings = [];
if ($resultHighestIncidentsBuilding->num_rows > 0) {
    while ($row = $resultHighestIncidentsBuilding->fetch_assoc()) {
        // Check if the location is not empty
        if (!empty($row['location'])) {
            // Add a field to indicate if an inspection is required based on the threshold
            $row['inspection_required'] = $row['incident_count'] > $buildingThreshold ? 'Yes' : 'No';
            $buildings[] = $row;
        }
    }

    // Return the result in JSON format
    echo json_encode([
        "status" => "success",
        "data" => $buildings
    ]);
} else {
    // No data found
    http_response_code(404); // Not Found
    echo json_encode([
        "status" => "error",
        "message" => "No incidents found in the last month"
    ]);
}

?>
