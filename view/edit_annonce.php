<?php
// Start session at the very beginning
session_start();

require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$annonce = null;

// Get the database connection
$pdo = config::getConnexion();
$controller = new AnnonceCovoiturageController($pdo);

// Get the announcement ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Load the announcement data
if ($id > 0) {
    try {
        $annonce = $controller->getAnnonceById($id);
        if (!$annonce) {
            $errorMessages[] = "Annonce non trouvée";
        }
    } catch (Exception $e) {
        $errorMessages = explode('<br>', $e->getMessage());
    }
} else {
    $errorMessages[] = "ID d'annonce invalide";
}

// Handle form submission
if (isset($_POST['submit'])) {
    try {
        // Add the ID to the form data for update
        $_POST['id_conducteur'] = $id;
        
        // Attempt to update the announcement
        $success = $controller->updateAnnonce($_POST);
        
        if ($success) {
            // Store success message in session and redirect
            $_SESSION['success_message'] = "Annonce mise à jour avec succès!";
            header("Location: ListConducteurs.php");
            exit();
        }
    } catch (Exception $e) {
        $errorMessages = explode('<br>', $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Modifier une annonce de covoiturage</title>
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

    /* Error highlighting */
    .error-highlight {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 1px #ef4444;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: -0.5rem;
        margin-bottom: 0.5rem;
        display: none;
    }

    .error-message.animate {
        display: block;
        animation: shake 0.3s ease;
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
      <h2>Modifier l'annonce de covoiturage</h2>
      
      <?php if (!empty($errorMessages) && !isset($_POST['submit'])): ?>
        <div class="popup-overlay popup-error">
          <div class="popup-content">
            <div class="popup-icon">
              <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="popup-title">Erreur</h3>
            <div class="popup-message">
              <?php foreach ($errorMessages as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
              <?php endforeach; ?>
            </div>
            <button onclick="window.location.href='/clickngo/view/ListConducteurs.php'" class="popup-button">Retour</button>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($annonce): ?>
      <form method="POST" action="">
        <input type="hidden" name="id_conducteur" value="<?php echo $annonce->getIdConducteur(); ?>">
        
        <div class="mb-4">
          <label for="prenom_conducteur">Prénom :</label>
          <input type="text" id="prenom_conducteur" name="prenom_conducteur" 
                 value="<?php echo htmlspecialchars($annonce->getPrenomConducteur()); ?>" 
                 placeholder="Votre prénom" required>
        </div>

        <div class="mb-4">
          <label for="nom_conducteur">Nom :</label>
          <input type="text" id="nom_conducteur" name="nom_conducteur" 
                 value="<?php echo htmlspecialchars($annonce->getNomConducteur()); ?>" 
                 placeholder="Votre nom" required>
        </div>

        <div class="mb-4">
          <label for="tel_conducteur">Téléphone :</label>
          <input type="text" id="tel_conducteur" name="tel_conducteur" 
                 value="<?php echo htmlspecialchars($annonce->getTelConducteur()); ?>" 
                 placeholder="Votre numéro de téléphone" required>
        </div>

        <div class="mb-4">
          <label for="date_depart">Date et heure de départ :</label>
          <input type="datetime-local" id="date_depart" name="date_depart" 
                 value="<?php echo htmlspecialchars($annonce->getDateDepart()->format('Y-m-d\TH:i')); ?>" 
                 required>
        </div>

        <div class="mb-4">
          <label for="lieu_depart">Lieu de départ :</label>
          <input type="text" id="lieu_depart" name="lieu_depart" 
                 value="<?php echo htmlspecialchars($annonce->getLieuDepart()); ?>" 
                 placeholder="Point de départ" required>
        </div>

        <div class="mb-4">
          <label for="lieu_arrivee">Destination :</label>
          <input type="text" id="lieu_arrivee" name="lieu_arrivee" 
                 value="<?php echo htmlspecialchars($annonce->getLieuArrivee()); ?>" 
                 placeholder="Destination" required>
        </div>

        <div class="mb-4">
          <label for="nombre_places">Nombre de places disponibles :</label>
          <input type="number" id="nombre_places" name="nombre_places" 
                 value="<?php echo htmlspecialchars($annonce->getNombrePlaces()); ?>" 
                 min="1" max="8" placeholder="Nombre de places" required>
        </div>

        <div class="mb-4">
          <label for="type_voiture">Type de voiture :</label>
          <input type="text" id="type_voiture" name="type_voiture" 
                 value="<?php echo htmlspecialchars($annonce->getTypeVoiture()); ?>" 
                 placeholder="Type de véhicule" required>
        </div>

        <div class="mb-4">
          <label for="prix_estime">Prix estimé (en €) :</label>
          <input type="number" step="0.01" id="prix_estime" name="prix_estime" 
                 value="<?php echo htmlspecialchars($annonce->getPrixEstime()); ?>" 
                 placeholder="Prix estimé" required>
        </div>

        <div class="mb-4">
          <label for="description">Description (optionnelle) :</label>
          <textarea id="description" name="description" rows="4" 
                    placeholder="Description de votre offre"><?php echo htmlspecialchars($annonce->getDescription()); ?></textarea>
        </div>

        <button type="submit" name="submit" class="elsa-gradient-primary hover:elsa-gradient-primary-hover">Mettre à jour</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Success Popup -->
<div id="success-popup" class="popup-overlay popup-success hidden">
  <div class="popup-content">
    <div class="popup-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h3 class="popup-title">Succès!</h3>
    <div class="popup-message" id="success-message"></div>
    <button onclick="hideSuccessPopup()" class="popup-button">Fermer</button>
  </div>
</div>

<!-- Error Popup -->
<div id="error-popup" class="popup-overlay popup-error hidden">
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
    // Success and Error Popup Functions
    function showSuccessPopup(message) {
        document.getElementById('success-message').textContent = message;
        document.getElementById('success-popup').classList.remove('hidden');
    }
    
    function hideSuccessPopup() {
        document.getElementById('success-popup').classList.add('hidden');
    }
    
    function showErrorPopup(messages) {
        const errorContainer = document.getElementById('error-message');
        errorContainer.innerHTML = '';
        
        if (typeof messages === 'string') {
            errorContainer.innerHTML = `<p>${messages}</p>`;
        } else {
            errorContainer.innerHTML = '<ul class="list-disc list-inside text-left">' + 
                messages.map(msg => `<li>${msg}</li>`).join('') + 
                '</ul>';
        }
        
        document.getElementById('error-popup').classList.remove('hidden');
    }
    
    function hideErrorPopup() {
        document.getElementById('error-popup').classList.add('hidden');
    }

    // Form validation on submit
    document.querySelector('form')?.addEventListener('submit', function(e) {
        // Clear previous error highlights
        document.querySelectorAll('.error-highlight').forEach(el => {
            el.classList.remove('error-highlight');
            const errorMsg = el.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.remove();
            }
        });

        let isValid = true;
        const inputs = this.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error-highlight');
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message animate';
                errorMsg.textContent = 'Ce champ est requis';
                input.parentNode.insertBefore(errorMsg, input.nextSibling);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showErrorPopup('Veuillez remplir tous les champs obligatoires');
        }
    });

    // Field-specific validations
    document.getElementById('tel_conducteur')?.addEventListener('input', function() {
        const phoneRegex = /^[0-9]{0,10}$/;
        if (!phoneRegex.test(this.value)) {
            this.value = this.value.slice(0, -1);
        }
    });
    
    document.getElementById('prix_estime')?.addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });

    // Show popups based on PHP validation
   window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errorMessages) && isset($_POST['submit'])): ?>
            showErrorPopup(<?php echo json_encode($errorMessages); ?>);
        <?php endif; ?>
    });
</script>
</body>
</html>