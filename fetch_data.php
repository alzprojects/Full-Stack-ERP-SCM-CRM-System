<?php
// Function to sanitize user input
function sanitize_input($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

// Get user input and process search
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database credentials
    $servername = "mydb.itap.purdue.edu";
$username = "azimbali";
$password = "Max!024902!!";
$database = "azimbali";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        $conn->close(); // Close connection on error
        die("Connection failed: " . $conn->connect_error);
    }

    // Get and sanitize search terms
    $search_terms = sanitize_input($_POST["search_terms"]);
    $search_by = "ProductID"; // We'll search by ProductID for this case

    // Explode search terms by commas
    $search_terms_array = explode(",", $search_terms);
    $sql = "SELECT * FROM Products WHERE ProductID IN (";

    // Constructing the SQL query dynamically
    foreach ($search_terms_array as $term) {
        $sql .= $term . ",";
    }
    // Remove the last comma and close parentheses
    $sql = rtrim($sql, ",") . ")";

    // Execute query
    $result = $conn->query($sql);

    // Close connection if query fails
    if (!$result) {
        $conn->close();
        die("Query failed: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        // Initialize an empty array to store the results
        $data = array();

        // Fetch associative array
        while ($row = $result->fetch_assoc()) {
            // Append each row to the data array
            $data[] = $row;
        }

        // Encode the data array as JSON
        $json = json_encode($data);

        // Close connection
        $conn->close();

        // Output JSON
        header('Content-Type: application/json');
        echo $json;
        exit; // Terminate script execution after sending JSON response
    } else {
        // Close connection
        $conn->close();

        echo json_encode(array("message" => "No results found"));
        exit; // Terminate script execution after sending JSON response
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Search</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #container {
            max-width: 800px; /* Adjust as needed */
            margin: 0 auto; /* Center the container horizontally */
            padding: 20px; /* Add some padding for spacing */
            border: 1px solid #ccc; /* Add a border for visual separation */
        }

        canvas#myChart {
            display: block;
            width: 400px; /* Set a fixed width for the canvas */
            height: 400px; /* Set a fixed height for the canvas */
            margin-top: 20px; /* Add some margin to separate it from the form */
        }

        #dataTable {
            width: 100%; /* Ensure the table takes up the full width */
            border-collapse: collapse; /* Collapse borders to prevent gaps between cells */
            margin-top: 20px; /* Add margin to separate it from the form */
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
        <h2>Product Search</h2>
        <form id="searchForm">
            <label for="search_terms">Enter Product IDs (comma-separated):</label><br>
            <input type="text" id="search_terms" name="search_terms"><br>
            <label for="graph_type">Select Graph Type:</label>
            <select id="graph_type" name="graph_type">
                <option value="bar">Bar Chart</option>
                <option value="line">Line Chart</option>
                <option value="pie">Pie Chart</option>
                <option value="radar">Radar Chart</option>
            </select>
            <input type="submit" value="Search">
        </form>

        <table id="dataTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be dynamically populated here -->
            </tbody>
        </table>

        <canvas id="myChart"></canvas>
    </div>

    <script>
    const searchForm = document.getElementById('searchForm');
    const graphTypeInput = document.getElementById('graph_type');
    let myChart;

    searchForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission
        const searchTerms = document.getElementById('search_terms').value;
        const graphType = graphTypeInput.value; // Get selected graph type

        const response = await fetch('fetch_data.php', {
            method: 'POST',
            body: new URLSearchParams({
                search_terms: searchTerms
            })
        });

        if (response.ok) {
            const data = await response.json();
            updateTable(data); // Update table with data
            updateChart(data, graphType); // Update chart with selected graph type
        } else {
            console.error('Failed to fetch data');
        }
    });

    function updateTable(data) {
        const tableBody = document.querySelector('#dataTable tbody');
        tableBody.innerHTML = ''; // Clear existing table body

        data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.ProductName}</td>
                <td>${item.UnitPrice}</td>
            `;
            tableBody.appendChild(row); // Append row to table body
        });
    }

    function updateChart(data, graphType) {
        const labels = data.map(item => item.ProductName);
        const values = data.map(item => item.UnitPrice);

        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Unit Price',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        let scales = {};
        if (graphType === 'line' || graphType === 'bar') {
            scales = {
                y: {
                    beginAtZero: true
                }
            };
        }

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: true, // Set maintainAspectRatio to true
            scales: scales
        };

        if (!myChart) {
            const ctx = document.getElementById('myChart').getContext('2d');
            myChart = new Chart(ctx, {
                type: graphType, // Set chart type based on selected graph type
                data: chartData,
                options: chartOptions
            });
        } else {
            myChart.config.type = graphType; // Update chart type
            myChart.data = chartData;
            myChart.options = chartOptions;
            myChart.update();
        }
    }
</script>

</body>
</html>