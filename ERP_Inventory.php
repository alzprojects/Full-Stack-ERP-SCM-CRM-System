<?php
/* 
Notes from Sarah!
This is how all functions will be commented.
This section here is all of the basic HTML structure that allows the contents
to be split into different containers and for everything to flow nicely.
This is also where all of the different functions are called and manipulated.
*/
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
        <h3>ERP Inventory</h3>
            <div id ="smallContainer">
                <div id="leftContainer">
                    <h3>Data</h3>
                    <p>we can put literally any table here, we had talked about being able to choose cols etc but thats a premium feature as of rn</p>
                    <form method="post" >
                        <label for="artist_id">Enter Product ID (0 will Select All):</label>
                        <input type="number" id="artist_id" name="artist_id" required>
                        <button type="submit">Submit</button>
                    </form>
                    <table id="artistTable"></table>
                    
                </div>
                <div id="rightContainer">
                    <div id="topRightContainer">
                        <h3>Plots & Figures</h3>
                        <p>dont rem if we are choosing pre-ordained charts or letting user pick params to graph</p>
                        <p>Date Purchases by Day of Week:</p>
                        <canvas id="chartCanvas2"></canvas>
                        <p>Purchases per Date </p>
                        <canvas id="chartCanvas3"></canvas>
                    </div>
                    <div id="bottomRightContainer">
                        <h3>Data Summary</h3>
                        <p><strong>Calculation 1:</strong> Average Purchase Price</p>
                        <script>window.onload = function() {
                            calculateAverage();
                        };</script>
                        <p id="average"></p>
                        <p><strong>Calculation 2:</strong> Day with Most Purchases</p>
                        <p id="mostP"></p>
                        <p><strong>Calculation 3:</strong> Total Number of Purchases Processed</p>
                        <p id="totalP"></p>
                    </div>
                </div>
            </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/split.js"></script>
            <!-- <script src="SCM_Script.js"></script> -->    
</body>
EOT;
/*
These next 2 echo statements are to call the JS script library, and then to
create the function that allows for the containers to be resizable. 
The separation is between left and right, and then top and bottom on the
right side. 
*/
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
/* 
This is where the database setup happens, it is currently connected to my 
Purdue database where the music database from lab10 is still loaded. 
*/

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

/*
This is where the first query happens. This is directly code from lab.
This query isn't actually being used right now. Its purpose is to highlight
the cells whose time is less than avg in red. We can repurpose. I kept it
because it is connected to other functions but will likely be eventually
deleted. 
*/

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

/*
This is where the JS calculations happen. Really the only calculation is 
the average function, which can defo be done in PHP as well if needed. 
*/

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
        document.getElementById("average").innerText = "Average price of a purchase (song length): " + avg;
    }

</script>
';

/*
This function calculated the date that has the most songs played. It actually
picks the specific time too, not just the day. Once the proper DB is linked, 
it will be the date with the most purchases. 
*/

$date = getDateWithMostSongsPlayed($conn);
echo "<script>document.getElementById('mostP').innerText = 'The date with the most items purchased is (songs played): " . $date . "';</script>";

function getDateWithMostSongsPlayed($conn) {
    // Construct the SQL query
    $sql = "SELECT played, COUNT(*) as song_count
            FROM played
            GROUP BY played
            ORDER BY song_count DESC
            LIMIT 1";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the date
        return $row['played'];
    } else {
        // No result, return null
        return null;
    }
}

/*
This function is similar to the previous one, but calculates what the total
number of songs played all year was. Will be amended to be number of purchases
placed all year. 
*/

$totalPlayed = getTotalSongsPlayed($conn);
echo "<script>document.getElementById('totalP').innerText = 'The total number of items purchased to date (songs played): " . $totalPlayed . "';</script>";

function getTotalSongsPlayed($conn) {
    // Construct the SQL query
    $sql = "SELECT COUNT(*) as total_played FROM played";

    // Execute the SQL query
    $result = $conn->query($sql);

    // Check if the query returned a result
    if ($result->num_rows > 0) {
        // Fetch the first row from the result
        $row = $result->fetch_assoc();
        // Return the total number of songs played
        return $row['total_played'];
    } else {
        // No result, return 0
        return 0;
    }
}

/*
This function is mostly defunct and is not actually displayed on the website, 
however, it is connected to literally all of the other graphs. So. Can't 
get rid of it without thinking. A lot. Later problem! If you were to readd
chartCanvas to the HTML though, you'd get a line chart with the time each 
track was played. 
*/

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
    var ctx = document.getElementById("chartCanvas").getContext("2d");
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

/*
This query and chart is for the "purchases per date" graph, and is formulated
to display a line graph of how purchases fluctuate with time. X-axis is 
date and y-axis is number of plays, which is going to be number of purchases.
*/

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

echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";
echo "<script>var playCounts = " . json_encode($play_counts) . ";</script>";


echo '

<script>
    var ctx = document.getElementById("chartCanvas3").getContext("2d");
    var chart = new Chart(ctx, {
        type: "line",
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

/*
This function does something very similar to the previous one, but splits
up the data by day of the week instead of just the date. Somewhere in the 
first 10 lines sorcery was performed to make the code work. Don't know how
or why it worked. Hasn't been tested much. Also makes a bar chart instead
of a line chart. 
*/

// First loop: Convert dates to days of the week
$result->data_seek(0); // Reset result pointer to the beginning
$track_ids = array();
$play_counts = array();
while ($row = $result->fetch_assoc()) {
    $date = $row['play_date'];
    $dayOfWeek = date('l', strtotime($date));
    $track_ids[] = $dayOfWeek;
}

// Second loop: Store play counts
$result->data_seek(0); // Reset result pointer to the beginning
while ($row = $result->fetch_assoc()) {
    $play_counts[] = $row['songs_played']; // Convert play count to integer
}

// Pass the PHP arrays to JavaScript
echo "<script>var trackIds = " . json_encode($track_ids) . ";</script>";
echo "<script>var playCounts = " . json_encode($play_counts) . ";</script>";

// Second loop: Generate the chart
echo '
<script>
    var ctx = document.getElementById("chartCanvas2").getContext("2d");
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

/*
This is where we ge to the fun stuff! This is the function that lets the user
properly query and control the data being displayed. The catch is that it 
only affects the table in the "data" section right now. Not anything else. 
There is an option to select all with 0. Otherwise you choose an ArtistID, 
which will eventually be an EmployeeID or ProductID to view details about
the thing you want to see.  
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $artist_id = intval($_POST["artist_id"]);
    echo "<script>console.log('Artist ID: " . $artist_id . "');</script>";
    
    // Typing 0 works as a select all
    if ($artist_id == "0") {
        // Construct the SQL query without a WHERE clause
        $sql = "SELECT artist.artist_name, track.album_id, track.track_name
                FROM artist
                JOIN track ON artist.artist_id = track.artist_id
                ORDER BY artist.artist_id";
        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);
    } else {
        // Convert the artist ID to an integer
        $artist_id = intval($artist_id);
        // Construct the SQL query with a placeholder for the artist ID
        $sql = "SELECT artist.artist_name, track.album_id, track.track_name
                FROM artist
                JOIN track ON artist.artist_id = track.artist_id
                WHERE artist.artist_id = ?
                ORDER BY track.album_id";
        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);
        // Bind the artist ID parameter
        if ($stmt) {
            $stmt->bind_param("i", $artist_id);
        }
    }
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
        
} else {
    echo "Error preparing SQL statement: " . $conn->error;
}

// Close the connection (REMEMBER TO DO THIS!)
$conn->close();
?>

