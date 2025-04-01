const filterBtn = document.getElementById("filter"); 
const filterSection = document.getElementById("filter-section"); 

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page is fully loaded');
    const logoutButton = document.querySelectorAll('.logoutIcon'); //Changed to cover mobile button
    
    if (logoutButton) {
        logoutButton.forEach(button => { 
            button.addEventListener('click', function() {
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
        });
    } else {
        console.error('Logout button not found');
    }
});
    
document.addEventListener("DOMContentLoaded", function () {
    const accountIcon = document.getElementById("accountsIcon");
    const closeAccountModal = document.getElementById("closeAccountModal");
    const accountModalOverlay = document.getElementById("accountInfoOverlay");
  
    if (accountIcon && closeAccountModal && accountModalOverlay) {
      // Show modal when account icon is clicked
      accountIcon.addEventListener("click", function () {
        accountModalOverlay.style.display = "flex";
      });
  
      // Hide modal when close button is clicked
      closeAccountModal.addEventListener("click", function () {
        accountModalOverlay.style.display = "none";
      });
    } else {
      console.error("Modal elements not found");
    }
  });


  // Change Page Title

if (pageTitle) {
    let currentPageTitle = document.title;
    pageTitle.innerHTML = currentPageTitle;
  }


// //Hide or Show filters
// if (filterBtn) {
//     filterBtn.addEventListener("click", () => {
//         console.log("working");
//         filterSection.classList.toggle("hide");
//     });
// }
