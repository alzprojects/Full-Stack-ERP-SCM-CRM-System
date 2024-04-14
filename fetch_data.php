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
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        $conn->close(); // Close connection on error
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
    $product_ids = explode(",", sanitize_input($_POST["search_terms"]));

    // Initialize an array to store the data
    $product_data = array();

    // Loop through each product ID
    foreach ($product_ids as $product_id) {
        // Query to fetch product information
        $sql = "SELECT * FROM product WHERE productID = $product_id";

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
                // Product information
                $product_data[] = array(
                    'product_info' => array(
                        'productID' => $row['productID'],
                        'productName' => $row['name'], // Using the 'Name' column directly
                        'productPrice' => $row['price'] // Using the 'price' column directly
                    )
                );
            }
        }
    }

    // Close connection
    $conn->close();

    // Encode the data array as JSON
    $json = json_encode($product_data);

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
            <th>Product ID</th>
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
            <td>${item.product_info.productID}</td>
            <td>${item.product_info.productName}</td>
            <td>${item.product_info.productPrice}</td>
        `;
        tableBody.appendChild(row); // Append row to table body
    });
}

function updateChart(data, graphType) {
    const labels = data.map(item => item.product_info.productID);
    const values = data.map(item => item.product_info.productPrice);

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
