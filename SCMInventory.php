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
    $username = "azimbali";
    $password = "Max!024902!!";
    $database = "azimbali";

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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Search</title>
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
        <h2>Inventory Search</h2>
        <form id="searchForm">
            <label for="search_terms">Enter Product IDs (comma-separated):<br>Enter "0" to view all products.</label><br>
            <input type="text" id="search_terms" name="search_terms"><br>
            <input type="submit" value="Search">
        </form>

        <div id="dataTableContainer">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Location ID</th>
                        <th>Location Name</th>
                        <th>Quantity on Hand</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically populated here -->
                </tbody>
            </table>
        </div>

        <canvas id="lowInventoryChart"></canvas>
        <canvas id="topPurchasedChart"></canvas>
    </div>

    <script>
    const searchForm = document.getElementById('searchForm');
    let lowInventoryChart, topPurchasedChart;

    searchForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission
        const searchTerms = document.getElementById('search_terms').value;

        // Validate search terms client-side
        if (!validateInput(searchTerms)) {
            // Invalid input, show alert to the user and stop form submission
            alert('Invalid input format. Please enter comma-separated non-negative integers for product IDs or "0" to view all products.');
            return;
        }

        const response = await fetch('SCMInventory.php', {
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
                <td>${item.productID}</td>
                <td>${item.productName}</td>
                <td>${item.locationID}</td>
                <td>${item.locationName}</td>
                <td>${item.inventoryQuantity}</td>
            `;
            tableBody.appendChild(row); // Append row to table body
        });
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

    function prepareLowInventoryChartData(data) {
    // Filter products with inventory below 10000
    const averageQuantity = data.reduce((acc, item) => acc + item.inventoryQuantity, 0) / data.length;
    const lowInventoryProducts = data.filter(item => item.inventoryQuantity < 10000);

    // Sort low inventory products by inventory quantity (ascending)
    lowInventoryProducts.sort((a, b) => a.inventoryQuantity - b.inventoryQuantity);

    return {
        canvasId: 'lowInventoryChart',
        type: 'bar',
        data: {
            labels: lowInventoryProducts.map(item => `${item.productID}:${item.locationID}`),
            datasets: [{
                label: 'Low Inventory Products',
                data: lowInventoryProducts.map(item => item.inventoryQuantity),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
}

    // Function to prepare data for the top purchased chart
    function prepareTopPurchasedChartData(data) {
        const aggregatedData = aggregateDataByProductID(data);
        const sortedAggregatedData = sortDataByPurchaseQuantity(aggregatedData);
        const topPurchasedProducts = sortedAggregatedData.slice(0, 10);

        return {
            canvasId: 'topPurchasedChart',
            type: 'bar',
            data: {
                labels: topPurchasedProducts.map(item => item.productID),
                datasets: [{
                    label: 'Top Purchased Products',
                    data: topPurchasedProducts.map(item => item.totalPurchaseQuantity),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };
    }

    // Aggregate data by product ID and sum the purchase quantities
    // Aggregate data by product ID and sum the purchase quantities
function aggregateDataByProductID(data) {
    const aggregatedData = {};
    data.forEach(item => {
        const productID = item.productID;
        if (aggregatedData[productID]) {
            aggregatedData[productID].totalPurchaseQuantity += parseInt(item.purchaseQuantity);
        } else {
            aggregatedData[productID] = {
                productID: productID,
                totalPurchaseQuantity: parseInt(item.purchaseQuantity)
            };
        }
    });
    return Object.values(aggregatedData);
}

// Sort data by total purchase quantity in descending order
function sortDataByPurchaseQuantity(data) {
    return data.sort((a, b) => b.totalPurchaseQuantity - a.totalPurchaseQuantity);
}


    // Function to update the charts
    function updateCharts(data) {
    // Update the table first
    updateTable(data);

    // Prepare data for the low inventory chart
    const lowInventoryChartData = prepareLowInventoryChartData(data);

    // Update the low inventory chart
    lowInventoryChart = updateChart(lowInventoryChart, lowInventoryChartData);

    // Prepare data for the top purchased chart
    const topPurchasedChartData = prepareTopPurchasedChartData(data);

    // Update the top purchased chart
    topPurchasedChart = updateChart(topPurchasedChart, topPurchasedChartData);
}
</script>
</body>
</html>