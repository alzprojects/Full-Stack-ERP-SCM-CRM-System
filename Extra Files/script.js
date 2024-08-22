// Function to calculate mean
function calculateMean(data) {
    var sum = 0;
    for (var i = 0; i < data.length; i++) {
        sum += parseFloat(data[i]);
    }
    return sum / data.length;
}

// Function to calculate median
function calculateMedian(data) {
    data.sort(function(a, b){return a - b});
    var middle = Math.floor(data.length / 2);
    if (data.length % 2 === 0) {
        return (parseFloat(data[middle - 1]) + parseFloat(data[middle])) / 2;
    } else {
        return parseFloat(data[middle]);
    }
}

// Function to calculate mode
function calculateMode(data) {
    var modeMap = {};
    var maxCount = 0;
    var modes = [];

    data.forEach(function(number) {
        if (!modeMap[number]) modeMap[number] = 0;
        modeMap[number]++;
        
        if (modeMap[number] > maxCount) {
            modes = [number];
            maxCount = modeMap[number];
        } else if (modeMap[number] === maxCount) {
            modes.push(number);
        }
    });

    return modes.join(', ');
}

var dates = []; // Array to store parsed dates
var numericData = []; // Array to store numeric data for other columns
var headers = [];


// Main function to handle CSV file upload
function handleFileSelect(evt) {
    var file = evt.target.files[0];
    var reader = new FileReader();

    reader.onload = function(event) {
        var csv = event.target.result;
        var rows = csv.split('\n');
        
        // Extract header from the first row
        headers = rows[0].split(',');

        for (var i = 1; i < rows.length-1; i++) { // Start from row 2
            var values = rows[i].split(',');
            var numericValues = [];

            for (var j = 0; j < values.length; j++) {
                if (j === 0) { // Date column
                    // Convert date to Unix timestamp
                    var timestamp = convertToTimestamp(values[j].trim());
                    dates.push(timestamp);
                } else { // Other numerical columns
                    numericValues.push(parseFloat(values[j].trim()));
                }
            }

            // Store numeric data for other columns
            numericData.push(numericValues);
        }

        // Display statistics in HTML
        displayStatistics(dates, numericData, headers);
    };

    reader.readAsText(file);
}


// Function to handle column selection
function selectColumn() {
    var selectedColumn = parseInt(prompt("Enter the column number (1-8):"));
    if (selectedColumn >= 1 && selectedColumn <= 8) {
        displayStatistics(dates, numericData, headers, selectedColumn);
    } else {
        alert("Invalid column number. Please enter a number between 1 and 8.");
    }
}

var chartType = '';

// Function to handle chart type selection
function selectChartType(type) {
    chartType = type;
}


// Function to display statistics based on column selection
function displayStatistics(dates, numericData, headers, selectedColumn = null) {
    var statisticsContainer = document.getElementById('statistics');
    statisticsContainer.innerHTML = '';

    /*// Clear the canvas
    var canvas = document.getElementById('chartCanvas');
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);*/

    if (selectedColumn === 1 ) {
        // Calculate statistics for dates
        var dateMean = calculateMean(dates);
        var dateMedian = calculateMedian(dates);
        var dateMode = calculateMode(dates);


        // Display statistics for dates
        var dateStats = document.createElement('div');
        dateStats.innerHTML = '<p>Column 1: Dates</p>' +
                               '<p>Mean: ' + new Date(dateMean * 1000).toLocaleDateString() + '</p>' +
                               '<p>Median: ' + new Date(dateMedian * 1000).toLocaleDateString() + '</p>' +
                               '<p>Mode: ' + new Date(dateMode * 1000).toLocaleDateString() + '</p>';
        statisticsContainer.appendChild(dateStats);


        if (chartType === 'line') {
            createLineGraph(dates, headers[0]);
        } else if (chartType === 'bar') {
            createBarChart(dates, headers[0]);
        } else if (chartType == 'pie') {
            createBarChart(dates, headers[0]);
        }

    }

    if (selectedColumn !== null && selectedColumn > 1) {
        var columnIndex = selectedColumn - 2;
        var column = numericData.map(row => row[columnIndex]);
        var mean = calculateMean(column);
        var median = calculateMedian(column);
        var mode = calculateMode(column);

        // Display statistics for other numerical columns
        var columnStats = document.createElement('div');
        columnStats.innerHTML = '<p>Column ' + selectedColumn + ': ' + headers[columnIndex + 1] + '</p>' +
                                '<p>Mean: ' + mean.toFixed(2) + '</p>' +
                                '<p>Median: ' + median.toFixed(2) + '</p>' +
                                '<p>Mode: ' + mode + '</p>';
        statisticsContainer.appendChild(columnStats);

        if (chartType === 'line') {
            createLineGraph(column, headers[columnIndex + 1]);
        } else if (chartType === 'bar') {
            createBarChart(column, headers[columnIndex + 1]);
        }

    }
}

// Function to transpose a 2D array
function transpose(array) {
    return array[0].map(function(_, i) {
        return array.map(function(row) {
            return row[i];
        });
    });
}

// Function to convert date string to Unix timestamp
function convertToTimestamp(dateString) {
    var parts = dateString.split('/');
    var month = parseInt(parts[1], 10); // Month (1-12)
    var day = parseInt(parts[0], 10);   // Day (1-31)
    var year = parseInt(parts[2], 10);  // Year

    // Create a new Date object and get the Unix timestamp in seconds
    var timestamp = new Date(year, month, day).getTime() / 1000; // Month is 0-indexed

    return timestamp;
}

// Function to create a line graph
function createLineGraph(data, columnName) {
    var ctx = document.getElementById('lineChartCanvas').getContext('2d');
    var lineGraph = new Chart(ctx, {
        type: 'line',
        data: {
            labels: Array.from(Array(data.length).keys()), // assuming x-axis is index
            datasets: [{
                label: columnName,
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
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

// Function to create a bar chart
function createBarChart(data, columnName) {
    var ctx = document.getElementById('barChartCanvas').getContext('2d');
    var barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Array.from(Array(data.length).keys()), // assuming x-axis is index
            datasets: [{
                label: columnName,
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
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

// Function to create a pie chart
function createPieChart(data, columnName) {
    var ctx = document.getElementById('pieChartCanvas').getContext('2d');
    var pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Array.from(Array(data.length).keys()), // assuming x-axis is index
            datasets: [{
                label: columnName,
                data: data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
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


document.getElementById('csv-file').addEventListener('change', handleFileSelect, false);
