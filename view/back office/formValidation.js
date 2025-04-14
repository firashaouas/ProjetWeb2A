// formValidation.js

// Fonction pour afficher un message d'erreur sous un champ
function showError(input, message) {
    console.log(`Erreur affichée pour ${input.id}: ${message}`);
    const formGroup = input.closest('.form-group');
    let errorElement = formGroup.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('p');
        errorElement.className = 'error-message';
        errorElement.style.color = 'red';
        errorElement.style.fontSize = '12px';
        errorElement.style.marginTop = '5px';
        formGroup.appendChild(errorElement);
    }
    errorElement.textContent = message;
    input.classList.add('error');
}

// Fonction pour supprimer un message d'erreur
function clearError(input) {
    console.log(`Erreur supprimée pour ${input.id}`);
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
    input.classList.remove('error');
}

// Fonction pour valider un champ obligatoire
function validateRequired(input, message) {
    console.log(`Validation required pour ${input.id}: valeur = "${input.value}"`);
    if (!input.value.trim()) {
        showError(input, message);
        return false;
    }
    clearError(input);
    return true;
}

// Fonction pour valider un nom (lettres uniquement, pas de chiffres ni caractères spéciaux)
function validateName(input, message) {
    console.log(`Validation name pour ${input.id}: valeur = "${input.value}"`);
    const regex = /^[A-Za-z\s]+$/;
    if (!regex.test(input.value.trim())) {
        showError(input, message);
        return false;
    }
    clearError(input);
    return true;
}

// Fonction pour valider un nombre positif
function validatePositiveNumber(input, message) {
    console.log(`Validation positive number pour ${input.id}: valeur = "${input.value}"`);
    const value = parseFloat(input.value);
    if (isNaN(value) || value <= 0) {
        showError(input, message);
        return false;
    }
    clearError(input);
    return true;
}

// Fonction pour valider une date future
function validateFutureDate(input, message) {
    console.log(`Validation future date pour ${input.id}: valeur = "${input.value}"`);
    const selectedDate = new Date(input.value);
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    selectedDate.setHours(0, 0, 0, 0);
    if (selectedDate <= now) {
        showError(input, message);
        return false;
    }
    clearError(input);
    return true;
}

// Fonction pour valider l'image (obligatoire pour ajout, doit être un fichier image)
function validateRequiredImage(input, message) {
    console.log(`Validation required image pour ${input.id}: files = ${input.files.length}`);
    if (!input.files || input.files.length === 0) {
        showError(input, message);
        return false;
    }
    const file = input.files[0];
    const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!validImageTypes.includes(file.type)) {
        showError(input, 'Veuillez sélectionner une image valide (JPEG, PNG, GIF, WebP).');
        return false;
    }
    const maxSize = 5 * 1024 * 1024; // 5MB en octets
    if (file.size > maxSize) {
        showError(input, 'L\'image ne doit pas dépasser 5 Mo.');
        return false;
    }
    clearError(input);
    return true;
}

