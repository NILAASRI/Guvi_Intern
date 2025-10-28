$(document).ready(function() {
  $("#loginForm").on("submit", function(e) {
    e.preventDefault();

    let email = $("#loginEmail").val().trim();
    let password = $("#loginPassword").val().trim();
    let remember = $("#rememberMe").is(":checked") ? 1 : 0;

    if (!email || !password) {
      alert("Please enter both email and password");
      return;
    }

    $.ajax({
      url: "https://guvi-intern-md3o.onrender.com/php/login.php",
      method: "POST",
      dataType: "json",
      data: { email, password, remember },
      beforeSend: function() {
        console.log("Sending login request...");
      },
      success: function(response) {
        console.log("Response:", response);
        if (response.status === "success") {
          // Use sessionStorage for cloud session
          sessionStorage.setItem("sessionId", response.sessionId);
          alert("Login successful!");
          window.location.href = "profile.html";
        } else {
          alert(response.msg || "Invalid credentials");
        }
      },
      error: function(xhr, status, error) {
        console.error("Login AJAX error:", status, error);
        console.log("Response text:", xhr.responseText);
        alert("Login request failed. Please check console logs.");
      }
    });
  });
});
