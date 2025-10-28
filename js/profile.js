$(document).ready(function () {

  const sessionId = sessionStorage.getItem("sessionId");
  if (!sessionId) {
    alert("You are not logged in!");
    window.location.href = "login.html";
    return;
  }

  // --- Fetch Profile ---
  function fetchProfile() {
    $.ajax({
      url: "https://guvi-intern-md3o.onrender.com/php/profile.php",
      type: "POST",
      dataType: "json",
      data: { sessionId: sessionId, action: "fetch" }, // Use form data
      success: function (res) {
        console.log("Profile fetch response:", res);
        if (res.status === "success") {
          $("#detailEmail").text(res.data.email);
          $("#detailName").text(res.data.name);
          $("#detailDob").text(res.data.dob);
          $("#detailContact").text(res.data.contact);
          $("#detailAge").text(res.data.age);
          $("#detailAddress").text(res.data.address);
          $("#detailGender").text(res.data.gender);
          $("#greeting").text(`Welcome, ${res.data.name}`);

          $("#editName").val(res.data.name);
          $("#editDob").val(res.data.dob);
          $("#editContact").val(res.data.contact);

        } else {
          alert(res.msg || "Session expired, please login again.");
          window.location.href = "login.html";
        }
      },
      error: function (xhr, status, error) {
        console.error("Profile AJAX Error:", status, error);
        console.log("Raw Response:", xhr.responseText);
      }
    });
  }

  fetchProfile();

  // --- Save Changes ---
  $("#saveChanges").click(function () {
    const name = $("#editName").val().trim();
    const dob = $("#editDob").val();
    const contact = $("#editContact").val().trim();

    if (!name || !dob || !contact) {
      alert("Please fill all fields.");
      return;
    }

    $.ajax({
      url: "https://guvi-intern-md3o.onrender.com/php/profile.php",
      type: "POST",
      dataType: "json",
      data: {
        sessionId: sessionId,
        action: "update",
        name: name,
        dob: dob,
        contact: contact
      },
      success: function (res) {
        console.log("Profile update response:", res);
        if (res.status === "success") {
          alert(res.msg);
          fetchProfile();
          $("#editModal").modal("hide");
        } else {
          alert(res.msg);
        }
      },
      error: function (xhr, status, error) {
        console.error("Profile update AJAX error:", status, error);
        console.log("Raw Response:", xhr.responseText);
      }
    });
  });

  // --- Logout ---
  $("#logoutBtn").click(function () {
    sessionStorage.removeItem("sessionId");
    window.location.href = "login.html";
  });

});
