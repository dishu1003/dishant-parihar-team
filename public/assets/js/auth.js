/**
 * Authentication Forms Logic
 *
 * Handles client-side logic for login, registration, and OTP verification forms.
 */

/**
 * Displays a message in the form's message container.
 * @param {string} formId - The ID of the form.
 * @param {string} message - The message to display.
 * @param {string} type - 'success' or 'error'.
 */
function showFormMessage(formId, message, type) {
    const errorContainer = document.querySelector(`#${formId} .error-message`);
    const successContainer = document.querySelector(`#${formId} .success-message`);

    // Reset both
    if (errorContainer) {
        errorContainer.textContent = '';
        errorContainer.style.display = 'none';
    }
    if (successContainer) {
        successContainer.textContent = '';
        successContainer.style.display = 'none';
    }

    if (type === 'error' && errorContainer) {
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
    } else if (type === 'success' && successContainer) {
        successContainer.textContent = message;
        successContainer.style.display = 'block';
    }
}


function handleLoginForm() {
    const form = document.getElementById('login-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = form.email.value;
        const password = form.password.value;

        try {
            const data = await window.apiFetch('/api/auth/login.php', {
                method: 'POST',
                body: { email, password }
            });

            if (data.success) {
                window.location.href = '/views/auth/verify_otp.php';
            }
        } catch (error) {
            showFormMessage('login-form', error.message, 'error');
        }
    });
}

function handleRegisterForm() {
    const form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            name: form.name.value,
            email: form.email.value,
            password: form.password.value,
            phone: form.phone.value,
            city: form.city.value,
        };

        try {
            const data = await window.apiFetch('/api/auth/register.php', {
                method: 'POST',
                body: formData
            });

            if (data.success) {
                showFormMessage('register-form', data.message, 'success');
                setTimeout(() => {
                    window.location.href = '/views/auth/login.php';
                }, 2000);
            }
        } catch (error) {
            showFormMessage('register-form', error.message, 'error');
        }
    });
}

function handleOtpForm() {
    const form = document.getElementById('otp-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const otp = form.otp.value;

        try {
            const data = await window.apiFetch('/api/auth/verify_otp.php', {
                method: 'POST',
                body: { otp }
            });

            if (data.success) {
                // On success, the API doesn't know the role, so redirect to a generic place
                // which will then redirect correctly based on role.
                window.location.href = '/';
            }
        } catch (error) {
            showFormMessage('otp-form', error.message, 'error');
        }
    });

    const resendBtn = document.getElementById('resend-otp-btn');
    if (resendBtn) {
        resendBtn.addEventListener('click', async () => {
            resendBtn.disabled = true;
            resendBtn.textContent = 'Sending...';

            try {
                const data = await window.apiFetch('/api/auth/send_otp.php', { method: 'POST' });
                if (data.success) {
                    showFormMessage('otp-form', data.message, 'success');
                }
            } catch (error) {
                showFormMessage('otp-form', error.message, 'error');
            } finally {
                setTimeout(() => {
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend OTP';
                }, 60000); // 60-second cooldown
            }
        });
    }
}


/**
 * Initializes all authentication form handlers.
 */
export function initAuthForms() {
    handleLoginForm();
    handleRegisterForm();
    handleOtpForm();
}
