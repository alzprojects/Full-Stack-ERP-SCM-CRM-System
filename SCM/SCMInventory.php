<?php
// Force HTTPS redirection if not already using HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search_terms"])) {
    function sanitize_input($input) {
        return htmlspecialchars(stripslashes(trim($input)));
    }

    // Validate and sanitize search terms (product IDs)
    $search_terms = isset($_POST["search_terms"]) ? $_POST["search_terms"] : "";
    $search_terms = sanitize_input($search_terms);

    // Validate search terms (comma-separated product IDs or "0")
    if ($search_terms !== "") {
        $product_ids = explode(",", $search_terms);
        foreach ($product_ids as $id) {
            // Check if each product ID is a non-negative integer or "0"
            if (!ctype_digit($id) || intval($id) < 0) {
                // Invalid input format, return error response
                $error_response = array(
                    'error' => 'Invalid input format. Please enter comma-separated non-negative integers for product IDs or "0" to view all orders.'
                );
                $json = json_encode($error_response);
                header('Content-Type: application/json');
                echo $json;
                exit;
            }
        }
    }

    // Database credentials
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    // Initialize variable to hold product data
    $inventory_data = array();

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        $error_response = array(
            'error' => 'Connection failed: ' . $conn->connect_error
        );
        // Encode the error response array as JSON
        $json = json_encode($error_response);
        // Output JSON and exit
        header('Content-Type: application/json');
        echo $json;
        exit;
    }

    // Query to fetch inventory data, including product name and location name
    if ($search_terms == "0" || $search_terms == "") {
        $sql = "SELECT p.productID, p.name AS productName, l.locationID, l.name AS locationName, 
                   id.quantity AS inventoryQuantity, 
                   SUM(pd.quantity) AS purchaseQuantity
            FROM inventoryDetail id
            INNER JOIN locations l ON id.locationID = l.locationID
            INNER JOIN product p ON id.productID = p.productID
            LEFT JOIN purchaseDetail pd ON id.inventoryDetailID = pd.inventoryDetailID
            GROUP BY p.productID, l.locationID
            ORDER BY p.productID, l.name";
    } else {
        // If user enters specific product IDs, retrieve inventory data for those products
        // Construct the WHERE clause to filter by product IDs
        $where_clause = implode(',', array_map('intval', $product_ids));

        $sql = "SELECT p.productID, p.name AS productName, l.locationID, l.name AS locationName, 
                   id.quantity AS inventoryQuantity, 
                   SUM(pd.quantity) AS purchaseQuantity
            FROM inventoryDetail id
            INNER JOIN locations l ON id.locationID = l.locationID
            INNER JOIN product p ON id.productID = p.productID
            LEFT JOIN purchaseDetail pd ON id.inventoryDetailID = pd.inventoryDetailID
            WHERE p.productID IN ($where_clause)
            GROUP BY p.productID, l.locationID
            ORDER BY p.productID, l.name";
    }
    $result = $conn->query($sql);

    if (!$result) {
        $error_response = array(
            'error' => 'Query failed: ' . $conn->error
        );
        // Encode the error response array as JSON
        $json = json_encode($error_response);
        // Output JSON and exit
        header('Content-Type: application/json');
        echo $json;
        exit;
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Inventory information including product and location details
            $inventory_data[] = array(
                'productID' => $row['productID'],
                'productName' => $row['productName'],
                'locationID' => $row['locationID'],
                'locationName' => $row['locationName'],
                'inventoryQuantity' => $row['inventoryQuantity'],
                'purchaseQuantity' => $row['purchaseQuantity']
            );
        }
    }

    // Close connection
    $conn->close();

    // Encode the data array as JSON
    $json = json_encode($inventory_data);

    // Output JSON
    header('Content-Type: application/json');
    echo $json;

    // Prevent any additional output
    exit;
}
?>