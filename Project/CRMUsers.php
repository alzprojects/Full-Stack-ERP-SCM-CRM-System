<?php
session_start();
// Check if this is an AJAX request for user data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch_users' && isset($_POST['locationID'])) {
    $locationID = $_POST['locationID']; 
    // Database connection settings
    $servername = "mydb.itap.purdue.edu";
    $username = "g1135081";
    $password = "4i1]4S*Mns83";
    $database = "g1135081";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Function to get all user IDs
    function getAllUserIDs($conn, $locationID) {
        $userDetails = array();
        if ($locationID == 0) {
            $sql = "SELECT userID, start_date, user_type FROM users";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $userDetails[] = array(
                        'userID' => $row['userID'],
                        'start_date' => $row['start_date'],
                        'user_type' => $row['user_type']
                    );
                }
            }
        } else {
            $sql = "(
                SELECT u.userID, u.start_date, u.user_type
                FROM users u
                WHERE u.user_type = 'supplier'
            )
            UNION
            (
                SELECT u.userID, u.start_date, u.user_type
                FROM users u
                INNER JOIN employees e ON u.userID = e.userID
                WHERE u.user_type = 'employee' AND e.locationID = ?
            )
            UNION
            (
                SELECT u.userID, u.start_date, u.user_type
                FROM users u
                JOIN enumCustomer ec ON u.userID = ec.userID
                JOIN purchase p ON ec.customerID = p.customerID
                WHERE p.locationID = ? AND u.user_type = 'customer'
            )";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                header('Content-Type: application/json');
                echo json_encode(['error' => "Error preparing query: " . mysqli_error($conn)]);
                exit;
            }
            $stmt->bind_param("ii", $locationID, $locationID);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result === false) {
                header('Content-Type: application/json');
                echo json_encode(['error' => "Error fetching data: " . mysqli_error($conn)]);
                exit;
            }
            while ($row = $result->fetch_assoc()) {
                $userDetails[] = array(
                    'userID' => $row['userID'],
                    'start_date' => $row['start_date'],
                    'user_type' => $row['user_type']
                );
            }
            $stmt->close();
        }
        return $userDetails;
    }
    // Fetch all user IDs
    $allUsers = getAllUserIDs($conn, $locationID);
    // Close the connection
    $conn->close();
    // Send the response as JSON
    header('Content-Type: application/json');
    echo json_encode($allUsers);
    exit;
}    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Micromanagement Central Yeehaw</title>
    <link rel="stylesheet" href="SCM_Style.css">
</head>

