<?php
$servername = "mydb.itap.purdue.edu";
$username = "azimbali";
$password = "Max!024902!!";
$database = "azimbali";

$conn = new mysqli($servername, $username, $password, $database);

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

function getRandomEnumValue($conn, $tableName, $columnName) {
    try {
        // Prepare the SQL statement to fetch the column type
        $sql = "SHOW COLUMNS FROM `$tableName` WHERE Field = ?";
        $stmt = $conn->prepare($sql);
        // Bind the $columnName variable as a string to the statement
        mysqli_stmt_bind_param($stmt, "s", $columnName); // 's' denotes the type of the variable, a string in this case
        $stmt->execute();
        $result = $stmt->get_result(); // Get the result set from the statement
        if ($row = $result->fetch_assoc()) {
            // Extract the enum's permissible values
            $type = $row['Type'];
            preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
            $enumValues = explode("','", $matches[1]);

            // Select a random value from the enum options
            $randomKey = array_rand($enumValues);
            return $enumValues[$randomKey];
        }
        
        throw new Exception("Column not found or is not an enum type.");
    } catch (Exception $e) {
        // Handle any errors, such as column not found or database connection issues
        echo "An error occurred: " . $e->getMessage();
        return null; // Or handle this case as needed
    }
}

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

function insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, $table) {
    $columnDetails = getTableColumnDetails($conn, $database, $table);
    $columnNames = array_keys($columnDetails);
    $usedIDs = array();
    // Begin preparing the SQL statement
    $columnsString = implode(", ", $columnNames);
    $valuesPlaceholder = implode(", ", array_fill(0, count($columnNames), "?"));
    $sql = "INSERT INTO $table ($columnsString) VALUES ($valuesPlaceholder)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        return;
    }
    $usedIDs = array();
    // Insert data
    for ($j = 0; $j < $quantity; $j++) {
        $bindTypes = ''; // String to hold bind types
        $values = []; // Array to hold the values for binding
        $bindParams = []; // Array to hold references for binding
    
        foreach ($columnDetails as $columnName => $dataType) {
            switch ($dataType) {
                case 'int':
                    $value = generateRandomInt($usedIDs); 
                    $bindTypes .= 'i';
                    array_push($usedIDs, $value);
                    break;
                case 'decimal':
                    $value = generateRandomDecimal();
                    $bindTypes .= 'd';
                    break;
                case 'varchar':
                    $value = generateRandomString(rand(5, 10)); 
                    $bindTypes .= 's';
                    break;
                case 'date':
                    $value = generateRandomDate($startDate, $endDate);
                    $bindTypes .= 's';
                    break;
                case `boolean`:
                    $value =  rand(0,1) == 1;
                    $bindTypes .= 'i';
                    break;
                case 'enum':
                    $value = getRandomEnumValue($conn, $table, $columnName); 
                    $bindTypes .= 's';
                    break;
                default:
                    // Handle other data types or throw an error
                    echo "Unsupported data type: $dataType";
                    return;
            }
            $values[] = $value;
        }
    
        // Prepare the parameters for binding
        $bindParams[] = & $bindTypes;
        foreach ($values as $key => $value) {
            $bindParams[] = & $values[$key]; // Bind each value by reference
        }
    
        // Call bind_param with a dynamic number of parameters
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    
        // Execute the statement
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
    }
    
    $stmt->close();
}

function getForeignKeys($dbname, $conn, $table_name) {
    $sql = "SELECT 
        k.COLUMN_NAME, 
        k.REFERENCED_TABLE_NAME 
    FROM 
        information_schema.KEY_COLUMN_USAGE k 
    WHERE 
        k.TABLE_SCHEMA = '$dbname' 
        AND k.REFERENCED_TABLE_SCHEMA IS NOT NULL 
        AND k.TABLE_NAME = '$table_name'";

    $result = $conn->query($sql);

    $foreignKeysInfo = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $foreignKeysInfo[$row["COLUMN_NAME"]] = $row["REFERENCED_TABLE_NAME"];
        }
    } else {
        echo "0 results";
    }
    return $foreignKeysInfo;
}


//insertRandomProducts($conn, 10, $database);
//insertRandomPurchase($conn, 10, $database, '2020-01-01', '2020-12-31');
//insertRandomPurchaseDetail($conn, 10, $database);
//insertBaseTableData(10, '2020-01-01', '2020-12-31', $conn, $database, 'product');
/*
$foreignKeysInfo = array();
$foreignKeysInfo = getForeignKeys('azimbali', $conn, 'purchaseDetail');
foreach ($foreignKeysInfo as $columnName => $referencedTableName) {
    echo "Column: $columnName, Referenced Table: $referencedTableName\n";
}*/
$quantity = 10;
$startDate = '2020-01-01';
$endDate = '2020-12-31';
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'customers'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'employees'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'locations'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'order'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'product'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'purchase'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'supplier');
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'users'); 


mysqli_close($conn);
?>
