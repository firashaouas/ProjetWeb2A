<?php
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$successMessage = '';
$demandeId = $_GET['id'] ?? null;
$demandeDetails = null;
$annonceDetails = null;

try {
    // Get the database connection via config
    $pdo = config::getConnexion();
    
    // Create controller instances
    $demandeController = new DemandeCovoiturageController();
    $annonceController = new AnnonceCovoiturageController($pdo);

    // Get demande details if ID is provided
    if ($demandeId) {
        $allDemandes = $demandeController->getAllDemandes();
        foreach ($allDemandes as $demande) {
            if ($demande['id_passager'] == $demandeId) {
                $demandeDetails = $demande;
                break;
            }
        }
        
        if (!$demandeDetails) {
            throw new Exception("Demande non trouvée");
        }
        
        // Get associated annonce details
        $annonces = $annonceController->getAllAnnonces();
        foreach ($annonces as $annonce) {
            if ($annonce->getIdConducteur() == $demandeDetails['id_conducteur']) {
                $annonceDetails = $annonce;
                break;
            }
        }
    }

    // Check if form is submitted
    if (isset($_POST['submit'])) {
        $data = $_POST;
        $data['id_passager'] = $demandeId;
        
        // Update the demande
        $success = $demandeController->updateDemande($data);
        
        if ($success) {
            // Set success message in session and redirect
            $_SESSION['success_message'] = "Demande mise à jour avec succès!";
            header("Location: ListPassager.php");
            exit();
        } else {
            throw new Exception("Échec de la mise à jour de la demande");
        }
    }
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}

