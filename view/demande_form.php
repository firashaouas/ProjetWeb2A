<?php
// Include your configuration and controller files
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';
require_once '../Controller/AnnonceCovoiturageController.php'; // Add this line

$errorMessages = [];
$successMessage = '';
$annonceId = $_GET['id'] ?? null;
$annonceDetails = null;

try {
    // Get the database connection via config
    $pdo = config::getConnexion();
    
    // Create a new instance of the Demande controller
    $controller = new DemandeCovoiturageController($pdo);

    // Check if form is submitted
    if (isset($_POST['submit'])) {
        $data = $_POST;
        $data['id_conducteur'] = $annonceId;
        $successMessage = $controller->reserverAnnonce($data);
    }

    // Get announcement details from Annonce controller if ID is provided
    if ($annonceId) {
        $annonceController = new AnnonceCovoiturageController($pdo);
        $annonces = $annonceController->getAllAnnonces();
        foreach ($annonces as $annonce) {
            if ($annonce->getIdConducteur() == $annonceId) {
                $annonceDetails = $annonce;
                break;
            }
        }
    }

    if (!$annonceDetails) {
        throw new Exception("Annonce non trouvée");
    }
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>


<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Réserver un covoiturage</title>
<link rel="stylesheet" href="/clickngo/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
<style>
    /* === FOND D'ÉCRAN === */
    .form-background {
      background-image: url('/clickngo/public/images/re.jpeg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      min-height: calc(100vh - 200px);
    }

    /* === GRADIENT PERSONNALISÉ === */
    .elsa-gradient-primary {
      background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    }

    .elsa-gradient-primary-hover:hover {
      background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    }

    /* === STYLE GÉNÉRAL DU FORMULAIRE === */
    .form-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 0;
    }

    .form-wrapper {
      background-color: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 15px;
      width: 450px;
      border: 2px solid #be3cf0;
      transition: 0.3s;
      margin: 20px 0;
    }

    .form-wrapper:hover {
      border-color: #ff50aa;
      box-shadow: 0 0 15px #be3cf0, 0 0 30px #dc46d7, 0 0 45px #ff50aa;
    }

    .form-wrapper h2 {
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      color: #333;
      margin-bottom: 20px;
    }

    .form-wrapper label {
      font-size: 14px;
      color: #555;
      font-weight: 500;
    }

    .form-wrapper input,
    .form-wrapper textarea,
    .form-wrapper select {
      width: 100%;
      padding: 10px;
      margin: 6px 0 12px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      transition: 0.3s;
    }

    .form-wrapper input:hover,
    .form-wrapper textarea:hover,
    .form-wrapper select:hover {
      border-color: #ffadc8;
      box-shadow: 0 0 6px #ffa3c2aa;
    }

    .form-wrapper button {
      padding: 12px 24px;
      font-size: 16px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      display: block;
      margin: 20px auto 0;
      color: white;
      transition: 0.3s ease;
      background-color: #be3cf0;
      font-weight: 600;
      width: 100%;
    }

    .form-wrapper button:hover {
      background-color: #ff50aa;
      transform: translateY(-2px);
    }

    .annonce-info {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      border-left: 4px solid #be3cf0;
    }

    .annonce-info p {
      margin: 5px 0;
    }

    .annonce-info .title {
      font-weight: bold;
      color: #be3cf0;
    }

    .error-message {
      color: red;
      font-size: 12px;
      display: none;
      margin-top: -10px;
      margin-bottom: 8px;
    }

    .error-message.animate {
      animation: shake 0.3s ease;
      display: block !important;
    }

    @keyframes shake {
      0% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      50% { transform: translateX(5px); }
      75% { transform: translateX(-5px); }
      100% { transform: translateX(0); }
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
      border-radius: 0.5rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 400px;
      padding: 1.5rem;
      text-align: center;
    }

    .popup-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
    }

    .popup-success .popup-icon {
      color: #10b981;
    }

    .popup-error .popup-icon {
      color: #ef4444;
    }

    .popup-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .popup-message {
      margin-bottom: 1.5rem;
    }

    .popup-button {
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
      font-weight: 500;
      cursor: pointer;
    }

    .popup-success .popup-button {
      background-color: #10b981;
      color: white;
    }

    .popup-error .popup-button {
      background-color: #ef4444;
      color: white;
    }
</style>
</head>
<body>   

