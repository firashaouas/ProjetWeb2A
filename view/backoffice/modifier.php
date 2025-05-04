<?php
// Add error reporting to help diagnose issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';

// Check if ID is provided
if (!isset($_GET['id_conducteur']) || empty($_GET['id_conducteur'])) {
    echo "ID de l'annonce non spécifié. <a href='annonce.php'>Retour à la liste</a>";
    exit;
}

$id = $_GET['id_conducteur'];
$errors = [];

try {
    $pdo = config::getConnexion();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize inputs
        $prenom = trim($_POST['prenom_conducteur']);
        $nom = trim($_POST['nom_conducteur']);
        $tel = trim($_POST['tel_conducteur']);
        $date_depart = $_POST['date_depart'];
        $lieu_depart = trim($_POST['lieu_depart']);
        $lieu_arrivee = trim($_POST['lieu_arrivee']);
        $nombre_places = $_POST['nombre_places'];
        $type_voiture = trim($_POST['type_voiture']);
        $prix_estime = $_POST['prix_estime'];
        $description = trim($_POST['description']);

        // Contrôle de saisie
        if (empty($prenom) || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/u', $prenom)) {
            $errors[] = "Le prénom est invalide.";
        }

        if (empty($nom) || !preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/u', $nom)) {
            $errors[] = "Le nom est invalide.";
        }

        if (empty($tel) || !preg_match('/^\d{8}$/', $tel)) {
            $errors[] = "Le numéro de téléphone doit comporter 8 chiffres.";
        }

        if (empty($date_depart)) {
            $errors[] = "La date de départ est requise.";
        }

        if (empty($lieu_depart)) {
            $errors[] = "Le lieu de départ est requis.";
        }

        if (empty($lieu_arrivee)) {
            $errors[] = "Le lieu d'arrivée est requis.";
        }

        if (!is_numeric($nombre_places) || $nombre_places >4) {
            $errors[] = "Le nombre de places doit être inf à 0.";
        }

        if (empty($type_voiture)) {
            $errors[] = "Le type de voiture est requis.";
        }

        if (!is_numeric($prix_estime) || $prix_estime < 0) {
            $errors[] = "Le prix estimé doit être un nombre positif.";
        }

        if (empty($description)) {
            $errors[] = "La description est requise.";
        }

        if (empty($errors)) {
            $query = "UPDATE annonce_covoiturage SET 
                        prenom_conducteur = :prenom,
                        nom_conducteur = :nom,
                        tel_conducteur = :tel,
                        date_depart = :date_depart,
                        lieu_depart = :lieu_depart,
                        lieu_arrivee = :lieu_arrivee,
                        nombre_places = :nombre_places,
                        type_voiture = :type_voiture,
                        prix_estime = :prix_estime,
                        description = :description
                    WHERE id_conducteur = :id";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':tel', $tel);
            $stmt->bindParam(':date_depart', $date_depart);
            $stmt->bindParam(':lieu_depart', $lieu_depart);
            $stmt->bindParam(':lieu_arrivee', $lieu_arrivee);
            $stmt->bindParam(':nombre_places', $nombre_places);
            $stmt->bindParam(':type_voiture', $type_voiture);
            $stmt->bindParam(':prix_estime', $prix_estime);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Annonce modifiée avec succès!');
                    window.location.href = 'annonces.php';
                </script>";
                exit;
            } else {
                $errors[] = "Erreur lors de la mise à jour.";
            }
        }
    }

    // Get current data
    $stmt = $pdo->prepare("SELECT * FROM annonce_covoiturage WHERE id_conducteur = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$annonce) {
        echo "Annonce non trouvée. <a href='annonce.php'>Retour à la liste</a>";
        exit;
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . " <a href='annonce.php'>Retour à la liste</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une Annonce</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #fff7fb;
            padding: 20px;
        }

        h1 {
            color: #f72975;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-container {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #ba68c8;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #f2d3e8;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .submit-btn, .cancel-btn {
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .submit-btn {
            background-color: #f72975;
            color: white;
            border: none;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #ba68c8;
        }

        .cancel-btn {
            background-color: #f2f2f2;
            color: #666;
            border: 1px solid #ddd;
            display: inline-block;
        }

        .cancel-btn:hover {
            background-color: #e6e6e6;
        }

        .error {
            color: #f44336;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>Modifier une Annonce</h1>

<div class="form-container">
    <?php if (!empty($errors)) : ?>
        <div class="error">
            <?php foreach ($errors as $error) {
                echo htmlspecialchars($error) . "<br>";
            } ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="prenom_conducteur">Prénom Conducteur</label>
            <input type="text" id="prenom_conducteur" name="prenom_conducteur" value="<?= htmlspecialchars($annonce['prenom_conducteur']) ?>" required>
        </div>

        <div class="form-group">
            <label for="nom_conducteur">Nom Conducteur</label>
            <input type="text" id="nom_conducteur" name="nom_conducteur" value="<?= htmlspecialchars($annonce['nom_conducteur']) ?>" required>
        </div>

        <div class="form-group">
            <label for="tel_conducteur">Téléphone</label>
            <input type="text" id="tel_conducteur" name="tel_conducteur" value="<?= htmlspecialchars($annonce['tel_conducteur']) ?>" required>
        </div>

        <div class="form-group">
            <label for="date_depart">Date Départ</label>
            <?php $date_value = str_replace(' ', 'T', $annonce['date_depart']); ?>
            <input type="datetime-local" id="date_depart" name="date_depart" value="<?= htmlspecialchars($date_value) ?>" required>
        </div>

        <div class="form-group">
            <label for="lieu_depart">Lieu Départ</label>
            <input type="text" id="lieu_depart" name="lieu_depart" value="<?= htmlspecialchars($annonce['lieu_depart']) ?>" required>
        </div>

        <div class="form-group">
            <label for="lieu_arrivee">Lieu Arrivée</label>
            <input type="text" id="lieu_arrivee" name="lieu_arrivee" value="<?= htmlspecialchars($annonce['lieu_arrivee']) ?>" required>
        </div>

        <div class="form-group">
            <label for="nombre_places">Nombre de Places</label>
            <input type="number" id="nombre_places" name="nombre_places" value="<?= htmlspecialchars($annonce['nombre_places']) ?>" required min="1">
        </div>

        <div class="form-group">
            <label for="type_voiture">Type Voiture</label>
            <input type="text" id="type_voiture" name="type_voiture" value="<?= htmlspecialchars($annonce['type_voiture']) ?>" required>
        </div>

        <div class="form-group">
            <label for="prix_estime">Prix Estimé (TND)</label>
            <input type="number" id="prix_estime" name="prix_estime" value="<?= htmlspecialchars($annonce['prix_estime']) ?>" required min="0" step="0.01">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($annonce['description']) ?></textarea>
        </div>

        <div class="button-container">
            <a href="annonces.php" class="cancel-btn">Annuler</a>
            <button type="submit" class="submit-btn">Enregistrer les modifications</button>
        </div>
    </form>
</div>
</body>
</html>
