let timeout;

function resetTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        alert('You have been logged out due to inactivity.');
        window.location = '/logout'; // Adjust this to your logout URL
    }, 900000); // 900000 milliseconds = 15 minutes
}

// Events that reset the timer
window.onload = resetTimer;
window.onmousemove = resetTimer;
window.onmousedown = resetTimer; // catches touchscreen presses
window.onclick = resetTimer;     // catches touchpad clicks
window.onscroll = resetTimer;    // catches scrolling with arrow keys
window.onkeypress = resetTimer;
