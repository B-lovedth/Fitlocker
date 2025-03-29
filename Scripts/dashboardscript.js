function clearSearch() {
  document.getElementById("searchBox").value = "";
}

function switchActive(clickedButton) {
  const buttons = document.querySelectorAll(".btn");

  buttons.forEach((button) => {
    button.classList.remove("active");
  });

  clickedButton.classList.add("active");
}

function setActiveBasedOnPage() {
  console.log("setActiveBasedOnPage is running");
  // Get the current page name from the URL (e.g., "dashboard" from "dashboard.php")
  const currentPage = window.location.pathname
    .split("/")
    .pop()
    .replace(".php", "");

  // Select all sidebar buttons
  const buttons = document.querySelectorAll(".nav-icon.btn");

  // Loop through buttons and set the active class on the matching one
  buttons.forEach((button) => {
    if (button.getAttribute("data-page") === currentPage) {
      button.classList.add("active");
    } else {
      button.classList.remove("active");
    }
  });
}

// Run setActiveBasedOnPage when the page loads
document.addEventListener("DOMContentLoaded", function () {
  setActiveBasedOnPage();
});
