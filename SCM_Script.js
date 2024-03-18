document.getElementById('uploadButton').addEventListener('click', () => {
    console.log('Button clicked');
    const fileInput = document.getElementById('csvFileInput');
    const file = fileInput.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const text = e.target.result;
        displayCSV(text);
      };
      reader.readAsText(file);
    }
  });
  
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
        
        // Extract header from the plot row
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
        plotAnalytics(dates, numericData, headers);
    };

    reader.readAsText(file);
}

function displayCSV(csvText) {
    const rows = csvText.split('\n').map(row => row.split(','));
    const table = document.getElementById('csvTable');
    table.innerHTML = ''; // Clear previous table
    // Generate table header
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    rows[0].forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Generate table body
    const tbody = document.createElement('tbody');
    rows.slice(1).forEach(row => {
        const tr = document.createElement('tr');
        row.forEach(cell => {
        const td = document.createElement('td');
        td.textContent = cell;
        tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);

    // Populate columnSelect for filtering
    const columnSelect = document.getElementById('columnSelect');
    columnSelect.innerHTML = ''; // Clear previous options
    rows[0].forEach((header, index) => {
        const option = document.createElement('option');
        option.value = index;
        option.textContent = header;
        columnSelect.appendChild(option);
    });
}
  
var chartType = 'line';

// Function to handle chart type selection
function selectChartType(type) {
    chartType = type;
}

function selectColumn(){
    var selectedColumn = parseInt(columnSelect.value);
    plotAnalytics(dates, numericData, headers, selectedColumn);

}

function plotAnalytics(dates, numericData, headers, selectedColumn = null){
    var plotsContainer = document.getElementById('plotsContainer');
    plotsContainer.innerHTML = ''; // Clear previous plots
    var ctx = document.getElementById('chartCanvas').getContext('2d');
    var columnIndex = selectedColumn - 2;
    var column = numericData.map(row => row[columnIndex]);
    if (chartType === 'line') {
        createLineGraph(ctx, column, headers[columnIndex + 1]);
        //lineChartButton.classList.add('bold');
    } /*else if (chartType === 'bar') {
        createBarChart(ctx, column, headers[0]);
        barChartButton.classList.add('bold');
    } else if (chartType === 'pie'){
        createPieChart(ctx, column, headers[0]);
        pieChartButton.classList.add('bold');
    }*/
}

// Function to create a line graph
function createLineGraph(ctx, data, columnName) {
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
