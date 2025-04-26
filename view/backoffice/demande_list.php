<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../Controller/DemandeCovoiturageController.php';
$controller = new DemandeCovoiturageController();

// Keep the POST handling logic but remove the debug output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept'])) {
        $result = $controller->updateDemandeStatus($_POST['accept'], 'accepte');
    } elseif (isset($_POST['reject'])) {
        $result = $controller->updateDemandeStatus($_POST['reject'], 'rejete');
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$demandes = $controller->getAllPendingDemandes();


?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Page des Demandes</title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #fff7fb;
    }

    
    .dashboard-container {
      display: flex;
    }

    .sidebar {
      width: 220px;
      background-color: #ffffff;
      color: #c04e9a;
      padding: 20px;
      height: 100vh;
      box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }

    .sidebar h2 {
      color: #f72975;
      margin-bottom: 25px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar ul li {
      margin-bottom: 15px;
    }

    .sidebar ul li a {
      text-decoration: none;
      color: #fa80d1;
      display: flex;
      align-items: center;
      padding: 8px 10px;
      border-radius: 8px;
    }

    .sidebar ul li a:hover {
      background-color: #fbe3f1;
    }

    .main-content {
      flex: 1;
      padding: 30px;
    }

    h1 {
      color: #f72975;
      margin-bottom: 25px;
      text-align: center;
    }

    .table-container {
      background-color: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid #f2d3e8;
    }

    th {
      background-color: #fce8f5;
      color: #ba68c8;
    }

    tr:hover {
      background-color: #fbe3f1;
    }

    .icon {
      color: #ba68c8;
      margin-right: 6px;
      vertical-align: middle;
    }
    
    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
      margin-right: 6px;
      color: white;
    }

    /* Bouton Approuver en violet */
    .btn.approve {
      background-color: #a64ac9;
    }

    .btn.approve:hover {
      background-color: #9336b4;
      transform: scale(1.05);
    }

    /* Bouton Rejeter en bleu ciel */
    .btn.reject {
      background-color: #4fc3f7;
    }

    .btn.reject:hover {
      background-color: #29b6f6;
      transform: scale(1.05);
    }
    
    .status {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }
    
    .status-en-cours {
      background-color: #fff3e0;
      color: #ff9800;
    }
    
    .status-accepte {
      background-color: #e8f5e9;
      color: #4caf50;
    }
    
    .status-rejete {
      background-color: #ffebee;
      color: #f44336;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <h2>Click'N'Go</h2>
      <ul>
        <li><a href="dashboard.php"><i data-lucide="home"></i>&nbsp;Dashboard</a></li>
        <li><a href="demande_list.php"><i data-lucide="message-square"></i>&nbsp;Demande</a></li>
        <li><a href="annonces.php"><i data-lucide="message-square"></i>&nbsp;Annonces</a></li>
  
      </ul>
    </aside>

    <main class="main-content">
      <h1>Demandes de Covoiturage</h1>
      <div class="table-container">        
        <form method="post">
          <table id="demandesTable">
            <thead>
              <tr>
                <th>#</th>
                <th>Passager</th>
                <th>Départ ➜ Destination</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Places</th>
                <th>Statut</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($demandes)): ?>
                <tr>
                  <td colspan="8" style="text-align: center;">Aucune demande en cours</td>
                </tr>
              <?php else: ?>
                <?php foreach ($demandes as $index => $demande): ?>
                  <?php
                  $statusClass = 'status-en-cours';
                  $statusText = 'En cours';
                  
                  if ($demande['status_demande'] === 'accepte') {
                      $statusClass = 'status-accepte';
                      $statusText = 'Accepté';
                  } elseif ($demande['status_demande'] === 'rejete') {
                      $statusClass = 'status-rejete';
                      $statusText = 'Rejeté';
                  }
                  
                  // Use the actual ID field name from your table
                  $idField = null;
                  // Check each possibility
                  foreach (['id', 'ID', 'id_demande', 'id_demande_covoiturage'] as $fieldName) {
                      if (isset($demande[$fieldName])) {
                          $idField = $fieldName;
                          break;
                      }
                  }
                  
                  $demandeId = $idField ? $demande[$idField] : null;
                  ?>
                  <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($demande['prenom_passager'] . ' ' . $demande['nom_passager']); ?></td>
                    <td>Non spécifié ➜ Non spécifié</td>
                    <td><?php echo date('Y-m-d', strtotime($demande['date_demande'])); ?></td>
                    <td><?php echo date('H:i', strtotime($demande['date_demande'])); ?></td>
                    <td><?php echo htmlspecialchars($demande['nbr_places_reservees']); ?></td>
                    <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                    <td>
  <?php if ($demande['status_demande'] === 'en cours'): ?>
    <button type="submit" name="accept" value="<?php echo $demande['id_passager']; ?>" class="btn approve">Approuver</button>
    <button type="submit" name="reject" value="<?php echo $demande['id_passager']; ?>" class="btn reject">Rejeter</button>
  <?php else: ?>
    Aucune action
  <?php endif; ?>
</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </form>
      </div>
    </main>
  </div>
  
  <script>
    // Initialize Lucide icons
    lucide.createIcons();
  </script>
</body>
</html>