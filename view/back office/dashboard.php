<?php
require_once __DIR__ . '/../../controller/ActivityController.php';

// Activer le débogage pour les logs
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');
error_reporting(E_ALL);

// Désactiver le cache pour forcer le rafraîchissement des données
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$controller = new ActivityController();

// Gérer les actions via l'URL
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

$data = [];
switch ($action) {
    case 'add':
        $data = $controller->add();
        break;
    case 'edit':
        if ($id) {
            $data = $controller->edit($id);
        } else {
            header("Location: dashboard.php");
            exit;
        }
        break;
    case 'delete':
        if ($id) {
            $controller->delete($id);
        } else {
            header("Location: dashboard.php");
            exit;
        }
        break;
    case 'notifications':
        $data = $controller->notifications();
        break;
    case 'calendar':
        $data = $controller->calendar();
        break;
    case 'statistics':
        $data = $controller->statistics();
        break;
    case 'daily_activity':
        $data = $controller->daily_activity();
        break;
    case 'history':
        $data = $controller->history();
        break;
    case 'settings':
        $data = $controller->settings();
        break;
    case 'logout':
        $controller->logout();
        break;
    default:
        $data = $controller->index();
        break;
}

$section = $data['section'] ?? 'control_data';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Activités</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dash.css">
  <script src="js/formValidation.js"></script>
  <style>
    /* Styles pour la section Contrôle de Données */
    .control-data-section {
      padding: 20px;
    }

    .add-activity-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .add-activity-section h3 {
      font-size: 24px;
      color: #333;
      margin: 0;
    }

    .add-button {
      background-color: #6941FF;
      color: white;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }

    .add-button:hover {
      background-color: #5635CC;
    }

    .activities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .activity-card {
      background-color: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .activity-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .activity-image {
      width: 100%;
      height: 150px;
      overflow: hidden;
      background-color: #f0f0f0;
    }

    .activity-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .no-image {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      color: #666;
      font-size: 14px;
      text-align: center;
      padding: 10px;
    }

    .activity-content {
      padding: 15px;
    }

    .activity-content h3 {
      font-size: 18px;
      margin: 0 0 10px;
      color: #333;
    }

    .activity-content p {
      font-size: 14px;
      color: #666;
      margin: 0 0 15px;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .activity-buttons {
      display: flex;
      gap: 10px;
    }

    .edit-button, .delete-button {
      flex: 1;
      text-align: center;
      padding: 8px;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }

    .edit-button {
      background-color: #6941FF;
      color: white;
    }

    .edit-button:hover {
      background-color: #5635CC;
    }

    .delete-button {
      background-color: #FF5A5A;
      color: white;
    }

    .delete-button:hover {
      background-color: #E04E4E;
    }

    .no-activities {
      text-align: center;
      color: #666;
      font-size: 16px;
      margin-top: 20px;
    }

    /* Styles pour la section Statistiques Générales */
    .charts-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
      width: 100%;
      min-height: 300px;
    }

    .chart-wrapper {
      flex: 1;
      min-width: 300px;
      height: 400px;
      background-color: #fff;
      border-radius: 10px;
      padding: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    #activityChart, #categoryChart {
      width: 100% !important;
      height: 350px !important;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div>
      <img src="../back office/logo.png" alt="Logo" class="logo">
      <h1>ClickNGo</h1>
      <div class="menu-item"><a href="dashboard.php?action=notifications">🔔 Notifications</a></div>
      <div class="menu-item"><a href="dashboard.php">📋 Contrôle de Données</a></div>
      <div class="menu-item"><a href="dashboard.php?action=calendar">📅 Calendrier</a></div>
      <div class="menu-item"><a href="dashboard.php?action=statistics">📊 Statistiques Générales</a></div>
      <div class="menu-item"><a href="dashboard.php?action=daily_activity">🌟 Activité du Jour</a></div>
      <div class="menu-item"><a href="dashboard.php?action=history">📜 Historique</a></div>
    </div>
    <div class="menu-item"><a href="dashboard.php?action=settings">⚙️ Paramètres</a></div>
    <div class="menu-item"><a href="dashboard.php?action=logout">🚪 Déconnexion</a></div>
  </div>

  <div class="dashboard">
    <div class="header">
      <h2>Bienvenue sur votre espace Activités</h2>
      <div class="profile-container">
        <form method="GET" action="dashboard.php" class="search-form">
          <input type="text" name="search" class="search" placeholder="Rechercher une activité..." value="<?php echo htmlspecialchars($data['searchTerm'] ?? ''); ?>">
          <button type="submit" style="display: none;">Rechercher</button>
        </form>
        <div class="profile">
          <img src="laetitia.webp" alt="Profile Picture">
        </div>
      </div>
    </div>

    <?php if ($section === 'notifications'): ?>
      <div class="notifications">
        <h3>🔔 Notifications</h3>
        <div class="notification-list">
          <?php if (isset($data['notifications']) && is_array($data['notifications'])): ?>
            <?php foreach ($data['notifications'] as $notification): ?>
              <div class="notification-item">
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Aucune notification disponible.</p>
          <?php endif; ?>
        </div>
      </div>

    <?php elseif ($section === 'control_data'): ?>
      <div class="control-data-section">
        <!-- Section Ajouter une activité -->
        <div class="add-activity-section">
         <h3>                       </h3>
          <a href="dashboard.php?action=add" class="add-button">Ajouter une nouvelle activité</a>
        </div>

        <!-- Afficher un message si une recherche est active -->
        <?php if (!empty($data['searchTerm'])): ?>
          <p style="color: #6951FF; margin-bottom: 20px;">
            Résultats de la recherche pour "<?php echo htmlspecialchars($data['searchTerm']); ?>" :
            <?php echo count($data['activities']); ?> activité(s) trouvée(s).
            <a href="dashboard.php" style="color: #6951FF; text-decoration: underline;">Effacer la recherche</a>
          </p>
        <?php endif; ?>

        <!-- Débogage : Vérifier les données récupérées -->
        <?php
          error_log("Données dans control_data : " . print_r($data['activities'], true));
        ?>

        <!-- Grille des cartes d'activités -->
        <div class="activities-grid">
          <?php if (isset($data['activities']) && is_array($data['activities']) && !empty($data['activities'])): ?>
            <?php foreach ($data['activities'] as $activity): ?>
              <div class="activity-card">
                <div class="activity-image">
                  <?php if (!empty($activity['image'])): ?>
                    <?php
                      $imagePath = "../../" . htmlspecialchars($activity['image']);
                      error_log("Chemin de l'image pour l'activité {$activity['id']} : " . $imagePath);
                    ?>
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
                  <?php else: ?>
                    <div class="no-image">Pas d'image disponible</div>
                  <?php endif; ?>
                </div>
                <div class="activity-content">
                  <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                  <p><?php echo htmlspecialchars($activity['description']); ?></p>
                  <div class="activity-buttons">
                    <a href="dashboard.php?action=edit&id=<?php echo htmlspecialchars($activity['id']); ?>" class="edit-button">Modifier</a>
                    <a href="dashboard.php?action=delete&id=<?php echo htmlspecialchars($activity['id']); ?>" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')">Supprimer</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="no-activities">
              <?php echo !empty($data['searchTerm']) ? 'Aucune activité trouvée pour cette recherche.' : 'Aucune activité trouvée. Essayez d\'ajouter une nouvelle activité.'; ?>
            </p>
          <?php endif; ?>
        </div>
      </div>

    <?php elseif ($section === 'add_activity'): ?>
      <div class="add-activity-form">
        <h3>📋 Formulaire d'Ajout d'Activité</h3>
        <?php if (isset($data['error'])): ?>
          <p style="color: red;"><?php echo htmlspecialchars($data['error']); ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" novalidate>
          <div class="form-group">
            <label for="activityName">Nom de l'activité</label>
            <input type="text" id="activityName" name="name" placeholder="Ex: Yoga du matin" required>
          </div>
          <div class="form-group">
            <label for="activityDescription">Description</label>
            <textarea id="activityDescription" name="description" placeholder="Décrivez l'activité..." rows="5" required></textarea>
          </div>
          <div class="form-group">
            <label for="activityPrice">Prix (en TND)</label>
            <input type="number" id="activityPrice" name="price" placeholder="Ex: 20" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label for="activityLocation">Lieu</label>
            <input type="text" id="activityLocation" name="location" placeholder="Ex: Parc Belvédère" required>
          </div>
          <div class="form-group">
            <label for="activityDate">Date et Heure</label>
            <input type="datetime-local" id="activityDate" name="date" required>
          </div>
          <div class="form-group">
            <label for="activityCategory">Catégorie</label>
            <select id="activityCategory" name="category" required>
              <option value="" disabled selected>Choisir une catégorie</option>
            <option value="sport">Sport</option>
            <option value="bien-etre">Bien-être</option>
            <option value="culture">Culture</option>
            <option value="Ateliers">Ateliers</option>
            <option value="Aérien">Aérien</option>
            <option value="Aquatique">Aquatique</option>
            <option value="Terestre">Terestre</option>
            <option value="Insolite">Insolite</option>
            <option value="Détente">Détente</option>
            <option value="Famille">Famille</option>
            <option value="Extreme">Extreme</option>
            <option value="autre">Autre</option>
            </select>
          </div>
          <div class="form-group">
            <label for="activityCapacity">Capacité maximale</label>
            <input type="number" id="activityCapacity" name="capacity" placeholder="Ex: 50" min="1" required>
          </div>
          <div class="form-group">
            <label for="imageFile">Image de l'activité</label>
            <div class="image-input-container">
              <input type="file" id="imageFile" name="image" accept="image/*" required>
            </div>
            <div id="imagePreview" style="margin-top: 10px; display: none;">
              <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px;">
            </div>
          </div>
          <div class="form-buttons">
            <button type="submit" class="submit-button">Ajouter l'activité</button>
            <a href="dashboard.php" class="cancel-button">Annuler</a>
          </div>
        </form>
      </div>

    <?php elseif ($section === 'edit_activity'): ?>
      <div class="add-activity-form">
        <h3>📋 Formulaire de Modification</h3>
        <?php if (isset($data['error'])): ?>
          <p style="color: red;"><?php echo htmlspecialchars($data['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($data['activity']) && is_array($data['activity'])): ?>
          <form method="POST" enctype="multipart/form-data" novalidate>
            <div class="form-group">
              <label for="activityName">Nom de l'activité</label>
              <input type="text" id="activityName" name="name" value="<?php echo htmlspecialchars($data['activity']['name']); ?>" required>
            </div>
            <div class="form-group">
              <label for="activityDescription">Description</label>
              <textarea id="activityDescription" name="description" rows="5" required><?php echo htmlspecialchars($data['activity']['description']); ?></textarea>
            </div>
            <div class="form-group">
              <label for="activityPrice">Prix (en TND)</label>
              <input type="number" id="activityPrice" name="price" value="<?php echo htmlspecialchars($data['activity']['price']); ?>" step="0.01" min="0" required>
            </div>
            <div class="form-group">
              <label for="activityLocation">Lieu</label>
              <input type="text" id="activityLocation" name="location" value="<?php echo htmlspecialchars($data['activity']['location']); ?>" required>
            </div>
            <div class="form-group">
              <label for="activityDate">Date et Heure</label>
              <input type="datetime-local" id="activityDate" name="date" value="<?php echo date('Y-m-d\TH:i', strtotime($data['activity']['date'])); ?>" required>
            </div>
            <div class="form-group">
              <label for="activityCategory">Catégorie</label>
              <select id="activityCategory" name="category" required>
                <option value="sport" <?php if ($data['activity']['category'] === 'sport') echo 'selected'; ?>>Sport</option>
                <option value="bien-etre" <?php if ($data['activity']['category'] === 'bien-etre') echo 'selected'; ?>>Bien-être</option>
                <option value="culture" <?php if ($data['activity']['category'] === 'culture') echo 'selected'; ?>>Culture</option>
                <option value="autre" <?php if ($data['activity']['category'] === 'autre') echo 'selected'; ?>>Autre</option>
              </select>
            </div>
            <div class="form-group">
              <label for="activityCapacity">Capacité maximale</label>
              <input type="number" id="activityCapacity" name="capacity" value="<?php echo htmlspecialchars($data['activity']['capacity']); ?>" min="1" required>
            </div>
            <div class="form-group">
              <label for="imageFile">Image de l'activité</label>
              <?php if (!empty($data['activity']['image'])): ?>
                <div>
                  <img src="../../<?php echo htmlspecialchars($data['activity']['image']); ?>" alt="Image actuelle" style="max-width: 100%; max-height: 200px;">
                  <p>Image actuelle</p>
                </div>
              <?php endif; ?>
              <div class="image-input-container">
                <input type="file" id="imageFile" name="image" accept="image/*">
              </div>
              <div id="imagePreview" style="margin-top: 10px; display: none;">
                <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px;">
              </div>
              <?php if (!empty($data['activity']['image'])): ?>
                <p>(Laissez vide pour conserver l'image actuelle)</p>
              <?php endif; ?>
            </div>
            <div class="form-buttons">
              <button type="submit" class="submit-button">Enregistrer les modifications</button>
              <a href="dashboard.php" class="cancel-button">Annuler</a>
            </div>
          </form>
        <?php else: ?>
          <p style="color: red;">Activité non trouvée.</p>
        <?php endif; ?>
      </div>

    <?php elseif ($section === 'calendar'): ?>
      <div class="calendar">
        <h3>📅 Calendrier</h3>
        <h5>Avril 2025</h5>
        <div class="calendar-grid">
          <div>Lun</div><div>Mar</div><div>Mer</div><div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
          <div></div><div>1</div><div>2</div><div>3</div><div>4</div><div>5</div><div>6</div>
          <div>7</div><div>8</div><div>9</div><div>10</div><div>11</div><div>12</div><div>13</div>
          <div>14</div><div>15</div><div>16</div><div>17</div><div>18</div><div>19</div><div>20</div>
          <div>21</div><div>22</div><div>23</div><div>24</div><div>25</div><div>26</div><div>27</div>
          <div>28</div><div>29</div><div>30</div>
        </div>
        <div class="upcoming-activities">
          <h3>📅 Activités à Venir</h3>
          <div class="upcoming-list">
            <?php if (isset($data['activities']) && is_array($data['activities'])): ?>
              <?php foreach ($data['activities'] as $activity): ?>
                <div class="upcoming-item">
                  <div>
                    <h4><?php echo htmlspecialchars($activity['name']); ?></h4>
                    <p><?php echo date('d M Y', strtotime($activity['date'])) . ' · ' . htmlspecialchars($activity['location']); ?></p>
                  </div>
                  <span class="badge upcoming">À venir</span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>Aucune activité à venir.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    <?php elseif ($section === 'statistics'): ?>
      <div class="stats-section">
        <h3 class="stats-title">📊 Statistiques Générales</h3>
        <div class="stats-cards">
          <div class="stat-card">
            <div class="stat-icon">🏃‍♀️</div>
            <div class="stat-content">
              <h4>Total des Activités</h4>
              <div class="count" id="count-activities"><?php echo htmlspecialchars($data['stats']['total_activities'] ?? 0); ?></div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
              <h4>Participants</h4>
              <div class="count" id="count-participants"><?php echo htmlspecialchars($data['stats']['total_participants'] ?? 0); ?></div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">📍</div>
            <div class="stat-content">
              <h4>Villes Ciblées</h4>
              <div class="count" id="count-cities"><?php echo htmlspecialchars($data['stats']['total_cities'] ?? 0); ?></div>
            </div>
          </div>
        </div>

        <div class="charts-container">
          <div class="chart-wrapper">
            <h3>📈 Participants et Activités par Mois</h3>
            <canvas id="activityChart"></canvas>
          </div>
          <div class="chart-wrapper">
            <h3>📊 Répartition par Catégorie</h3>
            <canvas id="categoryChart"></canvas>
          </div>
        </div>
      </div>

      <script>
        // Graphique des participants et activités par mois
        const ctxActivity = document.getElementById('activityChart');
        if (ctxActivity) {
          const monthsRaw = <?php echo json_encode(array_column($data['participantsByMonth'] ?? [], 'month')); ?>;
          const participantsDataRaw = <?php echo json_encode(array_column($data['participantsByMonth'] ?? [], 'total_participants')); ?>;
          const activitiesDataRaw = <?php echo json_encode(array_column($data['participantsByMonth'] ?? [], 'total_activities')); ?>;

          console.log('Données pour le graphique par mois :', {
            monthsRaw: monthsRaw,
            participantsDataRaw: participantsDataRaw,
            activitiesDataRaw: activitiesDataRaw
          });

          if (Array.isArray(monthsRaw) && monthsRaw.length > 0) {
            const months = monthsRaw.map(m => {
              const date = new Date(m + '-01');
              return date.toLocaleString('fr-FR', { month: 'short', year: 'numeric' });
            });
            const participantsData = participantsDataRaw.map(Number);
            const activitiesData = activitiesDataRaw.map(Number);

            new Chart(ctxActivity.getContext('2d'), {
              type: 'bar',
              data: {
                labels: months,
                datasets: [
                  {
                    label: 'Nombre de Participants',
                    data: participantsData,
                    backgroundColor: 'rgba(105, 81, 255, 0.6)',
                    borderColor: 'rgba(105, 81, 255, 1)',
                    borderWidth: 1
                  },
                  {
                    label: 'Nombre d\'Activités',
                    data: activitiesData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                  }
                ]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                  y: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'Nombre'
                    }
                  },
                  x: {
                    title: {
                      display: true,
                      text: 'Mois'
                    }
                  }
                },
                plugins: {
                  legend: {
                    position: 'top'
                  }
                }
              }
            });
          } else {
            const context = ctxActivity.getContext('2d');
            context.font = '16px Arial';
            context.fillStyle = '#666';
            context.textAlign = 'center';
            context.fillText('Aucune donnée disponible', ctxActivity.width / 2, ctxActivity.height / 2);
            console.log('Aucune donnée pour le graphique par mois');
          }
        } else {
          console.error('Canvas activityChart non trouvé');
        }

        // Graphique de répartition par catégorie (Donut)
        const ctxCategory = document.getElementById('categoryChart');
        if (ctxCategory) {
          const categoriesRaw = <?php echo json_encode(array_column($data['activitiesByCategory'] ?? [], 'category')); ?>;
          const categoryCountsRaw = <?php echo json_encode(array_column($data['activitiesByCategory'] ?? [], 'count')); ?>;

          console.log('Données pour le graphique par catégorie :', {
            categoriesRaw: categoriesRaw,
            categoryCountsRaw: categoryCountsRaw
          });

          if (Array.isArray(categoriesRaw) && categoriesRaw.length > 0) {
            const categories = categoriesRaw;
            const categoryCounts = categoryCountsRaw.map(Number);

            new Chart(ctxCategory.getContext('2d'), {
              type: 'doughnut',
              data: {
                labels: categories,
                datasets: [{
                  label: 'Activités par Catégorie',
                  data: categoryCounts,
                  backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                  ],
                  borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                  ],
                  borderWidth: 1
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: 'top'
                  },
                  title: {
                    display: true,
                    text: 'Répartition des Activités par Catégorie'
                  }
                }
              }
            });
          } else {
            const context = ctxCategory.getContext('2d');
            context.font = '16px Arial';
            context.fillStyle = '#666';
            context.textAlign = 'center';
            context.fillText('Aucune donnée disponible', ctxCategory.width / 2, ctxCategory.height / 2);
            console.log('Aucune donnée pour le graphique par catégorie');
          }
        } else {
          console.error('Canvas categoryChart non trouvé');
        }
      </script>

    <?php elseif ($section === 'daily_activity'): ?>
      <div class="activity">
        <h3>🌟 Activité du Jour</h3>
        <?php if (isset($data['dailyActivity']) && is_array($data['dailyActivity'])): ?>
          <div class="activity-card">
            <h4>Activité : <?php echo htmlspecialchars($data['dailyActivity']['name']); ?></h4>
            <p><strong>Participants :</strong> <?php echo htmlspecialchars($data['dailyActivity']['capacity']); ?></p>
            <p><strong>Lieu :</strong> <?php echo htmlspecialchars($data['dailyActivity']['location']); ?></p>
            <p><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($data['dailyActivity']['date'])); ?></p>
            <p><strong>Description :</strong> <?php echo htmlspecialchars($data['dailyActivity']['description']); ?></p>
          </div>
        <?php else: ?>
          <p>Aucune activité prévue pour aujourd'hui.</p>
        <?php endif; ?>
      </div>

    <?php elseif ($section === 'history'): ?>
      <div class="container">
        <div class="history">
          <h3>📜 Historique des Activités</h3>
          <div class="history-list">
            <?php if (isset($data['history']) && is_array($data['history'])): ?>
              <?php foreach ($data['history'] as $activity): ?>
                <div class="history-item">
                  <div>
                    <h4><?php echo htmlspecialchars($activity['name']); ?></h4>
                    <p><?php echo date('d M Y', strtotime($activity['date'])) . ' · ' . htmlspecialchars($activity['location']); ?></p>
                  </div>
                  <span class="badge past">Terminé</span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>Aucun historique disponible.</p>
            <?php endif; ?>
          </div>
        </div>
        <div class="upcoming-activities">
          <h3>📅 Activités à Venir</h3>
          <div class="upcoming-list">
            <?php if (isset($data['upcomingActivities']) && is_array($data['upcomingActivities'])): ?>
              <?php foreach ($data['upcomingActivities'] as $activity): ?>
                <div class="upcoming-item">
                  <div>
                    <h4><?php echo htmlspecialchars($activity['name']); ?></h4>
                    <p><?php echo date('d M Y', strtotime($activity['date'])) . ' · ' . htmlspecialchars($activity['location']); ?></p>
                  </div>
                  <span class="badge upcoming">À venir</span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>Aucune activité à venir.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    <?php elseif ($section === 'settings'): ?>
      <div class="settings">
        <h3>⚙️ Paramètres</h3>
        <p>À implémenter : cette section vous permettra de gérer vos paramètres.</p>
      </div>

    <?php else: ?>
      <div class="error">
        <h3>Erreur</h3>
        <p>Section non reconnue : <?php echo htmlspecialchars($section); ?></p>
      </div>
    <?php endif; ?>
  </div> <!-- Fin de <div class="dashboard"> -->

  <script>
    // Script pour l'aperçu de l'image dans les formulaires
    const imageFileInput = document.getElementById('imageFile');
    if (imageFileInput) {
      const imagePreview = document.getElementById('imagePreview');
      const previewImg = document.getElementById('previewImg');

      imageFileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else {
          imagePreview.style.display = 'none';
          previewImg.src = '';
        }
      });
    }

    // Script pour le calendrier
    document.querySelectorAll('.calendar-grid div').forEach(dateEl => {
      dateEl.addEventListener('click', () => {
        if (dateEl.innerText) {
          alert("Tu as cliqué sur la date : " + dateEl.innerText);
        }
      });
    });

    // Script pour les statistiques (animation des compteurs)
    function animateCounter(id, endValue, speed = 30) {
      let el = document.getElementById(id);
      if (el) {
        let current = 0;
        let increment = Math.ceil(endValue / 50);
        let interval = setInterval(() => {
          current += increment;
          if (current >= endValue) {
            current = endValue;
            clearInterval(interval);
          }
          el.textContent = current;
        }, speed);
      }
    }

    window.addEventListener('DOMContentLoaded', () => {
      animateCounter("count-activities", <?php echo (int)($data['stats']['total_activities'] ?? 0); ?>);
      animateCounter("count-participants", <?php echo (int)($data['stats']['total_participants'] ?? 0); ?>);
      animateCounter("count-cities", <?php echo (int)($data['stats']['total_cities'] ?? 0); ?>);
    });
  </script>

  <!-- Inclure Chart.js localement -->
  <script src="js/chart.min.js"></script>
  <script>
    if (typeof Chart !== 'undefined') {
      console.log('Chart.js est chargé avec succès ! Version :', Chart.version);
    } else {
      console.error('Erreur : Chart.js n\'est pas chargé.');
    }
  </script>
  <script src="js/formValidation.js"></script>
</body>
</html>