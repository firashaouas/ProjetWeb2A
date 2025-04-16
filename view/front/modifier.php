<?php
require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");
require_once(__DIR__ . "/../../config.php");

// Récupérer le sponsor à modifier
if (!isset($_GET['id'])) {
    header("Location: index.php?error=missing_id");
    exit();
}

$id_sponsor = (int)$_GET['id'];
$controller = new sponsorController();
$sponsor = $controller->getSponsorById($id_sponsor);

if (!$sponsor) {
    header("Location: index.php?error=sponsor_not_found");
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $nom_entreprise = htmlspecialchars($_POST['companyName']);
    $evenement = htmlspecialchars($_POST['evenement']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = (int)$_POST['phone'];
    $montant = (float)$_POST['amount'];
    $duree = htmlspecialchars($_POST['duration']);
    $avantage = htmlspecialchars($_POST['benefits']);
    $status = htmlspecialchars($_POST['status']);

    // Création de l'objet sponsor
    $sponsorObj = new sponsor(
        $nom_entreprise,
        $evenement,
        $email,
        $telephone,
        $montant,
        $duree,
        $avantage,
        $status
    );
    $sponsorObj->setId_sponsor($id_sponsor);

    // Mise à jour
    $success = $controller->updateSponsor($sponsorObj);

    if ($success) {
        header("Location: index.php?update_success=1");
        exit();
    } else {
        $error = "Une erreur est survenue lors de la mise à jour.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Sponsor</title>
 
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 40px;
        color: #333;
    }

    h1 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 30px;
    }

    form {
        max-width: 600px;
        margin: 0 auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }

    input, textarea {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        transition: border 0.3s;
    }

    input:focus, textarea:focus {
        border-color: #3498db;
        outline: none;
    }

    button {
        background-color: #3498db;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #2980b9;
    }

    a {
        display: inline-block;
        margin-top: 10px;
        color: #888;
        text-decoration: none;
        font-size: 14px;
    }

    a:hover {
        text-decoration: underline;
    }

    .error {
        color: red;
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
    }
</style>

</head>
<body>
    <h1>Modifier le Sponsor</h1>
    
    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="companyName">Nom de l'entreprise:</label>
            <input type="text" id="companyName" name="companyName" value="<?= htmlspecialchars($sponsor['nom_entreprise']) ?>" required>
        </div>
        
        <div>
            <label for="contactPerson">Evenement:</label>
            <input type="text" id="evenement" name="evenement" value="<?= htmlspecialchars($sponsor['evenement']) ?>" required>
        </div>
        
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($sponsor['email']) ?>" required>
        </div>
        
        <div>
            <label for="phone">Téléphone:</label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($sponsor['telephone']) ?>" required>
        </div>
        
        <div>
            <label for="amount">Montant:</label>
            <input type="number" step="0.01" id="amount" name="amount" value="<?= htmlspecialchars($sponsor['montant']) ?>" required>
        </div>
        
        <div>
            <label for="duration">Durée:</label>
            <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($sponsor['duree']) ?>" required>
        </div>
        
        <div>
            <label for="benefits">Avantages:</label>
            <textarea id="benefits" name="benefits" required><?= htmlspecialchars($sponsor['avantage']) ?></textarea>
        </div> 
        
        <button type="submit">Mettre à jour</button>
        <a href="index.php">Annuler</a>
    </form>
</body>
</html>