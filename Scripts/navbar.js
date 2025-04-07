const header = document.querySelector("header");
const hamburgerBtn = document.getElementById("hamburger");
const hamburgerMenu = document.querySelector(".hamburger-menu");
let hamburgerMenuOpen = false;
const body = document.querySelector("body");
const overlay = document.getElementById("overlay");
const signInBtn = document.querySelector(".sign-in");
const sideNav = document.querySelector("aside");
const pageTitle = document.querySelector(".page-title");
let menuItems = document.querySelectorAll(".menu-item");

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

// Change Page Title & Make it bold in mobile nav
if (pageTitle) {
    let currentPageTitle = document.title;
    pageTitle.innerHTML = currentPageTitle;
    menuItems.forEach(item => {
        if (item.textContent == pageTitle) {
            item.classList.add("bold")
        }
    });
}

// Clear menu when you scale out
window.addEventListener('resize', () => {
    if (window.innerWidth > 890) {
        hamburgerMenu.classList.add("hide");
        header.classList.remove("absolute");
        hamburgerMenuOpen = false;
        hamburgerBtn.setAttribute("src", "./assets/icons/menu-hamburger.svg");
        header.classList.remove("add-border-bottom");
        overlay.classList.add("hide");
        if (sideNav) {
            sideNav.classList.remove("hide");
        }
    } else {
        if (sideNav) {
            sideNav.classList.add("hide");
        }
    }
})

