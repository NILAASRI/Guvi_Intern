$(document).ready(function () {
  // Step 1 → Step 2
  $("#nextBtn").click(function () {
    let name = $("#name").val().trim();
    let email = $("#email").val().trim();
    let password = $("#password").val().trim();
    let confirmPassword = $("#confirmPassword").val().trim();
    let terms = $("#terms").is(":checked");

    if (!name || !email || !password || !confirmPassword) {
      alert("Please fill all fields.");
      return;
    }
    if (password !== confirmPassword) {
      alert("Passwords do not match.");
      return;
    }
    if (!terms) {
      alert("Please accept the terms and conditions.");
      return;
    }

    $("#step1").hide();
    $("#step2").show();
  });

  // Step 2 → Step 1
  $("#backBtn").click(function () {
    $("#step2").hide();
    $("#step1").show();
  });

  // Submit registration
  $("#registerBtn").click(function () {
    $.ajax({
      url: "https://guvi-intern-md3o.onrender.com/php/register.php",
      type: "POST",
      dataType: "json",
      data: {
        name: $("#name").val(),
        email: $("#email").val(),
        password: $("#password").val(),
        confirmPassword: $("#confirmPassword").val(),
        dob: $("#dob").val(),
        age: $("#age").val(),
        phone: $("#phone").val(),
        address: $("#address").val(),
        gender: $("#gender").val()
      },
      beforeSend: function () {
        console.log("Sending registration request...");
      },
      success: function (res) {
        console.log("Response:", res);
        alert(res.msg);
        if (res.status === "success") {
          $("#registerForm")[0].reset();
          $("#step2").hide();
          $("#step1").show();
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        console.log("Response Text:", xhr.responseText);
        alert("Error during registration. Check console logs.");
      }
    });
  });
});
