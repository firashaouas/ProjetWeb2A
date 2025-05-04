<?php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$annonces = [];

try {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);
    $annonces = $controller->getAllAnnonces(true); // Filter active annonces
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
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
      position: relative;
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

    .annonce tangy {
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

    .btn-save {
      color: #be3cf0;
      font-size: 20px;
      cursor: pointer;
      transition: 0.3s;
      position: absolute;
      top: 10px;
      right: 10px;
    }

    .btn-save:hover {
      color: #dc46d7;
      transform: translateY(-2px);
    }

    .btn-save.saved {
      color: #ff50aa;
    }

    body {
        background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
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
                    <a href="ListPassager.php" class="block px-4 py-2 hover:bg-gray-100">Mes demandes</a>
                    <a href="SavedTrajets.php" class="block px-4 py-2 hover:bg-gray-100">Trajets sauvegardés</a>
                </div>
            </div>
            
            <!-- LISTE DÉROULANTE SERVICES -->
            <div class="relative group">
                <button class="hover:text-[#be3cf0]">Services ▾</button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="#home" class="block px-4 py-2 hover:bg-gray-100">Trouver un covoiturage</a>
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

<!-- ANNONCES SECTION -->
<div class="form-background">
  <div class="annonce-container">
    <div class="annonce-wrapper">
      <h2>✨Covoiturages disponibles✨ </h2>
      
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
          <p>Aucun covoiturage disponible pour le moment.</p>
        </div>
      <?php else: ?>
        <div class="annonces-list">
          <?php foreach ($annonces as $annonce): ?>
            <div class="annonce-card" data-id="<?php echo htmlspecialchars($annonce->getIdConducteur()); ?>">
              <i class="fas fa-bookmark btn-save" onclick="toggleSaveAnnonce(<?php echo htmlspecialchars($annonce->getIdConducteur()); ?>, this)" title="Sauvegarder"></i>
              <div class="annonce-header">
                <div class="annonce-title">
                  <?php echo htmlspecialchars($annonce->getPrenomConducteur() . ' ' . $annonce->getNomConducteur()); ?>
                </div>
                <div class="annonce-price">
                  <?php echo htmlspecialchars(number_format($annonce->getPrixEstime(), 2)) ?>
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
                <div>
                  <a href="demande_form.php?id=<?php echo htmlspecialchars($annonce->getIdConducteur()); ?>" class="btn-reserver">Réserver</a>
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

    // Initialize saved annonces from localStorage
    let savedAnnonces = JSON.parse(localStorage.getItem('savedAnnonces')) || [];

    // Function to toggle save/unsave annonce
    function toggleSaveAnnonce(id, element) {
        const annonceCard = element.closest('.annonce-card');
        const annonceData = {
            id: id,
            title: annonceCard.querySelector('.annonce-title').textContent,
            price: annonceCard.querySelector('.annonce-price').textContent,
            lieuDepart: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(1) span').textContent,
            lieuArrivee: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(2) span').textContent,
            dateDepart: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(3) span').textContent,
            typeVoiture: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(4) span').textContent,
            places: annonceCard.querySelector('.annonce-places').textContent.replace('Places disponibles: ', '')
        };

        const index = savedAnnonces.findIndex(annonce => annonce.id === id);
        if (index === -1) {
            // Save annonce
            savedAnnonces.push(annonceData);
            element.classList.add('saved');
            showSuccessPopup('Trajet sauvegardé avec succès!');
        } else {
            // Unsave annonce
            savedAnnonces.splice(index, 1);
            element.classList.remove('saved');
            showSuccessPopup('Trajet retiré des sauvegardes.');
        }

        localStorage.setItem('savedAnnonces', JSON.stringify(savedAnnonces));
    }

    // Initialize save icon states on load
    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errorMessages)): ?>
            showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
        <?php endif; ?>

        // Set saved state for icons
        document.querySelectorAll('.annonce-card').forEach(card => {
            const id = parseInt(card.dataset.id);
            if (savedAnnonces.some(annonce => annonce.id === id)) {
                card.querySelector('.btn-save').classList.add('saved');
            }
        });
    });
</script>
</body>
</html>