<body>
    <div class="container">
        <h2>Micromanagement Central Yeehaw</h2>
            <div class="navbar">
                <a href="homePage.html">Home</a>
                <a href="login.html">Login</a>
                <a href="CRMUsers.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>">Users</a>
                <a href="CRMCustomers.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>">Customers</a>
                <a href="CRMSuppliers.php?userID=<?php echo $_SESSION['userID']; ?>&locationID=<?php echo $_SESSION['locationID']; ?>">Suppliers</a>
            </div>
        <div id ="smallContainer">
        <div id ="leftContainer">
        Choose the load data button to pull in all relevant user data from database!
        <br></br>
        <button id="loadDataBtn">Load Data</button>
        <br></br>
        <div>
            Filter users by type (employee, customer, supplier)
            <br></br>
            <label for="userTypeSelect">Filter by User Type:</label>
            <select id="userTypeSelect" onchange="filterData()">
                <option value="">All Types</option>
                <option value="employee">Employee</option>
                <option value="customer">Customer</option>
                <option value="supplier">Supplier</option>
            </select>
        </div>
        <br></br>

        Choose a date range to filter users by start date (optional)
        <br></br>
        <div>
            <label for="startDateFrom">Start Date From:</label>
            <input type="date" id="startDateFrom" onchange="filterData()">
            <label for="startDateTo">to</label>
            <input type="date" id="startDateTo" onchange="filterData()">
        </div>
        <br></br>
        <div id="dataDisplay"></div>
        </div>
    </div>
    </div>
    <script>
    function isLoggedIn() {
        // Get the URL parameters
        const urlParams = new URLSearchParams(window.location.search);

        // Check if the userID parameter exists in the URL
        return urlParams.has('userID');
    }

    if (!isLoggedIn()) {
        // Redirect the user to the login page
        window.location.href = "login.php";
    }
    let allUserData = [];  // This will store all the user data
    document.getElementById('loadDataBtn').addEventListener('click', function() {
        if (allUserData.length === 0) {  // Fetch only if data has not been loaded
            fetchData();
        } else {
            displayData(allUserData);  // Display all data if already loaded
        }
    });
    document.getElementById('showSummaryStats').addEventListener('click', showSummaryStats);
    document.getElementById('removePlots').addEventListener('click', function() {
        resetCanvas('summaryChart');
    });
    function resetCanvas(canvasId) {
        let canvas = document.getElementById(canvasId);
        if (canvas) {
            let ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let newCanvas = document.createElement('canvas');
            newCanvas.id = canvasId;
            newCanvas.width = canvas.width;
            newCanvas.height = canvas.height;   
            canvas.parentNode.replaceChild(newCanvas, canvas);
        }
    }

    function fetchData() {
        let locationID = <?php echo isset($_SESSION['locationID']) ? $_SESSION['locationID'] : 0; ?>;
        fetch('CRMUsers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'fetch_users',  // Correct spacing and format
                'locationID': locationID  // Ensuring this parameter is sent correctly
            })
        })
        .then(response => response.json())
        .then(data => {
            allUserData = data;  // Store fetched data
            displayData(allUserData);  // Display all data
        })
        .catch(error => {
            console.log("test2");
            console.error('Error fetching data:', error);
            document.getElementById('dataDisplay').innerHTML = '<strong>Failed to load data. Please try again.</strong>';
        });
    }

    function displayData(data, filterType) {
        const display = document.getElementById('dataDisplay');
        display.innerHTML = '';  // Clear previous data

        const table = document.createElement('table');
        table.style.width = '100%';
        table.setAttribute('border', '1');

        const headerRow = table.insertRow();
        let headers = Object.keys(data[0]);

        // Decide which headers to display
        if (filterType === 'userType') {
            headers = ['user_type'];  // Only display 'user_type' when filtering by type
        }

        // Create header cells
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


    function filterData() {
    let summaryChart = window.myChart;
    let chart = 0;
        if (summaryChart) {
            chart = 1;
        }
        resetCanvas('summaryChart');
        const userType = document.getElementById('userTypeSelect').value;
        const startDateFrom = document.getElementById('startDateFrom').value;
        const startDateTo = document.getElementById('startDateTo').value;

        let filteredData = allUserData.filter(user => {
            // Convert user's start_date from string to Date object
            const userDate = new Date(user.start_date);

            // Create Date objects from the input fields, default to extreme values if empty
            const from = startDateFrom ? new Date(startDateFrom) : new Date(-8640000000000000); // Very early date if no start date is provided
            const to = startDateTo ? new Date(startDateTo) : new Date(8640000000000000); // Very distant future date if no end date is provided

            // Check the user type and date range
            return (!userType || user.user_type === userType) && // Filter by user type if selected
                (!startDateFrom || userDate >= from) && // Filter by start date if provided
                (!startDateTo || userDate <= to); // Filter by end date if provided
        });
        displayData(filteredData);
        if (chart) {
            showSummaryStats();
        }
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
    function showSummaryStats() {
        let arr = extractDataFromTable();
        if (!arr.length) {
            console.error("No data to display stats for.");
            return;  // Exit if no data is available
        }

        // Check if 'date' or 'start_date' property exists on the first item
        let dateProp = arr[0].date ? 'date' : (arr[0].start_date ? 'start_date' : null);
        if (!dateProp) {
            console.error("Date property is missing from the data.");
            return;  // Exit if the required date property is missing
        }

        let data = {};
        arr.forEach(item => {
            if (!item[dateProp]) {
                console.error("Date value is missing from an item.");
                return;  // Skip items without a date value
            }
            let date = item[dateProp].slice(0, 7);  // Extract yyyy-mm from yyyy-mm-dd
            data[date] = (data[date] || 0) + 1;
        });

        // Proceed with chart generation
        let ctx = document.getElementById('summaryChart').getContext('2d');
        if (window.myChart) {
            window.myChart.destroy(); // Destroy the existing chart instance if present
        }
        window.myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    label: 'User IDs created per month',
                    data: Object.values(data),
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
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


</script>
</body>
</html>
