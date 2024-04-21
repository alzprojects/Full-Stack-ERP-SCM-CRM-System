<?php
// Check if this is an AJAX request for user data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_suppliers') {
    // Database connection settings
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    function getAllSupplierIDs($conn) {
        $sql = "SELECT supplierID, name, address FROM supplier";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            header('Content-Type: application/json');
            echo json_encode(['error' => "Error executing query: " . mysqli_error($conn)]);
            exit;
        }
        $customerDetails = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $customerDetails[] = array(
                'supplierID' => $row['supplierID'],
                'name' => $row['name'],
                'address' => $row['address']
            );
        }
        return $customerDetails;
    }

    // Call the function and return data
    $customerDetails = getAllSupplierIDs($conn);
    header('Content-Type: application/json');
    echo json_encode($customerDetails);
    $conn->close();
    exit;
}

// Check if this is an AJAX request for purchases
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_orders' && isset($_POST['supplierID'])) {
    $supplierID = $_POST['supplierID'];
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);


    function getOrdersBySupplierID($conn, $supplierID) {
        $stmt = $conn->prepare("SELECT * FROM `order` WHERE supplierID = ?");
        $stmt->bind_param("i", $supplierID); // 'i' denotes that supplierID is an integer
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            header('Content-Type: application/json');
            echo json_encode(['error' => "Error executing query: " . mysqli_error($conn)]);
            exit;
        }
        $orders = array();
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
        return $orders;
    }

    $orders = getOrdersBySupplierID($conn, $supplierID);
    header('Content-Type: application/json');
    echo json_encode($orders); 
    $conn->close();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_orderDetails' && isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];
    // Database connection settings
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    function getAllOrderDetailIDs($conn, $orderID) {
        $sql = "SELECT * FROM orderDetail WHERE orderID = $orderID";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            header('Content-Type: application/json');
            echo json_encode(['error' => "Error executing query: " . mysqli_error($conn)]);
            exit;
        }
        $orderDetails = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $orderDetails[] = array(
                'orderID' => $row['orderID'],
                'productID' => $row['productID'],
                'quantity' => $row['quantity'],
                'inventoryDetailID' => $row['inventoryDetailID'],
                'orderDetailID' => $row['orderDetailID']
            );
        }
        return $orderDetails;
    }

    // Call the function and return data
    $orderDetails = getAllOrderDetailIDs($conn, $orderID);
    header('Content-Type: application/json');
    echo json_encode($orderDetails);
    $conn->close();
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        .layout-container {
            display: flex;
            flex-wrap: nowrap;
            height: 100vh; /* Full viewport height */
        }

        .data-scrollbox {
            width: 50%;
            overflow-y: auto;
            height: calc(100% - 50px); /* Less the height of the top bar */
            border-right: 1px solid #ccc; /* A separator between data and graphs */
        }

        .graphs-container {
            width: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto; /* Allows scrolling if there are many graphs */
        }

        .graph-canvas {
            width: 100%;
            max-width: 600px; /* Adjust this to fit your needs */
            height: auto;
            margin-bottom: 20px; /* Space between graphs */
        }

        .top-bar {
            width: 100%;
            height: 50px; /* Adjust based on your actual content */
            display: flex;
            align-items: center;
            justify-content: space-around;
            border-bottom: 1px solid #ccc;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Fetch User Data</title>
</head>
<body>
    <div class="top-bar">
        <button id="loadDataBtn">Load Supplier Data</button> 
        <button id="loadOrderBtn">Load Order Data by Supplier</button>
        <input type="number" id="orderID" placeholder="Enter SupplierID Here">
        <button id="loadOrderDetailBtn">Load OrderDetail Data by OrderID</button>
        <input type="number" id="purchaseID" placeholder="Enter OrderID Here">
        <button id="showSummaryStats">Show Summary Stats</button>
        <button id="removePlots">Remove Plots</button>
    </div>
    <div class="layout-container">
        <div class="data-scrollbox">
            <div id="dataDisplay"></div>
        </div>
        <div class="graphs-container">
            <canvas id="myChart1" width="100" height="100"></canvas>
            <canvas id="myChart2" width="100" height="100"></canvas>
            <canvas id="myChart3" width="100" height="100"></canvas>
        </div>
    </div>
<script>    
    let allUserData = [];  // This will store all the user data
    let allPurchaseData = [];  // This will store all the purchase data
    let allPurchaseDetailData = [];  // This will store all the purchase detail data
    let myChart1 = null; // Reference for a chart that might go into myChart1 canvas
    let myChart2 = null; // Reference for the line chart
    let myChart3 = null; // Reference for the bar chart
    document.getElementById('loadDataBtn').addEventListener('click', function() {
        if (allUserData.length === 0) {  // Fetch only if data has not been loaded
            fetchSupplierData();
        } else {
            displayData(allUserData);  // Display all data if already loaded
        }
    });
    document.getElementById('loadOrderBtn').addEventListener('click', function() {
        fetchOrderData();
    });
    document.getElementById('loadOrderDetailBtn').addEventListener('click', function() {
        fetchOrderDetailData();
    });
    document.getElementById('showSummaryStats').addEventListener('click', function() {
        showSummaryStats();
    });
    document.getElementById('removePlots').addEventListener('click', function() {
        removePlots();
    });



    function fetchSupplierData() {
        fetch('CRMSuppliers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_suppliers'
        })
        .then(response => response.json())
        .then(data => {
            allUserData = data;  // Store fetched data
            displayData(allUserData);  // Display all data
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('dataDisplay').innerHTML = '<strong>Failed to load data. Please try again.</strong>';
        });
    }


    function removePlots() {
        if (myChart1) {
            myChart1.destroy(); // Destroy the chart instance
            myChart1 = null; // Clear the reference
        }
        if (myChart2) {
            myChart2.destroy(); // Destroy the chart instance
            myChart2 = null; // Clear the reference
        }
        if (myChart3) {
            myChart3.destroy();
            myChart3 = null;
        }
    }


    function fetchOrderData() {
        fetch('CRMSuppliers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_orders&supplierID=' + document.getElementById('orderID').value
        })
        .then(response => response.json())
        .then(data => {
            allPurchaseData = data;  // Store fetched data
            displayData(allPurchaseData);  // Display all data
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('dataDisplay').innerHTML = '<strong>Failed to load data. Please try again.</strong>';
        });
    }

    function fetchOrderDetailData() {
        fetch('CRMSuppliers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_orderDetails&orderID=' + document.getElementById('purchaseID').value
        })
        .then(response => response.json())
        .then(data => {
            allPurchaseDetailData = data;  // Store fetched data
            displayData(allPurchaseDetailData);  // Display all data
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('dataDisplay').innerHTML = '<strong>Failed to load data. Please try again.</strong>';
        });
    }

    function showSummaryStats() {
        let arr = extractDataFromTable();
        let headers = Object.keys(arr[0]);
        if (headers.includes('quantity')) {
            console.log(arr);
            let productQuantities = arr.reduce((acc, item) => {
                // Ensure item.quantity is treated as a number
                let quantity = Number(item.quantity);
                if (!isNaN(quantity)) {
                    acc[item.productID] = (acc[item.productID] || 0) + quantity;
                }
                return acc;
            }, {});
            let productLabels = Object.keys(productQuantities);
            let productData = Object.values(productQuantities);
            if (myChart3) {
                myChart3.destroy(); // Destroy the chart instance
            }
            let ctx = document.getElementById('myChart3').getContext('2d');
            let myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: productLabels,
                    datasets: [{
                        label: 'Total Quantity per Product ID',
                        data: productData,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 50
                            }
                        }
                    }
                }
            });
        }
        if (headers.includes('orderCost')) {
            let orderCosts = arr.reduce((acc, item) => {
                let month = item.deliveryDate.split('-')[1];
                acc[month] = (acc[month] || 0) + Number(item.orderCost); // Using Number() to ensure it's treated as a number
                return acc;
            }, {});
            // Sort the months numerically (as strings, this works as expected for month numbers)
            let monthLabels = Object.keys(orderCosts).sort((a, b) => a.localeCompare(b));
            let monthData = monthLabels.map(month => orderCosts[month]);
            console.log(monthLabels, monthData);
            let ctx = document.getElementById('myChart2').getContext('2d');
            let myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Total Order Cost per Month',
                        data: monthData,
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
   

    function displayData(data) {
        const display = document.getElementById('dataDisplay');
        display.innerHTML = '';  // Clear previous data
        const table = document.createElement('table');
        table.style.width = '100%';
        table.setAttribute('border', '1');
        const headerRow = table.insertRow();
        let headers = data.length > 0 ? Object.keys(data[0]) : [];
        // Create header cells
        if (headers.length === 0) {
            display.innerHTML = '<strong>Customer does not exist</strong>';
            return;
        }
        headers.forEach(headerText => {
            let headerCell = document.createElement('th');
            headerCell.textContent = headerText;
            headerRow.appendChild(headerCell);
        });

        // Populate table rows with data
        data.forEach(item => {
            const row = table.insertRow();
            headers.forEach(header => {
                let cell = row.insertCell();
                cell.textContent = item[header];
            });
        });

        // Append the table to the display element
        display.appendChild(table);
    }
    
    function extractDataFromTable() {
        const table = document.querySelector('#dataDisplay table');  // Assuming there's only one table inside #dataDisplay
        if (!table) {
            console.log("No table found.");
            return [];  // Return an empty array if no table is found
        }

        const rows = Array.from(table.rows);
        if (rows.length < 2) {
            console.log("Not enough data to extract.");
            return [];  // Need at least two rows to have headers and data
        }

        const headers = rows.shift().cells;  // The first row is headers
        const headerNames = Array.from(headers).map(header => header.textContent);

        const data = rows.map(row => {
            const cells = Array.from(row.cells);
            let item = {};
            cells.forEach((cell, index) => {
                item[headerNames[index]] = cell.textContent;
            });
            return item;
        });

        return data;
}

</script>
</body>
</html>
