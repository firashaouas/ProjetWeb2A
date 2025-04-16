<?php
// Assure-toi que le fichier contenant la classe User est inclus
require_once __DIR__.'/../../Model/User.php';  // Ou le chemin correct vers ta classe User

// Connexion à la base de données (avec PDO)
$db = Config::getConnexion();  // Assure-toi que Config::getConnexion() est correctement défini

// Vérifier si l'ID est passé en paramètre GET pour la suppression
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Appel de la méthode deleteById pour supprimer l'utilisateur
    if (User::deleteById($db, $id)) {
        echo "Utilisateur supprimé avec succès.";
        // Redirection après suppression (facultatif)
        header("Location: indeex.php"); // Cette ligne redirige l'utilisateur vers la page 'indeex.php'
        exit;
    } else {
        echo "Erreur lors de la suppression de l'utilisateur.";
    }
} else {
    echo "Aucun ID spécifié pour la suppression.";
}
?>
