<?php
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';

$errorMessages = [];
$demandes = [];
$annonceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $controller = new DemandeCovoiturageController();
    $demandes = $controller->getDemandesByAnnonceId($annonceId);
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Demandes pour l'annonce</title>
<link rel="stylesheet" href="/clickngoooo/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
<style>
    .form-background {
      background-image: url('');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      min-height: calc(100vh - 200px);
    }

    .demande-container {
      display: flex;
      justify-content: center;
      padding: 40px 0;
    }

    .demande-wrapper {
      background-color: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 15px;
      width: 80%;
      max-width: 1000px;
      border: 2px solid #be3cf0;
      transition: 0.3s;
      margin: 20px 0;
    }

    .demande-wrapper h2 {
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      color: #333;
      margin-bottom: 20px;
    }

    .demande-card {
      background-color: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-left: 4px solid #be3cf0;
      transition: transform 0.3s;
    }

    .demande-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .demande-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .demande-title {
      font-size: 18px;
      font-weight: bold;
      color: #333;
    }

    .demande-id {
      font-size: 16px;
      color: #666;
    }

    .demande-details {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 15px;
    }

    .demande-detail {
      display: flex;
      align-items: center;
    }

    .demande-detail i {
      margin-right: 10px;
      color: #be3cf0;
    }

    .demande-footer {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      margin-top: 15px;
      gap: 10px;
    }

    .btn-approve {
      padding: 8px 20px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-approve:hover {
      background-color: #45a049;
      transform: translateY(-2px);
    }

    .btn-reject {
      padding: 8px 20px;
      background-color: #f44336;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }
    
    .btn-sms {
      padding: 8px 20px;
      background-color: #2196F3;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
      display: flex;
      align-items: center;
      gap: 5px;
      text-decoration: none;
    }
    
    .btn-sms:hover {
      background-color: #0b7dda;
      transform: translateY(-2px);
    }
    
    body {
           
           background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
           
       }
    .btn-reject:hover {
      background-color: #d32f2f;
      transform: translateY(-2px);
    }

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
    
    /* Ajout de styles pour l'indicateur de chargement */
    .loading-spinner {
      display: inline-block;
      width: 50px;
      height: 50px;
      border: 5px solid rgba(190, 60, 240, 0.3);
      border-radius: 50%;
      border-top-color: #be3cf0;
      animation: spin 1s ease-in-out infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Style pour l'icône SMS */
    .sms-icon {
      color: #4CAF50;
      margin-right: 8px;
      font-size: 1.2em;
    }
    
    /* Ajout de la classe hidden */
    .hidden {
      display: none;
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
                   
                    <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Mes annonces </a>
                 
                </div>
            </div>
            <div class="relative group">
                <button class="hover:text-[#be3cf0]">Services ▾</button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                   
                    <a href="AjouterConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Proposer un covoiturage</a>
                </div>
            </div>
            <div class="relative group">
                <button class="hover:text-[#be3cf0]">Contact ▾</button>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[180px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="/clickngoooo/view/reclamation.php" class="block px-4 py-2 hover:bg-gray-100">Chatbot</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<br>
<br>
<br>

<!-- DEMANDES SECTION -->
<div class="form-background">
  <div class="demande-container">
    <div class="demande-wrapper">
      <h2>Demandes pour l'annonce ID: <?php echo htmlspecialchars($annonceId); ?></h2>
      
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

      <?php if (empty($demandes)): ?>
        <div class="empty-state">
          <i class="fas fa-users"></i>
          <p>Aucune demande pour cette annonce pour le moment.</p>
        </div>
      <?php else: ?>
        <div class="demandes-list">
          <?php foreach ($demandes as $demande): ?>
            <div class="demande-card" data-demande-id="<?php echo htmlspecialchars($demande->id_demande); ?>">
              <div class="demande-header">
                <div class="demande-title">
                  <?php echo htmlspecialchars($demande->prenom_passager . ' ' . $demande->nom_passager); ?>
                </div>
                <div class="demande-id">
                  ID Demande: <?php echo htmlspecialchars($demande->id_demande); ?>
                </div>
              </div>
              
              <div class="demande-details">
                <div class="demande-detail">
                  <i class="fas fa-phone"></i>
                  <span><?php echo htmlspecialchars($demande->tel_passager); ?></span>
                </div>
                <div class="demande-detail">
                  <i class="fas fa-info-circle"></i>
                  <span>Statut: <?php echo htmlspecialchars($demande->status); ?></span>
                </div>
                <div class="demande-detail">
                  <i class="fas fa-calendar-alt"></i>
                  <span>Date: <?php echo htmlspecialchars($demande->created_at->format('d/m/Y H:i')); ?></span>
                </div>
              </div>
              
              <div class="demande-footer">
                <?php if ($demande->status === 'en cours'): ?>
                  <button onclick="handleDemande(<?php echo $demande->id_demande; ?>, 'approve')" class="btn-approve">
                    <i class="fas fa-check"></i> Approuver
                  </button>
                  <button onclick="handleDemande(<?php echo $demande->id_demande; ?>, 'reject')" class="btn-reject">
                    <i class="fas fa-times"></i> Rejeter
                  </button>
                <?php elseif ($demande->status === 'approuvée'): ?>
                  <!-- Lien direct vers test_sms.php avec le numéro de téléphone en paramètre -->
                  <a href="test_sms.php?phone=<?php echo urlencode($demande->tel_passager); ?>" class="btn-sms" target="_blank">
                    <i class="fas fa-sms"></i> Envoyer SMS
                  </a>
                  <span class="text-green-500">
                    <i class="fas fa-check-circle"></i> Approuvée
                  </span>
                <?php else: ?>
                  <span class="text-<?php echo $demande->status === 'approuvée' ? 'green' : 'red'; ?>-500">
                    <i class="fas fa-<?php echo $demande->status === 'approuvée' ? 'check' : 'times'; ?>-circle"></i> 
                    <?php echo ucfirst($demande->status); ?>
                  </span>
                <?php endif; ?>
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

<!-- Loading Popup -->
<div id="loading-popup" class="popup-overlay popup-success hidden">
  <div class="popup-content">
    <div class="popup-icon">
      <i class="fas fa-spinner fa-spin"></i>
    </div>
    <h3 class="popup-title">Traitement en cours...</h3>
    <div class="popup-message" id="loading-message">Veuillez patienter...</div>
  </div>
</div>

<script>
    function showSuccessPopup(message) {
        document.getElementById('success-message').innerHTML = message;
        document.getElementById('success-popup').classList.remove('hidden');
    }
    
    function hideSuccessPopup() {
        document.getElementById('success-popup').classList.add('hidden');
    }
    
    function showErrorPopup(message) {
        document.getElementById('error-message').innerHTML = message;
        document.getElementById('error-popup').classList.remove('hidden');
    }
    
    function hideErrorPopup() {
        document.getElementById('error-popup').classList.add('hidden');
    }
    
    function showLoadingPopup(message) {
        document.getElementById('loading-message').textContent = message || 'Veuillez patienter...';
        document.getElementById('loading-popup').classList.remove('hidden');
    }
    
    function hideLoadingPopup() {
        document.getElementById('loading-popup').classList.add('hidden');
    }
    
    function handleDemande(idDemande, action) {
        // Afficher le popup de chargement
        showLoadingPopup(action === 'approve' ? 'Approbation en cours...' : 'Rejet en cours...');
        
        console.log('Sending request for idDemande:', idDemande, 'action:', action);
        
        // Utiliser le chemin correct vers handle_demande.php
        fetch('/clickngoooo/view/handle_demande.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                id_demande: parseInt(idDemande), 
                action: action 
            }),
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            hideLoadingPopup();
            
            if (data.success) {
                // Créer un message de succès
                let successMessage = '';
                
                if (action === 'approve') {
                    successMessage = '<div style="text-align: center;">' +
                        '<i class="fas fa-check-circle" style="color: #4CAF50; font-size: 24px; margin-bottom: 10px;"></i>' +
                        '<p style="font-weight: bold; font-size: 18px;">Demande approuvée!</p>' +
                        '<p>Cliquez sur le bouton "Envoyer SMS" pour notifier le passager.</p>' +
                        '</div>';
                } else if (action === 'reject') {
                    successMessage = '<div style="text-align: center;">' +
                        '<i class="fas fa-ban" style="color: #f44336; font-size: 24px; margin-bottom: 10px;"></i>' +
                        '<p style="font-weight: bold; font-size: 18px;">Demande rejetée avec succès!</p>' +
                        '</div>';
                }
                
                showSuccessPopup(successMessage);
                
                // Mettre à jour la carte sans recharger la page
                const card = document.querySelector(`[data-demande-id="${idDemande}"]`);
                if (card) {
                    const statusSpan = card.querySelector('.demande-detail:nth-child(2) span');
                    if (statusSpan) {
                        statusSpan.textContent = 'Statut: ' + (action === 'approve' ? 'approuvée' : 'rejetée');
                    }
                    
                    // Mettre à jour le footer de la carte
                    const footer = card.querySelector('.demande-footer');
                    if (footer) {
                        if (action === 'approve') {
                            // Récupérer le numéro de téléphone
                            const phoneElement = card.querySelector('.demande-detail:nth-child(1) span');
                            const phoneNumber = phoneElement ? phoneElement.textContent.trim() : '';
                            
                            footer.innerHTML = '<a href="test_sms.php?phone=' + encodeURIComponent(phoneNumber) + '" class="btn-sms" target="_blank">' +
                                '<i class="fas fa-sms"></i> Envoyer SMS</a>' +
                                '<span class="text-green-500"><i class="fas fa-check-circle"></i> Approuvée</span>';
                        } else if (action === 'reject') {
                            footer.innerHTML = '<span class="text-red-500"><i class="fas fa-times-circle"></i> Rejetée</span>';
                        }
                    }
                }
                
                // Recharger la page après un délai
                setTimeout(() => {
                    location.reload();
                }, 3000); // 3 secondes pour lire le message
            } else {
                showErrorPopup(data.message || `Erreur lors de l'action sur la demande`);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            hideLoadingPopup();
            showErrorPopup('Erreur réseau: ' + error.message);
        });
    }
    
    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errorMessages)): ?>
            showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
        <?php endif; ?>
    });
</script>
</body>
</html>