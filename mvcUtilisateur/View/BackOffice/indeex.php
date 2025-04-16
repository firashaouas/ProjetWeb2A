// public/index.php
<?php
require_once(__DIR__ . '../../../Controller/UserController.php');

$userController = new UserController();
$userModel = $userController->getAllUsers(); // récupère tous les utilisateurs

$action = $_GET['action'] ?? '';

switch ($action) {
  case 'addUser':
      $controller = new UserController();
      $controller->addUser();
      break;
  // Ajoute les autres actions ici (updateUser, deleteUser, etc.)
  default:
      // Vue par défaut

      break;
}

?>


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

</head>
<body>
  <div class="sidebar">
    <div>
      <img src="logo.png" alt="Logo" class="logo">
      <h1>Click'N'go</h1>
      <div class="menu-item active" data-section="overview">🏠 Tableau de Bord</div>
      <div class="menu-item" data-section="promos">🎁 Utilisateurs</div>
      <div class="menu-item" data-section="products">📦 Produits</div>
      <div class="menu-item" data-section="orders">📋 Commandes</div>
      <div class="menu-item" data-section="reviews">⭐ Avis</div>
      <div class="menu-item" data-section="settings">⚙️ Réglages</div>
    </div>
    <div class="menu-item">🚪 Déconnexion</div>
  </div>

  <div class="dashboard">
    <!-- Overview Section (Tableau de Bord) -->
    <div class="dashboard-section active" id="overview">
      <div class="header">
        <h2>Votre Tripe, Votre Magie ! ✨</h2>
        <div class="profile-container">
          <input class="search" type="text" placeholder="Rechercher...">
          <div class="profile">
            <img src="user.webp" alt="Profile Picture">
          </div>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="stats-container">
        <div class="stat-card" data-tooltip="Produits disponibles">
          <div class="stat-icon">📦</div>
          <h3>Stock Total</h3>
          <p class="stat-value">345</p>
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
          <h3>Répartition Stock</h3>
          <canvas id="stockDonut"></canvas>
        </div>
        <div class="chart-card">
          <h3>Tendances Ventes</h3>
          <canvas id="salesWave"></canvas>
        </div>
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
    <h2>Gestion des Utilisateurs 🎁</h2>
    <div class="profile-container">
      <input class="search" type="text" placeholder="Rechercher un utilisateur">
      <div class="profile">
        <img src="user.webp" alt="Profile Picture">
      </div>
    </div>
  </div>

  <div class="add-product-nav">
    <button class="add-product-btn" onclick="openPromoModal()">➕</button>
  </div>

  <div class="promos-table">
    <h3>Liste des Utilisateurs</h3>
    <table>
      <thead>
        <tr>
          <th>Profile</th>
          <th>ID</th>
          <th>Nom</th>
          <th>Email</th>
          <th>Password</th>
          <th>Date Inscription</th>
          <th>Num</th>
          <th>Role</th>
          <th>Actions</th> <!-- Nouvelle colonne -->
        </tr>
      </thead>


      <?php
if (!empty($userModel)) {
    foreach ($userModel as $user) {
        $absolutePath = "http://localhost/Projet%20Web/mvcUtilisateur/View/FrontOffice/" . $user->getProfilePicture();

        echo "<tr>
                <td><img src='{$absolutePath}' alt='Profile' style='width:40px;height:40px;border-radius:50%;object-fit:cover;'></td>
                <td>{$user->getIdUser()}</td>
                <td>{$user->getFullName()}</td>
                <td>{$user->getEmail()}</td>
                <td>{$user->getPassword()}</td>
                <td>{$user->getDateInscription()}</td>
                <td>{$user->getNumUser()}</td>
                <td>{$user->getRole()}</td>
        <td>
          <button class='action-btn purple edit' onclick='editUser({$user->getIdUser()})'>
            <i class='fas fa-edit'></i> Modifier
          </button>
          <button class='action-btn purple delete' onclick='deleteUser({$user->getIdUser()})'>
            <i class='fas fa-trash-alt'></i> Supprimer
          </button>
        </td>

              </tr>";
    }
} else {
    echo "<tr><td colspan='8'>Aucun utilisateur trouvé</td></tr>";
}
?>





  
    </table>
  </div>
</div>


    <!-- Reviews Section -->


    <!-- Settings Section -->


  <!-- Product Modal -->


  <!-- Promo Modal -->
  <div class="modal" id="promoModal">
    <div class="modal-content">
    <h3>Ajouter un Utilisateur</h3>
    <form id="userForm" action="index.php?action=addUser" method="POST" enctype="multipart/form-data">
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
  function editUser(id) {
    window.location.href = 'index.php?action=editUser&id=' + id;
  }

  function deleteUser(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
      window.location.href = 'index.php?action=deleteUser&id=' + id;
    }
  }
</script>


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
            labels: { font: { size: 12, weight: '600' }, color: '#333', padding: 20 }
          }
        },
        cutout: '70%'
      }
    });

    // Sales Trends (Line Chart)
    const salesWave = new Chart(document.getElementById('salesWave'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr'],
        datasets: [{
          label: 'Ventes (TND)',
          data: [5000, 7000, 4000, 9000],
          borderColor: '#ff6b6b',
          backgroundColor: 'rgba(255, 107, 107, 0.2)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#ff6b6b',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        animation: {
          duration: 2000,
          easing: 'easeOutQuart'
        },
        plugins: {
          legend: { display: false },
          title: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#333', font: { size: 12 } },
            grid: { color: 'rgba(0, 0, 0, 0.05)' }
          },
          x: {
            ticks: { color: '#333', font: { size: 12 } },
            grid: { display: false }
          }
        }
      }
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
          legend: { display: false },
          title: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: '#333', font: { size: 12 } },
            grid: { color: 'rgba(0, 0, 0, 0.05)' }
          },
          x: {
            ticks: { color: '#333', font: { size: 12 } },
            grid: { display: false }
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

    // CRUD Operations (Mock)
    document.getElementById('productForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const name = document.getElementById('productName').value;
      const price = document.getElementById('productPrice').value;
      const stock = document.getElementById('productStock').value;
      const purchase = document.getElementById('productPurchase').value;
      const rental = document.getElementById('productRental').value;
      
      // Simulate adding/editing product
      alert(`Produit ${name} sauvegardé ! Prix: ${price} TND, Stock: ${stock}, Achat: ${purchase}, Location: ${rental}`);
      closeModal();
    });

    document.getElementById('promoForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const product = document.getElementById('promoProduct').value;
      const discount = document.getElementById('promoDiscount').value;
      const endDate = document.getElementById('promoEndDate').value;
      
      alert(`Promotion sur ${product} : ${discount}% jusqu'au ${endDate}`);
      closeModal();
    });

    function confirmDelete(id) {
      if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
        alert(`Produit ${id} supprimé !`);
      }
    }

    function updateOrderStatus(id, status) {
      alert(`Commande ${id} marquée comme ${status}`);
    }

    function editPromo(button) {
      alert('Modifier la promotion');
    }

    function deletePromo(button) {
      if (confirm('Supprimer cette promotion ?')) {
        alert('Promotion supprimée');
      }
    }

    function approveReview(button) {
      alert('Avis approuvé');
    }

    function rejectReview(button) {
      alert('Avis rejeté');
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