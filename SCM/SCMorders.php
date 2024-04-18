<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search_terms"])) {
    function sanitize_input($input) {
        return htmlspecialchars(stripslashes(trim($input)));
    }

    // Validate and sanitize search terms (product IDs)
    $search_terms = isset($_POST["search_terms"]) ? $_POST["search_terms"] : "";
    $search_terms = sanitize_input($search_terms);

    // Database credentials
    $servername = "mydb.itap.purdue.edu";
    $username = "azimbali";
    $password = "Max!024902!!";
    $database = "azimbali";

    // Initialize variable to hold product data
    $order_data = array();

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

    // Get and sanitize search terms (product IDs)
    $search_terms = sanitize_input($_POST["search_terms"]);

    // Check if the search term is "0" to retrieve all order IDs
    if ($search_terms == "0") {
        // Get and sanitize start date
        $start_date = isset($_POST["start_date"]) ? $_POST["start_date"] : "";
        $start_date = sanitize_input($start_date);

        // Get and sanitize end date
        $end_date = isset($_POST["end_date"]) ? $_POST["end_date"] : "";
        $end_date = sanitize_input($end_date);

        // Query to fetch all product information within the specified date range, ordered by order date
        $sql = "SELECT DISTINCT o.orderID, o.orderDate, o.deliveryDate, o.orderCost, o.supplierID, SUM(od.quantity) AS totalQuantity
                FROM `order` o
                LEFT JOIN orderDetail od ON o.orderID = od.orderID
                WHERE o.orderDate BETWEEN '$start_date' AND '$end_date'
                GROUP BY o.orderID
                ORDER BY o.orderDate";
    } else {
        // Validate search terms (comma-separated product IDs)
        if ($search_terms !== "") {
            $product_ids = explode(",", $search_terms);
            foreach ($product_ids as $id) {
                // Check if each product ID is a non-negative integer
                if (!ctype_digit($id) || intval($id) < 0) {
                    // Invalid input format, return error response
                    $error_response = array(
                        'error' => 'Invalid input format. Please enter comma-separated non-negative integers for order IDs or "0" to view all.'
                    );
                    $json = json_encode($error_response);
                    header('Content-Type: application/json');
                    echo $json;
                    exit;
                }
            }
        }

        // Construct query to get the date range for the specified order IDs
        $sql_date_range = "SELECT MIN(orderDate) AS minDate, MAX(orderDate) AS maxDate
                           FROM `order`
                           WHERE orderID IN ($search_terms)";
        $result_date_range = $conn->query($sql_date_range);
        $row_date_range = $result_date_range->fetch_assoc();
        $start_date = $row_date_range['minDate'];
        $end_date = $row_date_range['maxDate'];

        // Query to fetch product information within the date range of the specified order IDs, ordered by order date
        $sql = "SELECT DISTINCT o.orderID, o.orderDate, o.deliveryDate, o.orderCost, o.supplierID, SUM(od.quantity) AS totalQuantity
                FROM `order` o
                LEFT JOIN orderDetail od ON o.orderID = od.orderID
                WHERE o.orderDate BETWEEN '$start_date' AND '$end_date'
                AND o.orderID IN ($search_terms)
                GROUP BY o.orderID
                ORDER BY o.orderDate";
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
            // Order information including total quantity
            $order_data[] = array(
                'order_info' => array(
                    'orderID' => $row['orderID'],
                    'orderDate' => $row['orderDate'], 
                    'deliveryDate' => $row['deliveryDate'], 
                    'orderCost' => $row['orderCost'],
                    'supplierID' => $row['supplierID'],
                    'totalQuantity' => $row['totalQuantity'] // Include total quantity in the response
                )
            );
        }
    }

    // Close connection
    $conn->close();

    // Encode the data array as JSON
    $json = json_encode($order_data);

    // Output JSON
    header('Content-Type: application/json');
    echo $json;

    // Prevent any additional output
    exit;
}
?>
