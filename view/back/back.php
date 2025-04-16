<?php
include '../../config.php';
$conn = config::getConnexion();

// Traitement Accepter / Refuser
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'accepter') {
        $stmt = $conn->prepare("UPDATE sponsor SET status = 'accepté' WHERE id_sponsor = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'refuser') {
        $stmt = $conn->prepare("UPDATE sponsor SET status = 'refusé' WHERE id_sponsor = ?");
        $stmt->execute([$id]);
    }
}

// Récupération des demandes
$stmt = $conn->query("SELECT * FROM sponsor ORDER BY id_sponsor DESC");
$sponsors = $stmt->fetchAll();
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sponsors</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f6f4f0;
            display: flex;
        }
        
        .sidebar {
            width: 240px;
            background: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        
        .dashboard {
            margin-left: 240px;
            padding: 20px;
            width: calc(100% - 240px);
        }
        
        /* CARTES CORRIGÉES - C'EST ICI LE PROBLÈME */
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 800px; /* Largeur maximale augmentée */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* CONTENEUR BOUTONS - STYLE GARANTI VISIBLE */
        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-weight: 500;
        }
        
        .btn-accepter {
            background: #4CAF50;
            color: white;
        }
        
        .btn-refuser {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <div>
            <h1>Click'N'Go</h1>
            <div class="menu-item active" data-tab="sponsors">🏠 Sponsors</div>
            <div class="menu-item" data-tab="events">🚗 Événements</div>
            <div class="menu-item">⚙️ Paramètres</div>
        </div>
        <div class="menu-item">🚪 Déconnexion</div>
    </div>
    
    <main class="dashboard">
        <h1>Demandes de sponsoring</h1>
        
        <?php foreach($sponsors as $sponsor): ?>
        <div class="card">
            <h3><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h3>
            <p>Contact: <?= htmlspecialchars($sponsor['evenement']) ?></p>
            <p>Email: <?= htmlspecialchars($sponsor['email']) ?></p>
            <p>Téléphone: <?= htmlspecialchars($sponsor['telephone']) ?></p>
            <p>Montant: <?= htmlspecialchars($sponsor['montant']) ?> €</p>
            
            <!-- CONTENEUR BOUTONS BIEN VISIBLE -->
            <div class="card-actions">
                <a href="?action=accepter&id=<?= $sponsor['id_sponsor'] ?>" 
                   class="btn btn-accepter">Accepter</a>
                <a href="?action=refuser&id=<?= $sponsor['id_sponsor'] ?>" 
                   class="btn btn-refuser"
                   onclick="return confirm('Êtes-vous sûr?')">Refuser</a>
            </div>
        </div>
        <?php endforeach; ?>
    </main>
</body>
</html>