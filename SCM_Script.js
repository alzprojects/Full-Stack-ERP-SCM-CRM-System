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
  
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM is loaded');

    Split(['#leftContainer', '#rightContainer'], {
        sizes: [50, 50],
        minSize: 100,
        gutterSize: 3,
        cursor: 'col-resize',
        gutter: function(index, direction) {
            const gutter = document.createElement('div');
            gutter.style.backgroundColor = '#181651';
            return gutter;
        }
    });

    Split(['#topRightContainer', '#bottomRightContainer'], {
        direction: 'vertical',
        sizes: [50, 50],
        minSize: 100,
        gutterSize: 3,
        cursor: 'row-resize',
        gutter: function(index, direction) {
            const gutter = document.createElement('div');
            gutter.style.backgroundColor = '#181651';
            return gutter;
        }
    });
});
