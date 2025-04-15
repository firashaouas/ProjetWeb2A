<?php
// Include your configuration and controller files
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';

$errorMessages = [];
$demandes = [];

try {
    // Create controller instance
    $controller = new DemandeCovoiturageController();
    
    // Get all demandes (regardless of status)
    $demandes = $controller->getAllDemandes();
} catch (Exception $e) {
    // If an error occurs, capture the error message
    $errorMessages = explode('<br>', $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Liste des demandes de covoiturage</title>
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

    /* === STYLE DE LA LISTE === */
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

    .btn-edit {
      padding: 8px 20px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-edit:hover {
      background-color: #45a049;
      transform: translateY(-2px);
    }

    .btn-delete {
      padding: 8px 20px;
      background-color: #f44336;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-delete:hover {
      background-color: #d32f2f;
      transform: translateY(-2px);
    }

    .btn-accept {
      padding: 8px 20px;
      background-color: #2196F3;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-accept:hover {
      background-color: #0b7dda;
      transform: translateY(-2px);
    }

    .btn-reject {
      padding: 8px 20px;
      background-color: #ff9800;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: 0.3s;
    }

    .btn-reject:hover {
      background-color: #e68a00;
      transform: translateY(-2px);
    }

    .status-pending {
      color: #ff9800;
      font-weight: bold;
    }

    .status-accepted {
      color: #4CAF50;
      font-weight: bold;
    }

    .status-rejected {
      color: #f44336;
      font-weight: bold;
    }
    .popup-button {
    padding: 0.5rem 1.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.popup-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.popup-button i {
    font-size: 0.9rem;
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
      .status-encours {
    color: #ff9800;
    font-weight: bold;
}

.status-acceptée {
    color: #4CAF50;
    font-weight: bold;
}

.status-rejetée {
    color: #f44336;
    font-weight: bold;
}

.status-annulée {
    color: #9e9e9e;
    font-weight: bold;
}


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

<!-- DEMANDES SECTION -->
<div class="form-background">
  <div class="demande-container">
    <div class="demande-wrapper">
      <h2>Liste des demandes de covoiturage</h2>
      
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
          <i class="fas fa-user-friends"></i>
          <p>Aucune demande de covoiturage disponible pour le moment.</p>
        </div>
      <?php else: ?>
        <div class="demandes-list">
          <?php foreach ($demandes as $demande): ?>
            <div class="demande-card">
              <div class="demande-header">
                <div class="demande-title">
                  <?php echo htmlspecialchars($demande['prenom_passager'] . ' ' . $demande['nom_passager']); ?>
                </div>
                <div class="demande-id">
    ID: <?php echo htmlspecialchars($demande['id_passager']); ?>
    <span class="status-<?php echo str_replace(' ', '', strtolower($demande['status_demande'])); ?>">
        (<?php echo htmlspecialchars($demande['status_demande']); ?>)
    </span>
</div>
              </div>
              
              <div class="demande-details">
                <div class="demande-detail">
                  <i class="fas fa-phone"></i>
                  <span><?php echo htmlspecialchars($demande['tel_passager']); ?></span>
                </div>
                
                <div class="demande-detail">
                  <i class="fas fa-car"></i>
                  <span>Conducteur ID: <?php echo htmlspecialchars($demande['id_conducteur']); ?></span>
                </div>
                
                <div class="demande-detail">
                  <i class="fas fa-users"></i>
                  <span>Places demandées: <?php echo htmlspecialchars($demande['nbr_places_reservees']); ?></span>
                </div>
                
                <div class="demande-detail">
                  <i class="fas fa-euro-sign"></i>
                  <span>Prix total: <?php echo htmlspecialchars($demande['prix_total']); ?> €</span>
                </div>
                
                <div class="demande-detail">
                  <i class="fas fa-credit-card"></i>
                  <span>Paiement: <?php echo htmlspecialchars($demande['moyen_paiement']); ?></span>
                </div>
                
                <div class="demande-detail">
                  <i class="fas fa-calendar-alt"></i>
                  <span>Date: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($demande['date_demande']))); ?></span>
                </div>
                
                <?php if (!empty($demande['message'])): ?>
                <div class="demande-detail">
                  <i class="fas fa-comment"></i>
                  <span>Message: <?php echo htmlspecialchars($demande['message']); ?></span>
                </div>
                <?php endif; ?>
              </div>
              
              <div class="demande-footer">
    
              <a href="edit_demande.php?id=<?php echo $demande['id_passager']; ?>" class="btn-edit">
  <i class="fas fa-edit"></i> Modifier
</a>
                <button onclick="confirmDelete(<?php echo $demande['id_passager']; ?>)" class="btn-delete">
                  <i class="fas fa-trash-alt"></i> Supprimer
                </button>
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

<!-- Delete Confirmation Popup -->
<div id="delete-confirm-popup" class="popup-overlay hidden">
    <div class="popup-content">
        <div class="popup-icon">
            <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
        </div>
        <h3 class="popup-title">Confirmer la suppression</h3>
        <div class="popup-message">Êtes-vous sûr de vouloir supprimer cette demande? Cette action est irréversible.</div>
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
            <button onclick="hideDeleteConfirmPopup()" class="popup-button" style="background-color: #6c757d;">Annuler</button>
            <button onclick="deleteDemande()" class="popup-button" style="background-color: #f44336;">
                <i class="fas fa-trash-alt"></i> Supprimer
            </button>
        </div>
    </div>
</div>

<script>
    let demandeToDelete = null;
    let currentAction = null;
    
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
    
    function confirmDelete(id) {
    demandeToDelete = id;
    document.getElementById('delete-confirm-popup').classList.remove('hidden');
}

function deleteDemande() {
    if (!demandeToDelete) return;
    
    fetch('../view/delete_demande.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: demandeToDelete })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSuccessPopup(data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showErrorPopup(data.message || 'Erreur lors de la suppression');
        }
    })
    .catch(error => {
        showErrorPopup('Erreur réseau lors de la suppression: ' + error.message);
    })
    .finally(() => {
        hideDeleteConfirmPopup();
    });
}

    // Show popups based on PHP validation
    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errorMessages)): ?>
            showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
        <?php endif; ?>
    });
</script>
</body>
</html>