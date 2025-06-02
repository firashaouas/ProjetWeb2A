<?php if (isset($_GET['deleted']) && isset($_GET['id'])): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Utilisateur supprimé ✅',
        html: 'Le compte avec l\'ID <?= htmlspecialchars($_GET["id"]) ?></strong> a été supprimé avec succès.',
        confirmButtonColor: '#6c63ff'
      });
      window.history.replaceState({}, document.title, window.location.pathname); // Nettoie l'URL
    });
  </script>
<?php endif; ?>



<?php
// Configuration de session sécurisée
if (session_status() === PHP_SESSION_NONE) {
  ini_set('session.use_only_cookies', 1);
  ini_set('session.cookie_httponly', 1);
  session_start();
}

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../Controller/UserController.php");

// Vérifier si utilisateur connecté
if (!isset($_SESSION['user']['id_user'])) {
  header('Location: /login.php');
  exit();
}

$currentUserId = $_SESSION['user']['id_user'];

try {
  $db = Config::getConnexion();

  // Compter les messages non vus
  $stmt = $db->prepare("
        SELECT COUNT(*) FROM chat_messages
        WHERE NOT FIND_IN_SET(:userId, seen_by)
    ");
  $stmt->execute(['userId' => $currentUserId]);
  $unreadCount = $stmt->fetchColumn();
} catch (Exception $e) {
  die('Erreur lors de la récupération des messages: ' . $e->getMessage());
}

// Gestion des utilisateurs
$userController = new UserController();
$userModel = $userController->getAllUsers(); // Liste de tous les utilisateurs
$totalUsers = $userController->countUsers();
$usersByRole = $userController->countUsersByRole();

// Mapping des rôles
$roleMap = [
  'admin' => ['label' => 'Admin', 'color' => '#FF6384'],
  'user'  => ['label' => 'User',  'color' => '#36A2EB'],
  'banni' => ['label' => 'Banni', 'color' => '#888888'],
];

// Préparer les données pour le graphe
$labels = [];
$data = [];
$colors = [];

foreach ($usersByRole as $row) {
  $key = strtolower($row['role']);
  if (isset($roleMap[$key])) {
    $labels[] = $roleMap[$key]['label'];
    $data[] = $row['total'];
    $colors[] = $roleMap[$key]['color'];
  }
}

// Gestion des actions (changer rôle, bannir, débannir)
$action = $_GET['action'] ?? '';

switch ($action) {
  case 'changerRole':
    if (isset($_GET['id']) && isset($_GET['role'])) {
      $userController->changerRole($_GET['id'], $_GET['role']);
    } else {
      echo "Paramètres manquants pour changer le rôle.";
    }
    break;

  case 'bannirUser': // ✅ nom cohérent avec l'URL
    if (isset($_GET['id']) && isset($_GET['raison'])) {
      $userController->bannirUser($_GET['id'], $_GET['raison']);
    } else {
      echo "Paramètres manquants pour le bannissement.";
    }
    break;

  case 'debannirUser':
    if (isset($_GET['id'])) {
      $userController->debannirUser($_GET['id']);
    } else {
      echo "ID utilisateur manquant pour le débannissement.";
    }
    break;

  case 'supprimerUser':
    if (isset($_GET['id'])) {
      $userController->supprimerUser($_GET['id']);
      // Rediriger avec le flag "deleted"
      header("Location: indeex.php?deleted=1&id={$id}");
      exit;
    }
    break;


  default:
    // Pas d'action ou afficher la page d'accueil par défaut
    break;
}


// Fonction pour générer une couleur à partir du nom
function stringToColor($str)
{
  $Colors = [
    '#FF6B6B',
    '#FF8E53',
    '#6B5B95',
    '#88B04B',
    '#F7CAC9',
    '#92A8D1',
    '#955251',
    '#B565A7',
    '#DD4124',
    '#D65076'
  ];
  $hash = 0;
  for ($i = 0; $i < strlen($str); $i++) {
    $hash = ord($str[$i]) + (($hash << 5) - $hash);
  }
  return $Colors[abs($hash) % count($Colors)];
}
?>

<!-- Gestion des alertes SweetAlert -->
<?php if (isset($_GET['ban_success'])): ?>
  <?php // Ensure PHP block is closed properly before including HTML/JS 
  ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'success',
        title: '🚫 Utilisateur banni',
        html: `
          <p><strong>Email :</strong> <?= htmlspecialchars($_GET["email"]) ?></p>
          <p><strong>ID :</strong> <?= htmlspecialchars($_GET["id"]) ?></p>
          <p><strong>Raison :</strong> <?= htmlspecialchars($_GET["raison"]) ?></p>
        `,
        confirmButtonColor: '#6c63ff'
      });
      window.history.replaceState({}, document.title, window.location.pathname);
    });
  </script>
<?php endif; ?>




<?php if (isset($_GET['unban_success'])): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'success',
        title: '✅ Utilisateur débanni',
        html: `
          <p><strong>Email :</strong> <?= htmlspecialchars($_GET["email"]) ?></p>
          <p><strong>ID :</strong> <?= htmlspecialchars($_GET["id"]) ?></p>
        `,
        confirmButtonColor: '#6c63ff'
      });
      window.history.replaceState({}, document.title, window.location.pathname);
    });
  </script>
<?php endif; ?>





<?php if (isset($_GET['role_update_success'])): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'success',
        title: 'Rôle modifié ✅',
        text: 'Le rôle de <?= htmlspecialchars($_GET["name"]) ?> (ID: <?= htmlspecialchars($_GET["id"]) ?>) a été changé en <?= htmlspecialchars($_GET["role"]) ?>.',
        confirmButtonColor: '#6c63ff',
        confirmButtonText: 'OK'
      });
      window.history.replaceState({}, document.title, window.location.pathname);
    });
  </script>
<?php elseif (isset($_GET['role_no_change'])): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'info',
        title: 'Aucun changement',
        text: 'Le rôle de <?= htmlspecialchars($_GET["name"]) ?> est déjà <?= htmlspecialchars($_GET["role"]) ?>.',
        confirmButtonColor: '#6c63ff',
        confirmButtonText: 'OK'
      });
      window.history.replaceState({}, document.title, window.location.pathname);
    });
  </script>
