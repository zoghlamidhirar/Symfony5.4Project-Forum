document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector(".form");
  const contentPost = document.getElementById("contentPost");
  const creationDatePost = document.getElementById("creationDatePost");
  const user = document.getElementById("user");
  const errorsP = document.querySelectorAll(".err");

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    resetErrors();

    let errors = [];

    // Validation for contentPost field
    if (!isValidContentPost(contentPost.value)) {
      errors.push(0);
    }

    // Validation for creationDatePost field
    if (!isValidCreationDate(creationDatePost.value)) {
      errors.push(1);
    }

    // Validation for user field
    if (!isValidUser(user.value)) {
      errors.push(2);
    }

    displayErrors(errors);
  });

  function resetErrors() {
    errorsP.forEach(function (error) {
      error.textContent = "";
    });
  }

  function isValidContentPost(content) {
    if (content.trim().length < 3) {
      errorsP[0].textContent = "Content must be at least 3 characters long.";
      return false;
    }
    return true;
  }

  function isValidCreationDate(date) {
    // Assuming simple validation for demonstration purpose
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
      errorsP[1].textContent = "Invalid date format. Please use yyyy-mm-dd.";
      return false;
    }
    return true;
  }

  function isValidUser(user) {
    // Add your validation logic for the user field here
    // Example: Check if user is not empty
    if (user.trim() === "") {
      errorsP[2].textContent = "User field cannot be empty.";
      return false;
    }
    return true;
  }

  function displayErrors(errors) {
    errors.forEach(function (error) {
      errorsP[error].style.color = "red";
    });
  }
});
