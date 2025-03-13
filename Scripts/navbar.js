const hamburgerBtn = document.getElementById("hamburger");
const hamburgerMenu = document.getElementsByClassName("hamburger-menu")[0];
const closeMenu = document.getElementById("close-menu");
const body = document.querySelector("body");
const overlay = document.getElementById("overlay");
const signInBtn = document.querySelector(".sign-in");

// Responsive navbar menu
hamburgerBtn.addEventListener('click', () => {
    hamburgerMenu.classList.toggle('hide');
    overlay.classList.toggle("hide")
})
closeMenu.addEventListener('click', () => {    
    hamburgerMenu.classList.toggle('hide')
})

// Clear menu when you scale out
window.addEventListener('resize', () => {
    if (window.innerWidth > 700) {
        hamburgerMenu.classList.add("hide")
    }
})
