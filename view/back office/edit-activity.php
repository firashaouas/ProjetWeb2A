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
    <div class="edit-activity-form">
      <h3>📋 Formulaire de Modification</h3>
      <?php
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "clickngo_db";

      try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer l'ID de l'activité à modifier
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Récupérer les données de l'activité
        $stmt = $conn->prepare("SELECT * FROM activities WHERE id = ?");
        $stmt->execute([$id]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$activity) {
          echo '<p style="color: red;">Activité non trouvée.</p>';
          exit;
        }

        // Gérer la mise à jour
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $name = $_POST['name'];
          $description = $_POST['description'];
          $price = $_POST['price'];
          $location = $_POST['location'];
          $date = $_POST['date'];
          $category = $_POST['category'];
          $capacity = $_POST['capacity'];
          
          // Conserver l'image existante par défaut
          $image = isset($_POST['current_image']) ? $_POST['current_image'] : $activity['image'];

          // Traitement de la nouvelle image si elle a été uploadée
          if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
            $uploadDir = '../../image/';
            
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Impossible de créer le répertoire d'images");
                }
            }
            
            // Générer un nom de fichier unique
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            // Déplacer le fichier uploadé
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                // Si une ancienne image existe, on pourrait la supprimer ici
                $image = 'image/' . $fileName;
            } else {
                throw new Exception("Echec de l'upload du fichier");
            }
          }

          // Mise à jour avec l'image
          $stmt = $conn->prepare("UPDATE activities SET name = ?, description = ?, price = ?, location = ?, date = ?, category = ?, capacity = ?, image = ? WHERE id = ?");
          $stmt->execute([$name, $description, $price, $location, $date, $category, $capacity, $image, $id]);

          echo '<p style="color: green;">Activité modifiée avec succès !</p>';
          header("Refresh: 2; url=dashboard.php");
        }
      } catch (Exception $e) {
        echo '<p style="color: red;">Erreur : ' . $e->getMessage() . '</p>';
      }
      $conn = null;
      ?>
      <form method="POST" enctype="multipart/form-data">
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
          <input type="datetime-local" id="activityDate" name="date" value="<?php echo date('Y-m-d\TH:i', strtotime($activity['date'])); ?>" required>
        </div>

        <div class="form-group">
          <label for="activityCategory">Catégorie</label>
          <select id="activityCategory" name="category" required>
            <option value="sport" <?php if ($activity['category'] === 'sport') echo 'selected'; ?>>Sport</option>
            <option value="bien-etre" <?php if ($activity['category'] === 'bien-etre') echo 'selected'; ?>>Bien-être</option>
            <option value="Ateliers" <?php if ($activity['category'] === 'Ateliers') echo 'selected'; ?>>Ateliers</option>
            <option value="Aérien" <?php if ($activity['category'] === 'Aérien') echo 'selected'; ?>>Aérien</option>
            <option value="Aquatique" <?php if ($activity['category'] === 'Aquatique') echo 'selected'; ?>>Aquatique</option>
            <option value="Terestre" <?php if ($activity['category'] === 'Terestre') echo 'selected'; ?>>Terrestre</option>
            <option value="Insolite" <?php if ($activity['category'] === 'Insolite') echo 'selected'; ?>>Insolite</option>
            <option value="culture" <?php if ($activity['category'] === 'culture') echo 'selected'; ?>>Culture</option>
            <option value="Détente" <?php if ($activity['category'] === 'Détente') echo 'selected'; ?>>Détente</option>
            <option value="nature" <?php if ($activity['category'] === 'nature') echo 'selected'; ?>>Nature</option>
            <option value="aventure" <?php if ($activity['category'] === 'aventure') echo 'selected'; ?>>Aventure</option>
            <option value="Famille" <?php if ($activity['category'] === 'Famille') echo 'selected'; ?>>Famille</option>
            <option value="Extreme" <?php if ($activity['category'] === 'Extreme') echo 'selected'; ?>>Extrême</option>
            <option value="autre" <?php if ($activity['category'] === 'autre') echo 'selected'; ?>>Autre</option>
          </select>
        </div>

        <div class="form-group">
          <label for="activityCapacity">Capacité maximale</label>
          <input type="number" id="activityCapacity" name="capacity" value="<?php echo htmlspecialchars($activity['capacity']); ?>" min="1" required>
        </div>
        <!-- Champ pour uploader une nouvelle image -->
        <div class="form-group">
            <label for="imageFile">Image de l'activité</label>
            <?php if ($activity['image']): ?>
              <div>
                <img src="../../<?php echo htmlspecialchars($activity['image']); ?>" alt="Image actuelle" style="max-width: 200px; max-height: 200px;">
                <p>Image actuelle</p>
              </div>
              <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($activity['image']); ?>">
            <?php endif; ?>
            <input type="file" id="imageFile" name="image" accept="image/*">
            <p>(Laissez vide pour conserver l'image actuelle)</p>
          </div>
          
        <!-- JavaScript pour gérer l'aperçu de l'image -->
        <script>
          const imageFileInput = document.getElementById('imageFile');
          if (imageFileInput) {
            imageFileInput.addEventListener('change', function () {
              const file = this.files[0];
              if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                  const previewDiv = document.createElement('div');
                  previewDiv.id = 'imagePreview';
                  previewDiv.style.marginTop = '10px';
                  
                  const previewImg = document.createElement('img');
                  previewImg.src = e.target.result;
                  previewImg.alt = 'Aperçu de la nouvelle image';
                  previewImg.style.maxWidth = '200px';
                  previewImg.style.maxHeight = '200px';
                  
                  const existingPreview = document.getElementById('imagePreview');
                  if (existingPreview) {
                    existingPreview.remove();
                  }
                  
                  previewDiv.appendChild(previewImg);
                  imageFileInput.parentNode.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
              }
            });
          }
        </script>

        <div class="form-buttons">
          <button type="submit" class="submit-button">Enregistrer les modifications</button>
          <a href="dashboard.php" class="cancel-button">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>