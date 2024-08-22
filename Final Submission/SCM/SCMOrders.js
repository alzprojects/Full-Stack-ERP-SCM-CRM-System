window.addEventListener('DOMContentLoaded', async () => {
    const response = await fetch('get_dates.php');
    if (response.ok) {
        const dates = await response.json();
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        // Set the minimum and maximum dates for the date inputs
        startDateInput.min = dates.minDate;
        startDateInput.max = dates.maxDate;
        endDateInput.min = dates.minDate;
        endDateInput.max = dates.maxDate;
    } else {
        console.error('Failed to fetch available dates');
    }
});

const searchForm = document.getElementById('searchForm');
let supplierChart, ordersByDateChart, ordersByDayOfWeekChart;

searchForm.addEventListener('submit', async (e) => {
    e.preventDefault(); // Prevent default form submission
    const searchTerms = document.getElementById('search_terms').value;
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    // Validate search terms client-side
    if (!validateOrders(searchTerms)) {
        // Invalid input, show alert to the user and stop form submission
        alert('Invalid input format. Please enter comma-separated non-negative integers for order IDs or "0".');
        return;
    }

    console.log("Start Date:", startDate);
    console.log("End Date:", endDate);
    // Check if start date is after end date
    if (startDate > endDate) {
        alert("Start date cannot be after end date!");
        return; // Stop further execution
    }

    const formData = new FormData();
    formData.append('search_terms', searchTerms);
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);

    const response = await fetch('SCMorders.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        const data = await response.json();
        updateCharts(data); // Update all charts with data
    } else {
        console.error('Failed to fetch data');
    }
});

// Function to validate search terms
function validateOrders(searchTerms) {
    // Check if search terms are empty or contain valid comma-separated non-negative integers
    return searchTerms === "" || /^(\d+,)*\d+$/.test(searchTerms);
}

function updateTable(data) {
    const tableBody = document.querySelector('#dataTable tbody');
    tableBody.innerHTML = ''; // Clear existing table body

    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.order_info.orderID}</td>
            <td>${item.order_info.orderDate}</td>
            <td>${item.order_info.deliveryDate}</td>
            <td>${item.order_info.orderCost}</td>
            <td>${item.order_info.supplierID}</td>
        `;
        tableBody.appendChild(row); // Append row to table body
    });
}

function countOrdersPerSupplier(data) {
    const orderCounts = {};
    data.forEach(item => {
        const supplierID = item.order_info.supplierID;
        if (orderCounts[supplierID]) {
            orderCounts[supplierID]++;
        } else {
            orderCounts[supplierID] = 1;
        }
    });
    return orderCounts;
}

function countTotalQuantityPerOrder(data) {
    const totalQuantityPerOrder = {};

    // Calculate total quantity per order from the data returned by the search query
    data.forEach(item => {
        const orderID = item.order_info.orderID;
        const totalQuantity = item.order_info.totalQuantity;
        totalQuantityPerOrder[orderID] = totalQuantity;
    });

    return totalQuantityPerOrder;
}

function countOrdersByDayOfWeek(data) {
    const orderCounts = {
        Sunday: 0,
        Monday: 0,
        Tuesday: 0,
        Wednesday: 0,
        Thursday: 0,
        Friday: 0,
        Saturday: 0
    };

    data.forEach(item => {
        const deliveryDate = new Date(item.order_info.deliveryDate);
        const dayOfWeek = deliveryDate.toLocaleString('en', { weekday: 'long' });
        orderCounts[dayOfWeek]++;
    });

    return orderCounts;
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

function updateCharts(data) {
    updateTable(data); // Update the table first

    const orderCountsBySupplier = countOrdersPerSupplier(data);
    const totalQuantityPerOrder = countTotalQuantityPerOrder(data);
    const orderCountsByDayOfWeek = countOrdersByDayOfWeek(data);

    const supplierChartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            // Overlay a message on the chart if there is no data
            annotation: {
                annotations: {
                    noDataMessage: {
                        type: 'text',
                        x: 'center',
                        y: 'center',
                        font: {
                            size: 20,
                            weight: 'bold'
                        },
                        color: '#666',
                        text: 'No data to display'
                    }
                }
            }
        }
    };

    const supplierChartData = {
        canvasId: 'supplier',
        type: 'bar',
        data: {
            labels: Object.keys(orderCountsBySupplier),
            datasets: [{
                label: 'Orders per Supplier',
                data: Object.values(orderCountsBySupplier),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: supplierChartOptions
    };

    supplierChart = updateChart(supplierChart, supplierChartData);

    const totalQuantityChartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                suggestedMax: Math.max(...Object.values(totalQuantityPerOrder)) + 1,
                beginAtZero: true
            }
        }
    };

    const totalQuantityChartData = {
        canvasId: 'ordersByDate',
        type: 'line',
        data: {
            labels: Object.keys(totalQuantityPerOrder),
            datasets: [{
                label: 'Total Quantity per Order',
                data: Object.values(totalQuantityPerOrder),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: totalQuantityChartOptions
    };

    ordersByDateChart = updateChart(ordersByDateChart, totalQuantityChartData);

    const ordersByDayOfWeekChartOptions = {
        responsive: true,
        maintainAspectRatio: true
    };

    const ordersByDayOfWeekChartData = {
        canvasId: 'ordersByDayOfWeek',
        type: 'bar',
        data: {
            labels: Object.keys(orderCountsByDayOfWeek),
            datasets: [{
                label: 'Deliveries on Each Day of the Week',
                data: Object.values(orderCountsByDayOfWeek),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: ordersByDayOfWeekChartOptions
    };

    ordersByDayOfWeekChart = updateChart(ordersByDayOfWeekChart, ordersByDayOfWeekChartData);
}
