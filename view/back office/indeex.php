<?php
require_once '../../Controller/produitcontroller.php';

$controller = new ProductController();
$response = $controller->getAllProducts();
$products = $response['success'] ? $response['products'] : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Gestion de Produits</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Styles existants pour les cartes de produits (inchangés) */
    .product-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 400px;
        width: 100%;
        max-width: 250px;
        margin: 0 auto;
    }

    .product-image {
        width: 100%;
        height: 150px;
        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-details {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    .product-details h3 {
        font-size: 16px;
        margin: 0 0 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-details p {
        font-size: 12px;
        margin: 4px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e0e0e0;
        border-radius: 5px;
        margin: 8px 0;
    }

    .progress-bar-fill {
        height: 100%;
        background: #4CAF50;
        border-radius: 5px;
        transition: width 0.3s ease;
    }

    /* Style pour le conteneur des boutons dans les cartes */
    .card-buttons {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    /* Style de base pour tous les boutons */
    .btn {
        padding: 8px;
        font-size: 12px;
        border-radius: 4px;
        cursor: pointer;
        text-align: center;
        color: white;
        border: none;
        transition: background-color 0.3s ease;
    }

    /* Styles spécifiques pour les boutons dans les cartes */
    .card-buttons .btn {
        flex: 1; /* Les boutons dans les cartes occupent tout l'espace disponible */
    }

    .edit-button {
        background-color: #2196F3;
    }

    .edit-button:hover {
        background-color: #1976D2;
    }

    .delete-button {
        background-color: #f44336;
    }

    .delete-button:hover {
        background-color: #d32f2f;
    }

    /* Style pour le conteneur du bouton Ajouter */
    .add-product-nav {
        display: flex;
        justify-content: center;
        margin: 20px 0;
    }

    /* Style pour le bouton Ajouter */
    .add-button {
        background-color: #4CAF50;
    }

    .add-button:hover {
        background-color: #388E3C;
    }

    /* Styles pour le modal (inchangés) */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 0;
      border: 1px solid #888;
      width: 80%;
      max-width: 600px;
      max-height: 80vh;
      display: flex;
      flex-direction: column;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .modal-header {
      padding: 16px;
      background-color: #f5f5f5;
      border-bottom: 1px solid #ddd;
    }
    
    .modal-body {
      padding: 16px;
      overflow-y: auto;
      flex-grow: 1;
    }
    
    .modal-footer {
      padding: 16px;
      background-color: #f5f5f5;
      border-top: 1px solid #ddd;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }
    
    .form-group input:focus,
    .form-group select:focus {
      border-color: #4CAF50;
      outline: none;
    }
    
    .error-message {
      color: #d32f2f;
      font-size: 0.8em;
      display: block;
      margin-top: 5px;
      min-height: 18px;
    }
    
    input:invalid, select:invalid {
      border-color: #ff4444;
    }
    
    input:valid, select:valid {
      border-color: #00C851;
    }
    
    .close-btn {
      padding: 8px 16px;
      background-color: #f44336;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    
    .save-btn {
      padding: 8px 16px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    
    .close-btn:hover {
      background-color: #d32f2f;
    }
    
    .save-btn:hover {
      background-color: #388E3C;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div>
      <img src="logo.png" alt="Logo" class="logo">
      <h1>Click'N'go</h1>
      <div class="menu-item active" data-section="overview">🏠 Tableau de Bord</div>
      <div class="menu-item" data-section="products">📦 Produits</div>
      <div class="menu-item" data-section="orders">📋 Commandes</div>
      <div class="menu-item" data-section="promos">🎁 Promotions</div>
      <div class="menu-item" data-section="reviews">⭐ Avis</div>
      <div class="menu-item" data-section="settings">⚙️ Réglages</div>
    </div>
    <div class="menu-item">🚪 Déconnexion</div>
  </div>

  <div class="dashboard">
    <!-- Overview Section (Tableau de Bord) -->
    <div class="dashboard-section active" id="overview">
      <div class="header">
        <h2>Suivez vos produits, boostez vos ventes ! ✨</h2>
        <div class="profile-container">
          <input class="search" type="text" placeholder="Rechercher...">
          <div class="profile">
            <img src="Sarah.webp" alt="Profile Picture">
          </div>
        </div>
      </div>

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

      <div class="stock-alert">
        <p><span class="alert-icon">🔥</span> <strong>Attention :</strong> 3 produits presque épuisés !</p>
        <button class="alert-btn">Agir Maintenant</button>
      </div>

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
    <div class="dashboard-section" id="products">
      <div class="header">
        <h2>Gestion des Produits 📦</h2>
        <div class="profile-container">
          <input class="search" type="text" placeholder="Rechercher un produit...">
          <div class="profile">
            <img src="Sarah.webp" alt="Profile Picture">
          </div>
        </div>
      </div>

      <div class="add-product-nav">
        <button class="btn add-button" onclick="openProductModal('add')">Ajouter</button>
      </div>

      <div class="product-cards">
        <?php if (!$response['success']): ?>
            <p class="error">Erreur lors du chargement des produits: <?= htmlspecialchars($response['error']) ?></p>
        <?php elseif (empty($products)): ?>
            <p class="no-products">Aucun produit disponible</p>
        <?php else: ?>
            <?php foreach ($products as $product): 
                $stock = (int)$product['stock'];
                $stockPercentage = min(100, max(0, ($stock / 100) * 100));
                $purchaseStatus = $product['purchase_available'] ? 'Oui' : 'Non';
                $rentalStatus = $product['rental_available'] ? 'Oui' : 'Non';
                $imagePath = !empty($product['photo']) ? $product['photo'] : 'images/products/logo.png';
            ?>
                <div class="card">
                    <div class="product-image">
                        <img src="../../<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="product-details">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>Prix: <?= number_format($product['price'], 2) ?> TND</p>
                        <p>Stock: <?= $stock ?> unités</p>
                        <p>Catégorie: <?= htmlspecialchars($product['category']) ?></p>
                        <p>Achat: <?= $purchaseStatus ?> | Location: <?= $rentalStatus ?></p>
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: <?= $stockPercentage ?>%;"></div>
                        </div>
                        <div class="card-buttons">
                            <button class="btn edit-button" onclick="openProductModal('edit', <?= $product['id'] ?>)">Modifier</button>
                            <button class="btn delete-button" onclick="confirmDelete(<?= $product['id'] ?>)">Supprimer</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="categories">
        <div class="category">
          <h4>Équipements Sportifs</h4>
          <p>150 produits</p>
        </div>
        <div class="category">
          <h4>Vêtements et Accessoires</h4>
          <p>90 produits</p>
        </div>
        <div class="category">
          <h4>Gadgets & Technologies</h4>
          <p>60 produits</p>
        </div>
        <div class="category">
          <h4>Articles de Bien-être & Récupération</h4>
          <p>45 produits</p>
        </div>
        <div class="category">
          <h4>Nutrition & Hydratation</h4>
          <p>30 produits</p>
        </div>
        <div class="category">
          <h4>Accessoires de Voyage & Mobilité</h4>
          <p>25 produits</p>
        </div>
        <div class="category">
          <h4>Supports et accessoires d’atelier</h4>
          <p>20 produits</p>
        </div>
        <div class="category">
          <h4>Univers du cerveau</h4>
          <p>15 produits</p>
        </div>
      </div>
    </div>

    <!-- Orders Section -->
    <div class="dashboard-section" id="orders">
      <div class="header">
        <h2>Gestion des Commandes 📋</h2>
        <div class="profile-container">
          <input class="search" type="text" placeholder="Rechercher une commande...">
          <div class="profile">
            <img src="images/products/logo.png" alt="Profile Picture">
          </div>
        </div>
      </div>

      <div class="orders-table">
        <h3>Liste des Commandes</h3>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Produit</th>
              <th>Type</th>
              <th>Client</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#001</td>
              <td>Montre connectée</td>
              <td>Achat</td>
              <td>Ahmed</td>
              <td>En attente</td>
              <td class="action-cell">
                <button class="btn" onclick="updateOrderStatus('#001', 'Livré')">Livrer</button>
              </td>
            </tr>
            <tr>
              <td>#002</td>
              <td>Caméra instantanée</td>
              <td>Location</td>
              <td>Sarah</td>
              <td>Livré</td>
              <td class="action-cell">
                <button class="btn" onclick="updateOrderStatus('#002', 'Retourné')">Retour</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Promos Section -->
    <div class="dashboard-section" id="promos">
      <div class="header">
        <h2>Gestion des Promotions 🎁</h2>
        <div class="profile-container">
          <input class="search" type="text" placeholder="Rechercher une promotion...">
          <div class="profile">
            <img src="images/products/logo.png" alt="Profile Picture">
          </div>
        </div>
      </div>

      <div class="add-product-nav">
        <button class="add-product-btn" onclick="openPromoModal()">➕</button>
      </div>

      <div class="promos-table">
        <h3>Liste des Promotions</h3>
        <table>
          <thead>
            <tr>
              <th>Produit</th>
              <th>Réduction</th>
              <th>Date Fin</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Montre connectée</td>
              <td>-20%</td>
              <td>30/04/2025</td>
              <td class="action-cell">
                <button class="btn" onclick="editPromo(this)">Modifier</button>
                <button class="btn" onclick="deletePromo(this)">Supprimer</button>
              </td>
            </tr>
            <tr>
              <td>Tapis de yoga</td>
              <td>-15%</td>
              <td>15/05/2025</td>
              <td class="action-cell">
                <button class="btn" onclick="editPromo(this)">Modifier</button>
                <button class="btn" onclick="deletePromo(this)">Supprimer</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Reviews Section -->
    <div class="dashboard-section" id="reviews">
      <div class="header">
        <h2>Gestion des Avis ⭐</h2>
        <div class="profile-container">
          <input class="search" type="text" placeholder="Rechercher un avis...">
          <div class="profile">
            <img src="images/products/logo.png" alt="Profile Picture">
          </div>
        </div>
      </div>

      <div class="reviews-table">
        <h3>Liste des Avis</h3>
        <table>
          <thead>
            <tr>
              <th>Produit</th>
              <th>Client</th>
              <th>Note</th>
              <th>Commentaire</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Montre connectée</td>
              <td>Ahmed</td>
              <td>★★★★☆</td>
              <td>Super produit !</td>
              <td class="action-cell">
                <button class="btn" onclick="approveReview(this)">Approuver</button>
                <button class="btn" onclick="rejectReview(this)">Rejeter</button>
              </td>
            </tr>
            <tr>
              <td>Tapis de yoga</td>
              <td>Sarah</td>
              <td>★★★☆☆</td>
              <td>Correct mais fragile.</td>
              <td class="action-cell">
                <button class="btn" onclick="approveReview(this)">Approuver</button>
                <button class="btn" onclick="rejectReview(this)">Rejeter</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Settings Section -->
    <div class="dashboard-section" id="settings">
      <div class="header">
        <h2>Réglages ⚙️</h2>
        <div class="profile-container">
          <div class="profile">
            <img src="user.webp" alt="Profile Picture">
          </div>
        </div>
      </div>
      <div class="card">
        <h3>Paramètres du Site</h3>
        <p>Frais de livraison : 10 TND<br>Durée max. location : 7 jours</p>
        <div class="card-buttons">
          <button class="btn">Modifier Paramètres</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Product Modal -->
  <div class="modal" id="productModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Ajouter un Produit</h3>
      </div>
      <div class="modal-body">
        <form id="productForm" action="../../Controller/produitcontroller.php" method="POST" enctype="multipart/form-data" novalidate>
          <div class="form-group">
            <label for="productName">Nom</label>
            <input type="text" id="productName" name="name" required>
            <span class="error-message" id="nameError"></span>
          </div>
          
          <div class="form-group">
            <label for="productPrice">Prix (TND)</label>
            <input type="number" id="productPrice" name="price" min="0" step="0.01" required>
            <span class="error-message" id="priceError"></span>
          </div>
          
          <div class="form-group">
            <label for="productStock">Stock</label>
            <input type="number" id="productStock" name="stock" min="0" required>
            <span class="error-message" id="stockError"></span>
          </div>
          
          <div class="form-group">
            <label for="productCategory">Catégorie</label>
            <select id="productCategory" name="category" required>
              <option value="">Choisissez une catégorie</option>
              <option value="Équipements Sportifs">Équipements Sportifs</option>
              <option value="Vêtements et Accessoires">Vêtements et Accessoires</option>
              <option value="Gadgets & Technologies">Gadgets & Technologies</option>
              <option value="Articles de Bien-être & Récupération">Articles de Bien-être & Récupération</option>
              <option value="Nutrition & Hydratation">Nutrition & Hydratation</option>
              <option value="Accessoires de Voyage & Mobilité">Accessoires de Voyage & Mobilité</option>
              <option value="Supports et accessoires d'atelier">Supports et accessoires d'atelier</option>
              <option value="Univers du cerveau">Univers du cerveau</option>
            </select>
            <span class="error-message" id="categoryError"></span>
          </div>
          
          <div class="form-group">
            <label for="productPurchase">Disponible à l'achat</label>
            <select id="productPurchase" name="purchase_available" required>
              <option value="yes">Oui</option>
              <option value="no">Non</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="productRental">Disponible à la location</label>
            <select id="productRental" name="rental_available" required>
              <option value="yes">Oui</option>
              <option value="no">Non</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="productPhoto">Photo</label>
            <input type="file" id="productPhoto" name="photo" accept="image/*">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="close-btn" onclick="closeModal()">Annuler</button>
        <button type="submit" form="productForm" class="save-btn">Enregistrer</button>
      </div>
    </div>
  </div>

  <!-- Promo Modal -->
  <div class="modal" id="promoModal">
    <div class="modal-content">
      <h3>Ajouter une Promotion</h3>
      <form id="promoForm">
        <label for="promoProduct">Produit</label>
        <select id="promoProduct">
          <option value="Montre connectée">Montre connectée</option>
          <option value="Tapis de yoga">Tapis de yoga</option>
          <option value="Caméra instantanée">Caméra instantanée</option>
        </select>
        <label for="promoDiscount">Réduction (%)</label>
        <input type="number" id="promoDiscount">
        <label for="promoEndDate">Date de fin</label>
        <input type="date" id="promoEndDate">
        <div class="form-buttons">
          <button type="button" class="close-btn" onclick="closeModal()">Annuler</button>
          <button type="submit" class="save-btn">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <script src="scripts.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const nameRegex = /^[A-Za-zÀ-ÖØ-öø-ÿ\s'-]+$/;
      const form = document.getElementById('productForm');
      const nameInput = document.getElementById('productName');
      const priceInput = document.getElementById('productPrice');
      const stockInput = document.getElementById('productStock');
      const categorySelect = document.getElementById('productCategory');

      nameInput.addEventListener('input', () => validateField(nameInput, 'nameError', validateName));
      priceInput.addEventListener('input', () => validateField(priceInput, 'priceError', validatePrice));
      stockInput.addEventListener('input', () => validateField(stockInput, 'stockError', validateStock));
      categorySelect.addEventListener('change', () => validateField(categorySelect, 'categoryError', validateCategory));

      form.addEventListener('submit', function(event) {
        const isNameValid = validateField(nameInput, 'nameError', validateName);
        const isPriceValid = validateField(priceInput, 'priceError', validatePrice);
        const isStockValid = validateField(stockInput, 'stockError', validateStock);
        const isCategoryValid = validateField(categorySelect, 'categoryError', validateCategory);

        if (!isNameValid || !isPriceValid || !isStockValid || !isCategoryValid) {
          event.preventDefault();
          const firstInvalid = document.querySelector('.invalid');
          if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
          }
        }
      });

      function validateField(input, errorId, validationFn) {
        const errorElement = document.getElementById(errorId);
        const isValid = validationFn(input.value.trim(), input);

        if (!isValid.valid) {
          errorElement.textContent = isValid.message;
          input.classList.add('invalid');
          input.classList.remove('valid');
          return false;
        } else {
          errorElement.textContent = '';
          input.classList.add('valid');
          input.classList.remove('invalid');
          return true;
        }
      }

      function validateName(value) {
        if (value === "") {
          return { valid: false, message: "Le nom du produit est requis." };
        }
        if (!nameRegex.test(value)) {
          return { valid: false, message: "Le nom ne doit contenir que des lettres et des espaces." };
        }
        return { valid: true };
      }

      function validatePrice(value) {
        if (value === "") {
          return { valid: false, message: "Le prix est requis." };
        }
        if (isNaN(value) || parseFloat(value) < 0) {
          return { valid: false, message: "Le prix doit être un nombre positif." };
        }
        return { valid: true };
      }

      function validateStock(value) {
        if (value === "") {
          return { valid: false, message: "Le stock est requis." };
        }
        if (isNaN(value) || !Number.isInteger(Number(value)) || parseInt(value) < 0) {
          return { valid: false, message: "Le stock doit être un entier positif." };
        }
        return { valid: true };
      }

      function validateCategory(value) {
        if (value === "") {
          return { valid: false, message: "La catégorie est requise." };
        }
        return { valid: true };
      }

      nameInput.addEventListener('blur', () => validateField(nameInput, 'nameError', validateName));
      priceInput.addEventListener('blur', () => validateField(priceInput, 'priceError', validatePrice));
      stockInput.addEventListener('blur', () => validateField(stockInput, 'stockError', validateStock));
    });

    function closeModal() {
      document.getElementById('productModal').style.display = "none";
    }

    function openModal() {
      document.getElementById('productModal').style.display = "block";
    }

    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      const message = urlParams.get('message');
      if (message) {
        alert(message);
      }
    };
  </script>
</body>
</html>