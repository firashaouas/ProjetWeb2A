<?php
require_once '../../config.php';

try {
    $pdo = config::getConnexion();
    $query = "SELECT * FROM annonce_covoiturage";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Annonces</title>
    <style>
        /* Your CSS styles here */
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f2d3e8;
        }
        
        th {
            background-color: #f72975;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #fff0f8;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        
        .btn-edit {
            background-color: #ba68c8;
            color: white;
        }
        
        .btn-delete {
            background-color: #ff5252;
            color: white;
        }
        
        .btn-add {
            background-color: #f72975;
            color: white;
            margin-bottom: 20px;
            padding: 10px 16px;
        }
    </style>
</head>
<body>
    <h1>Liste des Annonces de Covoiturage</h1>
    
    
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Conducteur</th>
                <th>Téléphone</th>
                <th>Date Départ</th>
                <th>Trajet</th>
                <th>Places</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($annonces as $annonce): ?>
            <tr>
                <td><?= htmlspecialchars($annonce['id_conducteur']) ?></td>
                <td><?= htmlspecialchars($annonce['prenom_conducteur'] . ' ' . $annonce['nom_conducteur']) ?></td>
                <td><?= htmlspecialchars($annonce['tel_conducteur']) ?></td>
                <td><?= htmlspecialchars($annonce['date_depart']) ?></td>
                <td><?= htmlspecialchars($annonce['lieu_depart'] . ' → ' . $annonce['lieu_arrivee']) ?></td>
                <td><?= htmlspecialchars($annonce['nombre_places']) ?></td>
                <td><?= htmlspecialchars($annonce['prix_estime']) ?> TND</td>
                <td>
                    <!-- IMPORTANT: Use the full file name and make sure it exists -->
                    <a href="modifier.php?id_conducteur=<?= $annonce['id_conducteur'] ?>" class="btn btn-edit">Modifier</a>
                    <a href="supprimer.php?id_conducteur=<?= $annonce['id_conducteur'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>