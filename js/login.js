$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault(); // This means Prevent form submission

        let email = $('#loginEmail').val().trim();
        let password = $('#loginPassword').val().trim();
        let remember = $('#rememberMe').is(':checked') ? 1 : 0;

        // AJAX request
        $.ajax({
            url: 'php/login.php',
            method: 'POST',
            dataType: 'json',
            data: { email, password, remember },
            success: function(response) {
                if(response.status === 'success') {
                    localStorage.setItem("sessionId", response.sessionId);          
                    alert('Login successful!');
                    window.location.href = 'profile.html'; 
                } else {
                    console.log(response);
                    alert(response.msg); //Notice here msg or message 
                }
            },
            error: function(error) {
                console.error(error);
                alert("Login request failed!");
            }
        });
    });
});
