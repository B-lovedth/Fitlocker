const header = document.querySelector("header");
const hamburgerBtn = document.getElementById("hamburger");
const hamburgerMenu = document.querySelector(".hamburger-menu");
let hamburgerMenuOpen = false;
const body = document.querySelector("body");
const overlay = document.getElementById("overlay");
const signInBtn = document.querySelector(".sign-in");

// Responsive navbar menu
hamburgerBtn.addEventListener('click', () => {
    toggleMenuBtn(hamburgerMenuOpen);
    hamburgerMenu.classList.toggle('hide');
    header.classList.toggle("add-border-bottom");
    header.classList.toggle("absolute");
    overlay.classList.toggle("hide");
})

function toggleMenuBtn(isOpen) {
    if (isOpen == false) {
        hamburgerMenuOpen = true;
        hamburgerBtn.setAttribute("src", "./assets/icons/close-x.svg");
    } else {
        hamburgerMenuOpen = false;
        hamburgerBtn.setAttribute("src", "./assets/icons/menu-hamburger.svg");
    }
}

// Clear menu when you scale out
window.addEventListener('resize', () => {
    if (window.innerWidth > 700) {
        hamburgerMenu.classList.add("hide")
        hamburgerMenuOpen = false;
        hamburgerBtn.setAttribute("src", "./assets/icons/menu-hamburger.svg");
    }
})
