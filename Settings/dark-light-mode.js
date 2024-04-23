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
            element.style.color = '#000'; // Black text
            element.style.backgroundColor = '#fff'; // White background
        } else {
            // Revert to default colors
            element.style.color = ''; // Revert text color
            element.style.backgroundColor = ''; // Revert background color
        }
    });
}

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
});
