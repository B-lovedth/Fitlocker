const hamburgerBtn = document.getElementById("hamburger");
const hamburgerMenu = document.getElementsByClassName("hamburger-menu")[0];
const closeMenu = document.getElementById("close-menu");
const body = document.querySelector("body");
const overlay = document.getElementById("overlay");
const signInBtn = document.querySelector(".sign-in");

const expandBtn = document.getElementById("expand");
const expandIcon = document.getElementById("expand-icon");
const leftSideBar = document.querySelector(".left-sidebar")
const leftSideBarTexts = document.getElementsByClassName("sidebar-text");

const passwordInput = document.getElementById("password");
const passwordParameter = document.getElementById("");

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

//Expand Sidebar
expandBtn.addEventListener('click', () => {
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
})

// Password Validation
//Check Special Characters
let validPassword;
passwordInput.addEventListener('input', () => {
    currentText = passwordInput.textContent; //T.B.C
})
