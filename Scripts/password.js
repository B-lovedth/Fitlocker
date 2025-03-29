const passwordInput = document.getElementById("password");
const passwordParameters = document.getElementsByClassName("password-parameters")
const passwordStatusIcons = document.getElementsByClassName("password-status-icon");
const passwordLengthParameter = document.getElementById("length-parameter");
const passwordNumberParameter = document.getElementById("number-parameter");
const passwordSpecialCharParameter = document.getElementById("special-character-parameter");
const createAccountBtn = document.getElementById("create-account");


// Password Validation
let validPassword = false;
let lengthValid = false;
let numberValid = false;
let specialCharValid = false;
passwordInput.addEventListener("input", () => {
    let currentText = passwordInput.value;
    validatePassword(currentText);
    checkPasswordLength(currentText);
    checkNumber(currentText);
    checkSpecialCharacter(password);
})

// Combines all three parameters to check if password is valid. Disables submit button if not valid
function validatePassword(password) {
    const conditions = [lengthValid, numberValid, specialCharValid];
    if (conditions.every(true)) {
        validPassword = true;
        createAccountBtn.disabled = false;
    } else {
        validPassword = false;
        createAccountBtn.disabled = true;
    }
    return validPassword;
}

// Check if password is of right length. Also changes the color and icon to match the state
function checkPasswordLength(password) {
        let lengthParameter = passwordParameters[0]
    if (password.length >= 8) {
        passwordParameters[0].classList.remove("red");
        passwordParameters[0].classList.add("green");
        passwordStatusIcons[0].src = "/assets/icons/check-green.svg";
        lengthValid = true;
    } else {
        passwordParameters[0].classList.remove("green");
        passwordParameters[0].classList.add("red");
        passwordStatusIcons[0].src = "/assets/icons/close-x-red.svg";
        lengthValid = false;
    }
}

// //Uses regex to check if password contains a digit
// function checkNumber (password) {
//     if (password.match(/\d/)) {
//         passwordParameters[1].classList.remove("red");
//         passwordParameters[1].classList.add("green");
//         passwordStatusIcons[1].src = "/assets/icons/check-green.svg";
//         numberValid = true;
//     } else {
//         passwordParameters[1].classList.add("green");
//         passwordParameters[1].classList.remove("red");
//         passwordStatusIcons[1].src = "/assets/icons/close-x-red.svg";
//         numberValid = false;
//     }
// }

// //Uses regex to check if it contains a special character
// function checkSpecialCharacter (password) {
//     if (password.match(/[^a-zA-Z0-9]/)) {
//         passwordParameters[2].classList.remove("red");
//         passwordParameters[2].classList.add("green");
//         passwordStatusIcons[2].src = "/assets/icons/check-green.svg";
//         specialCharValid = true;
//     } else {
//         passwordParameters[2].classList.add("green");
//         passwordParameters[2].classList.remove("red");
//         passwordStatusIcons[2].src = "/assets/icons/close-x-red.svg";
//         specialCharValid = false;
//     }
// }
