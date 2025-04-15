<?php
require_once '../config.php';

try {
    $pdo = config::getConnexion();
    

    $sql = "SELECT * FROM annonce_covoiturage";  
    $stmt = $pdo->query($sql);
    $annonces = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
    $annonces = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Annonces </title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="container">
        <h1>Liste des Annonces</h1>
        
        <!-- Table pour afficher les annonces -->
        <table border="1">
            <thead>
                <tr>
                    <th>Prénom Conducteur</th>
                    <th>Nom Conducteur</th>
                    <th>Téléphone</th>
                    <th>Date de Départ</th>
                    <th>Lieu de Départ</th>
                    <th>Lieu d'Arrivée</th>
                    <th>Nombre de Places</th>
                    <th>Type de Voiture</th>
                    <th>Prix Estimé</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
               
            <?php if (!empty($annonces)): ?>
                    <?php foreach ($annonces as $annonce): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($annonce['prenom_conducteur']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['nom_conducteur']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['tel_conducteur']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['date_depart']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['lieu_depart']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['lieu_arrivee']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['nombre_places']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['type_voiture']); ?></td>
                            <td><?php echo htmlspecialchars($annonce['prix_estime']); ?>€</td>
                            <td><?php echo htmlspecialchars($annonce['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Aucune annonce disponible.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>