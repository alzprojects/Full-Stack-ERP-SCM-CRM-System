    const searchForm = document.getElementById('searchForm');
    let lowInventoryChart, topPurchasedChart;

    searchForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission
        const searchTerms = document.getElementById('search_terms').value;

        // Validate search terms client-side
        if (!validateInput(searchTerms)) {
            // Invalid input, show alert to the user and stop form submission
            alert('Invalid input format. Please enter comma-separated non-negative integers for product IDs or "0" to view all products.');
            return;
        }

        const response = await fetch('SCMInventory.php', {
            method: 'POST',
            body: new URLSearchParams({
                search_terms: searchTerms
            })
        });

        if (response.ok) {
            const data = await response.json();
            updateCharts(data); // Update all charts with data
        } else {
            console.error('Failed to fetch data');
        }
    });

    // Function to validate search terms
    function validateInput(searchTerms) {
        // Check if search terms are empty or contain valid comma-separated non-negative integers
        return searchTerms === "" || /^(\d+,)*\d+$/.test(searchTerms);
    }

    function updateTable(data) {
        const tableBody = document.querySelector('#leftContainer #dataTable tbody');
        tableBody.innerHTML = ''; // Clear existing table body
    
        data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.productID}</td>
                <td>${item.productName}</td>
                <td>${item.locationID}</td>
                <td>${item.locationName}</td>
                <td>${item.inventoryQuantity}</td>
            `;
            tableBody.appendChild(row); // Append row to table body
        });
    }

    function updateChart(chart, chartData) {
        // Destroy existing chart if it exists
        if (chart) {
            chart.destroy();
        }

        // Initialize new chart
        const ctx = document.getElementById(chartData.canvasId).getContext('2d');
        const newChart = new Chart(ctx, {
            type: chartData.type,
            data: chartData.data,
            options: chartData.options
        });

        return newChart; // Return the newly created chart instance
    }

    function prepareLowInventoryChartData(data) {
    // Filter products with inventory below 10000
    const averageQuantity = data.reduce((acc, item) => acc + item.inventoryQuantity, 0) / data.length;
    const lowInventoryProducts = data.filter(item => item.inventoryQuantity < 10000);

    // Sort low inventory products by inventory quantity (ascending)
    lowInventoryProducts.sort((a, b) => a.inventoryQuantity - b.inventoryQuantity);

    return {
        canvasId: 'lowInventoryChart',
        type: 'bar',
        data: {
            labels: lowInventoryProducts.map(item => `${item.productID}:${item.locationID}`),
            datasets: [{
                label: 'Low Inventory Products',
                data: lowInventoryProducts.map(item => item.inventoryQuantity),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
}

    // Function to prepare data for the top purchased chart
    function prepareTopPurchasedChartData(data) {
        const aggregatedData = aggregateDataByProductID(data);
        const sortedAggregatedData = sortDataByPurchaseQuantity(aggregatedData);
        const topPurchasedProducts = sortedAggregatedData.slice(0, 10);

        return {
            canvasId: 'topPurchasedChart',
            type: 'bar',
            data: {
                labels: topPurchasedProducts.map(item => item.productID),
                datasets: [{
                    label: 'Top Purchased Products',
                    data: topPurchasedProducts.map(item => item.totalPurchaseQuantity),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };
    }

    // Aggregate data by product ID and sum the purchase quantities
    // Aggregate data by product ID and sum the purchase quantities
function aggregateDataByProductID(data) {
    const aggregatedData = {};
    data.forEach(item => {
        const productID = item.productID;
        if (aggregatedData[productID]) {
            aggregatedData[productID].totalPurchaseQuantity += parseInt(item.purchaseQuantity);
        } else {
            aggregatedData[productID] = {
                productID: productID,
                totalPurchaseQuantity: parseInt(item.purchaseQuantity)
            };
        }
    });
    return Object.values(aggregatedData);
}

// Sort data by total purchase quantity in descending order
function sortDataByPurchaseQuantity(data) {
    return data.sort((a, b) => b.totalPurchaseQuantity - a.totalPurchaseQuantity);
}


    // Function to update the charts
    function updateCharts(data) {
    // Update the table first
    updateTable(data);

    // Prepare data for the low inventory chart
    const lowInventoryChartData = prepareLowInventoryChartData(data);

    // Update the low inventory chart
    lowInventoryChart = updateChart(lowInventoryChart, lowInventoryChartData);

    // Prepare data for the top purchased chart
    const topPurchasedChartData = prepareTopPurchasedChartData(data);

    // Update the top purchased chart
    topPurchasedChart = updateChart(topPurchasedChart, topPurchasedChartData);
}
