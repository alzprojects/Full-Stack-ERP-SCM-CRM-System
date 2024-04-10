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

    return date('Y-m-d', $randomTimestamp);
}

// Function to generate random decimal for prices
function generateRandomDecimal() {
    $float = rand(100, 10000) / 100;
    return $float;
}


function insertPurchaseDetailData($conn, $database, $purchaseID, $locationID){
    $usedProducts = array();
    $usedIDs = array();
    $sql = "SELECT purchaseDetailID FROM purchaseDetail";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($usedIDs, $row['purchaseDetailID']);
        }
    }
    for ($i = 1; $i <= rand(1,15); $i++) {
        $productID = getRandomProductId($conn, $usedProducts, $database);
        array_push($usedProducts, $productID);
        $purchaseDetailID = generateRandomInt($usedIDs);
        array_push($usedIDs, $purchaseDetailID);
        $quantity = rand(1,7) * rand(1,3);
        $stmt = $conn->prepare("SELECT inventoryDetailID FROM $database.inventoryDetail WHERE locationID = ? AND productID = ?");
        $stmt->bind_param("ii", $locationID, $productID);
        $stmt->execute();
        $stmt->bind_result($inventoryDetailID);
        $stmt->fetch();
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO $database.purchaseDetail (purchaseDetailID, quantity, productID, purchaseID, inventoryDetailID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $purchaseDetailID, $quantity, $productID, $purchaseID, $inventoryDetailID);
        $stmt->execute();
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
    if ($table === 'order') {
        $table = '`order`'; 
    }
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
                case 'tinyint': // Added case for tinyint
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
                    $value = rand(0,1) == 1;
                    $bindTypes .= 'i'; // Though booleans are treated as integers, ensure correctness in the specific database context
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
        $bindParams[] = &$bindTypes;
        foreach ($values as $key => $value) {
            $bindParams[] = &$values[$key]; // Bind each value by reference
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
                insertPurchaseData($conn, $bindParams[1], rand(1,10), $startDate, $endDate, $database);
            }
            if ($table == 'supplier') {
                insertOrderData($conn, $bindParams[1], rand(1,10), $startDate, $endDate);
            }
            if($table == 'locations')
            {
                insertInventoryDetailData($conn, $bindParams[1], $database);
            }
        }
    }        
    $stmt->close();
}


function insertInventoryDetailData($conn, $locationID, $database) {
    $products = array();
    $stmt = $conn->prepare("SELECT productID FROM $database.product");
    $stmt->execute();
    $stmt->bind_result($productID);
    while ($stmt->fetch()) { 
        $products[] = $productID; 
    }
    $stmt->close();
    echo "lengthofarray: " . count($products) . "\n";
    $inventoryDetailIDs = array(); 
    for ($i = 0; $i < count($products); $i++) {
        $quantity = generateRandomInt() * rand(1,5);
        $inventoryDetailID = generateRandomInt($inventoryDetailIDs);
        array_push($inventoryDetailIDs, $inventoryDetailID);
        $stmt = $conn->prepare("INSERT INTO $database.inventoryDetail (inventoryDetailID, quantity, locationID, productID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $inventoryDetailID, $quantity, $locationID, $products[$i]);
        $stmt->execute();
        $stmt->close();
    }
}

function createEnumTables($table, $conn, $id, $database = 'azimbali') {
    $userIds = getAllUserIDs($conn);
    $tableName = ($table == 'customers') ? 'enumCustomer' : 'enumSupplier';
    $fk = ($table == 'supplier') ? 'supplierID' : 'customerID';
    if ($table == 'customers') {
        $table = 'customer';
    }
    $user_id = generateRandomInt($userIds);
    echo "fk: $fk\n";
    echo "tableName: $tableName\n";
    $stmt = $conn->prepare("INSERT INTO $database.$tableName (user_id, $fk) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $id);
    $stmt->execute();
    $stmt->close();
    insertUserData($conn, $user_id, $table, $tableName);
}

function insertOrderData($conn, $supplierID, $quantity, $startDate, $endDate) {
    $orderIDs = array();
    #fetch the orderIDs from the order table
    $sql = "SELECT orderID FROM `order`";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($orderIDs, $row['orderID']);
        }
    }
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
    insertOrderDetailData($conn, $orderID, $startDate, $endDate);
}

function insertOrderDetailData($conn, $orderID, $startDate, $endDate) {
    $orderDetailIDs = array();
    for ($i = 1; $i <= rand(1,10); $i++) {
        $productID = getRandomID($conn, 'product', 'productID');
        $inventoryDetailID = getRandomID($conn, 'inventoryDetail', 'inventoryDetailID');
        $orderDetailID = generateRandomInt($orderDetailIDs);
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
        return generateRandomDate($i, $j);
    }
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Earliest Order Date: " . $row['earliestOrderDate'];
        echo "id: $id\n\n";
        echo "table: $table\n\n";
        return $row['earliestOrderDate'];
    }
    return NULL;
}

function insertUserData($conn, $userID, $enumType, $table) {
    $username = generateRandomString();
    $password = generateRandomInt();
    $user_type = $enumType;
    if ($table == 'enumCustomer') {
        $sql = "SELECT customerID FROM enumCustomer WHERE user_id = $userID";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

        }
        $id = $row['customerID'];
    }
    else if ($table == 'enumSupplier') {
        $sql = "SELECT supplierID FROM enumSupplier WHERE user_id = $userID";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

        }
        $id = $row['supplierID'];
    }
    echo "table: $table\n\n";
    $startDate = getEarliestDate($conn, $id, $table);
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


function insertPurchaseData($conn, $customerID, $quantity, $startDate, $endDate, $database) {
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
function getAllUserIDs($conn) {
    $sql = "SELECT userID FROM users";    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }
    $userIDs = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $userIDs[] = $row['userID'];
    }
    return $userIDs;
}

function insertEmployeeTableData($conn, $database, $quantity, $startDate, $endDate) {
    $usedIDs = array();
    $userIDs = getAllUserIDs($conn);
    for($i=0; $i < $quantity; $i++) { 
        $userID = generateRandomInt($userIDs);
        insertUserData($conn, $userID, 'employee', 'employees');
        array_push($userIDs, $userID);
        $CRMaccess = rand(0,1) == 1;
        $SCMaccess = rand(0,1) == 1;
        $ERPaccess = rand(0,1) == 1;
        $locationID = getRandomID($conn, 'locations', 'locationID');
        $stmt = $conn->prepare("INSERT INTO $database.employees (userID, CRMaccess, SCMaccess, ERPaccess, locationID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $userID, $CRMaccess, $SCMaccess, $ERPaccess, $locationID);
        if ($stmt->execute()) {
            echo "New record created successfully for userID: $userID\n\n";
        } else {
            echo "Error: " . $stmt->error . "\n\n";
        }
        $stmt->close();
    }
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
insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'product'); 
insertBaseTableData(3, $startDate, $endDate, $conn, $database, 'locations'); 
insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'supplier');
insertBaseTableData($quantity, $startDate, $endDate, $conn, $database, 'customers'); 
insertEmployeeTableData($conn, $database, $quantity, $startDate, $endDate);
#$table = `customers`;
#getEarliestDate($conn, $customerID, $table);
#$userID = 436;
#$enumType = 'customer';
#insertUserData($conn, $userID, $enumType, $table);
#$orderIDs = 63;
#insertOrderDetailData($conn, $orderIDs, $startDate, $endDate);


mysqli_close($conn);
?>
