document.addEventListener('DOMContentLoaded', () => {
  const emailInputs = document.querySelectorAll('input[type="email"]');
  const phoneInput = document.querySelector('input[name="phone"]');
  const passwordInput = document.getElementById('password');
  const strengthBar = document.getElementById('passwordStrength');
  const hint = document.getElementById('passwordHint');
  const registerBtn = document.querySelector('.card-back button[type="submit"]');

  let isPhoneValid = false;
  let isPasswordStrong = false;

  // Fonction pour vérifier si bouton peut être activé
  function updateRegisterButtonState() {
    if (isPhoneValid && isPasswordStrong) {
      registerBtn.disabled = false;
    } else {
      registerBtn.disabled = true;
    }
  }

  // Validation Email (optionnel pour border color)
  emailInputs.forEach(emailInput => {
    emailInput.addEventListener('input', () => {
      if (isValidEmail(emailInput.value.trim())) {
        emailInput.style.border = '2px solid #00ff88';
      } else {
        emailInput.style.border = '2px solid #ff4d4d';
      }
    });
  });

  // Validation Phone
  if (phoneInput) {
    phoneInput.addEventListener('input', () => {
      phoneInput.value = phoneInput.value.replace(/\D/g, '');
      if (phoneInput.value.length > 8) {
        phoneInput.value = phoneInput.value.slice(0, 8);
      }
      if (isValidPhone(phoneInput.value)) {
        phoneInput.style.border = '2px solid #00ff88';
        isPhoneValid = true;
      } else {
        phoneInput.style.border = '2px solid #ff4d4d';
        isPhoneValid = false;
      }
      updateRegisterButtonState();
    });
  }

  // Validation Password
  if (passwordInput) {
    passwordInput.addEventListener('input', () => {
      const value = passwordInput.value;
      const missing = [];

      if (!/[A-Z]/.test(value)) missing.push("une majuscule");
      if (!/[a-z]/.test(value)) missing.push("une minuscule");
      if (!/[0-9]/.test(value)) missing.push("un chiffre");
      if (!/[ !@#$%^&*(),.?\":{}|<>]/.test(value)) missing.push("un caractère spécial");
      if (value.length < 8) missing.push("8 caractères minimum");

      if (value.length === 0) {
        hint.textContent = "";
        strengthBar.style.width = "0%";
        isPasswordStrong = false;
      } else if (missing.length === 0) {
        hint.textContent = "✅ Mot de passe fort";
        hint.className = "password-hint success";
        strengthBar.style.width = "100%";
        strengthBar.style.backgroundColor = "#00ff88";
        isPasswordStrong = true;
      } else {
        hint.textContent = "❌ Il manque : " + missing.join(", ");
        hint.className = "password-hint error";
        strengthBar.style.width = "50%";
        strengthBar.style.backgroundColor = "#ff4d4d";
        isPasswordStrong = false;
      }
      updateRegisterButtonState();
    });
  }

  // Gestion succès après inscription
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('registered') === '1') {
    document.getElementById('reg-log').checked = false;
    const alert = document.createElement('div');
    alert.className = 'alert alert-success';
    alert.textContent = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
    document.querySelector('.card-front .section.text-center').prepend(alert);
  }
});

// Fonctions Utilitaires
const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
const isValidPhone = (phone) => /^\d{8}$/.test(phone);
const isStrongPassword = (password) => /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{8,}$/.test(password);


