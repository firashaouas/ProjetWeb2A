<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajouter une Activité - ClickNGo</title>
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
      <h2>Ajouter une Nouvelle Activité</h2>
      <div class="profile-container">
        <input class="search" type="text" placeholder="Rechercher une activite...">
        <div class="profile">
          <img src="laetitia.webp" alt="Profile Picture">
        </div>
      </div>
    </div>

    <div class="add-activity-form">
      <h3>📋 Formulaire d'Ajout d'Activité</h3>
      <?php
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "clickngo_db";

        try {
          $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          // Récupérer les données du formulaire
          $name = $_POST['name'];
          $description = $_POST['description'];
          $price = $_POST['price'];
          $location = $_POST['location'];
          $date = $_POST['date'];
          $category = $_POST['category'];
          $capacity = $_POST['capacity'];

          // Insérer l'activité dans la base de données
          $stmt = $conn->prepare("INSERT INTO activities (name, description, price, location, date, category, capacity) VALUES (?, ?, ?, ?, ?, ?, ?)");
          $stmt->execute([$name, $description, $price, $location, $date, $category, $capacity]);

          echo '<p style="color: green;">Activité ajoutée avec succès !</p>';
          header("Refresh: 2; url=dashboard.php"); // Redirection après 2 secondes
        } catch (PDOException $e) {
          echo '<p style="color: red;">Erreur : ' . $e->getMessage() . '</p>';
        }
        $conn = null;
      }
      ?>
      <form method="POST" enctype="multipart/form-data">
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
        <!-- Champ pour uploader une image -->
        <div class="form-group">
          <label for="image">Image de l'activité *</label>
          <div class="image-input-container">
            <input type="file" id="imageFile" name="imageFile" accept="image/*">
          </div>
          <div id="imagePreview" style="margin-top: 10px; display: none;">
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
          <button type="submit" class="submit-button">Ajouter l'activité</button>
          <a href="dashboard.php" class="cancel-button">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>