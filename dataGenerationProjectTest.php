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
/*
function insertPurchaseDetailData($conn, $database, $purchaseID, $locationID){
    usedProducts = array();
    usedIDs = array();
    for ($i = 1; $i <= rand(1,15); $i++) {
        $productID = getRandomProductId($conn, $usedProducts, $database);
        $purchaseDetailID = generateRandomInt($usedIDs);
        $quantity = rand(1,7) * rand(1,3);
        #get inventory detail corresponding to locationID & productID

    }
}*/


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
        else {
            if ($table == 'customers' || $table == 'supplier') {
                createEnumTables($table, $conn, $bindParams[1], $database);
            }
            if ($table == 'customers') {
                insertPurchaseData($conn, $bindParams[1], rand(1,10), $startDate, $endDate);
            }
            if ($table == 'supplier') {
                insertOrderData($conn, $bindParams[1], rand(1,10), $startDate, $endDate);
            }
            if($table == 'product')
            {
                insertInventoryDetailData($conn, $bindParams[1], $database);
            }
        }
    }        
        $stmt->close();
}
/*
function insertInventoryDetailData($conn, $productID, $database) {
    locations = array();
    #get all the locationID's in the array
    for ($i = 1; $i <= arlen(locations); $i++) {
        $quantity = generateRandomInt() * rand(1,5);
        $stmt = $conn->prepare("INSERT INTO $database.inventoryDetail (inventoryDetailID, quantity, locationID, productID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $inventoryDetailID, $quantity, locations[$i], $productID);
        $stmt->execute();
        $stmt->close();
        insertUserData($conn, $user_id, $tableName, $table);
    }
}
*/
function createEnumTables($table, $conn, $id, $database = 'azimbali') {
    $sql = "SELECT user_id FROM enumCustomer UNION ALL SELECT user_id FROM enumSupplier";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        // Fetch all user ids into an array
        $userIds = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    else {
            echo "Error: " . $sql . "<br>" . mysqli_error($connection);
    }
    
    $tableName = ($table == 'customers') ? 'enumCustomer' : 'enumSupplier';
    $fk = ($table == 'customers') ? 'customerID' : 'supplierID';
    $user_id = generateRandomInt($userIds);
    echo "User ID: $user_id\n";

    $stmt = $conn->prepare("INSERT INTO $database.$tableName (user_id, $fk) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $id);
    $stmt->execute();
    $stmt->close();
    insertUserData($conn, $user_id, $tableName, $table);
}

function insertOrderData ($conn, $supplierID, $quantity, $startDate, $endDate) {
    $orderIDs = array();
    for ($i = 1; $i <= $quantity; $i++) {
        $orderID = generateRandomInt($orderIDs);
        if ($orderID === NULL) {
            echo "Failed to generate unique order ID after 10000 attempts.\n";
            exit;
        }
        else {
            array_push($orderIDs, $orderID);
        }
        $orderDate = generateRandomDate($startDate, $endDate);
        $deliveryDate = generateRandomDate($startDate, $endDate);
        if ($deliveryDate < $orderDate) {
            $temp = $orderDate;
            $orderDate = $deliveryDate;
            $deliveryDate = $temp;
        }
        $orderCost = generateRandomDecimal();
        $stmt = $conn->prepare("INSERT INTO `order` (orderID, orderDate, deliveryDate, orderCost, supplierID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdi", $orderID, $orderDate, $deliveryDate, $orderCost, $supplierID);

        // Execute the statement
        if ($stmt->execute()) {
            echo "New record created successfully for orderID: $orderID\n\n";
        } else {
            echo "Error: " . $stmt->error . "\n\n";
        }

        // Close the statement
        $stmt->close();
    }
    insertOrderDetailData($conn, $bindParams[1], $startDate, $endDate);
}
/*
insertOrderDetailData($conn, $orderIDs, $startDate, $endDate) {
    $orderDetailIDs = array();
    for ($i = 1; $i <= rand(1,10); $i++) {
        $productID = getRandomID($conn, 'product', 'productID');
        $inventoryDetailID = getRandomID($conn, 'inventoryDetail', 'inventoryDetailID');
        $orderDetailIDs = generateRandomInt($orderDetailIDs);
        array_push($orderDetailIDs, $orderDetailID);
        $quantity = generateRandomInt();
        $stmt = $conn->prepare("INSERT INTO orderDetail (orderDetailID, orderID, productID, inventoryDetailID, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $orderDetailID, $orderID, $productID, $inventoryDetailID, $quantity);
        
        if ($stmt->execute()) {
            echo "New record created successfully for orderDetailID: $orderDetailID\n\n";
        } else {
            echo "Error: " . $stmt->error . "\n\n";
        }
        $stmt->close();
    }    
}*/

function getEarliestDate($conn, $id, $table) {
    if ($table == 'supplier') {
        $sql = "SELECT MIN(orderDate) AS earliestOrderDate
        FROM `order`
        WHERE supplierID = $id;
        ";
    }
    else {
        $sql = "SELECT MIN(`date`) AS earliestOrderDate
        FROM purchase
        WHERE customerID = $id;
        ";
    }
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Earliest Order Date: " . $row['earliestOrderDate'];
        return $row['earliestOrderDate'];
    }
    return NULL;
}

