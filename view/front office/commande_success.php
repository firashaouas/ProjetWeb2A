<?php

session_start();

// Vérifiez si la session de succès est configurée
if (!isset($_SESSION['commande_success'])) {
    // Si la session n'est pas configurée, redirigez vers la page d'accueil.
    header("Location: produit.php");
    exit;
}

// Supprimez la variable de session pour ne pas afficher le message à nouveau lors d'une actualisation
unset($_SESSION['commande_success']);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de Commande</title>
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
        h2 {
            color: #28a745; /* Green color for success */
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
        <h2>✅ Commande enregistrée avec succès !</h2>
        <p>Votre achat a été traité avec succès. Merci pour votre confiance.</p>
        <a href="produit.php">Retour à la boutique</a>
    </div>
</body>
</html>
<!-- Dans commande_success.php -->
<script>
    // Supprime le panier du localStorage une fois la commande validée
    localStorage.removeItem('panier');
    
    // Optionnel : Rafraîchir le compteur du panier (si vous en avez un)
    if (typeof updateCartCount === 'function') {
        updateCartCount(0); // Remet le compteur à 0
    }
</script>