<?php
require_once '../config.php';
require_once '../Controller/AvisController.php';

$pdo = config::getConnexion();
$avisController = new AvisController($pdo);

$id_avis = isset($_GET['id_avis']) ? (int)$_GET['id_avis'] : 0;
$avis = $avisController->getAvisById($id_avis);

if (!$avis) {
    die("Avis non trouvé.");
}

$successMessage = '';
$errorMessage = '';

if (isset($_POST['submit'])) {
    try {
        $note = isset($_POST['note']) ? (int)$_POST['note'] : 0;
        $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
        $message = $avisController->updateAvis($id_avis, $note, $commentaire);
        $successMessage = $message;
        $avis = $avisController->getAvisById($id_avis); // Rafraîchir les données
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
    <title>Modifier l'avis - Click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 90%;
            padding: 40px;
        }

        h2 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .rating {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .rating input {
            display: none;
        }

        .rating label {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }

        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ff50aa;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            font-size: 16px;
            min-height: 100px;
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
            color: white;
            transition: 0.3s;
        }

        button:hover {
            background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .error {
            color: #ef4444;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }

        .success {
            color: #10b981;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
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
            padding: 0.5rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
            color: white;
        }

        .popup-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier l'avis</h2>
        <p style="text-align: center; margin-bottom: 20px;">
            Avis pour la demande ID: <?php echo htmlspecialchars($avis->getIdPassager()); ?>
            (Conducteur ID: <?php echo htmlspecialchars($avis->getIdConducteur()); ?>)
        </p>
        <form method="post" action="">
            <div class="form-group">
                <label for="note">Note (1 à 5 étoiles)</label>
                <div class="rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="note" value="<?php echo $i; ?>" <?php echo $avis->getNote() == $i ? 'checked' : ''; ?> required>
                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="commentaire">Commentaire (optionnel)</label>
                <textarea name="commentaire" id="commentaire" placeholder="Votre commentaire..."><?php echo htmlspecialchars($avis->getCommentaire()); ?></textarea>
            </div>
            <button type="submit" name="submit">Modifier l'avis</button>
        </form>
        <?php if (!empty($successMessage)): ?>
            <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
    </div>

    <!-- Success Popup -->
    <div id="success-popup" class="popup-overlay popup-success <?php echo !empty($successMessage) ? '' : 'hidden'; ?>">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="popup-title">Succès!</h3>
            <div class="popup-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <button onclick="redirectToList()" class="popup-button">Fermer</button>
        </div>
    </div>

    <!-- Error Popup -->
    <div id="error-popup" class="popup-overlay popup-error <?php echo !empty($errorMessage) ? '' : 'hidden'; ?>">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="popup-title">Erreur</h3>
            <div class="popup-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <button onclick="closePopup()" class="popup-button">Fermer</button>
        </div>
    </div>

    <script>
        function closePopup() {
            document.getElementById('success-popup').classList.add('hidden');
            document.getElementById('error-popup').classList.add('hidden');
        }

        function redirectToList() {
            window.location.href = 'mes_avis.php';
        }
    </script>
</body>
</html>