// Check for success message in session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une demande - Click'N'go</title>
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
        }

        .preview img {
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 20px;
            margin-bottom: 25px;
            border: 3px solid #fff;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
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
        }

        .preview .price {
            font-size: 22px;
            font-weight: 500;
            color: #fff;
            background: linear-gradient(45deg, #ff8fa3, #c084fc);
            padding: 8px 20px;
            border-radius: 20px;
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

        .location-form input,
        .location-form textarea,
        .location-form select {
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

        .register-btn {
            width: 100%;
            padding: 16px;
            margin-top: 20px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
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

        .annonce-info {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #c084fc;
        }

        .annonce-info p {
            margin: 8px 0;
        }

        .annonce-info .title {
            font-weight: bold;
            color: #c084fc;
            font-size: 18px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .preview,
            .location-form {
                width: 100%;
            }
        }

        /* Popup styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 400px;
            padding: 30px;
            text-align: center;
        }

        .popup-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .popup-success .popup-icon {
            color: #4CAF50;
        }

        .popup-error .popup-icon {
            color: #e63946;
        }

        .popup-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .popup-message {
            margin-bottom: 1.5rem;
        }

        .popup-button {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            transition: all 0.3s ease;
        }

        .popup-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 143, 163, 0.3);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="preview">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e" alt="Car Image">
            <h3>Aperçu du trajet</h3>
            <?php if ($annonceDetails): ?>
                <div class="price"><?php echo htmlspecialchars(number_format($annonceDetails->getPrixEstime(), 2)) . ' Dt'; ?></div>
            <?php endif; ?>
        </div>
        <div class="location-form">
            <h2>Modifier ma demande</h2>
            
            <?php if ($annonceDetails): ?>
                <div class="annonce-info">
                    <p class="title">Détails du covoiturage</p>
                    <p><strong>Conducteur:</strong> <?php echo htmlspecialchars($annonceDetails->getPrenomConducteur() . ' ' . $annonceDetails->getNomConducteur()); ?></p>
                    <p><strong>Trajet:</strong> <?php echo htmlspecialchars($annonceDetails->getLieuDepart() . ' → ' . $annonceDetails->getLieuArrivee()); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($annonceDetails->getDateDepart()->format('d/m/Y H:i')); ?></p>
                    <p><strong>Places disponibles:</strong> <?php echo htmlspecialchars($annonceDetails->getNombrePlaces()); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="demandeForm">
                <input type="hidden" name="id_passager" value="<?php echo htmlspecialchars($demandeId); ?>">
                
                <label for="prenom_passager">Prénom :</label>
                <input type="text" id="prenom_passager" name="prenom_passager" 
                       value="<?php echo htmlspecialchars($demandeDetails['prenom_passager'] ?? ''); ?>" 
                       placeholder="Votre prénom" required>
                <div class="error" id="prenom_passager-error">Prénom invalide (minimum 3 caractères alphabétiques)</div>

                <label for="nom_passager">Nom :</label>
                <input type="text" id="nom_passager" name="nom_passager" 
                       value="<?php echo htmlspecialchars($demandeDetails['nom_passager'] ?? ''); ?>" 
                       placeholder="Votre nom" required>
                <div class="error" id="nom_passager-error">Nom invalide (minimum 3 caractères alphabétiques)</div>

                <label for="tel_passager">Téléphone :</label>
                <input type="tel" id="tel_passager" name="tel_passager" 
                       value="<?php echo htmlspecialchars($demandeDetails['tel_passager'] ?? ''); ?>" 
                       placeholder="Votre numéro (8 chiffres)" maxlength="8" required>
                <div class="error" id="tel_passager-error">Numéro de téléphone invalide (8 chiffres requis)</div>

                <label for="nbr_places_reservees">Nombre de places à réserver :</label>
                <input type="number" id="nbr_places_reservees" name="nbr_places_reservees" 
                       value="<?php echo htmlspecialchars($demandeDetails['nbr_places_reservees'] ?? 1); ?>" 
                       min="1" max="<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 4); ?>" 
                       placeholder="Nombre de places" required>
                <div class="error" id="nbr_places_reservees-error">Nombre de places invalide (minimum 1, maximum <?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 4); ?>)</div>

                <label for="moyen_paiement">Moyen de paiement :</label>
                <select id="moyen_paiement" name="moyen_paiement" required>
                    <option value="">Sélectionnez un moyen</option>
                    <option value="espèces" <?php echo ($demandeDetails['moyen_paiement'] ?? '') === 'espèces' ? 'selected' : ''; ?>>Espèces</option>
                    <option value="carte bancaire" <?php echo ($demandeDetails['moyen_paiement'] ?? '') === 'carte bancaire' ? 'selected' : ''; ?>>Carte bancaire</option>
                    <option value="virement" <?php echo ($demandeDetails['moyen_paiement'] ?? '') === 'virement' ? 'selected' : ''; ?>>Virement</option>
                </select>
                <div class="error" id="moyen_paiement-error">Veuillez sélectionner un moyen de paiement</div>

                <label for="message">Message (optionnel) :</label>
                <textarea id="message" name="message" placeholder="Message pour le conducteur"><?php echo htmlspecialchars($demandeDetails['message'] ?? ''); ?></textarea>

                <button type="submit" name="submit" class="register-btn">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                
                <a href="ListPassager.php" style="display: block; text-align: center; margin-top: 20px; color: #c084fc; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </form>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="success-popup" class="popup-overlay popup-success" style="display: none;">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="popup-title">Succès!</h3>
            <div class="popup-message" id="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <button onclick="hideSuccessPopup()" class="popup-button">Fermer</button>
        </div>
    </div>

    <!-- Error Popup -->
    <div id="error-popup" class="popup-overlay popup-error" style="display: none;">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="popup-title">Erreur</h3>
            <div class="popup-message" id="error-message"></div>
            <button onclick="hideErrorPopup()" class="popup-button">Fermer</button>
        </div>
    </div>

    <script>
        const form = document.getElementById('demandeForm');
        const successPopup = document.getElementById('success-popup');
        const errorPopup = document.getElementById('error-popup');
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        // Validation functions
        function validatePrenom() {
            const prenom = document.getElementById('prenom_passager');
            const error = document.getElementById('prenom_passager-error');
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
            const nom = document.getElementById('nom_passager');
            const error = document.getElementById('nom_passager-error');
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
            const max = parseInt(nombrePlaces.getAttribute('max'));
            if (!nombrePlaces.value || nombrePlaces.value < 1 || nombrePlaces.value > max) {
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

        // Phone number validation (8 digits only)
        document.getElementById('tel_passager').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);
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
        function showSuccessPopup(message) {
            successMessage.textContent = message || 'Opération réussie!';
            successPopup.style.display = 'flex';
            setTimeout(() => {
                window.location.href = 'ListPassager.php';
            }, 2000); // Redirection après 2 secondes
        }

        function hideSuccessPopup() {
            successPopup.style.display = 'none';
        }

        function showErrorPopup(message) {
            errorMessage.innerHTML = message || 'Une erreur est survenue.';
            errorPopup.style.display = 'flex';
        }

        function hideErrorPopup() {
            errorPopup.style.display = 'none';
        }

        // Form submission validation
        form.addEventListener('submit', (e) => {
            if (!validateForm()) {
                e.preventDefault();
                showErrorPopup('Veuillez remplir tous les champs obligatoires correctement');
            }
        });

        // Show popups based on PHP validation
        window.addEventListener('DOMContentLoaded', () => {
            <?php if (!empty($successMessage)): ?>
                showSuccessPopup("<?php echo addslashes($successMessage); ?>");
            <?php endif; ?>
            
            <?php if (!empty($errorMessages)): ?>
                showErrorPopup("<?php echo addslashes(implode('<br>', $errorMessages)); ?>");
            <?php endif; ?>
        });
    </script>
</body>
</html>
