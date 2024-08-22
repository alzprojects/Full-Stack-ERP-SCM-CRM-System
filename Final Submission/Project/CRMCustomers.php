<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['userID']) && $_POST['action'] == 'fetch_user_access') {
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $userID = intval($_POST['userID']);
    $stmt = $conn->prepare("SELECT * FROM employees WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No user found']);
    }
    $stmt->close();
    $conn->close();
    exit;
}
// Check if this is an AJAX request for user data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_customers') {
    // Database connection settings
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    function getAllCustomerIDs($conn) {
        $sql = "SELECT customerID, gender, fname, lname FROM customers";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            header('Content-Type: application/json');
            echo json_encode(['error' => "Error executing query: " . mysqli_error($conn)]);
            exit;
        }
        $userDetails = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $userDetails[] = array(
                'customerID' => $row['customerID'],
                'fname' => $row['fname'],
                'lname' => $row['lname'],
                'gender' => $row['gender']
            );
        }
        return $userDetails;
    }

    // Call the function and return data
    $userDetails = getAllCustomerIDs($conn);
    header('Content-Type: application/json');
    echo json_encode($userDetails);
    $conn->close();
    exit;
}

// Check if this is an AJAX request for purchases
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_purchases' && isset($_POST['customerID'])) {
    $customerID = $_POST['customerID'];
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);


    function getPurchasesByCustomerID($conn, $customerID) {
        $stmt = $conn->prepare("SELECT * FROM purchase WHERE customerID = ?");
        $stmt->bind_param("i", $customerID); // 'i' denotes that customerID is an integer
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            header('Content-Type: application/json');
            echo json_encode(['error' => "Error executing query: " . mysqli_error($conn)]);
            exit;
        }
        $purchases = array();
        while ($row = $result->fetch_assoc()) {
            $purchases[] = $row;
        }
        $stmt->close();
        return $purchases;
    }

    $purchases = getPurchasesByCustomerID($conn, $customerID);
    header('Content-Type: application/json');
    echo json_encode($purchases); 
    $conn->close();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_purchasedetails' && isset($_POST['purchaseID'])) {
    $purchaseID = $_POST['purchaseID'];
    // Database connection settings
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    function getAllPurchaseDetailIDs($conn, $purchaseID) {
        $sql = "SELECT * FROM purchaseDetail WHERE purchaseID = $purchaseID";
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            header('Content-Type: application/json');
            echo json_encode(['error' => "Error executing query: " . mysqli_error($conn)]);
            exit;
        }
        $purchaseDetails = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $purchaseDetails[] = array(
                'purchaseDetailID' => $row['purchaseDetailID'],
                'quantity' => $row['quantity'],
                'purchaseID' => $row['purchaseID'],
                'productID' => $row['productID'],
                'inventoryDetailID' => $row['inventoryDetailID']
            );
        }
        return $purchaseDetails;
    }

    // Call the function and return data
    $purchaseDetails = getAllPurchaseDetailIDs($conn, $purchaseID);
    header('Content-Type: application/json');
    echo json_encode($purchaseDetails);
    $conn->close();
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="SCM_Style.css">
    <title>Micromanagement Central Yeehaw</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div id ="smallContainer">
            <div id="leftContainer">
                <h3>Data</h3>
                Please enter a customer ID, and then choose if you would like to see
                the customer data or the purchase data for that customer.
                <br></br>
                <input type="number" id="textInput" placeholder="Enter CustomerID Here">
                <button id="loadDataBtn">Load Cust Data</button> 
                <button id="loadPurchaseBtn">Load Purchase Data by CustID</button>
                <br></br>
                Please enter a purchase ID to see the purchase detail data for that purchase.
                <br></br>
                <input type="number" id="purchaseID" placeholder="Enter PurchaseID Here">
                <button id="loadPurchaseDetailBtn">Load PurchaseDetail Data by PurchaseID</button>
                <br></br>
                <br></br>
                <div id="dataDisplay"></div>
            </div>
            <div id="rightContainer">
                <h3>Plots & Figures</h3>
                The following functionalities are to see summary statistics or remove plots.
                <button id="showSummaryStats">Show Plots</button>
                <button id="removePlots">Remove Plots</button>
                <canvas id="myChart1" width="100" height="100"></canvas>
                <canvas id="myChart2" width="100" height="100"></canvas>
                <canvas id="myChart3" width="100" height="100"></canvas>
            </div>
        </div>
    </div>
