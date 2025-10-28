$(document).ready(function() {
  $("#loginForm").on("submit", function(e) {
    e.preventDefault();

    const email = $("#loginEmail").val().trim();
    const password = $("#loginPassword").val().trim();
    const remember = $("#rememberMe").is(":checked");

    if (!email || !password) {
      alert("Please enter both email and password");
      return;
    }

    $.ajax({
      url: "https://guvi-intern-md3o.onrender.com/php/login.php",
      method: "POST",
      dataType: "json",
      data: { email, password },
      beforeSend: function() {
        console.log("Logging in...");
      },
      success: function(res) {
        console.log("Server Response:", res);
        if (res.status === "success") {
          // Store session depending on Remember Me
          if (remember) {
            localStorage.setItem("sessionId", res.sessionId);
          } else {
            sessionStorage.setItem("sessionId", res.sessionId);
          }

          alert("Login successful!");
          window.location.href = "profile.html";
        } else {
          alert(res.msg || "Invalid email or password");
        }
      },
      error: function(xhr, status, error) {
        console.error("Login error:", status, error);
        console.log("Response:", xhr.responseText);
        alert("Server error. Please try again.");
      }
    });
  });
});