// Fonction principale de validation du formulaire
function validateForm(form) {
    let isValid = true;
    console.log("Validation du formulaire démarrée");

    const nameInput = form.querySelector('#activityName');
    const descriptionInput = form.querySelector('#activityDescription');
    const priceInput = form.querySelector('#activityPrice');
    const locationInput = form.querySelector('#activityLocation');
    const dateInput = form.querySelector('#activityDate');
    const categoryInput = form.querySelector('#activityCategory');
    const capacityInput = form.querySelector('#activityCapacity');
    const imageInput = form.querySelector('#imageFile');

    // Vérification de l'existence des champs
    if (!nameInput || !descriptionInput || !priceInput || !locationInput || !dateInput || !categoryInput || !capacityInput || !imageInput) {
        console.error("Un ou plusieurs champs n'ont pas été trouvés dans le formulaire");
        return false;
    }

    // Valider le nom (obligatoire et lettres uniquement)
    if (!validateRequired(nameInput, 'Le nom de l\'activité est requis.')) {
        isValid = false;
    }
    if (!validateName(nameInput, 'Le nom doit contenir uniquement des lettres (pas de chiffres ni de caractères spéciaux).')) {
        isValid = false;
    }

    // Valider la description (obligatoire)
    if (!validateRequired(descriptionInput, 'La description est requise.')) {
        isValid = false;
    }

    // Valider le prix (obligatoire et positif)
    if (!validateRequired(priceInput, 'Le prix est requis.')) {
        isValid = false;
    } else if (!validatePositiveNumber(priceInput, 'Le prix doit être un nombre positif.')) {
        isValid = false;
    }

    // Valider le lieu (obligatoire)
    if (!validateRequired(locationInput, 'Le lieu est requis.')) {
        isValid = false;
    }

    // Valider la date (obligatoire et future)
    if (!validateRequired(dateInput, 'La date est requise.')) {
        isValid = false;
    } else if (!validateFutureDate(dateInput, 'La date doit être dans le futur.')) {
        isValid = false;
    }

    // Valider la catégorie (obligatoire)
    if (!validateRequired(categoryInput, 'Veuillez sélectionner une catégorie.')) {
        isValid = false;
    }

    // Valider la capacité (obligatoire et positive)
    if (!validateRequired(capacityInput, 'La capacité est requise.')) {
        isValid = false;
    } else if (!validatePositiveNumber(capacityInput, 'La capacité doit être un nombre positif.')) {
        isValid = false;
    }

    // Valider l'image (obligatoire pour l'ajout)
    const isAddForm = form.closest('.add-activity-form').querySelector('h3').textContent.includes('Ajout');
    if (isAddForm) {
        if (!validateRequiredImage(imageInput, 'Une image est requise.')) {
            isValid = false;
        }
    } else {
        if (imageInput.files.length > 0 && !validateRequiredImage(imageInput, 'Une image valide est requise.')) {
            isValid = false;
        }
    }

    console.log(`Validation terminée, isValid: ${isValid}`);
    return isValid;
}

// Appliquer la validation sur les formulaires
document.addEventListener('DOMContentLoaded', () => {
    console.log("Script formValidation.js chargé");

    const forms = document.querySelectorAll('.add-activity-form form');
    if (forms.length === 0) {
        console.warn("Aucun formulaire trouvé avec le sélecteur '.add-activity-form form'");
        return;
    }

    forms.forEach(form => {
        console.log("Formulaire détecté, ajout des écouteurs d'événements");
        form.addEventListener('submit', (e) => {
            console.log("Événement submit déclenché");
            if (!validateForm(form)) {
                e.preventDefault();
                console.log("Soumission bloquée : formulaire invalide");
            } else {
                console.log("Soumission autorisée : formulaire valide");
            }
        });

        // Validation en temps réel pour certains champs
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                console.log(`Validation en temps réel pour ${input.id}`);
                if (input.id === 'activityName') {
                    validateRequired(input, 'Le nom de l\'activité est requis.');
                    validateName(input, 'Le nom doit contenir uniquement des lettres (pas de chiffres ni de caractères spéciaux).');
                } else if (input.id === 'activityDescription' || input.id === 'activityLocation' || input.id === 'activityCategory') {
                    validateRequired(input, `Ce champ est requis.`);
                } else if (input.id === 'activityPrice') {
                    validateRequired(input, 'Le prix est requis.');
                    validatePositiveNumber(input, 'Le prix doit être un nombre positif.');
                } else if (input.id === 'activityCapacity') {
                    validateRequired(input, 'La capacité est requise.');
                    validatePositiveNumber(input, 'La capacité doit être un nombre positif.');
                } else if (input.id === 'activityDate') {
                    validateRequired(input, 'La date est requise.');
                    validateFutureDate(input, 'La date doit être dans le futur.');
                } else if (input.id === 'imageFile') {
                    const isAddForm = form.closest('.add-activity-form').querySelector('h3').textContent.includes('Ajout');
                    if (isAddForm) {
                        validateRequiredImage(input, 'Une image est requise.');
                    } else if (input.files.length > 0) {
                        validateRequiredImage(input, 'Une image valide est requise.');
                    }
                }
            });
        });
    });
});
