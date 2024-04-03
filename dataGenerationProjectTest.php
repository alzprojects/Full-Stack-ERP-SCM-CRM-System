<?php
$servername = "mydb.itap.purdue.edu";
$username = "azimbali";
$password = "password";
$database = "azimbali";

$conn = new mysqli($servername, $username, $password);

// Check connection was successful, otherwise immediately exit the script
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";

// Function to generate random string for product names
function generateRandomString($length = 5) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Function to generate random int for quantites/IDs
function generateRandomInt(array $usedNums = []) {
    $attempts = 0;
    $attemptLimit = 10000;
    while ($attempts < $attemptLimit) {
        $randomInt = rand(1,10000);
        if (!in_array($randomInt, $usedNums)) {
            return $randomInt;
        }
        $attempts++;
    }
    return NULL;
}

function generateRandomDate($startDate, $endDate) {
    $startTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate);

    $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);

    $randomDateForSQL = date('Y-m-d', $randomTimestamp);

    return $randomDateForSQL;
}

// Function to generate random decimal for prices
function generateRandomDecimal() {
    $float = rand(100, 10000) / 100;
    return $float;
}

function insertRandomProducts($conn, $quantity, $database) {
    for ($i = 1; $i <= $quantity; $i++) {
        // Generate a random product name
        $name = generateRandomString(rand(5, 10));
        // Generate a random price between 1.00 and 100.00
        $price = generateRandomDecimal();

        // Prepare the INSERT statement
        $stmt = $conn->prepare("INSERT INTO $database.product (productID, name, price) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $i, $name, $price);

        // Execute the statement
        if ($stmt->execute()) {
            echo "New record created successfully for productID: $i\n\n";
        } else {
            echo "Error: " . $stmt->error . "\n\n";
        }

        // Close the statement
        $stmt->close();
    }
}
function insertRandomPurchase($conn, $quantity, $database, $startDate, $endDate) {
    $purchaseIDs = array();
    for ($i = 1; $i <= $quantity; $i++) {
        $purchaseID = generateRandomInt($purchaseIDs);
        if ($purchaseID === NULL) {
            echo "Failed to generate unique purchase ID after 10000 attempts.\n";
            exit;
        }
        else {
            array_push($purchaseIDs, $purchaseID);
        }
        $purchaseDate = generateRandomDate($startDate, $endDate);
        // Prepare the INSERT statement
        $stmt = $conn->prepare("INSERT INTO $database.purchase (purchaseID, date) VALUES (?, ?)");
        $stmt->bind_param("is", $purchaseID, $purchaseDate);

        // Execute the statement
        if ($stmt->execute()) {
            echo "New record created successfully for purchaseID: $purchaseID\n\n";
        } else {
            echo "Error: " . $stmt->error . "\n\n";
        }

        // Close the statement
        $stmt->close();
    }
    return NULL;
}

function insertRandomPurchaseDetail($conn, $quantity, $database) {
    $purchaseDetailIDs = array();
    $productIDs = array();
    for ($i = 1; $i <= $quantity; $i++) {
        $purchaseDetailID = generateRandomInt($purchaseDetailIDs);
        if ($purchaseDetailID === NULL) {
            echo "Failed to generate unique purchase ID after 10000 attempts.\n";
            exit;
        }
        else {
            array_push($purchaseDetailIDs, $purchaseDetailID);
        }
        $purchaseQuantity = generateRandomInt();
        $productID = getRandomProductId($conn, $productIDs, $database);
        if ($productID === NULL) {
            echo "Failed to get a unique product ID.\n";
            exit;
        }
        else {
            array_push($productIDs, $productID);
        }
        $purchaseID = getPurchaseID($conn, $database);
        // Prepare the INSERT statement
        $stmt = $conn->prepare("INSERT INTO $database.purchaseDetail (purchaseDetailID, quantity, purchaseID, productID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $purchaseDetailID, $purchaseQuantity, $purchaseID, $productID);

        // Execute the statement
        if ($stmt->execute()) {
            echo "New record created successfully for purchaseDetailID: $purchaseDetailID\n\n";
        } else {
            echo "Error: " . $stmt->error . "\n\n";
        }

        // Close the statement
        $stmt->close();
    }
}

function getPurchaseID($conn, $database) {
    $query = "SELECT purchaseID FROM $database.purchase ORDER BY RAND() LIMIT 1";
    $result = $conn->query($query);
    
    // Check if the query was successful and has at least one row
    if ($result !== false && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $purchaseID = $row['purchaseID'];
        echo "Purchase ID: " . $purchaseID;
        return $purchaseID;
    } 
    else {
        echo "Failed to get purchase ID.";
        return null;
    }
}

function getRandomProductId($conn, array $usedIds, $database) {
    // Convert the array of used IDs into a string for the SQL query, handling the case where there are no used IDs
    $usedIdsString = count($usedIds) > 0 ? implode(',', $usedIds) : '0';

    $query = "SELECT productID FROM $database.product WHERE productID NOT IN ($usedIdsString) ORDER BY RAND() LIMIT 1";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['productID'];
    }
    return null;
}

function getTableColumnNames($conn, $database, $table) {
    $query = "SELECT COLUMN_NAME 
              FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_SCHEMA = '$database' 
              AND TABLE_NAME = '$table'";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Column Name: " . $row['COLUMN_NAME'] . "\n";
        }
    } else {
        echo "No columns found for table '$table' in database '$database'.\n";
    }
}


//insertRandomProducts($conn, 10, $database);
//insertRandomPurchase($conn, 10, $database, '2020-01-01', '2020-12-31');
//insertRandomPurchaseDetail($conn, 10, $database);
getTableColumnNames($conn, $database, 'product');

//Close the connection
mysqli_close($conn);
?>
