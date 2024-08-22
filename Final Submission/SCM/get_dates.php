<?php
// Database credentials
$servername = "mydb.itap.purdue.edu";
$username = "azimbali";
$password = "Max!024902!!";
$database = "azimbali";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the minimum and maximum dates
$sql = "SELECT MIN(orderDate) AS minDate, MAX(orderDate) AS maxDate FROM `order`";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $minDate = $row["minDate"];
    $maxDate = $row["maxDate"];

    // Prepare response
    $response = array(
        'minDate' => $minDate,
        'maxDate' => $maxDate
    );

    // Encode the response as JSON
    echo json_encode($response);
} else {
    echo "No data available";
}

// Close connection
$conn->close();
?>