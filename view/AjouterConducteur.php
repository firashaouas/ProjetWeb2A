<?php
// clickngo/view/AjouterConducteur.php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$successMessage = '';
$errorMessage = '';

if (isset($_POST['submit'])) {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);

    try {
        $message = $controller->ajouterAnnonce($_POST);
        $successMessage = $message ?: 'Annonce ajoutée avec succès !';
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire Conducteur - Click'N'go</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 30px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            max-width: 1100px;
            width: 90%;
            overflow: hidden;
            transform: translateY(0);
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .preview {
            width: 45%;
            background: linear-gradient(135deg, #fff0f5, #f0f7ff);
            color: #333;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><circle cx="10" cy="10" r="2" fill="%23ff8fa3"/><circle cx="90" cy="90" r="3" fill="%23c084fc"/><circle cx="50" cy="20" r="2" fill="%23ff8fa3"/><circle cx="80" cy="50" r="2" fill="%23c084fc"/></svg>') repeat;
            z-index: 0;
        }

        .preview::after {
            content: '✨';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            opacity: 0.5;
            z-index: 1;
        }

        .preview img {
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 20px;
            margin-bottom: 25px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            z-index: 1;
            border: 3px solid #fff;
        }

        .preview img:hover {
            transform: scale(1.08) rotate(1deg);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .preview h3 {
            font-family: 'Playfair Display', serif;
            font-size: 30px;
            margin: 10px 0;
            text-align: center;
            color: #2d2d2d;
            z-index: 1;
            position: relative;
        }

        .preview h3::after {
            content: '';
            display: block;
            width: 40px;
            height: 2px;
            background: #ff8fa3;
            margin: 8px auto;
            border-radius: 1px;
        }

        .preview .price {
            font-size: 22px;
            font-weight: 500;
            color: #fff;
            background: linear-gradient(45deg, #ff8fa3, #c084fc);
            padding: 8px 20px;
            border-radius: 20px;
            z-index: 1;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .preview .price:hover {
            transform: translateY(-2px);
        }

        .location-form {
            width: 55%;
            padding: 50px;
            background: #fff;
            transition: transform 0.3s ease;
        }

        .location-form:hover {
            transform: scale(1.02);
        }

        .location-form h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #2d2d2d;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .location-form h2::after {
            content: '';
            width: 60px;
            height: 3px;
            background: #ff8fa3;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .location-form label {
            display: block;
            margin: 20px 0 10px;
            font-weight: 500;
            color: #333;
            font-size: 16px;
        }

        .location-form input[type="text"],
        .location-form input[type="datetime-local"],
        .location-form input[type="tel"],
        .location-form input[type="number"],
        .location-form select,
        .location-form textarea {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            font-size: 16px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        .location-form input:focus,
        .location-form select:focus,
        .location-form textarea:focus {
            border-color: #ff8fa3;
            box-shadow: 0 0 0 3px rgba(255, 143, 163, 0.2);
            outline: none;
            background: #fff;
        }

        .location-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .location-form button {
            width: 100%;
            padding: 16px;
            margin-top: 20px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .register-btn {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 143, 163, 0.4);
        }

        .error {
            color: #e63946;
            font-size: 13px;
            margin-top: 5px;
            display: none;
        }

        .invalid {
            border-color: #e63946;
            box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.2);
        }

        /* Popup Styles */
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 1000;
            display: none;
            border: 2px solid #4CAF50;
        }

        .popup.show {
            display: block;
        }

        .popup .icon {
            font-size: 40px;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .popup h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .popup p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .popup button {
            padding: 10px 20px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .popup button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 143, 163, 0.3);
        }

        .popup.error {
            border: 2px solid #e63946;
        }

        .popup.error .icon {
            color: #e63946;
        }

        .popup.error button {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
        }

        .popup.error button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 143, 163, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 95%;
            }

            .preview,
            .location-form {
                width: 100%;
            }

            .preview img {
                width: 180px;
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3" alt="Car Image">
            <h3>Aperçu de l'annonce</h3>
            <div class="price" id="preview-prix_estime">-</div>
        </div>
        <div class="location-form">
            <h2>Proposer un covoiturage</h2>
            <form method="post" action="" id="conducteurForm">
                <label for="prenom_conducteur">Prénom Conducteur</label>
                <input type="text" name="prenom_conducteur" id="prenom_conducteur" value="<?php echo htmlspecialchars($_POST['prenom_conducteur'] ?? ''); ?>" required>
                <div class="error" id="prenom_conducteur-error">Prénom invalide (minimum 3 caractères alphabétiques)</div>

                <label for="nom_conducteur">Nom Conducteur</label>
                <input type="text" name="nom_conducteur" id="nom_conducteur" value="<?php echo htmlspecialchars($_POST['nom_conducteur'] ?? ''); ?>" required>
                <div class="error" id="nom_conducteur-error">Nom invalide (minimum 3 caractères alphabétiques)</div>

                <label for="tel_conducteur">Téléphone Conducteur</label>
                <input type="tel" name="tel_conducteur" id="tel_conducteur" value="<?php echo htmlspecialchars($_POST['tel_conducteur'] ?? ''); ?>" required>
                <div class="error" id="tel_conducteur-error">Numéro de téléphone invalide (8 chiffres requis)</div>

                <label for="type_voiture">Type de Voiture</label>
                <input type="text" name="type_voiture" id="type_voiture" value="<?php echo htmlspecialchars($_POST['type_voiture'] ?? ''); ?>" required>
                <div class="error" id="type_voiture-error">Type de voiture invalide (minimum 2 caractères alphabétiques)</div>

                <label for="lieu_depart">Lieu de Départ</label>
                <input type="text" name="lieu_depart" id="lieu_depart" value="<?php echo htmlspecialchars($_POST['lieu_depart'] ?? ''); ?>" required>
                <div class="error" id="lieu_depart-error">Le lieu de départ est requis (minimum 2 caractères)</div>

                <label for="lieu_arrivee">Lieu d'Arrivée</label>
                <input type="text" name="lieu_arrivee" id="lieu_arrivee" value="<?php echo htmlspecialchars($_POST['lieu_arrivee'] ?? ''); ?>" required>
                <div class="error" id="lieu_arrivee-error">Le lieu d'arrivée est requis (minimum 2 caractères)</div>

                <label for="date_depart">Date de Départ</label>
                <input type="datetime-local" name="date_depart" id="date_depart" value="<?php echo htmlspecialchars($_POST['date_depart'] ?? ''); ?>" required>
                <div class="error" id="date_depart-error">La date de départ doit être dans le futur</div>

                <label for="nombre_places">Nombre de Places</label>
                <input type="number" name="nombre_places" id="nombre_places" min="1" max="4" value="<?php echo htmlspecialchars($_POST['nombre_places'] ?? ''); ?>" required>
                <div class="error" id="nombre_places-error">Nombre de places invalide (1-4)</div>

                <label for="prix_estime">Prix Estimé (€)</label>
                <input type="number" name="prix_estime" id="prix_estime" step="0.01" value="<?php echo htmlspecialchars($_POST['prix_estime'] ?? ''); ?>" required>
                <div class="error" id="prix_estime-error">Prix estimé invalide (doit être positif)</div>

                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Description (optionnel)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

                <button type="submit" name="submit" class="register-btn">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Success Popup -->
    <div class="popup" id="successPopup">
        <div class="icon">✔</div>
        <h3>Succès!</h3>
        <p id="successMessage"><?php echo htmlspecialchars($successMessage); ?></p>
        <button onclick="redirectToDisplay()">Fermer</button>
    </div>

    <!-- Error Popup -->
    <div class="popup error" id="errorPopup">
        <div class="icon">❌</div>
        <h3>Erreur</h3>
        <p id="errorMessage"><?php echo htmlspecialchars($errorMessage); ?></p>
        <button onclick="closePopup()">Fermer</button>
    </div>

    <script>
        const form = document.getElementById('conducteurForm');
        const prixInput = document.getElementById('prix_estime');
        const prixPreview = document.getElementById('preview-prix_estime');
        const successPopup = document.getElementById('successPopup');
        const errorPopup = document.getElementById('errorPopup');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');

        // Update price preview
        prixInput.addEventListener('input', () => {
            prixPreview.textContent = prixInput.value ? `${prixInput.value} €` : '-';
            validatePrixEstime();
        });

        // Validation functions
        function validatePrenom() {
            const prenom = document.getElementById('prenom_conducteur');
            const error = document.getElementById('prenom_conducteur-error');
            if (!prenom.value.match(/^[a-zA-ZÀ-ÿ -]{3,}$/)) {
                error.style.display = 'block';
                prenom.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                prenom.classList.remove('invalid');
                return true;
            }
        }

        function validateNom() {
            const nom = document.getElementById('nom_conducteur');
            const error = document.getElementById('nom_conducteur-error');
            if (!nom.value.match(/^[a-zA-ZÀ-ÿ -]{3,}$/)) {
                error.style.display = 'block';
                nom.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                nom.classList.remove('invalid');
                return true;
            }
        }

        function validateTel() {
            const tel = document.getElementById('tel_conducteur');
            const error = document.getElementById('tel_conducteur-error');
            if (!tel.value.match(/^[0-9]{8}$/)) {
                error.style.display = 'block';
                tel.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                tel.classList.remove('invalid');
                return true;
            }
        }

        function validateTypeVoiture() {
            const typeVoiture = document.getElementById('type_voiture');
            const error = document.getElementById('type_voiture-error');
            if (!typeVoiture.value.match(/^[a-zA-ZÀ-ÿ -]{2,}$/)) {
                error.style.display = 'block';
                typeVoiture.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                typeVoiture.classList.remove('invalid');
                return true;
            }
        }

        function validateLieuDepart() {
            const lieuDepart = document.getElementById('lieu_depart');
            const error = document.getElementById('lieu_depart-error');
            if (!lieuDepart.value || lieuDepart.value.trim().length < 2) {
                error.style.display = 'block';
                lieuDepart.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                lieuDepart.classList.remove('invalid');
                return true;
            }
        }

        function validateLieuArrivee() {
            const lieuArrivee = document.getElementById('lieu_arrivee');
            const error = document.getElementById('lieu_arrivee-error');
            if (!lieuArrivee.value || lieuArrivee.value.trim().length < 2) {
                error.style.display = 'block';
                lieuArrivee.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                lieuArrivee.classList.remove('invalid');
                return true;
            }
        }

        function validateDateDepart() {
            const dateDepart = document.getElementById('date_depart');
            const error = document.getElementById('date_depart-error');
            const now = new Date();
            const selectedDate = new Date(dateDepart.value);
            if (!dateDepart.value || selectedDate <= now) {
                error.style.display = 'block';
                dateDepart.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                dateDepart.classList.remove('invalid');
                return true;
            }
        }

        function validateNombrePlaces() {
            const nombrePlaces = document.getElementById('nombre_places');
            const error = document.getElementById('nombre_places-error');
            if (!nombrePlaces.value || nombrePlaces.value < 1 || nombrePlaces.value > 4) {
                error.style.display = 'block';
                nombrePlaces.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                nombrePlaces.classList.remove('invalid');
                return true;
            }
        }

        function validatePrixEstime() {
            const prixEstime = document.getElementById('prix_estime');
            const error = document.getElementById('prix_estime-error');
            if (!prixEstime.value || prixEstime.value <= 0) {
                error.style.display = 'block';
                prixEstime.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                prixEstime.classList.remove('invalid');
                return true;
            }
        }

        // Add real-time validation on input
        document.getElementById('prenom_conducteur').addEventListener('input', validatePrenom);
        document.getElementById('nom_conducteur').addEventListener('input', validateNom);
        document.getElementById('tel_conducteur').addEventListener('input', validateTel);
        document.getElementById('type_voiture').addEventListener('input', validateTypeVoiture);
        document.getElementById('lieu_depart').addEventListener('input', validateLieuDepart);
        document.getElementById('lieu_arrivee').addEventListener('input', validateLieuArrivee);
        document.getElementById('date_depart').addEventListener('input', validateDateDepart);
        document.getElementById('nombre_places').addEventListener('input', validateNombrePlaces);
        document.getElementById('prix_estime').addEventListener('input', validatePrixEstime);

        // Form submission validation (client-side only)
        function validateForm() {
            const isValid = [
                validatePrenom(),
                validateNom(),
                validateTel(),
                validateTypeVoiture(),
                validateLieuDepart(),
                validateLieuArrivee(),
                validateDateDepart(),
                validateNombrePlaces(),
                validatePrixEstime()
            ].every(Boolean);
            return isValid;
        }

        // Popup functions
        function showSuccessPopup() {
            successPopup.classList.add('show');
            setTimeout(() => {
                redirectToDisplay();
            }, 2000); // Redirection après 2 secondes
        }

        function showErrorPopup() {
            errorPopup.classList.add('show');
        }

        function closePopup() {
            successPopup.classList.remove('show');
            errorPopup.classList.remove('show');
        }

        function redirectToDisplay() {
            window.location.href = 'DisplayConducteur.php';
        }

        // Form submission validation (client-side only)
        form.addEventListener('submit', (e) => {
            if (!validateForm()) {
                e.preventDefault();
                showErrorPopup();
                errorMessage.textContent = 'Veuillez remplir tous les champs obligatoires correctement';
            }
        });

        // Show popup if PHP processed the form
        window.addEventListener('DOMContentLoaded', () => {
            <?php if (!empty($successMessage)): ?>
                showSuccessPopup();
            <?php elseif (!empty($errorMessage)): ?>
                showErrorPopup();
            <?php endif; ?>
        });
    </script>
</body>
</html>