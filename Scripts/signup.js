const passwordInput = document.getElementById('password');
        const termsCheckbox = document.getElementById('terms');
        const lengthParam = document.getElementById('length-parameter');
        const numberParam = document.getElementById('number-parameter');
        const specialCharParam = document.getElementById('special-character-parameter');
        const createAccountBtn = document.getElementById('create-account');
        let timeoutId;

        function updatePasswordValidation() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(validatePassword, 1000);
        }

        function validatePassword() {
            const password = passwordInput.value;
            const lengthValid = password.length >= 8;
            const hasNumber = /\d/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            updateStatus(lengthParam, lengthValid);
            updateStatus(numberParam, hasNumber);
            updateStatus(specialCharParam, hasSpecialChar);
            updateSubmitButton(lengthValid && hasNumber && hasSpecialChar);
        }

        function updateStatus(element, isValid) {
            const icon = element.querySelector('img');
            if (passwordInput.value === '') {
                element.style.color = '#000';
                icon.src = './assets/icons/close-x-red.svg';
            } else {
                element.style.color = isValid ? '#28a745' : '#dc3545';
                icon.src = isValid ? './assets/icons/check-green.svg' : 'assets/icons/close-x-red.svg';
            }
        }

        function updateSubmitButton(isValid) {
            const termsChecked = termsCheckbox.checked;
            const allValid = isValid && termsChecked;
            createAccountBtn.disabled = !allValid;
        }

        // Event listeners
        passwordInput.addEventListener('input', updatePasswordValidation);
        termsCheckbox.addEventListener('change', () => updateSubmitButton(
            lengthParam.style.color === 'rgb(40, 167, 69)' &&
            numberParam.style.color === 'rgb(40, 167, 69)' &&
            specialCharParam.style.color === 'rgb(40, 167, 69)'
        ));
        
        // Validate on any input change
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                const passwordValid = [
                    lengthParam.style.color === 'rgb(40, 167, 69)',
                    numberParam.style.color === 'rgb(40, 167, 69)',
                    specialCharParam.style.color === 'rgb(40, 167, 69)'
                ].every(valid => valid);
                
                updateSubmitButton(passwordValid);
            });
        });

        // Initial validation
        validatePassword();