function insertUserData($conn, $userID, $enumType, $table ) {
    $username = generateRandomString();
    $password = generateRandomInt();
    $user_type = $enumType;
    $startDate = getEarliestDate($conn, $userID, $table);
    $endDate = NULL;
    $stmt = $conn->prepare("INSERT INTO users (userID,start_Date, end_Date ,username, password, user_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $userID, $startDate, $endDate, $username, $password, $user_type);
    if ($stmt->execute()) {
        echo "New record created successfully for userID: $userID\n\n";
    } else {
        echo "Error: " . $stmt->error . "\n\n";
    }
    $stmt->close();
}


function insertPurchaseData($conn, $customerID, $quantity, $startDate, $endDate) {
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
        $locationID = getRandomID($conn, 'locations', 'locationID');
        $satisfactionRating = getRandomEnumValue($conn, 'purchase', 'satisfactionRating');
        $stmt = $conn->prepare("INSERT INTO purchase (purchaseID, date, customerID, locationID, satisfactionRating) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiis", $purchaseID, $purchaseDate, $customerID, $locationID, $satisfactionRating);

        // Execute the statement
        if ($stmt->execute()) {
            echo "New record created successfully for purchaseID: $purchaseID\n\n";
        } else {
            echo "Error: " . $stmt->error . "\n\n";
        }

        // Close the statement
        $stmt->close();
        insertPurchaseDetailData($conn, $database, $purchaseID, $locationID);
    }
}

function getRandomID($conn, $table, $column) {
    try {
        // Prepare the SQL statement to fetch a random locationID from the locations table
        $sql = "SELECT $column FROM $table ORDER BY RAND() LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        // Execute the statement
        $stmt->execute();
        
        // Get the result set from the statement
        $result = $stmt->get_result();
        
        // Fetch the row that contains the random locationID
        if ($row = $result->fetch_assoc()) {
            // Return the random locationID
            return $row[$column];
        } else {
            // If no rows were found, throw an exception
            throw new Exception("No locations found.");
        }
    } catch (Exception $e) {
        // Handle any errors, such as table not found or database connection issues
        echo "An error occurred: " . $e->getMessage();
        return null; // Or handle this case as needed
    }
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
insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'customers'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'employees'); 
#insertBaseTableData(3, $startDate, $endDate, $conn, $database, 'locations'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'order'); 
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'product'); 
insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'supplier');
#insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'users'); 
/*$table = `customers`;
#getEarliestDate($conn, $customerID, $table);
$userID = 436;
$enumType = 'customer';
insertUserData($conn, $userID, $enumType, $table);
*/
mysqli_close($conn);
?>