<?php endif; ?>



<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Gestion de Produits</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="styles.css">

  <script src="maiin.js"></script>
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">




</head>

<body>




  <div class="sidebar">
    <div>


      <img src="logo.png" alt="Logo" class="logo">
      <div class="menu-item active" data-section="overview">🏠 Tableau de Bord</div>
      <div class="menu-item" data-section="promos">👤 Utilisateurs</div>
      <div class="menu-item" data-section="unsplash">📷 Galerie Unsplash</div>
      <div class="menu-item" data-section="youtube-section">🎬 Explorer Vidéo</div>
      <div class="menu-item" data-section="chatbot-section">🤖 Ouvrir le Click'Bot</div>
      <div class="menu-item" data-section="ai-image-generator-section">🖼️ Générateur d'Images IA</div>  

      


      <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/chatbox.php" class="chat-admin-button" id="chatAdminBtn">
        💬 Aller au Chat Admin
        <span id="badgeCount" class="badge" style="display:none;"></span>
      </a>

      <style>
        .chat-admin-button {
          display: inline-flex;
          align-items: center;
          padding: 10px 20px;
          background-color: #CCB7E5;
          /* Set background color */
          color: white;
          text-decoration: none;
          font-weight: bold;
          border-radius: 5px;
          position: relative;
          /* Ensure badge positioning relative to button */
          transition: background-color 0.3s;
        }

        .chat-admin-button:hover {
          background-color: #BBA4D1;
          /* Darker shade when hovered */
        }

        .star-icon {
          margin-right: 5px;
          /* Reduced space between the star and the text */
          font-size: 20px;
          /* Adjust the star size to match the text */
        }

        .badge {
          background-color: #EF4444;
          color: white;
          font-size: 12px;
          padding: 3px 6px;
          border-radius: 50%;
          margin-left: 10px;
          position: absolute;
          top: 0;
          right: 0;
          /* Badge position at the top-right of the button */
          transform: translate(50%, -50%);
          /* Correct position adjustment */
        }
      </style>







      <div id="spotifySection" style="display: none;">
        <button id="spotifyTracksBtn" style="
    padding: 10px 20px;
    background-color: #1DB954;
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    margin: 20px;
  ">🎵 Voir mes chansons</button>

        <div id="tracksList" style="
    margin-top: 20px;
    padding: 20px;
    background: #f7f7f7;
    border-radius: 10px;
    max-width: 600px;
  "></div>
      </div>





      <style>
        .badge {
          position: absolute;
          top: 5px;
          /* 🛠️ Correction ici */
          right: 8px;
          /* 🛠️ Correction ici */
          background-color: red;
          color: white;
          border-radius: 50%;
          padding: 4px 8px;
          font-size: 0.75rem;
          font-weight: bold;
          display: flex;
          align-items: center;
          justify-content: center;
        }
      </style>

      <script>
        function refreshUnreadBadge() {
          fetch('/Projet%20Web/mvcUtilisateur/View/BackOffice/count_unread.php')
            .then(response => response.json())
            .then(data => {
              const badge = document.getElementById('badgeCount');
              const button = document.getElementById('chatAdminBtn');

              if (data.unread > 0) {
                badge.innerText = data.unread;
                badge.style.display = 'inline-block';
              } else {
                badge.style.display = 'none';
              }
            })
            .catch(error => console.error('Erreur rafraîchissement badge:', error));
        }

        // Rafraîchir immédiatement et toutes les 5 secondes
        refreshUnreadBadge();
        setInterval(refreshUnreadBadge, 5000);
      </script>





    </div>
    <div class="menu-item">🚪 Déconnexion</div>
  </div>

  <div class="dashboard">

    <div class="header">
      <div class="navbar-backoffice-wrapper">
        <nav class="navbar-backoffice">
          <ul>
            <li><a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link active">Utilisateurs</a></li>
            <li><a href="/Projet%20Web/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/Projet%20Web/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/Projet%20Web/mvcProduit/view/back%20office/indeex.php" class="nav-link">Produits</a></li>
            <li><a href="/Projet%20Web/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/Projet%20Web/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            <li class="profile-container">

              <div class="user-profile">
                <?php if (isset($_SESSION['user'])): ?>
                  <?php
                  $photoPath = $_SESSION['user']['profile_picture'] ?? '';
                  $fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';
                  $photoRelativePath = '../FrontOffice/' . $photoPath;
                  $absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
                  $showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);
                  ?>
                  <?php if ($showPhoto): ?>
                    <img src="/Projet Web/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                  <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                      <?= strtoupper(substr($fullName, 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                  <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/Projet Web/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
                    <a href="/Projet Web/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
                  </div>
                <?php endif; ?>
              </div>

              <style>
                .user-profile {
                  position: relative;
                  display: inline-block;
                }

                .profile-photo {
                  width: 55px;
                  height: 55px;
                  border-radius: 50%;
                  object-fit: cover;
                  cursor: pointer;
                  border: 2px solid purple;
                  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
                }

                .profile-circle {
                  width: 35px;
                  height: 35px;
                  border-radius: 50%;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  color: white;
                  font-weight: bold;
                  font-size: 16px;
                  cursor: pointer;
                }

                .dropdown-menu {
                  position: absolute;
                  top: 45px;
                  right: 0;
                  background-color: white;
                  border: 1px solid #ddd;
                  padding: 10px;
                  display: none;
                  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                  z-index: 100;
                }

                .user-profile:hover .dropdown-menu {
                  display: block;
                }

                .search-bar {
                  display: flex;
                  align-items: center;
                  background: rgba(255, 255, 255, 0.1);
                  border: 2px solid var(--spanish-gray);
                  border-radius: 50px;
                  overflow: hidden;
                  margin: 20px auto;
                  max-width: 800px;
                  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                  backdrop-filter: blur(5px);
                  height: 40px;
                  margin-top: 400px !important;
                }
              </style>



            </li>
          </ul>
        </nav>
      </div>

      <style>
        .navbar-backoffice-wrapper {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
          padding: 15px 30px;
          border-radius: 30px;
          box-shadow: 0 8px 32px rgba(151, 104, 209, 0.1);
          position: fixed;
          top: 40px;
          left: 58%;
          transform: translateX(-50%);
          z-index: 1000;
          width: fit-content;
          min-width: 800px;
          backdrop-filter: blur(10px);
          border: 1px solid rgba(151, 104, 209, 0.1);
        }

        .navbar-backoffice ul {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 30px;
          /* Réduit légèrement l'espacement entre les éléments */
          margin: 0;
          padding: 0;
          list-style: none;
        }

        .nav-link {
          text-decoration: none;
          font-weight: 500;
          font-size: 15px;
          transition: all 0.3s ease;
          padding: 10px 20px;
          border-radius: 20px;
          white-space: nowrap;
          position: relative;
        }

        /* Couleurs spécifiques pour chaque lien */
        .nav-link[href*="Utilisateur"] {
          color: #F687B3;
        }


        .nav-link[href*="act"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Event"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Produit"] {
          color: #9768D1;
        }

        .nav-link[href*="Covoiturage"] {
          color: #B794F4;
        }

        .nav-link[href*="Sponsor"] {
          color: #9F7AEA;
        }

        .nav-link:hover {
          background: rgba(246, 135, 179, 0.1);
          transform: translateY(-1px);
        }

        .nav-link.active {
          font-weight: 600;
          background: rgba(246, 135, 179, 0.15);
          color: #F687B3;
        }

        .nav-link.active::after {
          content: '';
          position: absolute;
          bottom: -5px;
          left: 50%;
          transform: translateX(-50%);
          width: 20px;
          height: 3px;
          background: #F687B3;
          border-radius: 10px;
        }

        .profile-container {
          display: flex;
          align-items: center;
          gap: 20px;
          margin-left: 25px;
          padding-left: 25px;
          border-left: 1px solid rgba(151, 104, 209, 0.2);
        }

        .profile-photo,
        .profile-circle {
          width: 35px;
          height: 35px;
          border-radius: 50%;
          cursor: pointer;
          border: 2px solid white;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu {
          position: absolute;
          top: 50px;
          right: 0;
          background: white;
          border-radius: 15px;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
          padding: 8px 0;
          min-width: 180px;
          border: 1px solid rgba(151, 104, 209, 0.1);
        }

        .dropdown-menu a {
          display: block;
          padding: 10px 20px;
          color: #666;
          text-decoration: none;
          font-size: 14px;
          transition: all 0.3s ease;
        }

        .dropdown-menu a:hover {
          background: rgba(247, 243, 255, 0.95);
          color: #9768D1;
        }
      </style>

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


    </div>


    <!-- Overview Section (Tableau de Bord) -->
    <div class="dashboard-section active" id="overview">
      <div class="header">
        <h2 class="dashboard-title">Planifiez la magie, vivez l'aventure ! ✨</h2>
        <div class="profile-container">








        </div>
      </div>

      <style>
        .dashboard-title {
          color: #666;
          font-size: 28px;
          font-weight: 600;
          margin: 0;
          padding: 20px;
          margin-top: 100px;
        }
      </style>

      <!-- Key Metrics -->
      <div class="stats-container" style="margin-top: 20px;">
        <div class="stat-card" style="
          background: white;
          border-radius: 20px;
          padding: 25px 40px;
          width: 100%;
          max-width: 900px;
          margin: 0 auto;
          display: flex;
          align-items: center;
          justify-content: flex-start;
          box-shadow: 0 4px 15px rgba(0,0,0,0.05);
          gap: 25px;
        ">
          <div class="stat-icon" style="
            background: #F3E8FF;
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
          ">👤</div>
          <div style="
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex: 1;
          ">
            <h3 style="
              color: #666;
              font-size: 20px;
              margin: 0 0 5px;
              font-weight: 500;
            ">Total Utilisateurs</h3>
            <p class="stat-value" style="
              color: #F687B3;
              font-size: 35px;
              font-weight: 600;
              margin: 0;
            "><?php echo $totalUsers; ?></p>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-container">
        <div class="chart-card" style="background: white; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
          <h3 style="color: #6B46C1; font-size: 20px; margin-bottom: 20px; padding: 20px;">Vue d'ensemble des Utilisateurs</h3>
          <div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 30px; padding: 0 20px;">
            <div style="text-align: center; padding: 15px 25px; background: #F3E8FF; border-radius: 15px; flex: 1; transition: transform 0.3s ease;">
              <div style="font-size: 28px; color: #9768D1; font-weight: 600; margin-bottom: 8px;"><?php echo $usersByRole[0]['total']; ?></div>
              <div style="color: #666; font-size: 14px; font-weight: 500;">Administrateurs</div>
            </div>
            <div style="text-align: center; padding: 15px 25px; background: #FCE7F3; border-radius: 15px; flex: 1; transition: transform 0.3s ease;">
              <div style="font-size: 28px; color: #EC4899; font-weight: 600; margin-bottom: 8px;"><?php echo $usersByRole[1]['total']; ?></div>
              <div style="color: #666; font-size: 14px; font-weight: 500;">Utilisateurs</div>
            </div>
            <div style="text-align: center; padding: 15px 25px; background: #FEE2E2; border-radius: 15px; flex: 1; transition: transform 0.3s ease;">
              <div style="font-size: 28px; color: #EF4444; font-weight: 600; margin-bottom: 8px;">
                <?php echo $usersByRole[2]['total'] ?? 'Bannis'; ?>
              </div>

              <div style="color: #666; font-size: 14px; font-weight: 500;">Bannis</div>
            </div>
          </div>
          <div style="padding: 20px;">
            <canvas id="userRoleDonut" style="max-height: 300px;"></canvas>
          </div>
        </div>

        <script>
          const userRolesLabels = <?php echo json_encode($labels); ?>;
          const userRolesData = <?php echo json_encode($data); ?>;

          console.log("Labels chargés:", userRolesLabels);
          console.log("Données chargées:", userRolesData);

          window.onload = () => {
            const canvas = document.getElementById('userRoleDonut');
            if (!canvas) {
              console.error("❌ Le canvas 'userRoleDonut' n'existe pas !");
              return;
            }

            console.log("Canvas trouvé ✅");
            const ctx = canvas.getContext('2d');
            console.log("Contexte 2D obtenu ✅");

            try {
              new Chart(ctx, {
                type: 'bar',
                data: {
                  labels: userRolesLabels,
                  datasets: [{
                    data: userRolesData,
                    backgroundColor: [
                      '#F3E8FF', // Lilas pastel pour Admin
                      '#FCE7F3', // Rose pastel pour User
                      '#FEE2E2' // Rouge pastel pour Banni
                    ],
                    hoverBackgroundColor: [
                      '#E9D5FF',
                      '#FBCFE8',
                      '#FCA5A5'
                    ],
                    borderWidth: 2,
                    borderColor: [
                      '#9768D1',
                      '#EC4899',
                      '#EF4444'
                    ],
                    borderRadius: {
                      topLeft: 10,
                      topRight: 10,
                      bottomLeft: 10,
                      bottomRight: 10
                    },
                    borderSkipped: false,
                    barThickness: 45
                  }]
                },
                options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  layout: {
                    padding: {
                      top: 20,
                      right: 20,
                      bottom: 20,
                      left: 20
                    }
                  },
                  scales: {
                    y: {
                      beginAtZero: true,
                      grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.03)',
                        drawBorder: false
                      },
                      ticks: {
                        font: {
                          size: 12,
                          family: "'Poppins', sans-serif"
                        },
                        color: '#666',
                        padding: 10
                      },
                      border: {
                        display: false
                      }
                    },
                    x: {
                      grid: {
                        display: false
                      },
                      ticks: {
                        font: {
                          size: 13,
                          family: "'Poppins', sans-serif",
                          weight: '500'
                        },
                        color: '#666',
                        padding: 10
                      },
                      border: {
                        display: false
                      }
                    }
                  },
                  plugins: {
                    legend: {
                      display: false
                    },
                    tooltip: {
                      backgroundColor: 'white',
                      titleColor: '#666',
                      bodyColor: '#666',
                      padding: 15,
                      cornerRadius: 10,
                      displayColors: true,
                      borderColor: 'rgba(0,0,0,0.1)',
                      borderWidth: 1,
                      titleFont: {
                        size: 14,
                        weight: '600',
                        family: "'Poppins', sans-serif"
                      },
                      bodyFont: {
                        size: 13,
                        family: "'Poppins', sans-serif"
                      },
                      callbacks: {
                        label: function(context) {
                          return `${context.raw} utilisateurs`;
                        }
                      }
                    }
                  },
                  animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                  }
                }
              });
              console.log("Graphique initialisé avec succès ✅");
            } catch (error) {
              console.error("❌ Erreur lors de l'initialisation du graphique:", error);
            }
          };
        </script>

        <div class="chart-card">
          <h3>Inscription Utilisateur</h3>
          <button id="downloadChart" style="margin-top: 10px; padding: 8px 16px; background-color: #CCB7E5; color: white; border: none; border-radius: 6px; cursor: pointer;">
            📸 Télécharger le Graphique
          </button>
          <div id="loading" style="display:none;">Chargement...</div>
          <div style="height: 300px;">
            <select id="filterPeriod" style="width: 150px; padding: 6px; font-size: 14px; border: 1px solid #E8B7D4;">
              <option value="7 DAY">7 jours</option>
              <option value="1 MONTH" selected>1 mois</option>
              <option value="4 MONTH">4 mois</option>
              <option value="6 MONTH">6 mois</option>
              <option value="1 YEAR">1 an</option>
              <option value="3 YEAR">3 an</option>
            </select>

            <canvas id="salesWave" style="width: 100%; height: 300px;"></canvas>
          </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="filterPeriod.js"></script>
      </div>
    </div>






