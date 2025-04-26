<?php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$annonces = [];

try {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);

    // Récupérer les paramètres de recherche
    $depart = isset($_GET['depart']) ? trim($_GET['depart']) : '';
    $arrivee = isset($_GET['arrivee']) ? trim($_GET['arrivee']) : '';

    // Log search parameters for debugging
    error_log("resultats.php received: depart='$depart', arrivee='$arrivee'");

    if ($depart && $arrivee) {
        // Filtrer les annonces en fonction des lieux de départ et d'arrivée
        $annonces = $controller->searchAnnonces($depart, $arrivee);
    } else {
        // Si aucun filtre n'est fourni, afficher toutes les annonces
        $annonces = $controller->getAllAnnonces();
    }

    // Log number of announcements found
    error_log("resultats.php found " . count($annonces) . " announcements");

    // Get server time for debugging
    $serverTime = (new DateTime())->format('Y-m-d H:i:s');
    error_log("Server time: $serverTime");
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
    error_log("Error in resultats.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Liste des covoiturages disponibles</title>
<link rel="stylesheet" href="/clickngo/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Geocoder pour la recherche d'adresses -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<!-- Leaflet Locate Control pour la géolocalisation -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js"></script>
<!-- Scripts personnalisés -->
<script src="/clickngo/public/js/leaflet-location-picker.js" defer></script>
<script src="/clickngo/public/js/search-form.js" defer></script>

<style>
/* Styles pour le sélecteur de carte */
.fixed {
  position: fixed;
}
.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.z-50 {
  z-index: 50;
}
.flex {
  display: flex;
}
.items-center {
  align-items: center;
}
.justify-center {
  justify-content: center;
}
.justify-between {
  justify-content: space-between;
}
.justify-end {
  justify-content: flex-end;
}
.bg-black\/50 {
  background-color: rgba(0, 0, 0, 0.5);
}
.bg-white {
  background-color: white;
}
.w-full {
  width: 100%;
}
.max-w-3xl {
  max-width: 48rem;
}
.max-h-\[90vh\] {
  max-height: 90vh;
}
.overflow-hidden {
  overflow: hidden;
}
.rounded-lg {
  border-radius: 0.5rem;
}
.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
.p-4 {
  padding: 1rem;
}
.p-0 {
  padding: 0;
}
.p-2 {
  padding: 0.5rem;
}
.px-4 {
  padding-left: 1rem;
  padding-right: 1rem;
}
.py-2 {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
.border-b {
  border-bottom-width: 1px;
  border-bottom-style: solid;
  border-bottom-color: #e5e7eb;
}
.border-t {
  border-top-width: 1px;
  border-top-style: solid;
  border-top-color: #e5e7eb;
}
.border {
  border-width: 1px;
  border-style: solid;
  border-color: #e5e7eb;
}
.rounded {
  border-radius: 0.25rem;
}
.rounded-full {
  border-radius: 9999px;
}
.text-xl {
  font-size: 1.25rem;
  line-height: 1.75rem;
}
.font-semibold {
  font-weight: 600;
}
.gap-2 {
  gap: 0.5rem;
}
.flex-1 {
  flex: 1 1 0%;
}
.hover\:bg-gray-100:hover {
  background-color: #f3f4f6;
}
.bg-gray-200 {
  background-color: #e5e7eb;
}
.hover\:bg-gray-300:hover {
  background-color: #d1d5db;
}
.text-white {
  color: white;
}
.h-\[400px\] {
  height: 400px;
}
</style>
<style>
    /* === FOND D'ÉCRAN === */
    .form-background {
        background-image: url('');
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

    /* === STYLE DE LA LISTE === */
    .annonce-container {
        display: flex;
        justify-content: center;
        padding: 40px 0;
    }

    .annonce-wrapper {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 30px;
        border-radius: 15px;
        width: 80%;
        max-width: 1000px;
        border: 2px solid #be3cf0;
        transition: 0.3s;
        margin: 20px 0;
    }

    .annonce-wrapper h2 {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }

    .annonce-card {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #be3cf0;
        transition: transform 0.3s;
    }

    .annonce-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .annonce-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .annonce-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }

    .annonce-price {
        font-size: 20px;
        font-weight: bold;
        color: #be3cf0;
    }

    .annonce-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .annonce-detail {
        display: flex;
        align-items: center;
    }

    .annonce-detail i {
        margin-right: 10px;
        color: #be3cf0;
    }

    .annonce-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }

    .annonce-places {
        font-weight: bold;
        color: #555;
    }
    .btn-reserver {
    padding: 8px 20px;
    background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.3s;
}

.btn-reserver:hover {
    background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    transform: translateY(-2px);
}


    /* === POPUP STYLES === */
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

    .empty-state {
        text-align: center;
        padding: 40px;
    }

    .empty-state i {
        font-size: 50px;
        color: #be3cf0;
        margin-bottom: 20px;
    }

    .empty-state p {
        font-size: 18px;
        color: #555;
    }

    .debug-info {
        background-color: #f8f8f8;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        font-size: 14px;
        color: #333;
    }
</style>
</head>
<body>   

<!-- NAVBAR -->
<nav class="absolute top-0 left-0 w-full z-50 p-4">
    <div class="flex items-center justify-center max-w-7xl mx-auto">
        <div class="flex space-x-8 text-lg font-bold text-black relative">
            <a href="index.php" class="hover:text-[#be3cf0]">Accueil</a>
            <a href="#about" class="hover:text-[#be3cf0]">À propos</a>
            <div class="group relative">
                <button class="hover:text-[#be3cf0] font-bold text-lg">
                    Nos Détails ▾
                </button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Nos trajets</a>
                   
                    <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Mes annonces</a>
                    <a href="ListPassager.php" class="block px-4 py-2 hover:bg-gray-100">Mes demandes</a>
                </div>
            </div>
            
            <div class="relative group">
                <button class="hover:text-[#be3cf0]">Services ▾</button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Trouver un covoiturage</a>
                <a href="AjouterConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Proposer un covoiturage</a>
                </div>
            </div>
            
            <div class="relative group">
                <button class="hover:text-[#be3cf0]">Contact ▾</button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[180px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="/clickngo/view/reclamation.php" class="block px-4 py-2 hover:bg-gray-100">Réclamation</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<br><br><br>

<!-- ANNONCES SECTION -->
<div class="form-background">
  <div class="annonce-container">
    <div class="annonce-wrapper">
      <h2>Covoiturages disponibles</h2>
      
      <!-- Display search criteria and server time for debugging -->
      <?php if ($depart && $arrivee): ?>
        <div class="debug-info">
          <p>Recherche pour : <strong><?php echo htmlspecialchars($depart); ?> → <?php echo htmlspecialchars($arrivee); ?></strong></p>
          <p>Heure du serveur : <strong><?php echo htmlspecialchars($serverTime); ?></strong></p>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($errorMessages)): ?>
        <div id="error-popup" class="popup-overlay popup-error">
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
            <button onclick="hideErrorPopup()" class="popup-button">Fermer</button>
          </div>
        </div>
      <?php endif; ?>

      <?php if (empty($annonces)): ?>
        <div class="empty-state">
          <i class="fas fa-car-side"></i>
          <p>Aucun covoiturage disponible pour ces critères.</p>
        </div>
      <?php else: ?>
        <div class="annonces-list">
          <?php foreach ($annonces as $annonce): ?>
            <div class="annonce-card">
              <div class="annonce-header">
                <div class="annonce-title">
                  <?php echo htmlspecialchars($annonce->getPrenomConducteur() . ' ' . $annonce->getNomConducteur()); ?>
                </div>
                <div class="annonce-price">
                  <?php echo htmlspecialchars(number_format($annonce->getPrixEstime(), 2)) . ' €'; ?>
                </div>
              </div>
              
              <div class="annonce-details">
                <div class="annonce-detail">
                  <i class="fas fa-map-marker-alt"></i>
                  <span><?php echo htmlspecialchars($annonce->getLieuDepart()); ?></span>
                </div>
                
                <div class="annonce-detail">
                  <i class="fas fa-map-marked-alt"></i>
                  <span><?php echo htmlspecialchars($annonce->getLieuArrivee()); ?></span>
                </div>
                
                <div class="annonce-detail">
                  <i class="fas fa-calendar-alt"></i>
                  <span><?php echo htmlspecialchars($annonce->getDateDepart()->format('d/m/Y H:i')); ?></span>
                </div>
                
                <div class="annonce-detail">
                  <i class="fas fa-car"></i>
                  <span><?php echo htmlspecialchars($annonce->getTypeVoiture()); ?></span>
                </div>
              </div>
              
             
                <div class="annonce-footer">
    <div class="annonce-places">
        Places disponibles: <?php echo htmlspecialchars($annonce->getNombrePlaces()); ?>
    </div>
    <?php
    $annonceId = $annonce->getIdConducteur();
    if ($annonceId) {
        echo '<a href="/clickngoooo/view/demande_form.php?id=' . htmlspecialchars($annonceId) . '" class="btn-reserver">Réserver</a>';
    } else {
        echo '<span class="btn-reserver disabled" style="background: grey; cursor: not-allowed;">Réserver (ID manquant)</span>';
    }
    ?>
</div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
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
    
    function reserverAnnonce(id) {
        showSuccessPopup('Votre réservation a été enregistrée! Nous vous contacterons bientôt.');
    }

    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errorMessages)): ?>
            showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
        <?php endif; ?>
    });
</script>
</body>
</html>