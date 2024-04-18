<?php
$servername = "mydb.itap.purdue.edu";
$username = "azimbali";
$password = "Max!024902!!";
$database = "azimbali";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get the column names and data types for a table
function getTableColumnDetails($conn, $database, $table) {
    $query = "SELECT COLUMN_NAME, DATA_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = '$database' 
            AND TABLE_NAME = '$table'";

    $result = $conn->query($query);
    $columnDetails = array();

    // Check if the query was successful and returned rows
    if ($result && $result->num_rows > 0) {
        // Fetch each row and add the column name and data type to the associative array
        while ($row = $result->fetch_assoc()) {
            echo "Row: " . $row['COLUMN_NAME'] . "\n";
            echo "Row: " . $row['DATA_TYPE'] . "\n";
            $columnDetails[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
        }
        echo "getTableColumnDetails completed \r\n";
        return $columnDetails; 
        } 
        else 
        {
        echo "No columns found for table '$table' in database '$database'.\n";
        return false; // Return false if no columns are found
    }
}

function getEarliestDate($conn, $id, $table) {
    if ($table == 'enumSupplier') {
        $sql = "SELECT MIN(orderDate) AS earliestOrderDate
        FROM `order`
        WHERE supplierID = $id;
        ";
    }
    else if ($table == 'enumCustomer'){
        $sql = "SELECT MIN(`date`) AS earliestOrderDate
        FROM purchase
        WHERE customerID = $id;
        ";
    }
    else {
        $sql1 = "SELECT MIN(`date`) AS earliestOrderDate
        FROM purchase
        WHERE customerID = $id;
        ";
        $sql2 = "SELECT MAX(`date`) AS latestOrderDate
        FROM purchase
        WHERE customerID = $id;
        ";
        $result1 = $conn->query($sql1);
        $result2 = $conn->query($sql2);
        $row1 = $result1->fetch_assoc();
        $row2 = $result2->fetch_assoc();
        $i = $row1['earliestOrderDate'];
        $j = $row2['latestOrderDate'];
        echo "i: $i\n";
        echo "j: $j\n";
        return generateRandomDate($i, $j);
    }
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['earliestOrderDate'];
    }
    return NULL;
}

function getAllUserIDs($conn) {
    // SQL query to select userID, start_date, and user_type from the 'users' table
    $sql = "SELECT userID, start_date, user_type FROM users";
    
    // Execute the SQL query
    $result = mysqli_query($conn, $sql);
    
    // Check for a query error and stop execution if it fails
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }
    
    // Initialize an array to store user details
    $userDetails = array();
    
    // Fetch each row from the query result
    while ($row = mysqli_fetch_assoc($result)) {
        // Add each user record directly to the array without using userID as a key
        $userDetails[] = array(
            'userID' => $row['userID'],
            'start_date' => $row['start_date'],
            'user_type' => $row['user_type']
        );
    }
    
    // Return the indexed array of user details
    return $userDetails;
}



function getCustomerDetails($conn, $id, $table) {
    getEarliestDate($conn, $id, $table); // Assuming this function works as expected.
    
    if($table == 'enumCustomer') {
        $sql = "SELECT customerID FROM enumCustomer WHERE user_id = $id";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $customerID = (int)$row['customerID']; // Cast to int to ensure it is an integer
        } else {
            die("No customer found with that ID.");
        }
        // Assuming you want to query purchases for this customer
        $sql = "SELECT * FROM purchase WHERE customerID = $customerID"; 
    } else if($table == 'enumSupplier'){
        $sql = "SELECT * FROM `order` WHERE supplierID = $id";
    } else {
        $sql = "SELECT * FROM employees WHERE userID = $id";
    }
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }
    $customerDetails = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $customerDetails[] = $row;
    }
    return $customerDetails;
}

function printDetails($customerDetails) {
    echo "<table border='1'>";
    echo "<tr>";
    if (!empty($customerDetails)) {
        foreach ($customerDetails[0] as $key => $value) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
    }
    echo "</tr>";
    foreach ($customerDetails as $customerRecord) {
        echo "<tr>";
        foreach ($customerRecord as $key => $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles2.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Display</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Data Viewer</h1>
    <button id="loadDataBtn">Show Users</button>
    <div id="dataDisplay"></div>

<script>
    document.getElementById('loadDataBtn').addEventListener('click', function() {
        fetchData();
    });

    function fetchData() {
        fetch('get_user_data.php') // Assuming your PHP script is named get_user_data.php
        .then(response => response.json()) // Convert the response to JSON
        .then(data => {
            displayData(data);
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    function displayData(data) {
    const display = document.getElementById('dataDisplay');
    display.innerHTML = ''; // Clear previous contents
    data.forEach(user => {
        const userDiv = document.createElement('div');
        userDiv.innerHTML = `ID: <strong>${user.userID}</strong>, Start Date: <strong>${user.start_date}</strong>, Type: <strong>${user.user_type}</strong>`;
        display.appendChild(userDiv);
    });
    }
</script>
</body>
</html>
<?php

mysqli_close($conn);
?>