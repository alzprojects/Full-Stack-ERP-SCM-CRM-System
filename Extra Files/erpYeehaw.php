<?php
echo <<<EOT
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micromanagement Central Yeehaw</title>
    <link rel="stylesheet" href="SCM_Style.css">
    <!-- <script src="SCM_Script.js"></script> -->    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
    <div class="container">
        <h2>Micromanagement Central Yeehaw</h2>
        <div class="navbar">
            <a href="homePage.html">Home</a>
            <a href="login.html">Login</a>
            <a href="ERP.php">ERP</a>
            <a href="SCM.html">SCM</a>
            <a href="CRM.html">CRM</a>  
        </div>
        <br></br>
            <div id ="smallContainer">
                <div id="leftContainer">
                    <h3>Data</h3>
                    <p>we can put literally any table here, we had talked about being able to choose cols etc but thats a premium feature as of rn</p>
                    <form method="post" >
                            <label for="artist_id">Enter Artist ID:</label>
                            <input type="number" id="artist_id" name="artist_id" required>
                            <button type="submit">Submit</button>
                        </form>
                    <table id="artistTable"></table>
                    
                </div>
                <div id="rightContainer">
                    <div id="topRightContainer">
                        <h3>Plots & Figures</h3>
                        <p>dont rem if we are choosing pre-ordained charts or letting user pick params to graph</p>
                        <p>1st chart can be adjusted easily to be order sizes, profit margins, etc</p>
                        <canvas id="chartCanvas"></canvas>
                        <p>2nd chart can be when each purchase happened in order of purchaseid (lowkey useless i just needed to make this to make the next one)</p>
                        <canvas id="chartCanvas2"></canvas>
                        <p>3rd chart can be adjusted easily to be actually useful! orders/ shipments per day/ week etc. </p>
                        <canvas id="chartCanvas3"></canvas>
                        <div id ="plotsContainer"></div>
                    </div>
                    <div id="bottomRightContainer">
                        <h3>Data Summary</h3>
                        <p>just a table for chart3, can be adjusted to anything this was more for testing</p>
                        <table id="testTable"></table>
                        <p>we can change these to be any data summaries/ calcs, ex: calc yearly earnings, most profitable item, etc</p>
                        <button onclick="calculateAverage()">Calculate Average</button>
                        <button onclick="calculateMin()">Calculate Minimum</button>
                        <button onclick="calculateMax()">Calculate Maximum</button>
                        <p id="average"></p>
                        <p id="min"></p>
                        <p id="max"></p>
                        <p>table below is useless just for code ref</p>
                        <table id="csvTable"></table>
                    </div>
                </div>
            </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/split.js"></script>
            <!-- <script src="SCM_Script.js"></script> -->    
</body>
EOT;
$servername = "mydb.itap.purdue.edu"; // Remove spaces
$username = "sdeniz"; // Replace with your actual myPHPAdmin username
$password = "Paragon#2014"; // Replace with your actual myPHPAdmin password
$database = "sdeniz"; // Replace with your actual myPHPAdmin database name

// Create connection (ONLY NEEDED ONCE per PHP page!)
$conn = new mysqli($servername, $username, $password);

// Check connection was successful, otherwise immediately exit the script
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select the specific database
if (!$conn->select_db($database)) {
    die("Database selection failed: " . $conn->error);
}

// Create a string for our SQL query
$sql = "SELECT track_name, time FROM track WHERE time < (SELECT AVG(time) FROM track) LIMIT 5";

// Submit the string to SQL through the connection indicated in $conn
$result = $conn->query($sql); // Use $conn->query to execute the query

// Check if the query was successful
if ($result === false) {
    die("Query failed: " . $conn->error);
}


// Start the HTML table
echo "<script>document.getElementById('csvTable').innerHTML = `";
echo "<table border='1'>";
echo "<tr><th>Track Name</th><th>Time</th></tr>";
// Initialize song_lengths array
$song_lengths = array();
$track_ids = array();

// Fetch and print the results
while ($row = $result->fetch_assoc()) {
    // You can access individual columns like $row['track_name'] and $row['time']
    // Use echo to print specific values or print_r to print the entire row data
    $color = $row['time'] < 3 ? 'red' : 'transparent';
    echo "<tr><td>" . $row['track_name'] . "</td><td style='background-color: $color;'>" . $row['time'] . "</td></tr>";
    $song_lengths[] = floatval($row['time']);
    $track_ids[] = $row['track_id'];
}

// End the HTML table
echo "</table>";

echo "`;</script>";

echo "<script>var songLengths = " . json_encode($song_lengths) . ";</script>";
echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";

// Include the Chart.js library
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Pass the PHP array to JavaScript
echo "<script>var songLengths = " . json_encode($song_lengths) . ";</script>";
// Add a JavaScript function to calculate the average
echo '
<script>
    function calculateAverage() {
        var total = 0;
        for(var i = 0; i < songLengths.length; i++) {
            total += songLengths[i];
        }
        var avg = total / songLengths.length;
        document.getElementById("average").innerText = "Average song length: " + avg;
    }
    function calculateMin() {
        var min = Math.min.apply(null, songLengths);
        document.getElementById("min").innerText = "Minimum song length: " + min;
    }

    function calculateMax() {
        var max = Math.max.apply(null, songLengths);
        document.getElementById("max").innerText = "Maximum song length: " + max;
    }
