<?php
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
        $sql = "SELECT customerID, gender, name FROM customers";
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
                'name' => $row['name'],
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
    <link rel="stylesheet" href="styles2.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Fetch User Data</title>
</head>
<body>
    <button id="loadDataBtn">Load Cust Data</button> 
    <button id="loadPurchaseBtn">Load Purchase Data by CustID</button>
    <input type="number" id="textInput" placeholder="Enter CustomerID Here">
    <button id="loadPurchaseDetailBtn">Load PurchaseDetail Data by PurchaseID</button>
    <input type="number" id="purchaseID" placeholder="Enter PurchaseID Here">
    <button id="showSummaryStats">Show Summary Stats</button>
    <div id="dataDisplay"></div>
    <canvas id="myChart1" width="100" height="100"></canvas>
    <canvas id="myChart2" width="100" height="100"></canvas>

<script>
    let allUserData = [];  // This will store all the user data
    let allPurchaseData = [];  // This will store all the purchase data
    let allPurchaseDetailData = [];  // This will store all the purchase detail data
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

            let ctx = document.getElementById('myChart1').getContext('2d');
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

            let ctx = document.getElementById('myChart2').getContext('2d');
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
