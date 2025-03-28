const hamburgerBtn = document.getElementById("hamburger");
const hamburgerMenu = document.getElementsByClassName("hamburger-menu")[0];
const closeMenu = document.getElementById("close-menu");
const body = document.querySelector("body");
const overlay = document.getElementById("overlay");
const signInBtn = document.querySelector(".sign-in");

const expandBtn = document.getElementById("expand");
const expandIcon = document.getElementById("expand-icon");
const leftSideBar = document.querySelector(".left-sidebar");
const leftSideBarTexts = document.getElementsByClassName("sidebar-text");

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page is fully loaded');
    const logoutButton = document.getElementById('logoutIcon');
  
    if (logoutButton) {
        console.log('Logout button found');
        logoutButton.addEventListener('click', function() {
            console.log('Logout button clicked');
            if (confirm('Are you sure you want to Log Out?')) {
                fetch('logout.php')
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            window.location.href = 'homepage.php';
                        }
                    })
                    .catch(error => {
                        console.error('Error during logout:', error);
                    });
            }
        });
    } else {
        console.error('Logout button not found');
    }
  });

  document.addEventListener("DOMContentLoaded", function() {
    const accountIcon = document.getElementById("accountsIcon");
    const closeModal = document.getElementById("closeModal");
    const modalOverlay = document.getElementById("accountInfoOverlay");

    if (accountIcon && closeModal && modalOverlay) {
        // Show modal when account icon is clicked
        accountIcon.addEventListener("click", function() {
            modalOverlay.style.display = "flex";
        });

        // Hide modal when close button is clicked
        closeModal.addEventListener("click", function() {
            modalOverlay.style.display = "none";
        });
    } else {
        console.error("Modal elements not found");
    }
});

  // Responsive navbar menu
hamburgerBtn.addEventListener("click", () => {
  hamburgerMenu.classList.toggle("hide");
  overlay.classList.toggle("hide");
});
closeMenu.addEventListener("click", () => {
  hamburgerMenu.classList.toggle("hide");
});

// Clear menu when you scale out
window.addEventListener("resize", () => {
  if (window.innerWidth > 700) {
    hamburgerMenu.classList.add("hide");
  }
});

//Expand Sidebar
expandBtn.addEventListener("click", () => {
  for (let i = 0; i < leftSideBarTexts.length; i++) {
    const text = leftSideBarTexts[i];
    text.classList.toggle("hide");
    console.log(text);
  }
  if (expandBtn.classList.contains("hide")) {
    expandIcon.setAttribute("src", "assets/icons/expand.svg");
  } else {
    expandIcon.setAttribute("src", "assets/icons/collapse.svg");
  }
});