
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


