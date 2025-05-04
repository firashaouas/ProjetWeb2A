<?php
// Include your configuration and controller files
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$successMessage = '';
$annonceId = $_GET['id'] ?? null;
$annonceDetails = null;

try {
    // Get the database connection via config
    $pdo = config::getConnexion();
    
    // Create a new instance of the Demande controller
    $controller = new DemandeCovoiturageController();

    // Get announcement details from Annonce controller if ID is provided
    if ($annonceId) {
        $annonceController = new AnnonceCovoiturageController($pdo);
        $annonceDetails = $annonceController->getAnnonceById($annonceId); // Use getAnnonceById instead
        
        if (!$annonceDetails) {
            throw new Exception("Annonce non trouvée pour l'ID $annonceId.");
        }

        // Check if the announcement is still available for reservation
        $currentDateTime = new DateTime();
        $dateDepart = $annonceDetails->getDateDepart();
        if ($annonceDetails->getStatus() !== 'disponible' || $dateDepart < $currentDateTime) {
            throw new Exception("Cette annonce n'est plus disponible pour la réservation (statut: " . $annonceDetails->getStatus() . ", date de départ: " . $dateDepart->format('Y-m-d H:i:s') . ").");
        }
    } else {
        throw new Exception("Erreur: Aucun ID d'annonce fourni dans l'URL. Utilisez ?id=[ID] dans l'URL.");
    }

    // Check if form is submitted
    if (isset($_POST['submit'])) {
        $data = $_POST;
        $data['id_conducteur'] = $annonceId;
        $successMessage = $controller->reserverAnnonce($data);
    }
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un covoiturage - Click'N'go</title>
    <link rel="stylesheet" href="/clickngo/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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

        .preview .details {
            font-size: 16px;
            color: #666;
            text-align: center;
            z-index: 1;
        }

        .preview .details p {
            margin: 5px 0;
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
            margin-top: 10px;
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
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .location-form button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .location-form button:hover::before {
            left: 100%;
        }

        .location-form button:hover {
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
            <h3>Détails du covoiturage</h3>
            <?php if ($annonceDetails): ?>
                <div class="details">
                    <p><strong>Conducteur:</strong> <?php echo htmlspecialchars($annonceDetails->getPrenomConducteur() . ' ' . $annonceDetails->getNomConducteur()); ?></p>
                    <p><strong>Trajet:</strong> <?php echo htmlspecialchars($annonceDetails->getLieuDepart() . ' → ' . $annonceDetails->getLieuArrivee()); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($annonceDetails->getDateDepart()->format('d/m/Y H:i')); ?></p>
                    <p><strong>Places disponibles:</strong> <?php echo htmlspecialchars($annonceDetails->getNombrePlaces()); ?></p>
                </div>
                <div class="price"><?php echo htmlspecialchars(number_format($annonceDetails->getPrixEstime(), 2)) . ' Dt'; ?></div>
            <?php endif; ?>
        </div>
        <div class="location-form">
            <h2>Réserver un covoiturage</h2>
            <form method="POST" action="" id="reservationForm">
                <input type="hidden" name="id_conducteur" value="<?php echo htmlspecialchars($annonceId); ?>">

                <label for="prenom_passager">Prénom</label>
                <input type="text" id="prenom_passager" name="prenom_passager" placeholder="Votre prénom" value="<?php echo htmlspecialchars($_POST['prenom_passager'] ?? ''); ?>" required>
                <div class="error" id="prenom_passager-error">Prénom invalide (minimum 2 caractères alphabétiques)</div>

                <label for="nom_passager">Nom</label>
                <input type="text" id="nom_passager" name="nom_passager" placeholder="Votre nom" value="<?php echo htmlspecialchars($_POST['nom_passager'] ?? ''); ?>" required>
                <div class="error" id="nom_passager-error">Nom invalide (minimum 2 caractères alphabétiques)</div>

                <label for="tel_passager">Téléphone</label>
                <input type="tel" id="tel_passager" name="tel_passager" placeholder="Votre numéro (8 chiffres)" maxlength="8" value="<?php echo htmlspecialchars($_POST['tel_passager'] ?? ''); ?>" required>
                <div class="error" id="tel_passager-error">Numéro de téléphone invalide (8 chiffres requis)</div>

                <label for="nbr_places_reservees">Nombre de places à réserver</label>
                <input type="number" id="nbr_places_reservees" name="nbr_places_reservees" min="1" max="<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 8); ?>" placeholder="Nombre de places" value="<?php echo htmlspecialchars($_POST['nbr_places_reservees'] ?? ''); ?>" required>
                <div class="error" id="nbr_places_reservees-error">Nombre de places invalide (1-<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 8); ?>)</div>

                <label for="moyen_paiement">Moyen de paiement</label>
                <select id="moyen_paiement" name="moyen_paiement" required>
                    <option value="">Sélectionnez un moyen</option>
                    <option value="espèces" <?php echo (isset($_POST['moyen_paiement']) && $_POST['moyen_paiement'] === 'espèces') ? 'selected' : ''; ?>>Espèces</option>
                    <option value="carte bancaire" <?php echo (isset($_POST['moyen_paiement']) && $_POST['moyen_paiement'] === 'carte bancaire') ? 'selected' : ''; ?>>Carte bancaire</option>
                    <option value="virement" <?php echo (isset($_POST['moyen_paiement']) && $_POST['moyen_paiement'] === 'virement') ? 'selected' : ''; ?>>Virement</option>
                </select>
                <div class="error" id="moyen_paiement-error">Veuillez sélectionner un moyen de paiement</div>

                <label for="message">Message (optionnel)</label>
                <textarea id="message" name="message" placeholder="Message pour le conducteur"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>

                <button type="submit" name="submit" class="register-btn">Confirmer la réservation</button>
            </form>
        </div>
    </div>

    <!-- Success Popup -->
    <div class="popup" id="successPopup">
        <div class="icon">✔</div>
        <h3>Succès!</h3>
        <p id="success-message"><?php echo htmlspecialchars($successMessage); ?></p>
        <button onclick="redirectToList()">Fermer</button>
    </div>

    <!-- Error Popup -->
    <div class="popup error" id="errorPopup">
        <div class="icon">❌</div>
        <h3>Erreur</h3>
        <p id="error-message"><?php echo implode('<br>', array_map('htmlspecialchars', $errorMessages)); ?></p>
        <button onclick="closePopup()">Fermer</button>
    </div>

    <script>
        const form = document.getElementById('reservationForm');
        const successPopup = document.getElementById('successPopup');
        const errorPopup = document.getElementById('errorPopup');
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        // Validation functions
        function validatePrenom() {
            const prenom = document.getElementById('prenom_passager');
            const error = document.getElementById('prenom_passager-error');
            if (!prenom.value.match(/^[a-zA-ZÀ-ÿ -]{2,}$/)) {
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
            const nom = document.getElementById('nom_passager');
            const error = document.getElementById('nom_passager-error');
            if (!nom.value.match(/^[a-zA-ZÀ-ÿ -]{2,}$/)) {
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
            const tel = document.getElementById('tel_passager');
            const error = document.getElementById('tel_passager-error');
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

        function validateNombrePlaces() {
            const nombrePlaces = document.getElementById('nbr_places_reservees');
            const error = document.getElementById('nbr_places_reservees-error');
            const maxPlaces = parseInt(nombrePlaces.max);
            if (!nombrePlaces.value || nombrePlaces.value < 1 || nombrePlaces.value > maxPlaces) {
                error.style.display = 'block';
                nombrePlaces.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                nombrePlaces.classList.remove('invalid');
                return true;
            }
        }

        function validateMoyenPaiement() {
            const moyenPaiement = document.getElementById('moyen_paiement');
            const error = document.getElementById('moyen_paiement-error');
            if (!moyenPaiement.value) {
                error.style.display = 'block';
                moyenPaiement.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                moyenPaiement.classList.remove('invalid');
                return true;
            }
        }

        // Add real-time validation on input
        document.getElementById('prenom_passager').addEventListener('input', validatePrenom);
        document.getElementById('nom_passager').addEventListener('input', validateNom);
        document.getElementById('tel_passager').addEventListener('input', validateTel);
        document.getElementById('nbr_places_reservees').addEventListener('input', validateNombrePlaces);
        document.getElementById('moyen_paiement').addEventListener('change', validateMoyenPaiement);

        // Phone number validation (8 digits)
        document.getElementById('tel_passager').addEventListener('input', function() {
            const phoneRegex = /^[0-9]{0,8}$/;
            if (!phoneRegex.test(this.value)) {
                this.value = this.value.slice(0, -1);
            }
        });

        // Form submission validation
        function validateForm() {
            const isValid = [
                validatePrenom(),
                validateNom(),
                validateTel(),
                validateNombrePlaces(),
                validateMoyenPaiement()
            ].every(Boolean);
            return isValid;
        }

        // Popup functions
        function showSuccessPopup() {
            successPopup.classList.add('show');
        }

        function showErrorPopup() {
            errorPopup.classList.add('show');
        }

        function closePopup() {
            successPopup.classList.remove('show');
            errorPopup.classList.remove('show');
        }

        function redirectToList() {
            window.location.href = 'ListPassager.php';
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
            <?php elseif (!empty($errorMessages)): ?>
                showErrorPopup();
            <?php endif; ?>
        });
    </script>
</body>
</html>