</script>
';
// Create a canvas for your chart and use the Chart.js library to create a bar chart
echo '

<script>
    var ctx = document.getElementById("chartCanvas").getContext("2d");
    var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: songLengths,
            datasets: [{
                label: "Song Lengths",
                data: songLengths,
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
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
</script>
';



// Fetch the data from your database
$sql = "SELECT track_id, played FROM played";
$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

// Initialize the arrays
$track_ids = array();
$played_times = array();

// Fetch and store the results
while ($row = $result->fetch_assoc()) {
    $track_ids[] = $row['track_id'];
    $played_times[] = $row['played'];
}

// Pass the PHP arrays to JavaScript
echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";
echo "<script>var playedTimes = " . json_encode($played_times) . ";</script>";


// Create a canvas for your chart and use the Chart.js library to create a line chart
echo '
<canvas id="chart"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById("chartCanvas2").getContext("2d");
    var chart = new Chart(ctx, {
        type: "line",
        data: {
            labels: playedTimes, // Use playedTimes as labels (x-axis)
            datasets: [{
                label: "Track ID",
                data: trackIds, // Use trackIds as data (y-axis)
                fill: false,
                borderColor: "rgb(75, 192, 192)",
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
</script>
';
$sql = "SELECT DATE(played) AS play_date, COUNT( track_id ) AS songs_played
FROM played
GROUP BY DATE( played )
ORDER BY play_date";

$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}
echo "<script>document.getElementById('testTable').innerHTML = `";
echo '<table>';
echo '<tr><th>Play Date</th><th>Songs Played</th></tr>';
$track_ids = array();
$play_counts = array();
while ($row = $result->fetch_assoc()) {
    echo '<tr><td>' . $row['play_date'] . '</td><td>' . $row['songs_played'] . '</td></tr>';
    $track_ids[] = $row['play_date'];
    $play_counts[] = $row['songs_played'];
}

echo '</table>';
echo "`;</script>";
while ($row = $result->fetch_assoc()) {
    var_dump($row);
    $track_ids[] = $row['play_date'];
    $play_counts[] = $row['songs_played'];
}
echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";
echo "<script>var playCounts = " . json_encode($play_counts) . ";</script>";
echo '

<script>
    var ctx = document.getElementById("chartCanvas3").getContext("2d");
    var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: trackIds, // Use trackIds as labels (x-axis)
            datasets: [{
                label: "Number of Plays",
                data: playCounts, // Use playCounts as data (y-axis)
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
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
</script>
';
echo "<script src='https://cdn.jsdelivr.net/npm/split.js'></script>";

echo '
<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM is loaded");

    Split(["#leftContainer", "#rightContainer"], {
        sizes: [50, 50],
        minSize: 100,
        gutterSize: 3,
        cursor: "col-resize",
        gutter: function(index, direction) {
            const gutter = document.createElement("div");
            gutter.style.backgroundColor = "#181651";
            return gutter;
        }
    });

    Split(["#topRightContainer", "#bottomRightContainer"], {
        direction: "vertical",
        sizes: [50, 50],
        minSize: 100,
        gutterSize: 3,
        cursor: "row-resize",
        gutter: function(index, direction) {
            const gutter = document.createElement("div");
            gutter.style.backgroundColor = "#181651";
            return gutter;
        }
    });
});
</script>
';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $artist_id = intval($_POST["artist_id"]);
    echo "<script>console.log('Artist ID: " . $artist_id . "');</script>";
    
    // Construct the SQL query with a placeholder for the artist ID
    $sql = "SELECT artist.artist_name, track.album_id, track.track_name
            FROM artist
            JOIN track ON artist.artist_id = track.artist_id
            WHERE artist.artist_id = ?
            ORDER BY track.album_id";
    
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind the artist ID parameter
        $stmt->bind_param("i", $artist_id);
    
        // Execute the statement
        $stmt->execute();
    
        // Bind the results
        $stmt->bind_result($artist_name, $album_id, $track_name);
    
        // Fetch and display the data in a table row
        $table = "<table id='artistTable'>";
        $table .= "<tr><th>Artist Name</th><th>Album_ID</th><th>Track Name</th></tr>";
        while ($stmt->fetch()) {
            $table .= "<tr>";
            $table .= "<td>" . $artist_name . "</td>";
            $table .= "<td>" . $album_id . "</td>";
            $table .= "<td>" . $track_name . "</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";
        echo "<script>document.getElementById('artistTable').innerHTML = `" . $table . "`;</script>";
        }
        else {
            echo "No results found.";
        }

} else {
    echo "Error preparing SQL statement: " . $conn->error;
}
// Close the connection (REMEMBER TO DO THIS!)
$conn->close();
?>

