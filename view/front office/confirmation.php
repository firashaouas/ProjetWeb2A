<?php
// confirmation.php
session_start();

// Vérifiez si l'utilisateur est redirigé après l'insertion
if (!isset($_SESSION['location_success'])) {

    // Si l'enregistrement échoue ou si la session n'est pas configurée, redirigez vers la page d'accueil.
    header("Location: produit.php");
    exit;
}

// Définir la session de succès pour éviter que la page ne s'affiche à nouveau après actualisation
unset($_SESSION['location_success']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de location</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 50px;
            text-align: center;
            background-color: #f2f2f2;
        }
        .message {
            padding: 30px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        a {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="message">
        <h2>✅ Location enregistrée avec succès !</h2>
        <p>Merci pour votre confiance.</p>
        <a href="produit.php">Retour à l'accueil</a>
    </div>
</body>
</html>
