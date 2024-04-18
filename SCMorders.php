<?php
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
                    'error' => 'Invalid input format. Please enter comma-separated non-negative integers for order IDs or "0" to view all.'
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
        // Query to fetch all product information, including total quantity per order (replace null quantities with 1)
        $sql = "SELECT DISTINCT o.orderID, o.orderDate, o.deliveryDate, o.orderCost, o.supplierID, SUM(od.quantity) AS totalQuantity
                FROM `order` o
                LEFT JOIN orderDetail od ON o.orderID = od.orderID
                GROUP BY o.orderID";
    } else {
        // Get and sanitize search terms (order IDs)
        $order_ids = explode(",", $search_terms);
    
        // Initialize the WHERE clause
        $where_clause = "";
    
        // Loop through each order ID
        foreach ($order_ids as $order_id) {
            $where_clause .= "o.orderID = $order_id OR ";
        }
    
        // Remove the trailing "OR"
        $where_clause = rtrim($where_clause, "OR ");
    
        // Query to fetch product information for specific order IDs, including total quantity per order (replace null quantities with 1)
        $sql = "SELECT DISTINCT o.orderID, o.orderDate, o.deliveryDate, o.orderCost, o.supplierID, COALESCE(SUM(od.quantity), 1) AS totalQuantity
                FROM `order` o
                LEFT JOIN orderDetail od ON o.orderID = od.orderID
                WHERE $where_clause
                GROUP BY o.orderID";
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Search</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #container {
            max-width: 800px; /* Adjust as needed */
            margin: 0 auto; /* Center the container horizontally */
            padding: 20px; /* Add some padding for spacing */
            border: 1px solid #ccc; /* Add a border for visual separation */
        }

        canvas {
            display: block;
            width: 400px; /* Set a fixed width for the canvas */
            height: 400px; /* Set a fixed height for the canvas */
            margin-top: 20px; /* Add some margin to separate it from the form */
        }

        #dataTableContainer {
            max-height: 400px; /* Set maximum height for the container */
            overflow-y: scroll; /* Enable vertical scrolling */
            margin-top: 20px; /* Add margin to separate it from the form */
        }

        #dataTable {
            width: 100%; /* Ensure the table takes up the full width */
            border-collapse: collapse; /* Collapse borders to prevent gaps between cells */
        }

        #dataTable th, #dataTable td {
            border: 1px solid #ddd; /* Add borders to table cells */
            padding: 8px; /* Add padding to table cells */
            text-align: center; /* Center-align text in table cells */
        }

        #dataTable th {
            background-color: #f2f2f2; /* Add background color to table header cells */
        }
    </style>
