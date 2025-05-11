<?php
$originalDir = getcwd();

chdir('../../Controller'); 

require_once '../config.php'; // Resolves to C:\xampp\htdocs\clickngoooo\config.php
require_once 'AnnonceCovoiturageController.php';
require_once 'DemandeCovoiturageController.php';

chdir($originalDir);

// Initialize controllers
$pdo = config::getConnexion();
$annonceController = new AnnonceCovoiturageController($pdo);
$demandeController = new DemandeCovoiturageController($pdo); // Pass PDO instance

// Fetch all data
$allAnnonces = $annonceController->getAllAnnonces();
$allDemandes = $demandeController->getAllDemandes();

// Get total counts
$totalAnnonces = count($allAnnonces);
$totalDemandes = count($allDemandes);
$totalAvis = 0; 

// Fetch destination statistics - lieux d'arrivée les plus utilisés (top 5)
try {
    $stmt = $pdo->query("
        SELECT 
            lieu_arrivee as destination, 
            COUNT(*) as destination_count
        FROM annonce_covoiturage
        GROUP BY lieu_arrivee
        ORDER BY destination_count DESC
        LIMIT 5
    ");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $destinations = [];
    error_log("Error fetching destination stats: " . $e->getMessage());
}

// Encode the destinations data as JSON for JavaScript to use
$destinationsJson = json_encode(['destinations' => $destinations]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap" rel="stylesheet">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #fff;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            height: 100vh;
            position: fixed;
            border-radius: 0 20px 20px 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            animation: slideIn 0.8s ease-out;
            background: linear-gradient(180deg, #ffffff, #f9f9f9);
        }

        @keyframes slideIn {
            from { transform: translateX(-50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .sidebar .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            background-color: #d9e4ff;
        }

        .sidebar .logo-container img:hover {
            transform: scale(1.05);
        }

        .sidebar h2 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: rgb(198, 61, 201);
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            flex-grow: 1;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: rgb(222, 178, 255);
            transform: translateX(5px);
        }

        .sidebar ul li a i {
            margin-right: 12px;
            font-size: 20px;
        }

        .sidebar .logout {
            margin-top: auto;
        }

        .sidebar .logout a {
            text-decoration: none;
            color: rgb(235, 162, 254);
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar .logout a:hover {
            background: #d9e4ff;
            color: #fff;
            transform: translateX(5px);
        }

        .sidebar .logout a i {
            margin-right: 12px;
            font-size: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .dashboard-header {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #2d2d2d;
            text-align: center;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '✨';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            opacity: 0.5;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: #ff8fa3;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 18px;
            color: #666;
        }

        .card i {
            font-size: 40px;
            color: #ff8fa3;
            margin-bottom: 10px;
        }

        /* Statistics Section */
        .stats-section {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .stats-section h3 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #ff8fa3;
            text-align: center;
            margin-bottom: 20px;
        }

        .stats-section .chart-container {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            height: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
            border-radius: 15px;
        }

        .stats-section .stats-details {
            text-align: center;
            margin-top: 20px;
        }

        .stats-section .stats-details p {
            font-size: 18px;
            color: #666;
            margin: 5px 0;
        }

        .stats-section .stats-details p span:first-child {
            font-weight: 500;
            color: #333;
        }

        .stats-section .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-top: 20px;
        }

        /* Navigation Buttons Styles */
        .navigation-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .navigation-buttons a, .navigation-buttons button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            color: #fff;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
        }

        .navigation-buttons a:hover, .navigation-buttons button:hover {
            transform: scale(1.1);
        }

        .nav-annonces { background: linear-gradient(145deg, #ff8acb, #a7bfff); }
        .nav-archiver { background: linear-gradient(145deg, #ff6f61, #ff9a8b); }
        .nav-restaurer { background: linear-gradient(145deg, #6b7280, #9ca3af); }
        .nav-demandes { background: linear-gradient(145deg, #60a5fa, #93c5fd); }
        .nav-previous { background: linear-gradient(145deg, #a1a1aa, #d4d4d8); }

        /* Popup Styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 1000;
        }

        .popup {
            background: linear-gradient(145deg, #ff8acb, #a7bfff);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 250px;
            position: absolute;
            top: 20px;
            right: 20px;
            text-align: center;
            color: #fff;
            animation: popupFadeIn 0.5s ease-out;
            cursor: pointer;
        }

        @keyframes popupFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .popup h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .popup p {
            font-size: 14px;
            margin-bottom: 0;
        }

        .popup .close-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #fff;
            color: #ff8acb;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .popup .close-btn:hover {
            background: #ff8acb;
            color: #fff;
            transform: rotate(90deg);
        }

        .popup .close-btn {
            cursor: pointer;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                border-radius: 0 15px 15px 0;
            }

            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .sidebar .logo-container img {
                width: 60px;
                height: 60px;
            }

            .sidebar h2 {
                font-size: 20px;
            }

            .sidebar ul li a {
                font-size: 16px;
                padding: 10px 12px;
            }

            .sidebar .logout a {
                font-size: 16px;
                padding: 10px 12px;
            }

            .stats-section .chart-container {
                height: 350px;
            }

            .stats-section .stats-details p,
            .stats-section .no-data {
                font-size: 16px;
            }

            .navigation-buttons a, .navigation-buttons button {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .popup {
                width: 200px;
                top: 10px;
                right: 10px;
            }

            .popup h3 {
                font-size: 18px;
            }

            .popup p {
                font-size: 12px;
            }

            .popup .close-btn {
                width: 20px;
                height: 20px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                border-radius: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .sidebar .logo-container img {
                width: 50px;
                height: 50px;
            }

            .sidebar h2 {
                font-size: 18px;
            }

            .sidebar ul li a {
                font-size: 14px;
                padding: 8px 10px;
            }

            .sidebar .logout a {
                font-size: 14px;
                padding: 8px 10px;
            }

            .stats-section .chart-container {
                height: 300px;
            }

            .stats-section .stats-details p,
            .stats-section .no-data {
                font-size: 14px;
            }

            .navigation-buttons a, .navigation-buttons button {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .popup {
                width: 180px;
                top: 5px;
                right: 5px;
            }

            .popup h3 {
                font-size: 16px;
            }

            .popup p {
                font-size: 10px;
            }

            .popup .close-btn {
                width: 18px;
                height: 18px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Popup Notification -->
    <div class="popup-overlay" id="popupOverlay">
        <div class="popup" onclick="redirectToAnnonces(event)">
            <button class="close-btn" onclick="closePopup(event)">✖</button>
            <h3>Rappel 📢</h3>
            <p>Reminder: Check new annonces! You have <?php echo htmlspecialchars($totalAnnonces); ?> annonces to review.</p>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="logo-container">
            <img src="/clickngo/public/images/téléchargement__5_-removebg-preview.png" alt="Logo">
            </div><br><br>
            <ul>
                <li><a href="dashboard.php?page=1"> 🏠 Tableau de Bord</a></li>
                <li><a href="annonces.php">  📢 Annonces</a></li>
                <li class="submenu">
                    
                    <ul style="display: none; padding-left: 20px; margin-top: 10px; background: #f9f9f9; border-radius: 10px;">
                        <li style="margin: 10px 0;"><a href="annonces.php?status=active" style="padding: 8px 15px;">
                            <span style="display: inline-block; width: 10px; height: 10px; background-color: #4CAF50; border-radius: 50%; margin-right: 10px;"></span> Actives
                        </a></li>
                        <li style="margin: 10px 0;"><a href="annonces.php?status=archived" style="padding: 8px 15px;">
                            <span style="display: inline-block; width: 10px; height: 10px; background-color: #f44336; border-radius: 50%; margin-right: 10px;"></span> Archivées
                        </a></li>
                    </ul>
                </li>
                <li><a href="demande_list.php">  📋 Demandes</a></li>
               
            </ul>
        </div>
        <div class="logout">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">

    
    <a href="/Projet Web/mvcUtilisateur/View/BackOffice/indeex.php">🏠 Acceuil</a>


    <div class="user-profile">
          <?php if (isset($_SESSION['user'])): ?>
            <?php
            $photoPath = $_SESSION['user']['profile_picture'] ?? '';
            $fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';

            // Correction du chemin relatif pour le test file_exists (chemin serveur)
            $photoRelativePath = '../../mvcUtilisateur/View/FrontOffice/' . $photoPath;
            $absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
            $showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);
            ?>

            <?php if ($showPhoto): ?>
              <!-- Affichage de la photo (chemin URL côté client) -->
              <img src="/Projet Web/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>"
                alt="Photo de profil"
                class="profile-photo"
                onclick="toggleDropdown()">
            <?php else: ?>
              <!-- Cercle avec initiale -->
              <div class="profile-circle"
                style="background-color: <?= function_exists('stringToColor') ? stringToColor($fullName) : '#999' ?>;"
                onclick="toggleDropdown()">
                <?= strtoupper(htmlspecialchars(substr($fullName, 0, 1))) ?>
              </div>
            <?php endif; ?>

            <!-- Menu déroulant -->
            <div class="dropdown-menu" id="dropdownMenu">
              <a href="/Projet Web/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
              <a href="/Projet Web/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
            </div>
          <?php endif; ?>
        </div>





        <script>
          // Fonction pour ouvrir/fermer le menu
          function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            if (menu.style.display === 'block') {
              menu.style.display = 'none';
            } else {
              menu.style.display = 'block';
            }
          }

          // ✅ Fermer le menu si on clique en dehors
          document.addEventListener('click', function(event) {
            const menu = document.getElementById('dropdownMenu');
            const profile = document.querySelector('.user-profile');
            if (!profile.contains(event.target)) {
              menu.style.display = 'none';
            }
          });
        </script>
        <style>
          .user-profile {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
          }

          .profile-photo,
          .profile-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
          }

          .profile-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #666;
            color: white;
            font-weight: bold;
            font-size: 18px;
          }

          .dropdown-menu {
            display: none;
            position: absolute;
            top: 55px;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 160px;
            overflow: hidden;
            z-index: 1001;
          }

          .dropdown-menu a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
          }

          .dropdown-menu a:hover {
            background-color: #f5f5f5;
          }

          /* Style pour le menu déroulant */
          .dropdown-menu {
            display: none;
            position: absolute;
            top: 60px;
            right: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 10px;
            z-index: 1000;
          }

          .dropdown-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
          }

          .dropdown-menu a:hover {
            background-color: #f9f9f9;
          }
        </style>


        <div class="dashboard-header">
            <h1>Tableau de Bord </h1>
        </div>
        
        <div class="dashboard-cards">
            <div class="card">
                <i class="fas fa-box"></i>
                <h3>Annonces</h3>
                <p><?php echo htmlspecialchars($totalAnnonces); ?> annonces</p>
            </div>
            <div class="card">
                <i class="fas fa-list"></i>
                <h3>Demandes</h3>
                <p><?php echo htmlspecialchars($totalDemandes); ?> demandes</p>
            </div>
           
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="chart-container">
                <canvas id="destinationStatsChart"></canvas>
            </div>
            <div class="stats-details" id="statsDetails">
                <!-- Details will be populated by JavaScript -->
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <a href="annonces.php" class="nav-annonces" title="Annonces"><i class="fas fa-box"></i></a>
         
            <a href="demande_list.php" class="nav-demandes" title="Demandes"><i class="fas fa-list"></i></a>
            <button onclick="window.history.back()" class="nav-previous" title="Précédent"><i class="fas fa-arrow-left"></i></button>
        </div>
    </div>

    <!-- Embed the destination stats as JSON -->
    <script>
        const destinationStats = <?php echo $destinationsJson; ?>;
    </script>

    <!-- JavaScript to Display Statistics, Handle Submenu, and Control Popup -->
    <script>
        // Toggle submenu 
        document.addEventListener('DOMContentLoaded', function() {
            const submenuTrigger = document.querySelector('.submenu > a');
            const submenu = document.querySelector('.submenu > ul');
            
            if (submenuTrigger) {
                submenuTrigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (submenu.style.display === 'none' || submenu.style.display === '') {
                        submenu.style.display = 'block';
                        this.querySelector('.fa-chevron-down').classList.replace('fa-chevron-down', 'fa-chevron-up');
                    } else {
                        submenu.style.display = 'none';
                        this.querySelector('.fa-chevron-up').classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                });
            }
            
            // Define active page links
            const pageLinks = {
                'annonces.php': ['C:\\xampp\\htdocs\\clickngo\\view\\backoffice\\annonces.php'],
              
                'demande_list.php': ['C:\\xampp\\htdocs\\clickngo\\view\\backoffice\\demande_list.php']
            };

            // Show the popup on page load
            const popupOverlay = document.getElementById('popupOverlay');
            popupOverlay.style.display = 'block';
        });

        // Function to close the popup
        function closePopup(event) {
            event.stopPropagation();
            const popupOverlay = document.getElementById('popupOverlay');
            popupOverlay.style.display = 'none';
        }

        // Function to redirect to annonces.php when clicking the popup
        function redirectToAnnonces(event) {
            if (event.target.classList.contains('close-btn')) {
                return;
            }
            window.location.href = 'annonces.php';
        }

        // Access the embedded JSON data
        const data = destinationStats;

        const statsDetails = document.getElementById('statsDetails');
        const chartContainer = document.querySelector('.chart-container');

        // Check if data is empty
        if (!data.destinations || data.destinations.length === 0) {
            chartContainer.style.display = 'none';
            statsDetails.innerHTML = '<p class="no-data">Aucune donnée disponible</p>';
        } else {
            // Prepare data for the chart
            const labels = data.destinations.map(destination => destination.destination);
            const destinationCounts = data.destinations.map(destination => destination.destination_count);

            // Define gradient colors for the doughnut chart
            const generateGradients = (ctx) => {
                const gradients = [];
                const colors = [
                    { start: '#ff8acb', end: '#a7bfff' },
                    { start: '#ff8acb', end: '#d9e4ff' },
                    { start: '#ffa8da', end: '#98b8ff' },
                    { start: '#ffbce3', end: '#c4d6ff' },
                    { start: '#ff9ad2', end: '#8aaeff' }
                ];
                
                colors.forEach(color => {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, color.start);
                    gradient.addColorStop(1, color.end);
                    gradients.push(gradient);
                });
                
                return gradients;
            };

            // Create the doughnut chart using Chart.js
            const ctx = document.getElementById('destinationStatsChart').getContext('2d');
            const gradients = generateGradients(ctx);
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: destinationCounts,
                        backgroundColor: gradients,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Roboto, sans-serif'
                                },
                                color: '#333',
                                padding: 20
                            }
                        },
                        title: {
                            display: true,
                            text: 'Les destinations les plus demandées',
                            font: {
                                size: 18,
                                family: 'Playfair Display, serif',
                                weight: 'bold'
                            },
                            color: '#ff8acb',
                            padding: {
                                bottom: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} trajets (${percentage}%)`;
                                }
                            },
                            titleFont: {
                                family: 'Roboto, sans-serif',
                                size: 14
                            },
                            bodyFont: {
                                family: 'Roboto, sans-serif',
                                size: 14
                            },
                            padding: 12,
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#ff8acb',
                            bodyColor: '#333',
                            borderColor: '#ffeaf2',
                            borderWidth: 1
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutCirc'
                    }
                }
            });

            // Display the destination statistics details with more styling
            if (data.destinations.length > 0) {
                const mostPopularDestination = data.destinations[0];
                const totalTrips = data.destinations.reduce((sum, dest) => sum + parseInt(dest.destination_count), 0);
                
                statsDetails.innerHTML = `
                    <div style="margin-top: 30px; padding: 15px; background: linear-gradient(145deg, #ff8acb, #a7bfff); border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); color: white;">
                       
                        <p><span style="font-weight: 500; color: white;">Destination la Plus Populaire:</span> <span style="color: white;">${mostPopularDestination.destination}</span></p>
                        <p><span style="font-weight: 500; color: white;">Nombre de Trajets:</span> <span style="color: white;">${mostPopularDestination.destination_count} sur un total de ${totalTrips} trajets</span></p>
                        <p><span style="font-weight: 500; color: white;">Pourcentage:</span> <span style="color: white;">${Math.round((mostPopularDestination.destination_count/totalTrips)*100)}% des trajets</span></p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>