<div class="dashboard-section" id="ai-image-generator-section" style="margin-top: 100px;">
  <h2 style="margin-bottom: 20px; color: #b94ee7;">🖼️ Générateur d'Images IA</h2>

  <div class="ai-image-box" style="background: linear-gradient(135deg, #ffe0f7, #f0e1ff); border-radius: 15px; padding: 20px; box-shadow: 0 0 10px rgba(190, 133, 255, 0.3);">
    <textarea id="prompt" placeholder="Décrivez l’image à générer ici..." 
      style="width: 100%; padding: 15px; border-radius: 10px; border: 1px solid #d4aaff; font-size: 16px; resize: vertical; background: white;"></textarea>
    
    <button id="generateBtn" style="margin-top: 15px; background: linear-gradient(to right, #cc66ff, #ff66cc); color: white; border: none; padding: 12px 20px; font-size: 16px; border-radius: 10px; cursor: pointer; transition: 0.3s;">
      🎨 Générer l’image
    </button>

    <!-- SPINNER de chargement -->
    <div id="loadingSpinner" style="display: none; margin-top: 30px; text-align: center;">
      <div class="lds-dual-ring"></div>
      <p style="color: #b94ee7; font-weight: bold;">Génération en cours...</p>
    </div>

    <div class="result" id="result" style="margin-top: 30px; text-align: center;"></div>
  </div>
