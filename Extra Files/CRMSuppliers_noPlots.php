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

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }    
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
                'orderDetailID' => $row['orderDetailID'],
                'orderID' => $row['orderID'],
                'productID' => $row['productID'],
                'quantity' => $row['quantity'],
                'inventoryDetailID' => $row['inventoryDetailID']
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
    <link rel="stylesheet" href="SCM_Style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Micromanagement Central Yeehaw</title>
</head>
<body>
    <div class="container">
            <h2>Micromanagement Central Yeehaw</h2>
            <div class="navbar">
                <a href="homePage.html">Home</a>
                <a href="login.html">Login</a>
                <a href="CRMUsers.php">Users</a>
                <a href="CRMCustomers.php">Customers</a>
                <a href="CRMSuppliers.php">Suppliers</a> 
            </div>
        <div class ="smallContainer">
            <div id="leftContainer">
                Please enter a supplier ID, and then choose if you would like to see
                the supplier data or the purchase data for that customer.
                <br></br>    
                <input type="number" id="orderID" placeholder="Enter SupplierID Here">
                <button id="loadDataBtn">Load Supplier Data</button> 
                <button id="loadOrderBtn">Load Order Data by Supplier</button>
                <br></br>
                Please enter an order ID to see the order detail data for that purchase.
                <br></br>
                <input type="number" id="purchaseID" placeholder="Enter OrderID Here">
                <button id="loadOrderDetailBtn">Load OrderDetail Data by OrderID</button>
                <br></br>
                The following functionalities are to see summary statistics or remove plots.
                <br></br>
                <button id="showSummaryStats">Show Summary Stats</button>
                <button id="removePlots">Remove Plots</button>
                <div id="dataDisplay"></div>
            </div>
            <div id ="rightContainer">
                <canvas id="myChart1" width="100" height="100"></canvas>
                <canvas id="myChart2" width="100" height="100"></canvas>
                <canvas id="myChart3" width="100" height="100"></canvas>
            </div>
        </div>
    </div>
<script>
    let fileName = 'CRMSuppliers_noPlots.php';
    let allUserData = [];  // This will store all the user data
    let allPurchaseData = [];  // This will store all the purchase data
    let allPurchaseDetailData = [];  // This will store all the purchase detail data
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
        resetCanvas('myChart1');
        resetCanvas('myChart2');
        resetCanvas('myChart3');
    });



    function fetchSupplierData() {
        fetch(fileName, {
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


    function resetCanvas(canvasId) {
        let canvas = document.getElementById(canvasId);
        if (canvas) {
            let ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Remove and recreate canvas element to completely reset it
            let newCanvas = document.createElement('canvas');
            newCanvas.id = canvasId;
            newCanvas.width = canvas.width;
            newCanvas.height = canvas.height;
            canvas.parentNode.replaceChild(newCanvas, canvas);
        }
    }


    function fetchOrderData() {
        fetch(fileName, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_orders&supplierID=' + document.getElementById('orderID').value
        })
        .then(response => response.json())
        .then(data => {
            allPurchaseData = data;  // Store fetched data
            console.log(allPurchaseData);
            displayData(allPurchaseData);  // Display all data
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('dataDisplay').innerHTML = '<strong>Failed to load data. Please try again.</strong>';
        });
    }

    function fetchOrderDetailData() {
        fetch(fileName, {
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
            let canvas = getFirstAvailableCanvas();
            let productLabels = Object.keys(productQuantities);
            let productData = Object.values(productQuantities);
            let ctx = document.getElementById(canvas).getContext('2d');
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
            let canvas = getFirstAvailableCanvas();
            let ctx = document.getElementById(canvas).getContext('2d');
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
   
    function getFirstAvailableCanvas() {
        const canvasIds = ['myChart1', 'myChart2', 'myChart3'];
        for (let id of canvasIds) {
            let canvas = document.getElementById(id);
            if (!window.charts || !window.charts[id] || (window.charts[id] && window.charts[id].data.datasets.length === 0)) {
                console.log(canvas.id);
                return canvas.id;
            }
        }
        return null;
    }

    function displayData(data) {
        resetCanvas('myChart1');
        resetCanvas('myChart2');
        resetCanvas('myChart3');
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
