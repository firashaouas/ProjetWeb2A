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

  case 'bannirUser':
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

  default:
    // Pas d'action
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

<?php if (isset($_GET['unban_success'])): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'success',
        title: 'Utilisateur débanni ✅',
        text: 'Le compte de <?= htmlspecialchars($_GET["name"]) ?> (ID: <?= htmlspecialchars($_GET["id"]) ?>) a été réactivé.',
        confirmButtonColor: '#6c63ff',
        confirmButtonText: 'OK'
      });
      window.history.replaceState({}, document.title, window.location.pathname);
    });
  </script>
<?php endif; ?>

<?php if (isset($_GET['ban_success'])): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'success',
        title: 'Utilisateur banni 🚫',
        text: 'Le compte de <?= htmlspecialchars($_GET["name"]) ?> (ID: <?= htmlspecialchars($_GET["id"]) ?>) a été banni.',
        confirmButtonColor: '#6c63ff',
        confirmButtonText: 'OK'
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

</head>

<body>
  <div class="sidebar">
    <div>
      <img src="logo.png" alt="Logo" class="logo">
      <h1>Click'N'go</h1>
      <div class="menu-item active" data-section="overview">🏠 Tableau de Bord</div>
      <div class="menu-item" data-section="promos">👤 Utilisateurs</div>
      <div class="menu-item" data-section="products">📦 Produits</div>
      <div class="menu-item" data-section="orders">📋 Commandes</div>
      <div class="menu-item" data-section="reviews">⭐ Avis</div>
      <div class="menu-item" data-section="settings">⚙️ Réglages</div>


      <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/chatbox.php" class="chat-admin-button" id="chatAdminBtn">
        💬 Aller au Chat Admin
        <span id="badgeCount" class="badge" style="display:none;"></span>
      </a>
      <style>
        .chat-admin-button {
          background-color: #8e44ad;
          /* Violet */
          color: white;
          padding: 15px 25px;
          border-radius: 16px;
          text-decoration: none;
          font-weight: bold;
          font-size: 16px;
          position: relative;
          /* 🛠️ Très important ! */
          display: inline-block;
          overflow: hidden;
        }

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

              // Toujours garder le bouton violet
              button.style.backgroundColor = '#8e44ad';

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


    <!-- Overview Section (Tableau de Bord) -->
    <div class="dashboard-section active" id="overview">
      <div class="header">
        <h2>Planifiez la magie, vivez l’aventure ! ✨</h2>
        <div class="profile-container">
          <input class="search" type="text" placeholder="Rechercher...">


          <div class="user-profile">
            <?php if (isset($_SESSION['user'])): ?>
              <?php
              // Données de l'utilisateur
              $photoPath = $_SESSION['user']['profile_picture'] ?? '';
              $fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';

              // Construire le chemin absolu correct (on est dans BackOffice, l'image est dans FrontOffice)
              $photoRelativePath = '../FrontOffice/' . $photoPath; // pour file_exists
              $absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
              $showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);

              // DEBUG pour tests
              echo "<!-- DEBUG: photoPath = $photoPath -->";
              echo "<!-- DEBUG: absolutePath = $absolutePath -->";
              echo "<!-- DEBUG: file_exists = " . ($showPhoto ? 'true' : 'false') . " -->";
              ?>

              <?php if ($showPhoto): ?>
                <!-- Affichage de la photo (URL côté client) -->
                <img src="/Projet Web/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>"
                  alt="Photo de profil"
                  class="profile-photo"
                  onclick="toggleDropdown()">
              <?php else: ?>
                <!-- Cercle avec initiale si pas de photo -->
                <div class="profile-circle"
                  style="background-color: <?= stringToColor($fullName) ?>;"
                  onclick="toggleDropdown()">
                  <?= strtoupper(substr($fullName, 0, 1)) ?>
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


        </div>
      </div>

      <!-- Key Metrics -->
      <div class="stats-container">


        <div class="stat-card">
          <div class="stat-icon">👤</div>
          <h3>Total Utilisateurs</h3>
          <p class="stat-value"><?php echo $totalUsers; ?></p>
        </div>

        <div class="stat-card" data-tooltip="Revenus en TND">
          <div class="stat-icon">💸</div>
          <h3>Revenus</h3>
          <p class="stat-value">15,230</p>
        </div>
        <div class="stat-card" data-tooltip="Commandes à traiter">
          <div class="stat-icon">📋</div>
          <h3>Commandes</h3>
          <p class="stat-value">12</p>
        </div>
        <div class="stat-card" data-tooltip="Avis à modérer">
          <div class="stat-icon">⭐</div>
          <h3>Avis</h3>
          <p class="stat-value">5</p>
        </div>

      </div>

      <!-- Charts -->
      <div class="charts-container">


        <div class="chart-card">
          <h3>Répartition Utilisateurs</h3>
          <canvas id="userRoleDonut" width="300" height="300"></canvas>
        </div>

        <script>
          const userRolesLabels = <?php echo json_encode($labels); ?>;
          const userRolesData = <?php echo json_encode($data); ?>;
          const userRolesColors = <?php echo json_encode($colors); ?>;
        </script>



        <script>
          window.onload = () => {
            const userRolesLabels = <?php echo json_encode($labels); ?>;
            const userRolesData = <?php echo json_encode($data); ?>;
            const userRolesColors = <?php echo json_encode($colors); ?>;

            const canvas = document.getElementById('userRoleDonut');
            if (!canvas) {
              console.error("❌ Le canvas 'userRoleDonut' n'existe pas !");
              return;
            }

            const ctx = canvas.getContext('2d');

            new Chart(ctx, {
              type: 'doughnut',
              data: {
                labels: userRolesLabels,
                datasets: [{
                  data: userRolesData,
                  backgroundColor: userRolesColors,
                  borderWidth: 1
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: 'bottom'
                  }
                }
              }
            });

            // 🔧 Exemple d’un bouton que tu veux sécuriser
            const btn = document.getElementById('ton-bouton');
            if (btn) {
              btn.addEventListener('click', () => {
                alert('ça marche');
              });
            }
          };
        </script>

        <div class="chart-card">
          <h3>Ventes Catégories</h3>
          <button id="downloadChart" style="margin-top: 10px; padding: 8px 16px; background-color: #8e44ad; color: white; border: none; border-radius: 6px; cursor: pointer;">
            📸 Télécharger le Graphique
          </button>
          <div id="loading" style="display:none;">Chargement...</div>
          <div style="height: 300px;">
            <select id="filterPeriod" style="width: 150px; padding: 6px; font-size: 14px;">
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







        <div class="chart-card">
          <h3>Ventes Catégories</h3>
          <canvas id="categorySalesBar"></canvas>
        </div>
      </div>

      <!-- Stock Alert -->
      <div class="stock-alert">
        <p><span class="alert-icon">🔥</span> <strong>Attention :</strong> 3 produits presque épuisés !</p>
        <button class="alert-btn">Agir Maintenant</button>
      </div>

      <!-- Activity Log -->
      <div class="activity-log">
        <h3>Activités Récentes</h3>
        <div class="activity-list">
          <div class="activity-item">
            <span class="activity-date">10/04/2025</span>
            <span class="activity-details">Restock Montre connectée (20 unités) par <strong>Marie</strong></span>
          </div>
          <div class="activity-item">
            <span class="activity-date">09/04/2025</span>
            <span class="activity-details">Vente Tapis de yoga (10 unités) par <strong>Luc</strong></span>
          </div>
          <div class="activity-item">
            <span class="activity-date">08/04/2025</span>
            <span class="activity-details">Ajout Caméra instantanée par <strong>Sophie</strong></span>
          </div>
        </div>
      </div>
    </div>



    <!-- Products Section -->


    <!-- Orders Section -->


    <!-- Promos Section -->
    <!-- views/users/index.php -->
    <div class="dashboard-section" id="promos">
  <div class="header">
    <h2>Gestion des Utilisateurs 👤</h2>
    <div class="profile-container">
      <input class="search" type="text" placeholder="Rechercher un utilisateur">
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
      </div> <!-- user-profile -->
    </div> <!-- profile-container -->
  </div> <!-- header -->

  <!-- Ici on continue dans la même DIV promos -->
  
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

            echo "<tr>
                    <td><img src='{$photoPath}' alt='Profile' style='width:40px;height:40px;border-radius:50%;object-fit:cover;'></td>
                    <td>{$id}</td>
                    <td>{$fullName}</td>
                    <td>{$email}</td>
                    <td>{$date}</td>
                    <td>{$num}</td>
                    <td>{$displayRole}</td>
                    <td>
                      <button class='action-btn purple edit' onclick='changeRole({$id}, \"{$role}\")'>
                        <i class='fas fa-user-cog'></i> Modifier le rôle
                      </button>";

            if ($displayRole !== 'banni') {
              echo "<button class='action-btn purple delete' onclick='banUser({$id})'>
                      <i class='fas fa-ban'></i> Bannir
                    </button>";
            } else {
              echo "<button class='action-btn gray' onclick='unbanUser({$id})'>
                      <i class='fas fa-check-circle'></i> Désactiver le bannissement
                    </button>";
            }

            echo "</td></tr>";
          }
        } else {
          echo "<tr><td colspan='8'>Aucun utilisateur trouvé</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div> <!-- promos-table -->

</div> <!-- dashboard-section promos -->



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
    // Chart.js Configurations for Dashboard
    // Stock Distribution (Doughnut Chart)
    const stockDonut = new Chart(document.getElementById('stockDonut'), {
      type: 'doughnut',
      data: {
        labels: ['Sport', 'Tech', 'Bien-être', 'Vêtements'],
        datasets: [{
          data: [150, 60, 45, 90],
          backgroundColor: ['#ff6b6b', '#4b6cb7', '#ff8fa3', '#82cffa'],
          borderWidth: 0,
          hoverOffset: 30
        }]
      },
      options: {
        responsive: true,
        animation: {
          animateScale: true,
          animateRotate: true
        },
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              font: {
                size: 12,
                weight: '600'
              },
              color: '#333',
              padding: 20
            }
          }
        },
        cutout: '70%'
      }
    });


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