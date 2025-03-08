/**
 * Fichier JavaScript pour les fonctionnalités d'authentification
 * À placer dans /public/assets/js/auth.js
 */

  // Vous pouvez ajouter du JavaScript personnalisé ici
  document.addEventListener('DOMContentLoaded', function() {
    // Exemple: validation de formulaire côté client
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
// Fonction pour basculer la visibilité du mot de passe
function togglePasswordVisibility(inputId, toggleBtnId) {
    const passwordInput = document.getElementById(inputId);
    const toggleBtn = document.getElementById(toggleBtnId);
    
    if (passwordInput && toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    }
}

// Fonction pour valider le formulaire de connexion
function validateLoginForm() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    let isValid = true;
    
    // Validation de l'email
    if (emailInput) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value)) {
            showError(emailInput, 'Veuillez entrer une adresse email valide');
            isValid = false;
        } else {
            clearError(emailInput);
        }
    }
    
    // Validation du mot de passe
    if (passwordInput && passwordInput.value.length < 6) {
        showError(passwordInput, 'Le mot de passe doit contenir au moins 6 caractères');
        isValid = false;
    } else if (passwordInput) {
        clearError(passwordInput);
    }
    
    return isValid;
}

// Fonction pour valider le formulaire d'inscription
function validateRegisterForm() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    let isValid = true;
    
    // Validation du nom
    if (nameInput && nameInput.value.trim() === '') {
        showError(nameInput, 'Veuillez entrer votre nom');
        isValid = false;
    } else if (nameInput) {
        clearError(nameInput);
    }
    
    // Validation de l'email
    if (emailInput) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value)) {
            showError(emailInput, 'Veuillez entrer une adresse email valide');
            isValid = false;
        } else {
            clearError(emailInput);
        }
    }
    
    // Validation du mot de passe
    if (passwordInput && passwordInput.value.length < 6) {
        showError(passwordInput, 'Le mot de passe doit contenir au moins 6 caractères');
        isValid = false;
    } else if (passwordInput) {
        clearError(passwordInput);
    }
    
    // Validation de la confirmation du mot de passe
    if (confirmPasswordInput && passwordInput && confirmPasswordInput.value !== passwordInput.value) {
        showError(confirmPasswordInput, 'Les mots de passe ne correspondent pas');
        isValid = false;
    } else if (confirmPasswordInput) {
        clearError(confirmPasswordInput);
    }
    
    return isValid;
}

// Fonction pour afficher une erreur
function showError(input, message) {
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup.querySelector('.invalid-feedback') || document.createElement('div');
    
    input.classList.add('is-invalid');
    
    if (!formGroup.querySelector('.invalid-feedback')) {
        errorElement.className = 'invalid-feedback';
        formGroup.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

// Fonction pour effacer une erreur
function clearError(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}

// Initialiser les fonctionnalités lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le toggle de visibilité du mot de passe
    togglePasswordVisibility('password', 'togglePassword');
    togglePasswordVisibility('confirmPassword', 'toggleConfirmPassword');
    
    // Ajouter la validation au formulaire de connexion
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            if (!validateLoginForm()) {
                event.preventDefault();
            }
        });
    }
    
    // Ajouter la validation au formulaire d'inscription
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            if (!validateRegisterForm()) {
                event.preventDefault();
            }
        });
    }
    
    // Ajouter la validation au formulaire de réinitialisation de mot de passe
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(event) {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            
            if (passwordInput && confirmPasswordInput && passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                showError(confirmPasswordInput, 'Les mots de passe ne correspondent pas');
            }
        });
    }
});
