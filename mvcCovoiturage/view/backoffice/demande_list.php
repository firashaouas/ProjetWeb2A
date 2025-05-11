<?php
require_once '../../config.php';
require_once '../../Controller/DemandeCovoiturageController.php';

// Connexion DB
try {
    $pdo = new PDO("mysql:host=localhost;dbname=click'n'go", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB: ".$e->getMessage());
}

$controller = new DemandeCovoiturageController($pdo);

// Fonction pour récupérer le trajet par conducteur
function getTrajetByConducteur($pdo, $id_conducteur) {
    if (!$id_conducteur) return ['lieu_depart'=>'', 'lieu_arrivee'=>''];
    $stmt = $pdo->prepare("SELECT lieu_depart, lieu_arrivee FROM annonce_covoiturage WHERE id_conducteur=? ORDER BY date_depart DESC LIMIT 1");
    $stmt->execute([$id_conducteur]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['lieu_depart'=>'', 'lieu_arrivee'=>''];
}

// Fonction pour récupérer les infos passager
function getPassagerInfo($pdo, $id_passager) {
    if (!$id_passager) return ['prenom_passager'=>'Inconnu', 'nom_passager'=>''];
    $stmt = $pdo->prepare("SELECT prenom_passager, nom_passager FROM demande_covoiturage WHERE id_passager=?");
    $stmt->execute([$id_passager]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['prenom_passager'=>'Inconnu', 'nom_passager'=>''];
}

// Filtre et tri
$status_filter = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'date_desc';

// Fonction pour normaliser les statuts
function normalizeStatus($status) {
    $status = strtolower(trim($status));
    if (strpos($status, 'approuv') !== false) return 'approuvée';
    if (strpos($status, 'rejet') !== false) return 'rejetée';
    return 'en cours';
}

// Export Excel
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="demandes.xls"');
    
    $demandes = $controller->getAllDemandes();
    
    echo "<table border='1'>";
    echo "<tr>
            <th>ID Passager</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Départ</th>
            <th>Arrivée</th>
            <th>Date/Heure</th>
            <th>Places</th>
            <th>Statut</th>
          </tr>";
    
    foreach ($demandes as $d) {
        $current_status = normalizeStatus($d['status_demande'] ?? 'en cours');
        
        if ($status_filter !== 'all' && $current_status !== normalizeStatus($status_filter)) {
            continue;
        }
        
        $trajet = getTrajetByConducteur($pdo, $d['id_conducteur'] ?? null);
        $passager = getPassagerInfo($pdo, $d['id_passager'] ?? null);
        
        echo "<tr>
                <td>".htmlspecialchars($d['id_passager'] ?? '')."</td>
                <td>".htmlspecialchars($passager['prenom_passager'] ?? '')."</td>
                <td>".htmlspecialchars($passager['nom_passager'] ?? '')."</td>
                <td>".htmlspecialchars($trajet['lieu_depart'] ?? '')."</td>
                <td>".htmlspecialchars($trajet['lieu_arrivee'] ?? '')."</td>
                <td>".(isset($d['date_demande']) ? date('d/m/Y H:i', strtotime($d['date_demande'])) : '')."</td>
                <td>".htmlspecialchars($d['nbr_places_reservees'] ?? '')."</td>
                <td>".htmlspecialchars($current_status)."</td>
              </tr>";
    }
    
    echo "</table>";
    exit();
}

// Récupération et tri des données
$all_demandes = $controller->getAllDemandes();

// Fonction de comparaison pour le tri
function compareDemandes($a, $b, $sort) {
    switch ($sort) {
        case 'status_asc':
            return strcmp(normalizeStatus($a['status_demande']), normalizeStatus($b['status_demande']));
        case 'status_desc':
            return strcmp(normalizeStatus($b['status_demande']), normalizeStatus($a['status_demande']));
        case 'date_asc':
            return strtotime($a['date_demande']) <=> strtotime($b['date_demande']);
        case 'date_desc':
        default:
            return strtotime($b['date_demande']) <=> strtotime($a['date_demande']);
    }
}

// Appliquer le tri
usort($all_demandes, function($a, $b) use ($sort) {
    return compareDemandes($a, $b, $sort);
});

$demandes_a_afficher = [];
foreach ($all_demandes as $d) {
    $current_status = normalizeStatus($d['status_demande'] ?? 'en cours');
    
    if ($status_filter !== 'all' && $current_status !== normalizeStatus($status_filter)) {
        continue;
    }
    
    $trajet = getTrajetByConducteur($pdo, $d['id_conducteur'] ?? null);
    $passager = getPassagerInfo($pdo, $d['id_passager'] ?? null);
    
    $demandes_a_afficher[] = [
        'id_passager' => $d['id_passager'] ?? '',
        'prenom' => $passager['prenom_passager'] ?? '',
        'nom' => $passager['nom_passager'] ?? '',
        'depart' => $trajet['lieu_depart'] ?? '',
        'arrivee' => $trajet['lieu_arrivee'] ?? '',
        'date' => isset($d['date_demande']) ? date('d/m/Y H:i', strtotime($d['date_demande'])) : '',
        'places' => $d['nbr_places_reservees'] ?? '',
        'statut' => $current_status
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes - Click'N'Go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            height: 100vh;
            position: fixed;
            border-radius: 0 20px 20px 0;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #ffffff, #f9f9f9);
        }

        .sidebar .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #d9e4ff;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: #333;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .sidebar ul li a:hover {
            color: rgb(222, 178, 255);
            transform: translateX(5px);
        }

        .sidebar .logout {
            margin-top: auto;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .page-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            color: #c63dc9;
            font-family: 'Playfair Display';
            font-size: 32px;
        }

        /* Filtres et tri */
        .filter-sort-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-box, .sort-box {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
        }

        .filter-box h3, .sort-box h3 {
            margin-bottom: 10px;
            color: #c63dc9;
            font-size: 16px;
        }

        select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #d9e4ff;
            font-size: 14px;
        }

        /* Table */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: linear-gradient(145deg, #ff8acb, #a7bfff);
            color: white;
            position: relative;
        }

        th.sortable:hover {
            cursor: pointer;
            background: linear-gradient(145deg, #e67ab5, #95a5e6);
        }

        .sort-arrow {
            margin-left: 5px;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        /* Export Excel */
        .export-btn {
            background:#c63dc9;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            margin-top: 30px;
            float: right;
            text-decoration: none;
            transition: all 0.3s;
        }

        .export-btn:hover {
            background:rgb(245, 169, 245);
            transform: translateY(-2px);
        }

        .export-btn i {
            margin-right: 8px;
        }

        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-approved {
            background-color: #4CAF50;
            color: white;
        }

        .badge-rejected {
            background-color: #f44336;
            color: white;
        }

        .badge-pending {
            background-color:rgb(138, 195, 248);
            color: #000;
        }
    </style>
</head>
<body>


    <!-- Sidebar -->
    <div class="sidebar">

        <div>
            <div class="logo-container">
            <img src="/clickngo/public/images/téléchargement__5_-removebg-preview.png" alt="Logo">
            </div><br><br>
            <ul>
                
                <li><a href="dashboard.php">🏠 Tableau de Bord</a></li>
                <li><a href="annonces.php">   📢 Annonces</a></li>
                <li><a href="demande_list.php" style="color: rgb(222, 178, 255);">📋 Demandes</a></li>
                
            </ul>
        </div>
        <div class="logout">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Liste des Demandes</h1>
        </div>

        <!-- Filtres et tri -->
        <div class="filter-sort-container">
            <div class="filter-box">
                <h3>Filtrer par statut</h3>
                <form method="GET">
                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tous les statuts</option>
                        <option value="approuvée" <?= $status_filter === 'approuvée' ? 'selected' : '' ?>>Approuvée</option>
                        <option value="rejetée" <?= $status_filter === 'rejetée' ? 'selected' : '' ?>>Rejetée</option>
                        <option value="en cours" <?= $status_filter === 'en cours' ? 'selected' : '' ?>>En attente</option>
                    </select>
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                </form>
            </div>
            
            <div class="sort-box">
                <h3>Trier par</h3>
                <form method="GET">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date (récent)</option>
                        <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Date (ancien)</option>
                        <option value="status_asc" <?= $sort === 'status_asc' ? 'selected' : '' ?>>Statut (A-Z)</option>
                        <option value="status_desc" <?= $sort === 'status_desc' ? 'selected' : '' ?>>Statut (Z-A)</option>
                    </select>
                    <input type="hidden" name="status" value="<?= $status_filter ?>">
                </form>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Passager</th>
                        <th>Prénom</th>
                        <th>Nom</th>
                        <th>Départ</th>
                        <th>Arrivée</th>
                        <th class="sortable" onclick="window.location='?status=<?= $status_filter ?>&sort=<?= $sort === 'date_desc' ? 'date_asc' : 'date_desc' ?>'">
                            Date/Heure
                            <span class="sort-arrow">
                                <?php if ($sort === 'date_desc'): ?>↓<?php elseif ($sort === 'date_asc'): ?>↑<?php endif; ?>
                            </span>
                        </th>
                        <th>Places</th>
                        <th class="sortable" onclick="window.location='?status=<?= $status_filter ?>&sort=<?= $sort === 'status_desc' ? 'status_asc' : 'status_desc' ?>'">
                            Statut
                            <span class="sort-arrow">
                                <?php if ($sort === 'status_desc'): ?>↓<?php elseif ($sort === 'status_asc'): ?>↑<?php endif; ?>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes_a_afficher)): ?>
                        <tr><td colspan="8" style="text-align:center;">Aucune demande trouvée</td></tr>
                    <?php else: ?>
                        <?php foreach ($demandes_a_afficher as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['id_passager']) ?></td>
                            <td><?= htmlspecialchars($d['prenom']) ?></td>
                            <td><?= htmlspecialchars($d['nom']) ?></td>
                            <td><?= htmlspecialchars($d['depart']) ?></td>
                            <td><?= htmlspecialchars($d['arrivee']) ?></td>
                            <td><?= htmlspecialchars($d['date']) ?></td>
                            <td><?= htmlspecialchars($d['places']) ?></td>
                            <td>
                                <?php if ($d['statut'] === 'approuvée'): ?>
                                    <span class="badge badge-approved">Approuvée</span>
                                <?php elseif ($d['statut'] === 'rejetée'): ?>
                                    <span class="badge badge-rejected">Rejetée</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">En attente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="?status=<?= urlencode($status_filter) ?>&sort=<?= urlencode($sort) ?>&export=1" class="export-btn">
                <i class="fas fa-file-excel"></i> Exporter en Excel
            </a>
        </div>
    </div>

    <script>
        // Confirmation avant actions
        const confirmAction = (e) => {
            if (!confirm(e.target.getAttribute('data-confirm') || 'Êtes-vous sûr ?')) {
                e.preventDefault();
            }
        };
        
        document.querySelectorAll('.btn-action').forEach(btn => {
            btn.addEventListener('click', confirmAction);
        });
    </script>
</body>
</html>