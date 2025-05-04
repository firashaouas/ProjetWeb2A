<?php
session_start();

require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$successMessage = '';
$annonce = null;

try {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
        $annonce = $controller->getAnnonceById($id);
        if (!$annonce) {
            $errorMessages[] = "Annonce non trouvée";
        }
    } else {
        $errorMessages[] = "ID d'annonce invalide";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        try {
            $_POST['id_conducteur'] = $id;
            $success = $controller->updateAnnonce($_POST);

            if ($success) {
                $_SESSION['success_message'] = "Modification avec succès!";
                header('Location: /clickngoooo/view/ListConducteurs.php');
                exit();
            } else {
                $errorMessages[] = "Échec de la mise à jour de l'annonce";
            }
        } catch (Exception $e) {
            $errorMessages[] = $e->getMessage();
        }
    }
} catch (Exception $e) {
    $errorMessages[] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une annonce - Click'N'go</title>
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
        }

        .location-form {
            width: 55%;
            padding: 50px;
            background: #fff;
        }

        .location-form h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #2d2d2d;
            text-align: center;
            margin-bottom: 30px;
        }

        .location-form label {
            display: block;
            margin: 20px 0 10px;
            font-weight: 500;
        }

        .location-form input,
        .location-form textarea {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            font-size: 16px;
            background: #f9f9f9;
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
        }

        .error-message {
            color: #e63946;
            margin: 10px 0;
            padding: 10px;
            background: rgba(230, 57, 70, 0.1);
            border-radius: 5px;
        }

        .success-message {
            color: #4CAF50;
            margin: 10px 0;
            padding: 10px;
            background: rgba(76, 175, 80, 0.1);
            border-radius: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="preview">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e" alt="Car Image">
            <h3>Aperçu de l'annonce</h3>
            <div class="price" id="preview-prix_estime"><?= $annonce ? htmlspecialchars($annonce->getPrixEstime()) . ' €' : '-' ?></div>
        </div>
        <div class="location-form">
            <h2>Modifier l'annonce</h2>
            
            <?php if (!empty($errorMessages)): ?>
                <?php foreach ($errorMessages as $error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($annonce): ?>
                <form method="POST">
                    <input type="hidden" name="id_conducteur" value="<?= htmlspecialchars($annonce->getIdConducteur()) ?>">
                    
                    <label for="prenom_conducteur">Prénom Conducteur</label>
                    <input type="text" id="prenom_conducteur" name="prenom_conducteur" 
                           value="<?= htmlspecialchars($annonce->getPrenomConducteur()) ?>" required>

                    <label for="nom_conducteur">Nom Conducteur</label>
                    <input type="text" id="nom_conducteur" name="nom_conducteur" 
                           value="<?= htmlspecialchars($annonce->getNomConducteur()) ?>" required>

                    <label for="tel_conducteur">Téléphone Conducteur</label>
                    <input type="tel" id="tel_conducteur" name="tel_conducteur" 
                           value="<?= htmlspecialchars($annonce->getTelConducteur()) ?>" required>

                    <label for="date_depart">Date et heure de départ</label>
                    <input type="datetime-local" id="date_depart" name="date_depart" 
                           value="<?= htmlspecialchars($annonce->getDateDepart()->format('Y-m-d\TH:i')) ?>" required>

                    <label for="lieu_depart">Lieu de Départ</label>
                    <input type="text" id="lieu_depart" name="lieu_depart" 
                           value="<?= htmlspecialchars($annonce->getLieuDepart()) ?>" required>

                    <label for="lieu_arrivee">Lieu d'Arrivée</label>
                    <input type="text" id="lieu_arrivee" name="lieu_arrivee" 
                           value="<?= htmlspecialchars($annonce->getLieuArrivee()) ?>" required>

                    <label for="nombre_places">Nombre de Places</label>
                    <input type="number" id="nombre_places" name="nombre_places" 
                           value="<?= htmlspecialchars($annonce->getNombrePlaces()) ?>" min="1" max="8" required>

                    <label for="type_voiture">Type de Voiture</label>
                    <input type="text" id="type_voiture" name="type_voiture" 
                           value="<?= htmlspecialchars($annonce->getTypeVoiture()) ?>" required>

                    <label for="prix_estime">Prix Estimé (€)</label>
                    <input type="number" step="0.01" id="prix_estime" name="prix_estime" 
                           value="<?= htmlspecialchars($annonce->getPrixEstime()) ?>" required>

                    <label for="description">Description (optionnelle)</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($annonce->getDescription()) ?></textarea>

                    <button type="submit" name="submit" class="register-btn">Mettre à jour</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>