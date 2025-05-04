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
$demandeController = new DemandeCovoiturageController();

// Fetch data for dashboard overview
$totalAnnonces = count($annonceController->getAllAnnonces());
$totalDemandes = count($demandeController->getAllDemandes());
$totalAvis = 0; 

// Fetch driver statistics directly in this file
try {
    $stmt = $pdo->query("
        SELECT 
            id_conducteur, 
            prenom_conducteur, 
            nom_conducteur, 
            COUNT(*) as annonce_count
        FROM annonce_covoiturage
        GROUP BY id_conducteur, prenom_conducteur, nom_conducteur
        ORDER BY annonce_count DESC
        LIMIT 5
    ");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $drivers = [];
    error_log("Error fetching driver stats: " . $e->getMessage());
}

// Encode the drivers data as JSON for JavaScript to use
$driversJson = json_encode(['drivers' => $drivers]);
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
            /* Revert to original gradient (pink to light blue) */
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
            max-width: 500px; /* Adjusted for radar chart */
            margin: 0 auto;
            position: relative;
            height: 400px; /* Increased height for radar chart */
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
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="logo-container">
                <img src="/clickngoooo/view/images/logo.png" alt="Logo">
            </div><br><br>
            <ul>
                <li><a href="dashboard.php"> 🏠 Tableau de Bord</a></li>
                <li><a href="annonces.php">  📦 Annonces</a></li>
                <li><a href="demandes_list.php">  📋 Demandes</a></li>
                <li><a href="avis.php">      ⭐ Avis</a></li>
            </ul>
        </div>
        <div class="logout">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Tableau de Bord 🎀</h1>
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
            <div class="card">
                <i class="fas fa-star"></i>
                <h3>Avis</h3>
                <p><?php echo htmlspecialchars($totalAvis); ?> avis</p>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <h3>Statistiques des Conducteurs</h3>
            <div class="chart-container">
                <canvas id="driverStatsChart"></canvas>
            </div>
            <div class="stats-details" id="statsDetails">
                <!-- Details will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Embed the driver stats as JSON -->
    <script>
        const driverStats = <?php echo $driversJson; ?>;
    </script>

    <!-- JavaScript to Display Statistics -->
    <script>
        // Access the embedded JSON data
        const data = driverStats;

        const statsDetails = document.getElementById('statsDetails');
        const chartContainer = document.querySelector('.chart-container');

        // Check if data is empty
        if (!data.drivers || data.drivers.length === 0) {
            chartContainer.style.display = 'none';
            statsDetails.innerHTML = '<p class="no-data">Aucune donnée disponible</p>';
        } else {
            // Prepare data for the chart
            const labels = data.drivers.map(driver => `${driver.prenom_conducteur} ${driver.nom_conducteur}`);
            const annonceCounts = data.drivers.map(driver => driver.annonce_count);

            // Define elegant colors for the radar chart
            const colors = {
                backgroundColor: 'rgba(255, 127, 127, 0.3)', // Soft coral fill
                borderColor: 'rgba(255, 127, 127, 0.8)',     // Coral border
                pointBackgroundColor: 'rgba(255, 182, 127, 0.8)', // Soft peach points
                pointBorderColor: '#fff',                    // White point borders
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(255, 127, 127, 1)'
            };

            // Create the radar chart using Chart.js
            const ctx = document.getElementById('driverStatsChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre d\'Annonces',
                        data: annonceCounts,
                        backgroundColor: colors.backgroundColor,
                        borderColor: colors.borderColor,
                        pointBackgroundColor: colors.pointBackgroundColor,
                        pointBorderColor: colors.pointBorderColor,
                        pointHoverBackgroundColor: colors.pointHoverBackgroundColor,
                        pointHoverBorderColor: colors.pointHoverBorderColor,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1, // Ensure whole numbers for announcement counts
                                font: {
                                    size: 12,
                                    family: 'Roboto, sans-serif'
                                },
                                color: '#666'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            pointLabels: {
                                font: {
                                    size: 14,
                                    family: 'Roboto, sans-serif'
                                },
                                color: '#333'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Roboto, sans-serif'
                                },
                                color: '#333'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Comparaison des Annonces par Conducteur',
                            font: {
                                size: 16,
                                family: 'Playfair Display, serif'
                            },
                            color: '#333'
                        }
                    }
                }
            });

            // Display the most active driver's details
            const mostActiveDriver = data.drivers[0];
            if (mostActiveDriver) {
                statsDetails.innerHTML = `
                    <p><span>Conducteur le Plus Actif:</span> ${mostActiveDriver.prenom_conducteur} ${mostActiveDriver.nom_conducteur}</p>
                    <p><span>Annonces:</span> ${mostActiveDriver.annonce_count}</p>
                `;
            }
        }
    </script>
</body>
</html>