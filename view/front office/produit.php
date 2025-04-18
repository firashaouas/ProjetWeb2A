<?php
// 1. Charger les produits depuis le contrôleur
require_once '../../Controller/produitcontroller.php';

$controller = new ProductController();
$allProducts = $controller->getAllProducts(); // Cela retourne tous les produits avec catégorie, nom, prix, photo
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go - Produits</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles pour les cartes de catégories */
        .trending {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            margin-bottom: 40px;
            white-space: nowrap;
            padding-bottom: 10px;
        }

        .activity-card {
            flex: 0 0 200px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .activity-card:hover {
            transform: translateY(-5px);
        }

        .activity-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .activity-card h4 {
            padding: 10px;
            margin: 0;
            font-size: 14px;
            font-weight: 500;
            color: #222;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
            white-space: normal;
            word-wrap: break-word;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60px;
        }

        /* Styles pour l'affichage des produits par catégorie */
        .category-products {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-top: 20px;
            justify-items: center;
            width: 100%;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .product-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            width: 100%;
            max-width: 250px;
        }
        
        .product-item:hover {
            transform: translateY(-5px);
        }
        
        .product-item img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        
        .product-item h4 {
            padding: 10px 15px 5px;
            margin: 0;
            font-size: 16px;
        }
        
        .product-item p {
            padding: 0 15px;
            color: #333;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .product-item .button-container {
            display: flex;
            justify-content: space-between;
            margin: 0 15px 15px;
            gap: 10px;
        }
        
        .product-item .register-btn {
            flex: 1;
            text-align: center;
            background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
            color: white;
            padding: 8px 0;
            border-radius: 5px;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .product-item .register-btn:hover {
            opacity: 0.9;
        }
        
        .category-title {
            font-size: 22px;
            font-weight: 600;
            margin: 30px 0 20px;
            color: #111;
            position: relative;
            padding-bottom: 10px;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
        }
        
        .category-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #ff4d4d;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
        }

        /* Styles pour la section avis */
        .reviews-section {
            margin: 40px 0;
            padding: 20px;
            background: linear-gradient(135deg, #f9f9f9, #ffffff);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .reviews-section .subtitle {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #222;
            position: relative;
        }

        .reviews-section .subtitle::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: #ff4d4d;
            margin: 10px auto;
        }

        .review-form-container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .review-form-container h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .review-form select,
        .review-form input,
        .review-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        .review-form select:focus,
        .review-form input:focus,
        .review-form textarea:focus {
            border-color: #ff4d4d;
            outline: none;
            box-shadow: 0 0 5px rgba(255, 77, 77, 0.3);
        }

        .review-form textarea {
            height: 100px;
            resize: vertical;
        }

        .star-input {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .star-input span {
            font-size: 28px;
            cursor: pointer;
            color: #ccc;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .star-input span:hover,
        .star-input span.active {
            color: #ffcc00;
            transform: scale(1.1);
        }

        .review-form button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        .review-form button:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .review-form-container {
                padding: 15px;
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <header class="header header-produits" style="background-image: url('images/bd.jpg'); background-size: cover; background-position: center; padding-top: 10px;">
        <nav style="margin-top: -90px;">
            <img src="images/logo.png" class="logo" alt="Logo ClickNGo" style="filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.9));">
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="activite.html">Activités</a></li>
                <li><a href="events.html">Événements</a></li>
                <li><a href="produit.php">Produits</a></li>
                <li><a href="transports.html">Transports</a></li>
                <li><a href="sponsors.html">Sponsors</a></li>
            </ul>
            <a href="#" class="register-btn">Register</a>
        </nav>
        <h1>Découvrez nos produits exclusifs !</h1>
    </header>

    <div class="container">
        <h2 class="subtitle" id="nos-produits">Nos Produits</h2>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Rechercher un produit...">
        </div>

        <!-- Affichage des cartes de catégories -->
        <div class="trending" id="productsContainer">
            <div class="activity-card">
                <img src="images/p1.jpg" alt="Produit 1" onclick="showCategory('Équipements Sportifs')">
                <h4>Équipements Sportifs</h4>
            </div>
            <div class="activity-card">
                <img src="images/p2.jpg" alt="Produit 2" onclick="showCategory('Vêtements et Accessoires')">
                <h4>Vêtements et Accessoires</h4>
            </div>
            <div class="activity-card">
                <img src="images/p3.jpg" alt="Produit 3" onclick="showCategory('Gadgets & Technologies')">
                <h4>Gadgets & Technologies</h4>
            </div>
            <div class="activity-card">
                <img src="images/p4.jpg" alt="Produit 4" onclick="showCategory('Articles de Bien-être & Récupération')">
                <h4>Articles de Bien-être & Récupération</h4>
            </div>
            <div class="activity-card">
                <img src="images/p5.jpg" alt="Produit 5" onclick="showCategory('Nutrition & Hydratation')">
                <h4>Nutrition & Hydratation</h4>
            </div>
            <div class="activity-card">
                <img src="images/p6.jpg" alt="Produit 6" onclick="showCategory('Accessoires de Voyage & Mobilité')">
                <h4>Accessoires de Voyage & Mobilité</h4>
            </div>
            <div class="activity-card">
                <img src="images/p7.jpeg" alt="Produit 7" onclick="showCategory('Supports et accessoires d\'atelier')">
                <h4>Supports et accessoires d'atelier</h4>
            </div>
            <div class="activity-card">
                <img src="images/p8.jpeg" alt="Produit 8" onclick="showCategory('Univers du cerveau')">
                <h4>Univers du cerveau</h4>
            </div>
        </div>

        <!-- Section pour afficher les produits par catégorie -->
        <div id="category-display"></div>

        <!-- Section "Produits en promotion" -->
        <div class="promo-section">
            <h2 class="subtitle">Produits en promotion</h2>
            <button id="showPromoBtn" class="show-promo-btn">Afficher les promotions</button>
            <div class="promo-container hidden" id="promoContainer">
                <!-- Première ligne : 3 produits -->
                <div class="promo-row">
                    <div class="promo-card">
                        <img src="images/sportt1.png" alt="Pack récupération">
                        <div class="promo-text">
                            <h5>Pack récupération</h5>
                            <p class="promo-label"><strong>Pack récupération</strong></p>
                            <h5 style="font-size: 24px;">Pack récupération</h5>
                            <p class="old-price">83.75 TND</p>
                            <p class="new-price">67 TND</p>
                            <a href="acheter.html?produit=Pack%20récupération&prix=67&image=images/sportt1.png" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                    <div class="promo-card">
                        <img src="images/sportt2.jpg" alt="Pack Sport">
                        <div class="promo-text">
                            <h5>Pack Sport</h5>
                            <p class="promo-label"><strong>Pack Sport</strong></p>
                            <p class="old-price">100.5 TND</p>
                            <p class="new-price">83.75 TND</p>
                            <a href="acheter.html?produit=Pack%20Sport&prix=83.75&image=images/sportt2.jpg" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                    <div class="promo-card">
                        <img src="images/sportt3.jpg" alt="Pack Soleil">
                        <div class="promo-text">
                            <h5>Pack Soleil</h5>
                            <p class="promo-label"><strong>Pack Soleil</strong></p>
                            <p class="old-price">67 TND</p>
                            <p class="new-price">50.25 TND</p>
                            <a href="acheter.html?produit=Pack%20Soleil&prix=50.25&image=images/sportt3.jpg" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                </div>
                <!-- Deuxième ligne : 2 produits -->
                <div class="promo-row">
                    <div class="promo-card">
                        <img src="images/sportt4.jpg" alt="Duo nature">
                        <div class="promo-text">
                            <h5>Duo nature</h5>
                            <p class="promo-label"><strong>Duo nature</strong></p>
                            <p class="old-price">201 TND</p>
                            <p class="new-price">167.5 TND</p>
                            <a href="acheter.html?produit=Duo%20nature&prix=167.5&image=images/sportt4.jpg" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                    <div class="promo-card">
                        <img src="images/sportt5.jpg" alt="Pack Microfibre">
                        <div class="promo-text">
                            <h5>Pack Microfibre</h5>
                            <p class="promo-label"><strong>Pack Microfibre</strong></p>
                            <p class="old-price">16.75 TND</p>
                            <p class="new-price">13.4 TND</p>
                            <a href="acheter.html?produit=Pack%20Microfibre&prix=13.4&image=images/sportt5.jpg" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section "Produits Best Sellers" -->
        <div class="best-sellers-section">
            <h2 class="subtitle"><i class="fas fa-heart"></i> Nos Best Sellers</h2>
            <div class="best-seller-grid">
                <!-- Première ligne -->
                <div class="best-seller-card">
                    <div class="best-seller-image">
                        <img src="images/bienetre1.jpg" alt="Tapis de yoga">
                        <span class="best-seller-label">Best Seller</span>
                    </div>
                    <div class="best-seller-info">
                        <h4>Tapis de yoga</h4>
                        <p>67 TND</p>
                        <a href="acheter.html?produit=Tapis%20de%20yoga&prix=67&image=images/bienetre1.jpg" class="best-seller-btn">Acheter</a>
                    </div>
                </div>
                <div class="best-seller-card">
                    <div class="best-seller-image">
                        <img src="images/voyage3.jpg" alt="Coussin voyage">
                        <span class="best-seller-label">Best Seller</span>
                    </div>
                    <div class="best-seller-info">
                        <h4>Coussin voyage</h4>
                        <p>50.25 TND</p>
                        <a href="acheter.html?produit=Coussin%20voyage&prix=50.25&image=images/voyage3.jpg" class="best-seller-btn">Acheter</a>
                    </div>
                </div>
                <div class="best-seller-card">
                    <div class="best-seller-image">
                        <img src="images/uni3.jpeg" alt="Jeux de table">
                        <span class="best-seller-label">Best Seller</span>
                    </div>
                    <div class="best-seller-info">
                        <h4>Jeux de table</h4>
                        <p>67 TND</p>
                        <a href="acheter.html?produit=Jeux%20de%20table&prix=67&image=images/uni3.jpeg" class="best-seller-btn">Acheter</a>
                    </div>
                </div>
                <!-- Deuxième ligne -->
                <div class="best-seller-card">
                    <div class="best-seller-image">
                        <img src="images/supp4.jpeg" alt="Serviette">
                        <span class="best-seller-label">Best Seller</span>
                    </div>
                    <div class="best-seller-info">
                        <h4>Serviette</h4>
                        <p>50.25 TND</p>
                        <a href="acheter.html?produit=Serviette&prix=50.25&image=images/supp4.jpeg" class="best-seller-btn">Acheter</a>
                    </div>
                </div>
                <div class="best-seller-card">
                    <div class="best-seller-image">
                        <img src="images/bienetre5.jpg" alt="Crème post-effort">
                        <span class="best-seller-label">Best Seller</span>
                    </div>
                    <div class="best-seller-info">
                        <h4>Crème post-effort</h4>
                        <p>40.2 TND</p>
                        <a href="acheter.html?produit=Crème%20post-effort&prix=40.2&image=images/bienetre5.jpg" class="best-seller-btn">Acheter</a>
                    </div>
                </div>
                <div class="best-seller-card">
                    <div class="best-seller-image">
                        <img src="images/uni2.jpeg" alt="Jeu de mémoire visuelle">
                        <span class="best-seller-label">Best Seller</span>
                    </div>
                    <div class="best-seller-info">
                        <h4>Jeu de mémoire visuelle</h4>
                        <p>33.5 TND</p>
                        <a href="acheter.html?produit=Jeu%20de%20mémoire%20visuelle&prix=33.5&image=images/uni2.jpeg" class="best-seller-btn">Acheter</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Avis Clients -->
        <div class="reviews-section">
            <h2 class="subtitle">Avis de nos clients</h2>
            <div class="review-form-container">
                <h3>Laissez votre avis</h3>
                <form id="reviewForm" class="review-form">
                    <select id="productSelect">
                        <option value="" disabled selected>Choisissez un produit</option>
                        <option value="Raquette et Balle de Tennis">Raquette et Balle de Tennis</option>
                        <option value="Ballons">Ballons</option>
                        <option value="Corde à sauter">Corde à sauter</option>
                        <option value="Haltères réglables">Haltères réglables</option>
                        <option value="Tapis de yoga">Tapis de yoga</option>
                        <option value="Coussin voyage">Coussin voyage</option>
                        <option value="Jeux de table">Jeux de table</option>
                        <option value="Serviette">Serviette</option>
                        <option value="Crème post-effort">Crème post-effort</option>
                        <option value="Jeu de mémoire visuelle">Jeu de mémoire visuelle</option>
                        <option value="Pack récupération">Pack récupération</option>
                        <option value="Pack Sport">Pack Sport</option>
                        <option value="Pack Soleil">Pack Soleil</option>
                        <option value="Duo nature">Duo nature</option>
                        <option value="Pack Microfibre">Pack Microfibre</option>
                    </select>
                    <div class="star-input" id="starInput">
                        <span data-value="1">★</span>
                        <span data-value="2">★</span>
                        <span data-value="3">★</span>
                        <span data-value="4">★</span>
                        <span data-value="5">★</span>
                    </div>
                    <input type="text" id="reviewerName" placeholder="Votre nom">
                    <textarea id="reviewText" placeholder="Votre commentaire"></textarea>
                    <button type="submit">Envoyer l'avis</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variable pour suivre la catégorie actuellement affichée
        let currentCategory = null;

        // Fonction pour afficher ou masquer les produits par catégorie
        function showCategory(categoryName) {
            const categoryDisplay = document.getElementById('category-display');

            if (currentCategory === categoryName) {
                categoryDisplay.innerHTML = '';
                currentCategory = null;
                return;
            }

            currentCategory = categoryName;

            fetch('../../Controller/produitcontroller.php?category=' + encodeURIComponent(categoryName))
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        categoryDisplay.innerHTML = `
                            <h2 class="category-title">${categoryName}</h2>
                            <p class="error">${data.error}</p>
                        `;
                        return;
                    }
                    
                    if (!Array.isArray(data)) {
                        console.error('Données invalides reçues:', data);
                        throw new Error('Format de données inattendu');
                    }
                    
                    if (data.length === 0) {
                        categoryDisplay.innerHTML = `
                            <h2 class="category-title">${categoryName}</h2>
                            <p>Aucun produit trouvé dans cette catégorie.</p>
                        `;
                        return;
                    }
                    
                    let html = `<h2 class="category-title">${categoryName}</h2><div class="category-products">`;
                    
                    data.forEach(product => {
                        const imagePath = product.image || 'images/products/logo.png';
                        html += `
                            <div class="product-item">
                                <img src="../../${imagePath}" alt="${product.name}">
                                <h4>${product.name}</h4>
                                <p>${product.price} TND</p>
                                <div class="button-container">
                                    <a href="acheter.html?id=${product.id}&produit=${encodeURIComponent(product.name)}&prix=${product.price.split(' ')[0]}&image=../../${imagePath}" class="register-btn">Acheter</a>
                                    <a href="louer.html?id=${product.id}&produit=${encodeURIComponent(product.name)}&prix=${product.price.split(' ')[0]}&image=../../${imagePath}" class="register-btn">Louer</a>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    categoryDisplay.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    categoryDisplay.innerHTML = `
                        <h2 class="category-title">${categoryName}</h2>
                        <p class="error">Erreur lors du chargement: ${error.message}</p>
                    `;
                });
        }

        // Gestion du formulaire d'avis
        const starInput = document.getElementById('starInput');
        let selectedRating = 0;

        starInput.querySelectorAll('span').forEach(star => {
            star.addEventListener('click', () => {
                selectedRating = parseInt(star.getAttribute('data-value'));
                starInput.querySelectorAll('span').forEach(s => {
                    s.classList.toggle('active', parseInt(s.getAttribute('data-value')) <= selectedRating);
                });
            });
        });

        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const product = document.getElementById('productSelect').value;
            const reviewerName = document.getElementById('reviewerName').value.trim();
            const reviewText = document.getElementById('reviewText').value.trim();

            if (!product || !reviewerName || !reviewText || selectedRating === 0) {
                alert("Veuillez remplir tous les champs et sélectionner une note !");
                return;
            }

            // Simuler l'envoi sans stocker ni afficher
            alert("Avis envoyé ! Merci pour votre retour.");
            this.reset();
            starInput.querySelectorAll('span').forEach(s => s.classList.remove('active'));
            selectedRating = 0;
        });

        window.onload = function() {
            const target = document.getElementById('nos-produits');
            target.scrollIntoView({ behavior: 'smooth' });
        };

        document.getElementById('searchInput').addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            const cards = document.querySelectorAll('.activity-card');
            cards.forEach(card => {
                const category = card.querySelector('h4').textContent.toLowerCase();
                card.style.display = category.includes(filter) ? 'block' : 'none';
            });

            const productItems = document.querySelectorAll('.product-item');
            productItems.forEach(item => {
                const productName = item.querySelector('h4').textContent.toLowerCase();
                item.style.display = productName.includes(filter) ? 'block' : 'none';
            });
        });

        document.getElementById('showPromoBtn').addEventListener('click', function() {
            const promoContainer = document.getElementById('promoContainer');
            if (promoContainer.classList.contains('hidden')) {
                promoContainer.classList.remove('hidden');
                setTimeout(() => {
                    promoContainer.classList.add('visible');
                }, 10);
                this.textContent = 'Masquer les promotions';
            } else {
                promoContainer.classList.remove('visible');
                setTimeout(() => {
                    promoContainer.classList.add('hidden');
                }, 500);
                this.textContent = 'Afficher les promotions';
            }
        });
    </script>
</body>
</html>