</div>

<style>
/* Spinner style */
.lds-dual-ring {
  display: inline-block;
  width: 64px;
  height: 64px;
}
.lds-dual-ring:after {
  content: " ";
  display: block;
  width: 46px;
  height: 46px;
  margin: 1px;
  border-radius: 50%;
  border: 6px solid #c94cf7;
  border-color: #c94cf7 transparent #ff66cc transparent;
  animation: lds-dual-ring 1.2s linear infinite;
}

@keyframes lds-dual-ring {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.result img {
  max-width: 100%;
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(204, 102, 255, 0.3);
}
</style>

<script>
document.getElementById('generateBtn').addEventListener('click', async function () {
  const prompt = document.getElementById('prompt').value;
  const apiKey = "21NdUykT79eHloPvtzSeNmB6k7nKTuo46mqsW6PooYFn381BD1G7g54Cdj9s"; // Remplace avec ta clé
  const negativePrompt = "blurry, bad quality";
  const [width, height] = [768, 768];

  if (!prompt) {
    alert('Veuillez décrire l’image.');
    return;
  }

  const btn = this;
  const spinner = document.getElementById('loadingSpinner');
  const result = document.getElementById('result');

  btn.disabled = true;
  spinner.style.display = 'block';
  result.innerHTML = '';

  try {
    const res = await fetch('generate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        key: apiKey,
        prompt: prompt,
        negative_prompt: negativePrompt,
        width: width,
        height: height
      })
    });

    const data = await res.json();

    if (data.status === 'success') {
      result.innerHTML = `
        <h3 style="color: #c94cf7;">Résultat</h3>
        <img src="${data.output[0]}" alt="Image IA générée">
        <br>
        <a href="${data.output[0]}" download="image.png" style="display: inline-block; margin-top: 10px; background-color: #c94cf7; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">📥 Télécharger</a>
      `;
    } else {
      result.innerHTML = `<p style="color: red;">Erreur: ${data.message}</p>`;
    }
  } catch (err) {
    result.innerHTML = `<p style="color: red;">Erreur réseau: ${err.message}</p>`;
  } finally {
    btn.disabled = false;
    spinner.style.display = 'none';
  }
});
</script>