<!-- NAVBAR -->
<nav class="absolute top-0 left-0 w-full z-50 p-4">
    <div class="flex items-center justify-center max-w-7xl mx-auto">
        <div class="flex space-x-8 text-lg font-bold text-black relative">
            <a href="#home" class="hover:text-[#be3cf0]">Accueil</a>
            <a href="#about" class="hover:text-[#be3cf0]">À propos</a>
            <div class="group relative">
                <button class="hover:text-[#be3cf0] font-bold text-lg">
                    Nos Détails ▾
                </button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Nos trajets</a>
                    <a href="#top-conducteurs" class="block px-4 py-2 hover:bg-gray-100">Top Conducteurs</a>
                    <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Liste des Conducteurs</a>
                    <a href="ListPassager.php" class="block px-4 py-2 hover:bg-gray-100">Liste des Passagers</a>
                </div>
            </div>
            
            <!-- LISTE DÉROULANTE SERVICES -->
            <div class="relative group">
                <button class="hover:text-[#be3cf0]">Services ▾</button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="/clickngo/view/trouver.php" class="block px-4 py-2 hover:bg-gray-100">Trouver un covoiturage</a>
                    <a href="/clickngo/view/proposer.php" class="block px-4 py-2 hover:bg-gray-100">Proposer un covoiturage</a>
                </div>
            </div>
            
            <!-- LISTE DÉROULANTE CONTACT -->
            <div class="relative group">
                <button class="hover:text-[#be3cf0]">Contact ▾</button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[180px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="/clickngo/view/reclamation.php" class="block px-4 py-2 hover:bg-gray-100">Réclamation</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<br>
<br>
<br>

<!-- FORM SECTION -->
<div class="form-background">
  <div class="form-container">
    <div class="form-wrapper">
      <h2>Réserver un covoiturage</h2>
      
      <?php if ($annonceDetails): ?>
        <div class="annonce-info">
          <p class="title">Détails du covoiturage</p>
          <p><strong>Conducteur:</strong> <?php echo htmlspecialchars($annonceDetails->getPrenomConducteur() . ' ' . $annonceDetails->getNomConducteur()); ?></p>
          <p><strong>Trajet:</strong> <?php echo htmlspecialchars($annonceDetails->getLieuDepart() . ' → ' . $annonceDetails->getLieuArrivee()); ?></p>
          <p><strong>Date:</strong> <?php echo htmlspecialchars($annonceDetails->getDateDepart()->format('d/m/Y H:i')); ?></p>
          <p><strong>Prix par place:</strong> <?php echo htmlspecialchars(number_format($annonceDetails->getPrixEstime(), 2)) . ' €'; ?></p>
          <p><strong>Places disponibles:</strong> <?php echo htmlspecialchars($annonceDetails->getNombrePlaces()); ?></p>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="id_conducteur" value="<?php echo htmlspecialchars($annonceId); ?>">
        
        <div class="mb-4">
          <label for="prenom_passager">Prénom :</label>
          <input type="text" id="prenom_passager" name="prenom_passager" placeholder="Votre prénom" required>
        </div>

        <div class="mb-4">
          <label for="nom_passager">Nom :</label>
          <input type="text" id="nom_passager" name="nom_passager" placeholder="Votre nom" required>
        </div>

        <div class="mb-4">
          <label for="tel_passager">Téléphone :</label>
          <input type="text" id="tel_passager" name="tel_passager" placeholder="Votre numéro (8 chiffres)" maxlength="8" required>
        </div>

        <div class="mb-4">
          <label for="nbr_places_reservees">Nombre de places à réserver :</label>
          <input type="number" id="nbr_places_reservees" name="nbr_places_reservees" min="1" max="<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 8); ?>" placeholder="Nombre de places" required>
        </div>

        <div class="mb-4">
          <label for="moyen_paiement">Moyen de paiement :</label>
          <select id="moyen_paiement" name="moyen_paiement" required>
            <option value="">Sélectionnez un moyen</option>
            <option value="espèces">Espèces</option>
            <option value="carte bancaire">Carte bancaire</option>
            <option value="virement">Virement</option>
          </select>
        </div>

        <div class="mb-4">
          <label for="message">Message (optionnel) :</label>
          <textarea id="message" name="message" rows="4" placeholder="Message pour le conducteur"></textarea>
        </div>

        <button type="submit" name="submit" class="elsa-gradient-primary hover:elsa-gradient-primary-hover">Confirmer la réservation</button>
      </form>
    </div>
  </div>
</div>
<!-- Success Popup -->
<div id="success-popup" class="popup-overlay popup-success <?php echo empty($successMessage) ? 'hidden' : ''; ?>">
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
<div id="error-popup" class="popup-overlay popup-error <?php echo empty($errorMessages) ? 'hidden' : ''; ?>">
  <div class="popup-content">
    <div class="popup-icon">
      <i class="fas fa-exclamation-circle"></i>
    </div>
    <h3 class="popup-title">Erreur</h3>
    <div class="popup-message" id="error-message">
      <?php foreach ($errorMessages as $error): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
      <?php endforeach; ?>
    </div>
    <button onclick="hideErrorPopup()" class="popup-button">Fermer</button>
  </div>
</div>

<script>
    function showSuccessPopup(message) {
        document.getElementById('success-message').textContent = message;
        document.getElementById('success-popup').classList.remove('hidden');
    }
    
    function hideSuccessPopup() {
        document.getElementById('success-popup').classList.add('hidden');
    }
    
    function showErrorPopup(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('error-popup').classList.remove('hidden');
    }
    
    function hideErrorPopup() {
        document.getElementById('error-popup').classList.add('hidden');
    }

    // Phone number validation (8 digits)
    document.getElementById('tel_passager').addEventListener('input', function() {
        const phoneRegex = /^[0-9]{0,8}$/;
        if (!phoneRegex.test(this.value)) {
            this.value = this.value.slice(0, -1);
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let isValid = true;
        const inputs = this.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error-highlight');
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message animate';
                errorMsg.textContent = 'Ce champ est requis';
                input.parentNode.insertBefore(errorMsg, input.nextSibling);
            } else {
                input.classList.remove('error-highlight');
                const errorMsg = input.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.remove();
                }
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showErrorPopup('Veuillez remplir tous les champs obligatoires');
        }
    });

    // Show popups based on PHP validation
    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($successMessage)): ?>
            showSuccessPopup("<?php echo addslashes($successMessage); ?>");
        <?php elseif (!empty($errorMessages)): ?>
            showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
        <?php endif; ?>
    });
</script>
</body>
</html>