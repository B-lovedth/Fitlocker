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
    }
});



// //Hide or Show filters
// if (filterBtn) {
//     filterBtn.addEventListener("click", () => {
//         console.log("working");
//         filterSection.classList.toggle("hide");
//     });
// }