<!-- ✅ SECTION CHATBOT -->
<!-- ✅ SECTION CHATBOT AVEC BULLES STYLEES -->
<div class="dashboard-section" id="chatbot-section" style="margin-top: 100px;">
  <div style="display: flex; justify-content: center;">
    <h2 style="margin-bottom: 20px; background: linear-gradient(45deg, #ff6b6b, #4b6cb7); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-align: center;">
      💬 Discuter avec notre Click'Bot
    </h2>
  </div>

  <div id="chatbox"
    style="height: 400px; border: 2px solid #d946ef; padding: 15px; overflow-y: auto; margin-bottom: 15px; border-radius: 15px; background: #fdf4ff; box-shadow: 0 0 10px rgba(168, 85, 247, 0.2); display: flex; flex-direction: column; gap: 10px;">
  </div>

  <div style="display: flex; gap: 10px;">
    <input type="text" id="message" placeholder="Tapez votre message..."
      style="flex: 1; padding: 12px; border: 2px solid #d946ef; border-radius: 12px; outline: none; background: #fff0fb;">
    <button onclick="sendMessage()"
      style="padding: 12px 25px; border: none; background: linear-gradient(135deg, #d946ef, #a855f7); color: white; border-radius: 12px; cursor: pointer; transition: background 0.3s;">
      🚀 Envoyer
    </button>
  </div>
</div>




<script>
  function sendMessage() {
    const message = document.getElementById('message').value.trim();
    if (!message) return;

    const chatbox = document.getElementById('chatbox');

    // Message utilisateur
    const userBubble = document.createElement('div');
    userBubble.style.alignSelf = 'flex-end';
    userBubble.style.background = '#e9d5ff';
    userBubble.style.color = '#6b21a8';
    userBubble.style.padding = '10px 15px';
    userBubble.style.borderRadius = '15px 15px 0 15px';
    userBubble.style.maxWidth = '75%';
    userBubble.innerText = message;
    chatbox.appendChild(userBubble);
    document.getElementById('message').value = '';

    fetch('chatbot.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'message=' + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
      const reply = data.error ? `❌ ${data.error}` : data.reply;

      const botBubble = document.createElement('div');
      botBubble.style.alignSelf = 'flex-start';
      botBubble.style.background = '#fce7f3';
      botBubble.style.color = '#be185d';
      botBubble.style.padding = '10px 15px';
      botBubble.style.borderRadius = '15px 15px 15px 0';
      botBubble.style.maxWidth = '75%';
      botBubble.innerText = reply;
      chatbox.appendChild(botBubble);
      chatbox.scrollTop = chatbox.scrollHeight;
    })
    .catch(() => {
      const errorBubble = document.createElement('div');
      errorBubble.style.alignSelf = 'flex-start';
      errorBubble.style.background = '#fee2e2';
      errorBubble.style.color = '#b91c1c';
      errorBubble.style.padding = '10px 15px';
      errorBubble.style.borderRadius = '15px 15px 15px 0';
      errorBubble.style.maxWidth = '75%';
      errorBubble.innerText = "❌ Erreur de connexion.";
      chatbox.appendChild(errorBubble);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('message').addEventListener('keypress', function (e) {
      if (e.key === 'Enter') sendMessage();
    });
  });
</script>


<!-- ✅ SECTION YOUTUBE -->
<div class="dashboard-section" id="youtube-section">
  <h2 style="margin-bottom: 20px; margin-top: 95px;">📺 Explorer les vidéos YouTube</h2>

  <div class="youtube-controls" style="display: flex; gap: 10px;">
    <input type="text" id="youtubeSearchInput" placeholder="Ex: Balti"
      style="padding: 10px; flex: 1; border-radius: 10px; border: 1px solid #ccc;">
    <button id="youtubeSearchBtn" class="unsplash-btn red"
      style="padding: 10px 20px; border-radius: 10px; background-color: #FF0000; color: white; border: none; cursor: pointer;">
      🔍 Rechercher
    </button>
  </div>

  <div id="youtubeResults" class="youtube-grid" style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;"></div>
</div>


<!-- ✅ SECTION YOUTUBE -->
<div class="dashboard-section" id="youtube-section">
  <h2 style="margin-bottom: 20px; margin-top: 95px;">📺 Explorer les vidéos YouTube</h2>

  <div class="youtube-controls" style="display: flex; gap: 10px;">
    <input type="text" id="youtubeSearchInput" placeholder="Ex: Balti"
      style="padding: 10px; flex: 1; border-radius: 10px; border: 1px solid #ccc;">
    <button id="youtubeSearchBtn" class="unsplash-btn red"
      style="padding: 10px 20px; border-radius: 10px; background-color: #FF0000; color: white; border: none; cursor: pointer;">
      🔍 Rechercher
    </button>
  </div>

  <div id="youtubeResults" class="youtube-grid"
    style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;"></div>
</div>

