<?php
require_once '../../config.php';
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Gestion des messages
if (isset($_GET['success'])) {
    echo '<div class="alert" style="background:#dff0d8;color:#3c763d;padding:15px;margin:20px;border-radius:5px">L\'annonce a été archivée avec succès!</div>';
}
if (isset($_GET['error'])) {
    echo '<div class="alert" style="background:#f2dede;color:#a94442;padding:15px;margin:20px;border-radius:5px">Erreur: '.htmlspecialchars($_GET['error']).'</div>';
}

// Paramètres
$filter = $_GET['filter'] ?? 'active';
$search = $_GET['search'] ?? '';
$order = $_GET['order'] ?? 'date_depart DESC';

try {
    $pdo = config::getConnexion();
    
    $query = "SELECT * FROM annonce_covoiturage WHERE ";
    $query .= ($filter === 'active') ? "(statut = 'active' OR statut IS NULL)" : "statut = 'archivée'";
    
    if (!empty($search)) {
        $query .= " AND (CONCAT(prenom_conducteur,' ',nom_conducteur) LIKE :search OR tel_conducteur LIKE :search)";
    }
    
    $query .= " ORDER BY $order";
    
    $stmt = $pdo->prepare($query);
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    
    $stmt->execute();
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Export PDF
    if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        
        $html = '<h1 style="text-align:center;color:#c63dc9;font-family:Playfair Display">Liste des Annonces '.($filter === 'active' ? 'Actives' : 'Archivées').'</h1>';
        $html .= '<table border="1" cellpadding="8" style="width:100%;border-collapse:collapse">';
        $html .= '<tr style="background:#9c27b0;color:white">
                    <th>ID</th><th>Conducteur</th><th>Téléphone</th>
                    <th>Date Départ</th><th>Trajet</th>
                    <th>Places</th><th>Prix</th><th>Statut</th>
                 </tr>';
        
        foreach ($annonces as $annonce) {
            $html .= '<tr>
                        <td>'.$annonce['id_conducteur'].'</td>
                        <td>'.$annonce['prenom_conducteur'].' '.$annonce['nom_conducteur'].'</td>
                        <td>'.$annonce['tel_conducteur'].'</td>
                        <td>'.$annonce['date_depart'].'</td>
                        <td>'.$annonce['lieu_depart'].' → '.$annonce['lieu_arrivee'].'</td>
                        <td>'.$annonce['nombre_places'].'</td>
                        <td>'.$annonce['prix_estime'].' TND</td>
                        <td style="color:'.(($annonce['statut'] ?? 'active') === 'active' ? '#4CAF50' : '#F44336').'">
                            '.($annonce['statut'] ?? 'active').'
                        </td>
                     </tr>';
        }
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("annonces_".date('Y-m-d').".pdf");
        exit;
    }

} catch (PDOException $e) {
    echo '<div class="alert" style="background:#f2dede;color:#a94442;padding:15px;margin:20px;border-radius:5px">Erreur: '.$e->getMessage().'</div>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click'N'Go - Annonces</title>
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

        /* Sous-menu */
        .has-submenu {
            position: relative;
        }
        
        .submenu-toggle {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        .dropdown-icon {
            transition: transform 0.3s;
            font-size: 14px;
        }
        
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s;
            padding-left: 20px;
        }
        
        .submenu li {
            margin: 8px 0 !important;
        }
        
        .submenu a {
            font-size: 16px !important;
            padding: 10px 15px !important;
        }
        
        .submenu a.active {
            background-color: #f0e6ff;
            color: #9c27b0 !important;
            font-weight: 500;
            border-radius: 8px;
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
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        /* Boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            margin-right: 5px;
            font-size: 14px;
        }

        .btn-archive {
            background: #9c27b0;
            color: white;
        }

        .btn-archive:hover {
            background: #7b1fa2;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-restore {
            background: #4CAF50;
            color: white;
        }

        .btn-restore:hover {
            background: #3e8e41;
            transform: translateY(-2px);
        }

        /* Barre de recherche */
        .search-box {
            margin-bottom: 20px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 25px;
            border: 1px solid #d9e4ff;
            font-size: 16px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c63dc9;
        }

        /* Export PDF */
        .export-btn {
            background: #c63dc9;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            margin-top: 20px;
            float: right;
            text-decoration: none;
        }

        .export-btn i {
            margin-right: 8px;
        }

        /* Tri */
        .sort-arrows {
            margin-left: 5px;
        }

        .sort-arrows a {
            color: white;
            margin: 0 2px;
        }

        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-success {
            background-color: #4CAF50;
            color: white;
        }

        .badge-danger {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Sidebar avec sous-menu -->
    <div class="sidebar">
        <div>
            <div class="logo-container">
                <img src="/clickngoooo/view/images/logo.png" alt="Logo">
            </div>
            <ul>
                <li><a href="dashboard.php">🏠 Tableau de Bord</a></li>
                <li class="has-submenu">
                    <a class="submenu-toggle">
                        📦 Annonces 
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="annonces.php?filter=active" class="<?= $filter === 'active' ? 'active' : '' ?>">
                                <i class="fas fa-circle" style="color:#4CAF50;font-size:12px;margin-right:8px"></i> Actives
                            </a>
                        </li>
                        <li>
                            <a href="annonces.php?filter=archived" class="<?= $filter === 'archived' ? 'active' : '' ?>">
                                <i class="fas fa-circle" style="color:#F44336;font-size:12px;margin-right:8px"></i> Archivées
                            </a>
                        </li>
                    </ul>
                </li>
                <li><a href="demande_list.php">📋 Demandes</a></li>
                <li><a href="avis.php">⭐ Avis</a></li>
            </ul>
        </div>
        <div class="logout">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Liste des Annonces <?= $filter === 'active' ? 'Actives' : 'Archivées' ?></h1>
        </div>

        <!-- Barre de recherche -->
        <div class="search-box">
            <i class="fas fa-search"></i>
            <form method="GET">
                <input type="hidden" name="filter" value="<?= $filter ?>">
                <input type="text" name="search" placeholder="Rechercher par conducteur ou téléphone..." 
                       value="<?= htmlspecialchars($search) ?>" onkeypress="if(event.keyCode==13) this.form.submit()">
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Conducteur</th>
                        <th>Téléphone</th>
                        <th>
                            Date Départ
                            <span class="sort-arrows">
                                <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=date_depart ASC" title="Croissant">↑</a>
                                <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=date_depart DESC" title="Décroissant">↓</a>
                            </span>
                        </th>
                        <th>Trajet</th>
                        <th>Places</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($annonces)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center;">Aucune annonce trouvée</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($annonces as $annonce): ?>
                        <tr>
                            <td><?= $annonce['id_conducteur'] ?></td>
                            <td><?= htmlspecialchars($annonce['prenom_conducteur'].' '.$annonce['nom_conducteur']) ?></td>
                            <td><?= $annonce['tel_conducteur'] ?></td>
                            <td><?= $annonce['date_depart'] ?></td>
                            <td><?= htmlspecialchars($annonce['lieu_depart'].' → '.$annonce['lieu_arrivee']) ?></td>
                            <td><?= $annonce['nombre_places'] ?></td>
                            <td><?= $annonce['prix_estime'] ?> TND</td>
                            <td>
                                <span class="badge <?= ($annonce['statut'] ?? 'active') === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $annonce['statut'] ?? 'active' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (($annonce['statut'] ?? 'active') === 'active'): ?>
                                    <a href="archiver.php?id_conducteur=<?= $annonce['id_conducteur'] ?>" class="btn btn-archive" onclick="return confirm('Archiver cette annonce?')">
                                        <i class="fas fa-archive"></i> Archiver
                                    </a>
                                <?php else: ?>
                                    <a href="restaurer.php?id_conducteur=<?= $annonce['id_conducteur'] ?>" class="btn btn-restore" onclick="return confirm('Restaurer cette annonce?')">
                                        <i class="fas fa-undo"></i> Restaurer
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Bouton Export PDF -->

            <br>
            <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=<?= urlencode($order) ?>&export=pdf" class="export-btn">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </a>
        </div>
    </div>

    <script>
        // Gestion du sous-menu
        document.querySelector('.submenu-toggle').addEventListener('click', function() {
            const submenu = this.closest('.has-submenu').querySelector('.submenu');
            const icon = this.querySelector('.dropdown-icon');
            
            submenu.style.maxHeight = submenu.style.maxHeight ? null : submenu.scrollHeight + 'px';
            icon.style.transform = icon.style.transform === 'rotate(180deg)' ? 'rotate(0deg)' : 'rotate(180deg)';
        });
        
        // Ouvrir automatiquement si on est dans Annonces
        if (window.location.href.includes('annonces.php')) {
            document.querySelector('.submenu').style.maxHeight = '200px';
            document.querySelector('.dropdown-icon').style.transform = 'rotate(180deg)';
        }
        
        // Confirmation avant actions
        const confirmAction = (e) => {
            if (!confirm(e.target.getAttribute('data-confirm') || 'Êtes-vous sûr ?')) {
                e.preventDefault();
            }
        };
        
        document.querySelectorAll('.btn-archive, .btn-restore').forEach(btn => {
            btn.addEventListener('click', confirmAction);
        });
    </script>
</body>
</html>