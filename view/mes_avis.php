<?php
require_once '../config.php';
require_once '../Controller/AvisController.php';

$pdo = config::getConnexion();
$avisController = new AvisController($pdo);

$errorMessages = [];
$avisList = [];

try {
    $avisList = $avisController->getAllAvis();
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Mes avis - Click'N'go</title>
<link rel="stylesheet" href="/clickngo/public/css/style.css">
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
    body {
        background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
    }

    .elsa-gradient-primary {
        background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
        color: white;
    }

    .elsa-gradient-primary-hover:hover {
        background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    }

    .avis-container {
        display: flex;
        justify-content: center;
        padding: 40px 0;
    }

    .avis-wrapper {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 30px;
        border-radius: 15px;
        width: 80%;
        max-width: 1000px;
        border: 2px solid #be3cf0;
        transition: 0.3s;
        margin: 20px 0;
    }

    .avis-wrapper h2 {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }

    .avis-card {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #be3cf0;
        transition: transform 0.3s;
    }

    .avis-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .avis-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .avis-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }

    .avis-id {
        font-size: 16px;
        color: #666;
    }

    .avis-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .avis-detail {
        display: flex;
        align-items: center;
    }

    .avis-detail i {
        margin-right: 10px;
        color: #be3cf0;
    }

    .avis-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-top: 15px;
        gap: 10px;
    }

    .btn-edit,
    .btn-delete {
        padding: 8px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
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

    .stars {
        color: #ff50aa;
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
                    <a href="mes_avis.php" class="block px-4 py-2 hover:bg-gray-100">Mes avis</a>
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
<br>
<br>
<br>

<!-- AVIS SECTION -->
<div class="form-background">
    <div class="avis-container">
        <div class="avis-wrapper">
            <h2>✨ Mes avis ✨</h2>
            
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
                        <button onclick="hideErrorPopup()" class="popup-button elsa-gradient-primary elsa-gradient-primary-hover">Fermer</button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($avisList)): ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <p>Aucun avis disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="avis-list">
                    <?php foreach ($avisList as $avis): ?>
                        <div class="avis-card">
                            <div class="avis-header">
                                <div class="avis-title">
                                    Avis pour la demande ID: <?php echo htmlspecialchars($avis->getIdPassager()); ?>
                                </div>
                                <div class="avis-id">
                                    ID: <?php echo htmlspecialchars($avis->getIdAvis()); ?>
                                </div>
                            </div>
                            
                            <div class="avis-details">
                                <div class="avis-detail">
                                    <i class="fas fa-car"></i>
                                    <span>Conducteur ID: <?php echo htmlspecialchars($avis->getIdConducteur()); ?></span>
                                </div>
                                <div class="avis-detail">
                                    <i class="fas fa-star"></i>
                                    <span>Note: <span class="stars"><?php echo str_repeat('★', $avis->getNote()) . str_repeat('☆', 5 - $avis->getNote()); ?></span></span>
                                </div>
                                <?php if (!empty($avis->getCommentaire())): ?>
                                    <div class="avis-detail">
                                        <i class="fas fa-comment"></i>
                                        <span>Commentaire: <?php echo htmlspecialchars($avis->getCommentaire()); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="avis-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Date: <?php echo htmlspecialchars($avis->getDateCreation()->format('d/m/Y H:i')); ?></span>
                                </div>
                            </div>
                            
                            <div class="avis-footer">
                                <a href="edit_avis.php?id_avis=<?php echo $avis->getIdAvis(); ?>" class="btn-edit elsa-gradient-primary elsa-gradient-primary-hover">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <button onclick="confirmDelete(<?php echo $avis->getIdAvis(); ?>)" class="btn-delete elsa-gradient-primary elsa-gradient-primary-hover">
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
        <button onclick="hideSuccessPopup()" class="popup-button elsa-gradient-primary elsa-gradient-primary-hover">Fermer</button>
    </div>
</div>

<!-- Delete Confirmation Popup -->
<div id="delete-confirm-popup" class="popup-overlay hidden">
    <div class="popup-content">
        <div class="popup-icon">
            <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
        </div>
        <h3 class="popup-title">Confirmer la suppression</h3>
        <div class="popup-message">Êtes-vous sûr de vouloir supprimer cet avis ? Cette action est irréversible.</div>
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
            <button onclick="hideDeleteConfirmPopup()" class="popup-button" style="background-color: #6c757d; color: white;">Annuler</button>
            <button onclick="deleteAvis()" class="popup-button elsa-gradient-primary elsa-gradient-primary-hover">
                <i class="fas fa-trash-alt"></i> Supprimer
            </button>
        </div>
    </div>
</div>

<script>
    let avisToDelete = null;

    function showSuccessPopup(message) {
        document.getElementById('success-message').textContent = message;
        document.getElementById('success-popup').classList.remove('hidden');
    }

    function hideSuccessPopup() {
        document.getElementById('success-popup').classList.add('hidden');
        location.reload();
    }

    function showErrorPopup(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('error-popup').classList.remove('hidden');
    }

    function hideErrorPopup() {
        document.getElementById('error-popup').classList.add('hidden');
    }

    function confirmDelete(id) {
        avisToDelete = id;
        document.getElementById('delete-confirm-popup').classList.remove('hidden');
    }

    function hideDeleteConfirmPopup() {
        document.getElementById('delete-confirm-popup').classList.add('hidden');
        avisToDelete = null;
    }

    function deleteAvis() {
        if (!avisToDelete) return;

        fetch('delete_avis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_avis: avisToDelete })
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

    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errorMessages)): ?>
            showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
        <?php endif; ?>
    });
</script>
</body>
</html>