<!-- ✅ MINI LECTEUR FLOTTANT (amélioré) -->
<div id="floatingYoutubePlayer"
  style="display: none; position: fixed; bottom: 20px; right: 20px; width: 300px; height: 170px; background: black; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.2); z-index: 1000; cursor: move;">
  
  <!-- ❌ Bouton fermer -->
  <button onclick="closeFloatingPlayer()"
    style="position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.6); border: none; color: white; border-radius: 50%; width: 24px; height: 24px; cursor: pointer;">✖</button>

  <!-- 🧲 Bouton agrandir/réduire -->
  <button onclick="toggleSize()"
    style="position: absolute; bottom: 5px; left: 5px; background: rgba(255,255,255,0.1); border: none; color: white; border-radius: 6px; padding: 4px 8px; cursor: pointer;">⛶</button>

  <iframe id="floatingIframe" width="100%" height="100%" frameborder="0" allowfullscreen allow="autoplay"></iframe>
</div>


<style>
  .youtube-grid .video-thumbnail {
    position: relative;
    cursor: pointer;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .youtube-grid .video-thumbnail:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  }

  .youtube-grid .video-thumbnail img {
    width: 100%;
    display: block;
  }

  .youtube-grid .play-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 48px;
    color: white;
    text-shadow: 0 0 10px black;
  }
</style>

<script>

  function closeFloatingPlayer() {
    const player = document.getElementById('floatingYoutubePlayer');
    player.style.display = 'none';
    document.getElementById('floatingIframe').src = '';
  }

  // ✅ Taille toggle (agrandir/réduire)
  let isLarge = false;
  function toggleSize() {
    const player = document.getElementById('floatingYoutubePlayer');
    if (!isLarge) {
      player.style.width = '560px';
      player.style.height = '315px';
    } else {
      player.style.width = '300px';
      player.style.height = '170px';
    }
    isLarge = !isLarge;
  }

  // ✅ Drag & drop
  let isDragging = false;
  let offsetX, offsetY;
  const player = document.getElementById('floatingYoutubePlayer');

  player.addEventListener('mousedown', (e) => {
    if (e.target.tagName === 'BUTTON') return; // Ne pas déplacer si clic sur un bouton
    isDragging = true;
    offsetX = e.clientX - player.getBoundingClientRect().left;
    offsetY = e.clientY - player.getBoundingClientRect().top;
    document.body.style.userSelect = 'none';
  });

  document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    player.style.left = `${e.clientX - offsetX}px`;
    player.style.top = `${e.clientY - offsetY}px`;
    player.style.bottom = 'auto';
    player.style.right = 'auto';
  });

  document.addEventListener('mouseup', () => {
    isDragging = false;
    document.body.style.userSelect = '';
  });


  document.addEventListener("DOMContentLoaded", () => {
    const apiKey = 'AIzaSyDyk9qxkoCI4oMpZ5fst6lIlkQUloN-Ymc'; // 🔐 Ta vraie clé ici

    document.getElementById('youtubeSearchBtn').addEventListener('click', async () => {
      const query = document.getElementById('youtubeSearchInput').value.trim();
      const resultDiv = document.getElementById('youtubeResults');
      resultDiv.innerHTML = '';

      if (!query) return;

      try {
        const videoIds = await searchYouTube(query);
        if (videoIds.length === 0) {
          resultDiv.innerHTML = "<p>❌ Aucune vidéo trouvée.</p>";
          return;
        }

        videoIds.forEach(id => {
          const videoWrapper = document.createElement('div');
          videoWrapper.className = 'video-thumbnail';

          const thumbnail = document.createElement('img');
          thumbnail.src = `https://img.youtube.com/vi/${id}/0.jpg`;
          thumbnail.alt = 'Miniature vidéo';

          const playIcon = document.createElement('div');
          playIcon.className = 'play-icon';
          playIcon.innerHTML = '▶️';

          videoWrapper.appendChild(thumbnail);
          videoWrapper.appendChild(playIcon);

          videoWrapper.addEventListener('click', () => {
            const floatingIframe = document.getElementById('floatingIframe');
            floatingIframe.src = `https://www.youtube.com/embed/${id}?autoplay=1`;
            document.getElementById('floatingYoutubePlayer').style.display = 'block';
          });

          resultDiv.appendChild(videoWrapper);
        });

      } catch (err) {
        console.error("❌ Erreur API YouTube :", err);
        resultDiv.innerHTML = "<p>❌ Une erreur est survenue.</p>";
      }
    });

    async function searchYouTube(query) {
      const apiUrl = `https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=6&q=${encodeURIComponent(query)}&key=${apiKey}`;
      const res = await fetch(apiUrl);
      const data = await res.json();

      if (data.error) {
        console.error("❌ Erreur API YouTube :", data.error);
        throw new Error(data.error.message);
      }

      return data.items.map(item => item.id.videoId);
    }
  });
