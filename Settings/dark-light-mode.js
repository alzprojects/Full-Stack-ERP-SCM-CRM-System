// Function to set dark mode
function setDarkMode(isDarkMode) {
    localStorage.setItem('darkMode', isDarkMode);
    document.body.classList.toggle('dark-mode', isDarkMode);
    document.body.classList.toggle('light-mode', !isDarkMode); // Toggle light mode
}

// Function to toggle dark mode
function toggleDarkMode() {
    const isDarkMode = localStorage.getItem('darkMode') !== 'true';
    setDarkMode(isDarkMode);

    // Example: Change button text based on mode
    const darkModeButton = document.querySelector('.dark-mode-button');
    darkModeButton.textContent = isDarkMode ? 'Light Mode' : 'Dark Mode';
}


// Function to change font size
function changeFontSize(size) {
    // Store the selected font size in local storage
    localStorage.setItem('fontSize', size);

    // Apply the font size to the body and all relevant elements
    document.body.style.fontSize = size;
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.style.fontSize = size;
    });

    // Load the font size from local storage and apply it to other pages
    loadFontSize();
}

// Function to load font size from local storage and apply it
function loadFontSize() {
    const savedFontSize = localStorage.getItem('fontSize');
    if (savedFontSize) {
        // Apply the saved font size to the body and all relevant elements
        document.body.style.fontSize = savedFontSize;
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.style.fontSize = savedFontSize;
        });
    }
}

// Function to set font size preference
function setFontSize(size) {
    localStorage.setItem('fontSize', size);
    document.body.style.fontSize = size;
}

// Check for user's preferred font size on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedFontSize = localStorage.getItem('fontSize');
    if (savedFontSize) {
        setFontSize(savedFontSize);
    }
});

// Check for user's preference on page load
document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('darkMode') === null) {
        localStorage.setItem('darkMode', false); // Default to light mode
    }
    if (localStorage.getItem('colorBlindMode') === null) {
        localStorage.setItem('colorBlindMode', false); // Default to normal mode
    }

    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    setDarkMode(isDarkMode);

    // Load font size from local storage and apply it
    loadFontSize();
});
