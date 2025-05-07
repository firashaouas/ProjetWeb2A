<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier une Activité - ClickNGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dash.css">
</head>
<body>
  <div class="sidebar">
    <div>
      <img src="logo.png" alt="Logo" class="logo">
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
      <h2>Modifier une Activité</h2>
      <div class="profile-container">
        <input class="search" type="text" placeholder="Rechercher une activite...">
        <div class="profile">
          <img src="laetitia.webp" alt="Profile Picture">
        </div>
      </div>
    </div>
    <div class="add-activity-form">
      <h3>📋 Formulaire de Modification d'Activité</h3>
      <?php
      session_start();
      require_once '../../model/ActivityModel.php';
      
      if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo '<p style="color: red;">ID d\'activité non spécifié</p>';
        echo '<a href="dashboard.php" class="cancel-button">Retour au tableau de bord</a>';
        exit;
      }
      
      $id = $_GET['id'];
      $activityModel = new ActivityModel();
      $activity = $activityModel->getActivityById($id);
      
      if (!$activity) {
        echo '<p style="color: red;">Activité non trouvée</p>';
        echo '<a href="dashboard.php" class="cancel-button">Retour au tableau de bord</a>';
        exit;
      }
      
      // Affichage des messages de succès ou d'erreur
      if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']);
      }
      if (isset($_SESSION['success'])) {
        echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
        unset($_SESSION['success']);
      }
      ?>
      
      <form method="POST" action="process_activity.php" enctype="multipart/form-data">
        <input type="hidden" name="operation" value="edit">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($activity['id']); ?>">
        
        <div class="form-group">
          <label for="activityName">Nom de l'activité</label>
          <input type="text" id="activityName" name="name" value="<?php echo htmlspecialchars($activity['name']); ?>" required>
        </div>

        <div class="form-group">
          <label for="activityDescription">Description</label>
          <textarea id="activityDescription" name="description" rows="5" required><?php echo htmlspecialchars($activity['description']); ?></textarea>
        </div>

        <div class="form-group">
          <label for="activityPrice">Prix (en TND)</label>
          <input type="number" id="activityPrice" name="price" value="<?php echo htmlspecialchars($activity['price']); ?>" step="0.01" min="0" required>
        </div>

        <div class="form-group">
          <label for="activityLocation">Lieu</label>
          <input type="text" id="activityLocation" name="location" value="<?php echo htmlspecialchars($activity['location']); ?>" required>
        </div>

        <div class="form-group">
          <label for="activityDate">Date et Heure</label>
          <input type="datetime-local" id="activityDate" name="date" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($activity['date']))); ?>" required>
        </div>

        <div class="form-group">
          <label for="activityCategory">Catégorie</label>
          <select id="activityCategory" name="category" required>
            <option value="" disabled>Choisir une catégorie</option>
            <option value="Ateliers" <?php echo $activity['category'] == 'Ateliers' ? 'selected' : ''; ?>>Ateliers</option>
            <option value="bien-etre" <?php echo $activity['category'] == 'bien-etre' ? 'selected' : ''; ?>>Bien-être</option>
            <option value="Aérien" <?php echo $activity['category'] == 'Aérien' ? 'selected' : ''; ?>>Aérien</option>
            <option value="Aquatique" <?php echo $activity['category'] == 'Aquatique' ? 'selected' : ''; ?>>Aquatique</option>
            <option value="Terestre" <?php echo $activity['category'] == 'Terestre' ? 'selected' : ''; ?>>Terrestre</option>
            <option value="Insolite" <?php echo $activity['category'] == 'Insolite' ? 'selected' : ''; ?>>Insolite</option>
            <option value="culture" <?php echo $activity['category'] == 'culture' ? 'selected' : ''; ?>>Culture</option>
            <option value="Détente" <?php echo $activity['category'] == 'Détente' ? 'selected' : ''; ?>>Détente</option>
            <option value="sport" <?php echo $activity['category'] == 'sport' ? 'selected' : ''; ?>>Sport</option>
            <option value="nature" <?php echo $activity['category'] == 'nature' ? 'selected' : ''; ?>>Nature</option>
            <option value="aventure" <?php echo $activity['category'] == 'aventure' ? 'selected' : ''; ?>>Aventure</option>
            <option value="Famille" <?php echo $activity['category'] == 'Famille' ? 'selected' : ''; ?>>Famille</option>
            <option value="Extreme" <?php echo $activity['category'] == 'Extreme' ? 'selected' : ''; ?>>Extrême</option>
          </select>
        </div>

        <div class="form-group">
          <label for="activityCapacity">Capacité maximale</label>
          <input type="number" id="activityCapacity" name="capacity" value="<?php echo htmlspecialchars($activity['capacity']); ?>" min="1" required>
        </div>
        
        <div class="form-group">
          <label for="imageFile">Image de l'activité</label>
          <div class="image-input-container">
            <input type="file" id="imageFile" name="image" accept="image/*">
            <p>Laissez vide pour conserver l'image actuelle</p>
          </div>
          <?php if (!empty($activity['image'])): ?>
          <div id="currentImage" style="margin-top: 10px;">
            <p>Image actuelle:</p>
            <img src="../../<?php echo htmlspecialchars($activity['image']); ?>" alt="Image de l'activité" style="max-width: 100%; max-height: 200px;">
          </div>
          <?php endif; ?>
          <div id="imagePreview" style="margin-top: 10px; display: none;">
            <p>Nouvelle image:</p>
            <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px;">
          </div>
        </div>
        
        <!-- JavaScript pour gérer l'aperçu de l'image -->
        <script>
          const imageFileInput = document.getElementById('imageFile');
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
        </script>

        <div class="form-buttons">
          <button type="submit" class="submit-button">Mettre à jour l'activité</button>
          <a href="dashboard.php" class="cancel-button">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>