</script>








    <style>

      .dashboard-section {
  display: none;
}
.dashboard-section.active {
  display: block;
}

      .youtube-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 20px;
        margin-top: 20px;
      }

      .youtube-grid iframe {
        width: 100%;
        height: 200px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .youtube-grid iframe:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      }
    </style>







    <!-- Section Unsplash (images) -->
    <div class="dashboard-section" id="unsplash">
      <h2 style="margin-bottom: 20px;margin-top:95px;">📷 Galerie d'images Unsplash</h2>

      <div class="unsplash-controls">
        <input type="text" id="searchInput" placeholder="Tape un mot-clé...">
        <button class="unsplash-btn violet" onclick="searchPhotos()">🔍 Rechercher</button>
        <button class="unsplash-btn" onclick="getRandom()">🎲 Aléatoire</button>
        <button class="unsplash-btn" onclick="getLatest()">🕒 Récentes</button>
      </div>

      <div id="results" class="unsplash-grid"></div>


    </div>


    <!-- Promos Section -->
    <!-- views/users/index.php -->
    <div class="dashboard-section" id="promos">
      <div class="section-header">
        <h2 class="section-title">Gestion des Utilisateurs 👤</h2>
        <div class="search-container">
          <input class="search" type="text" placeholder="Rechercher un utilisateur">
        </div>
      </div>

      <style>
        .section-header {
          margin-top: 100px;
          padding: 20px;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .section-title {
          color: #666;
          font-size: 28px;
          font-weight: 600;
          margin: 0;
        }

        .search-container {
          margin-right: 20px;
        }

        .search {
          background: rgba(247, 243, 255, 0.95);
          border: none;
          border-radius: 25px;
          padding: 12px 25px;
          width: 300px;
          font-size: 14px;
          color: #666;
          transition: all 0.3s ease;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .search::placeholder {
          color: #999;
          font-weight: 400;
        }

        .search:focus {
          outline: none;
          background: rgba(247, 243, 255, 1);
          box-shadow: 0 2px 8px rgba(151, 104, 209, 0.1);
        }

        .promos-table {
          margin-top: 30px;
        }
      </style>

      <div class="promos-table">
        <h3>Liste des Utilisateurs</h3>
        <table>
          <thead>
            <tr>
              <th>Profile</th>
              <th>ID</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Date Inscription</th>
              <th>Numéro</th>
              <th>Rôle</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (!empty($userModel)) {
              foreach ($userModel as $user) {
                $id = $user->getIdUser();
                $fullName = htmlspecialchars($user->getFullName());
                $email = htmlspecialchars($user->getEmail());
                $date = htmlspecialchars($user->getDateInscription());
                $num = htmlspecialchars($user->getNumUser());
                $role = addslashes($user->getRole());
                $displayRole = htmlspecialchars($user->getRole());
                $photoPath = "/Projet Web/mvcUtilisateur/View/FrontOffice/" . htmlspecialchars($user->getProfilePicture());

                echo "<tr id='user-row-{$id}'>
      <td><img src='{$photoPath}' alt='Profile' style='width:40px;height:40px;border-radius:50%;object-fit:cover;'></td>
      <td>{$id}</td>
      <td>{$fullName}</td>
      <td>{$email}</td>
      <td>{$date}</td>
      <td>{$num}</td>
      <td>{$displayRole}</td>
      <td>
        <div style='display: flex; flex-direction: column; gap: 10px;'>
          <button class='action-btn' style='
            background: #F3E8FF;
            color: #9768D1;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: 500;
            width: 200px;
            text-align: left;
          ' onclick='changeRole({$id}, \"{$role}\")'>
            <i class='fas fa-user-cog'></i> Modifier le rôle
          </button>

          <form onsubmit='return confirmDelete(this)' style='width: 200px;'>
            <input type='hidden' name='action' value='supprimerUser'>
            <input type='hidden' name='id' value='{$id}'>
            <button type='submit' style='
              background: #F3E8FF;
              color: #9768D1;
              border: none;
              padding: 8px 15px;
              border-radius: 8px;
              cursor: pointer;
              font-size: 14px;
              transition: all 0.3s ease;
              font-weight: 500;
              width: 100%;
              text-align: left;
            '>
              <i class='fas fa-trash'></i> Supprimer le profil
            </button>
          </form>
        ";

                if ($displayRole !== 'banni') {
                  echo "<button style='
            background: #F3E8FF;
            color: #9768D1;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: 500;
            width: 200px;
            text-align: left;
          ' onclick='banUser({$id})'>
            <i class='fas fa-ban'></i> Bannir
          </button>";
                } else {
                  echo "<button style='
            background: #F3E8FF;
            color: #9768D1;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: 500;
            width: 200px;
            text-align: left;
          ' onclick='unbanUser({$id})'>
            <i class='fas fa-check-circle'></i> Désactiver le bannissement
          </button>";
                }
                echo "</div>";
              }
            } else {
              echo "<tr><td colspan='8'>Aucun utilisateur trouvé</td></tr>";
            }
            ?>


          </tbody>
        </table>
      </div> <!-- promos-table -->

    </div> <!-- dashboard-section promos -->


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      function deleteUser(id, name) {
        if (!id || isNaN(id) || id <= 0) {
          Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'ID utilisateur invalide.',
            confirmButtonColor: '#e74c3c'
          });
          return;
        }

        Swal.fire({
          title: '❌ Supprimer ce profil ?',
          html: `Voulez-vous vraiment supprimer <strong>${name}</strong> (ID: ${id}) ?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Oui, supprimer',
          cancelButtonText: 'Annuler',
          confirmButtonColor: '#d33',
          cancelButtonColor: '#aaa'
        }).then((result) => {
          if (result.isConfirmed) {
            fetch('indeex.php?action=supprimerUser', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${encodeURIComponent(id)}` // Encoder l'ID pour éviter les problèmes
              })
              .then(res => {
                if (!res.ok) {
                  throw new Error(`Erreur HTTP: ${res.status}`);
                }
                return res.json();
              })
              .then(data => {
                console.log("🔥 Réponse suppression :", data);
                if (data.success) {
                  const row = document.getElementById(`user-row-${id}`);
                  if (row) {
                    row.remove();
                  }
                  Swal.fire({
                    icon: 'success',
                    title: 'Utilisateur supprimé ✅',
                    text: `Le compte a été supprimé.`,
                    confirmButtonColor: '#6c63ff'
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: data.error || 'La suppression a échoué.',
                    confirmButtonColor: '#e74c3c'
                  });
                }
              })
              .catch(err => {
                console.error("❌ Erreur FETCH :", err);
                Swal.fire({
                  icon: 'error',
                  title: 'Erreur',
                  text: 'Une erreur est survenue lors de la suppression : ' + err.message,
                  confirmButtonColor: '#e74c3c'
                });
              });
          }
        });
      }
    </script>




    <!-- Reviews Section -->


    <!-- Settings Section -->


    <!-- Product Modal -->


    <!-- Promo Modal -->
    <div class="modal" id="promoModal">
      <div class="modal-content">
        <h3>Ajouter un Utilisateur</h3>
        <form id="userForm" method="POST" enctype="multipart/form-data">
          <label for="fullName">Nom complet</label>
          <input type="text" id="fullName" name="fullName" placeholder="Nom complet" required>

          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Email" required>

          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" placeholder="Mot de passe" required>

          <label for="dateInscription">Date d'inscription</label>
          <input type="date" id="dateInscription" name="dateInscription" required>

          <label for="numUser">Numéro d'utilisateur</label>
          <input type="text" id="numUser" name="numUser" placeholder="Numéro d'utilisateur" required>

          <label for="profilePicture">Photo de profil</label>
          <input type="file" id="profilePicture" name="profilePicture" accept="image/*" required>

          <label for="role">Rôle</label>
          <div id="role">
            <input type="radio" id="roleAdmin" name="role" value="admin" required>
            <label for="roleAdmin">Admin</label>
            <input type="radio" id="roleUser" name="role" value="user" required>
            <label for="roleUser">Utilisateur</label>
          </div>

          <div class="form-buttons">
            <button type="button" class="close-btn" onclick="window.location.href='indeex.php'">Annuler</button>
            <button type="submit" class="save-btn">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>


    <script>
      const ACCESS_KEY = "j9a9z5y6pypWDoDwlhCDGqpHzK-IY29XI1pMfKRqolM";

      function displayImages(photos) {
        const results = document.getElementById("results");
        results.innerHTML = "";
        photos.forEach(photo => {
          const container = document.createElement("div");
          container.className = "image-container";

          const img = document.createElement("img");
          img.src = photo.urls.small;

          const btn = document.createElement("button");
          btn.className = "download-button";
          btn.innerText = "Télécharger";
          btn.onclick = () => downloadImage(photo.urls.full);

          container.appendChild(img);
          container.appendChild(btn);
          results.appendChild(container);
        });
      }

      function searchPhotos() {
        const query = document.getElementById("searchInput").value;
        fetch(`https://api.unsplash.com/search/photos?query=${query}&per_page=10&client_id=${ACCESS_KEY}`)
          .then(res => res.json())
          .then(data => displayImages(data.results));
      }

      function getRandom() {
        fetch(`https://api.unsplash.com/photos/random?count=10&client_id=${ACCESS_KEY}`)
          .then(res => res.json())
          .then(data => displayImages(data));
      }

      function getLatest() {
        fetch(`https://api.unsplash.com/photos?per_page=10&order_by=latest&client_id=${ACCESS_KEY}`)
          .then(res => res.json())
          .then(data => displayImages(data));
      }

      function downloadImage(url) {
        fetch("download_image.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              image_url: url
            })
          })
          .then(res => res.json())
          .then(data => {
            Swal.fire({
              icon: data.error ? 'error' : 'success',
              title: data.error ? 'Échec du téléchargement' : 'Image téléchargée ✅',
              text: data.message || data.error,
              confirmButtonColor: '#6c63ff'
            });
          })
          .catch(err => {
            Swal.fire({
              icon: 'error',
              title: 'Erreur de connexion',
              text: 'Impossible de contacter le serveur.',
              confirmButtonColor: '#e74c3c'
            });
          });
      }


      // Chart.js Configurations for Dashboard
      // Stock Distribution (Doughnut Chart)



      document.getElementById('filterPeriod').addEventListener('change', function() {
        const selectedPeriod = this.value;
        window.location.href = '?period=' + encodeURIComponent(selectedPeriod);
      });

      // Sales by Category (Bar Chart)
      const categorySalesBar = new Chart(document.getElementById('categorySalesBar'), {
        type: 'bar',
        data: {
          labels: ['Sport', 'Tech', 'Vêtements', 'Bien-être'],
          datasets: [{
            label: 'Ventes (TND)',
            data: [5000, 3000, 2000, 1500],
            backgroundColor: ['#ff6b6b', '#4b6cb7', '#ff8fa3', '#82cffa'],
            borderWidth: 0,
            borderRadius: 10,
            barThickness: 30
          }]
        },
        options: {
          responsive: true,
          animation: {
            duration: 2000,
            easing: 'easeOutQuart'
          },
          plugins: {
            legend: {
              display: false
            },
            title: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                color: '#333',
                font: {
                  size: 12
                }
              },
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              }
            },
            x: {
              ticks: {
                color: '#333',
                font: {
                  size: 12
                }
              },
              grid: {
                display: false
              }
            }
          }
        }
      });

      // Navigation
      const menuItems = document.querySelectorAll('.menu-item');
      const sections = document.querySelectorAll('.dashboard-section');

      menuItems.forEach(item => {
        item.addEventListener('click', () => {
          menuItems.forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          const sectionId = item.getAttribute('data-section');
          sections.forEach(section => section.classList.remove('active'));
          document.getElementById(sectionId).classList.add('active');
        });
      });

      // Modal Management
      function openProductModal(mode, button) {
        const modal = document.getElementById('productModal');
        const form = document.getElementById('productForm');
        const title = document.getElementById('modalTitle');


        if (mode === 'edit' && button) {
          title.textContent = 'Modifier un Produit';
          const card = button.closest('.card');
          const name = card.querySelector('h3').textContent;
          const price = card.querySelector('p').textContent.match(/Prix: (\d+)/)[1];
          const stock = card.querySelector('p').textContent.match(/Stock: (\d+)/)[1];
          const purchase = card.querySelector('p').textContent.includes('Achat: Oui') ? 'yes' : 'no';
          const rental = card.querySelector('p').textContent.includes('Location: Oui') ? 'yes' : 'no';
          document.getElementById('productName').value = name;
          document.getElementById('productPrice').value = price;
          document.getElementById('productStock').value = stock;
          document.getElementById('productPurchase').value = purchase;
          document.getElementById('productRental').value = rental;
        } else {
          title.textContent = 'Ajouter un Produit';
          form.reset();
        }

        modal.style.display = 'flex';
      }

      function openPromoModal() {
        const modal = document.getElementById('promoModal');
        modal.style.display = 'flex';
      }

      function closeModal() {
        document.getElementById('productModal').style.display = 'none';
        document.getElementById('promoModal').style.display = 'none';
      }


      // Search Functionality
      // Search Functionality
      document.querySelectorAll('.search').forEach(searchInput => {
        searchInput.addEventListener('input', (e) => {
          const query = e.target.value.toLowerCase();
          const section = e.target.closest('.dashboard-section');
          if (section) {
            const rows = section.querySelectorAll('tbody tr');
            rows.forEach(row => {
              const text = row.textContent.toLowerCase();
              row.style.display = text.includes(query) ? '' : 'none';
            });
          }
        });
      });

      function editUser(id) {
        alert("Modifier l'utilisateur ID: " + id);
        // Tu peux rediriger vers une page: window.location.href = 'editUser.php?id=' + id;
      }

      function deleteUser(id) {
        if (confirm("Es-tu sûr de vouloir supprimer cet utilisateur ?")) {
          alert("Supprimer utilisateur ID: " + id);
          // Appel AJAX ou redirection vers deleteUser.php?id=...
        }
      }
    </script>


</body>

</html>