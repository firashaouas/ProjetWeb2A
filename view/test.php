<?php

require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

if (isset($_POST['submit'])) {
    
    $pdo = config::getConnexion();
    
    
    $controller = new AnnonceCovoiturageController($pdo);

    try {
        
        $message = $controller->ajouterAnnonce($_POST);
        echo "<p style='color: green;'>$message</p>"; 
    } catch (Exception $e) {
       
        echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
    }
}

?>

<!-- The Form -->
<form method="post" action="">
    <label for="prenom_conducteur">Prénom Conducteur</label>
    <input type="text" name="prenom_conducteur" id="prenom_conducteur" required><br>

    <label for="nom_conducteur">Nom Conducteur</label>
    <input type="text" name="nom_conducteur" id="nom_conducteur" required><br>

    <label for="tel_conducteur">Téléphone Conducteur</label>
    <input type="text" name="tel_conducteur" id="tel_conducteur" required><br>

    <label for="date_depart">Date de Départ</label>
    <input type="datetime-local" name="date_depart" id="date_depart" required><br>

    <label for="lieu_depart">Lieu de Départ</label>
    <input type="text" name="lieu_depart" id="lieu_depart" required><br>

    <label for="lieu_arrivee">Lieu d'Arrivée</label>
    <input type="text" name="lieu_arrivee" id="lieu_arrivee" required><br>

    <label for="nombre_places">Nombre de Places</label>
    <input type="number" name="nombre_places" id="nombre_places" required><br>

    <label for="type_voiture">Type de Voiture</label>
    <input type="text" name="type_voiture" id="type_voiture" required><br>

    <label for="prix_estime">Prix Estimé</label>
    <input type="number" name="prix_estime" id="prix_estime" step="0.01" required><br>

    <label for="description">Description</label>
    <textarea name="description" id="description" placeholder="Description (optionnel)"></textarea><br>

    <input type="submit" name="submit" value="Ajouter">
</form>