</head>
<body>
    <div id="container">
        <h2>Order Search</h2>
        <form id="searchForm">
            <label for="search_terms">Enter Order IDs (comma-separated):<br>Enter "0" to view all orders.</label><br>
            <input type="text" id="search_terms" name="search_terms"><br>
            <input type="submit" value="Search">
        </form>

        <div id="dataTableContainer">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Order Cost</th>
                        <th>Supplier ID</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically populated here -->
                </tbody>
            </table>
        </div>

        <canvas id="supplier"></canvas>
        <canvas id="ordersByDate"></canvas>
        <canvas id="ordersByDayOfWeek"></canvas>
    </div>

    <script>
        const searchForm = document.getElementById('searchForm');
        let supplierChart, ordersByDateChart, ordersByDayOfWeekChart;

        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Prevent default form submission
            const searchTerms = document.getElementById('search_terms').value;

            // Validate search terms client-side
        if (!validateInput(searchTerms)) {
            // Invalid input, show alert to the user and stop form submission
            alert('Invalid input format. Please enter comma-separated non-negative integers for order IDs or "0".');
            return;
        }

            const response = await fetch('SCMorders.php', {
                method: 'POST',
                body: new URLSearchParams({
                    search_terms: searchTerms
                })
            });

            if (response.ok) {
                const data = await response.json();
                updateCharts(data); // Update all charts with data
            } else {
                console.error('Failed to fetch data');
            }
        });

        // Function to validate search terms
    function validateInput(searchTerms) {
        // Check if search terms are empty or contain valid comma-separated non-negative integers
        return searchTerms === "" || /^(\d+,)*\d+$/.test(searchTerms);
    }

        function updateTable(data) {
            const tableBody = document.querySelector('#dataTable tbody');
            tableBody.innerHTML = ''; // Clear existing table body

            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.order_info.orderID}</td>
                    <td>${item.order_info.orderDate}</td>
                    <td>${item.order_info.deliveryDate}</td>
                    <td>${item.order_info.orderCost}</td>
                    <td>${item.order_info.supplierID}</td>
                `;
                tableBody.appendChild(row); // Append row to table body
            });
        }

        function countOrdersPerSupplier(data) {
            const orderCounts = {};
            data.forEach(item => {
                const supplierID = item.order_info.supplierID;
                if (orderCounts[supplierID]) {
                    orderCounts[supplierID]++;
                } else {
                    orderCounts[supplierID] = 1;
                }
            });
            return orderCounts;
        }

        function countTotalQuantityPerOrder(data) {
    const totalQuantityPerOrder = {};

    // Calculate total quantity per order
    data.forEach(item => {
        const orderID = item.order_info.orderID;
        const totalQuantity = item.order_info.totalQuantity;
        totalQuantityPerOrder[orderID] = totalQuantity;
    });

    // Sort orders by total quantity
    const sortedOrders = Object.entries(totalQuantityPerOrder).sort((a, b) => b[1] - a[1]);

    // Take the top 15 orders
    const topOrders = sortedOrders.slice(0, 15);

    // Convert back to object
    const topTotalQuantityPerOrder = Object.fromEntries(topOrders);

    return topTotalQuantityPerOrder;
}

        function countOrdersByDayOfWeek(data) {
            const orderCounts = {
                Sunday: 0,
                Monday: 0,
                Tuesday: 0,
                Wednesday: 0,
                Thursday: 0,
                Friday: 0,
                Saturday: 0
            };

            data.forEach(item => {
                const deliveryDate = new Date(item.order_info.deliveryDate);
                const dayOfWeek = deliveryDate.toLocaleString('en', { weekday: 'long' });
                orderCounts[dayOfWeek]++;
            });

            return orderCounts;
        }

        function updateChart(chart, chartData) {
    // Destroy existing chart if it exists
    if (chart) {
        chart.destroy();
    }

    // Initialize new chart
    const ctx = document.getElementById(chartData.canvasId).getContext('2d');
    const newChart = new Chart(ctx, {
        type: chartData.type,
        data: chartData.data,
        options: chartData.options
    });

    return newChart; // Return the newly created chart instance
}

function updateCharts(data) {
    updateTable(data); // Update the table first

    const orderCountsBySupplier = countOrdersPerSupplier(data);
    const totalQuantityPerOrder = countTotalQuantityPerOrder(data);
    const orderCountsByDayOfWeek = countOrdersByDayOfWeek(data);

    const supplierChartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };

    const supplierChartData = {
        canvasId: 'supplier',
        type: 'bar',
        data: {
            labels: Object.keys(orderCountsBySupplier),
            datasets: [{
                label: 'Orders per Supplier',
                data: Object.values(orderCountsBySupplier),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: supplierChartOptions
    };

    const totalQuantityChartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                suggestedMax: Math.max(...Object.values(totalQuantityPerOrder)) + 1,
                beginAtZero: true
            }
        }
    };

    const totalQuantityChartData = {
        canvasId: 'ordersByDate',
        type: 'line',
        data: {
            labels: Object.keys(totalQuantityPerOrder),
            datasets: [{
                label: 'Total Quantity per Order',
                data: Object.values(totalQuantityPerOrder),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: totalQuantityChartOptions
    };

    const ordersByDayOfWeekChartOptions = {
        responsive: true,
        maintainAspectRatio: true
    };

    const ordersByDayOfWeekChartData = {
        canvasId: 'ordersByDayOfWeek',
        type: 'bar',
        data: {
            labels: Object.keys(orderCountsByDayOfWeek),
            datasets: [{
                label: 'Deliveries on Each Day of the Week',
                data: Object.values(orderCountsByDayOfWeek),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: ordersByDayOfWeekChartOptions
    };

    supplierChart = updateChart(supplierChart, supplierChartData);
    ordersByDateChart = updateChart(ordersByDateChart, totalQuantityChartData);
    ordersByDayOfWeekChart = updateChart(ordersByDayOfWeekChart, ordersByDayOfWeekChartData);

}
    </script>
</body>
</html>