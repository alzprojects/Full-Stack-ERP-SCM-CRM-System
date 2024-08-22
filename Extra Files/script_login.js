document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form from submitting normally

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // validation
    if (!email || !password || password.length < 6) {
        alert('Please fill in all fields correctly.');
        return;
    }


});