<script>
    let fileName = 'CRMCustomers.php';
    let allUserData = [];  // This will store all the user data
    let allPurchaseData = [];  // This will store all the purchase data
    let allPurchaseDetailData = [];  // This will store all the purchase detail data
    charts = {
        myChart1: null,
        myChart2: null,
        myChart3: null
    };
    document.getElementById('loadDataBtn').addEventListener('click', function() {
        if (allUserData.length === 0) {  // Fetch only if data has not been loaded
            fetchCustomerData();
        } else {
            displayData(allUserData);  // Display all data if already loaded
        }
    });
    document.getElementById('loadPurchaseBtn').addEventListener('click', function() {
        fetchPurchaseData();
    });
    document.getElementById('loadPurchaseDetailBtn').addEventListener('click', function() {
        fetchPurchaseDetailData();
    });
    document.getElementById('showSummaryStats').addEventListener('click', function() {
        showSummaryStats();
    });
    document.getElementById('removePlots').addEventListener('click', function() {
        resetCanvas('myChart1');
        resetCanvas('myChart2');
        resetCanvas('myChart3');
    });

    function fetchUserAccess() {
        let userID = <?php echo isset($_SESSION['userID']) ? $_SESSION['userID'] : 0; ?>;
        console.log('Fetching user access for user ID:', userID);
        fetch('CRMCustomers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=fetch_user_access&userID=${userID}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data || data.error || data.userID != userID || data.CRMAccess != 1) {
                alert('You do not have access to view this page.');
                window.location.href = 'homepage.html';
            }
        })
        .catch(error => {
            console.error('Error fetching user data:', error);
            alert('Error fetching user data. You do not have access to view this page.');
            window.location.href = 'homepage.html';
        });
    }


    function fetchCustomerData() {
        fetch('CRMCustomers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_customers'
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

    function fetchPurchaseData() {
        fetch('CRMCustomers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_purchases&customerID=' + document.getElementById('textInput').value
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

    function resetCanvas(canvasId) {
        let canvas = document.getElementById(canvasId);
        if (canvas) {
            let ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Check if a chart instance exists and is a Chart.js instance before destroying
            if (window[canvasId] && typeof window[canvasId].destroy === 'function') {
                window[canvasId].destroy();
            }

            // Remove and recreate canvas element to completely reset it
            let newCanvas = document.createElement('canvas');
            newCanvas.id = canvasId;
            newCanvas.width = canvas.width;
            newCanvas.height = canvas.height;
            canvas.parentNode.replaceChild(newCanvas, canvas);

            // Reset the reference to ensure no residual linkage
            window[canvasId] = null;
        }
    }


    function getFirstAvailableCanvas() {
        const canvasIds = ['myChart1', 'myChart2', 'myChart3'];
        for (let id of canvasIds) {
            let canvas = document.getElementById(id);
            // Check if the canvas has a chart instance and if it is empty
            if (!window.charts || !window.charts[id] || window.charts[id].data.datasets.length === 0) {
                console.log(canvas.id);
                return canvas.id;
            }
        }
        return null;
    }

    function fetchPurchaseDetailData() {
        fetch('CRMCustomers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetch_purchasedetails&purchaseID=' + document.getElementById('purchaseID').value
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
        if (headers.includes('satisfactionRating')) {
            let satisfactionRatings = arr.map(item => item.satisfactionRating);
            let satisfactionCounts = satisfactionRatings.reduce((acc, rating) => {
                acc[rating] = (acc[rating] || 0) + 1;
                return acc;
            }, {});
            let satisfactionLabels = Object.keys(satisfactionCounts);
            let satisfactionData = Object.values(satisfactionCounts);
            let canvas = getFirstAvailableCanvas();
            let ctx = document.getElementById(canvas).getContext('2d');
            let myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: satisfactionLabels,
                    datasets: [{
                        label: 'Satisfaction Ratings',
                        data: satisfactionData,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        if (headers.includes('locationID')) {
            let locationIDs = arr.map(item => item.locationID);
            let locationCounts = locationIDs.reduce((acc, location) => {
                acc[location] = (acc[location] || 0) + 1;
                return acc;
            }, {});
            let locationLabels = Object.keys(locationCounts);
            let locationData = Object.values(locationCounts);
            let canvas = getFirstAvailableCanvas();
            let ctx = document.getElementById(canvas).getContext('2d');
            let myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: locationLabels,
                    datasets: [{
                        label: 'Location IDs',
                        data: locationData,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {                    
                    scales: {
                        y: {                            
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        if (headers.includes('quantity')) {
            // Create a histogram with total quantity on the y-axis and productID on the x-axis
            let productQuantities = arr.reduce((acc, item) => {
                acc[item.productID] = (acc[item.productID] || 0) + item.quantity;
                return acc;
            }, {});
            let productLabels = Object.keys(productQuantities);
            let productData = Object.values(productQuantities);
            let canvas = getFirstAvailableCanvas();
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
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
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

fetchUserAccess();
</script>
</body>
</html>
