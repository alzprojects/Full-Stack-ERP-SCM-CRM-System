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
  

  // Example function to filter data based on the selected column and input value
  document.getElementById('filterButton').addEventListener('click', () => {
    const columnSelect = document.getElementById('columnSelect');
    const queryInput = document.getElementById('queryInput');
    const selectedColumnIndex = columnSelect.value;
    const queryValue = queryInput.value.trim().toLowerCase(); // Case-insensitive comparison
  
    // Retrieve all table rows
    const table = document.getElementById('csvTable');
    const allRows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
  
    // Convert HTMLCollection to array to use array methods
    const rowsArray = Array.from(allRows);
  
    // Toggle row visibility based on the filter
    rowsArray.forEach(row => {
      const cells = row.getElementsByTagName('td');
      if (cells.length > selectedColumnIndex) { // Check if the cell exists
        const cellValue = cells[selectedColumnIndex].textContent.trim().toLowerCase();
        if (cellValue.includes(queryValue)) {
          row.style.display = ''; // Show row if it matches
        } else {
          row.style.display = 'none'; // Hide row if it does not match
        }
      }
    });
  });
  