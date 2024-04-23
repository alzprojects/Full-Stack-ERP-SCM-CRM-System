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

// Function to toggle colorblind mode
function toggleColorBlindMode() {
    const isColorBlindMode = localStorage.getItem('colorBlindMode') !== 'true';
    localStorage.setItem('colorBlindMode', isColorBlindMode);
    document.body.classList.toggle('colorblind-mode', isColorBlindMode);

    // Example: Change button text based on mode
    const colorBlindModeButton = document.querySelector('.colorblind-mode-button');
    colorBlindModeButton.textContent = isColorBlindMode ? 'Colorblind Mode' : 'Normal Mode';

    // Adjust colors for improved readability in colorblind mode
    const elements = document.querySelectorAll('.colorblind-adjust');
    elements.forEach(element => {
        if (isColorBlindMode) {
            // Set colors suitable for colorblind users
            // For example, switch non-black or non-white colors to grey
            const computedStyle = window.getComputedStyle(element);
            const backgroundColor = computedStyle.getPropertyValue('background-color');
            const textColor = computedStyle.getPropertyValue('color');
            if (backgroundColor !== 'rgb(0, 0, 0)' && backgroundColor !== 'rgb(255, 255, 255)') {
                element.style.backgroundColor = 'grey';
            }
            if (textColor !== 'rgb(0, 0, 0)' && textColor !== 'rgb(255, 255, 255)') {
                element.style.color = 'grey';
            }
        } else {
            // Revert to default colors
            element.style.color = ''; // Revert text color
            element.style.backgroundColor = ''; // Revert background color
        }
    });
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

    const isColorBlindMode = localStorage.getItem('colorBlindMode') === 'true';
    document.body.classList.toggle('colorblind-mode', isColorBlindMode);

    const colorBlindModeButton = document.querySelector('.colorblind-mode-button');
    colorBlindModeButton.textContent = isColorBlindMode ? 'Colorblind Mode' : 'Normal Mode';

    // Load font size from local storage and apply it
    loadFontSize();
});
