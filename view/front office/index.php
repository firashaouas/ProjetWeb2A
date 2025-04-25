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
        /* Styles pour l'affichage des produits par catégorie */
        .category-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .product-item:hover {
            transform: translateY(-5px);
        }
        
        .product-item img {
            width: 100%;
            height: 180px;
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
        
        .product-item .register-btn {
            display: block;
            margin: 0 15px 15px;
            text-align: center;
            background: #ff4d4d;
            color: white;
            padding: 8px 0;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .product-item .register-btn:hover {
            background: #ff3333;
        }
        
        .category-title {
            font-size: 24px;
            margin: 30px 0 20px;
            color: #333;
            position: relative;
            padding-bottom: 10px;
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
    </style>
</head>
<body>
    <div class="header">
        <nav>
            <img src="images/logo.png" class="logo">
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="activite.html">Activités</a></li>
                <li><a href="events.html">Événements</a></li>
                <li><a href="produit.php">Produits</a></li>
                <li><a href="transports.html">Transports</a></li>
                <li><a href="sponsors.html">Sponsors</a></li>
            </ul>
            <a href="#" class="register-btn">Register</a>
        </nav>
        <h1>Découvrez nos produits exclusifs !</h1>
    </div>

    <div class="container">
        <h2 class="subtitle" id="nos-produits">Nos Produits</h2>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Rechercher un produit...">
        </div>
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
                        <img src="images/sportt1.png" alt="pack récupération">
                        <div class="promo-text">
                            <h5>pack récupération</h5>
                            <p class="promo-label"><strong>pack récupération</strong></p>
                            <h5 style="font-size: 24px;">pack récupération</h5>
                            <p class="old-price">83.75 TND</p>
                            <p class="new-price">67 TND</p>
                            <a href="acheter.html?produit=Raquette+Balle%20de%20Tenis&prix=67" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                    <div class="promo-card">
                        <img src="images/sportt2.jpg" alt="Pack Sport">
                        <div class="promo-text">
                            <h5>Pack Sport</h5>
                            <p class="promo-label"><strong>Pack Sport</strong></p>
                            <p class="old-price">100.5 TND</p>
                            <p class="new-price">83.75 TND</p>
                            <a href="acheter.html?produit=Powerbank%20solaire&prix=83.75" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                    <div class="promo-card">
                        <img src="images/sportt3.jpg" alt="Pack Soleil">
                        <div class="promo-text">
                            <h5>Pack Soleil</h5>
                            <p class="promo-label"><strong>Pack Soleil</strong></p>
                            <p class="old-price">67 TND</p>
                            <p class="new-price">50.25 TND</p>
                            <a href="acheter.html?produit=Tapis%20de%20yoga&prix=50.25" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                </div>
                <!-- Deuxième ligne : 2 produits -->
                <div class="promo-row">
                    <div class="promo-card">
                        <img src="images/sportt4.jpg" alt="duo nature">
                        <div class="promo-text">
                            <h5>duo nature</h5>
                            <p class="promo-label"><strong>duo nature</strong></p>
                            <p class="old-price">201 TND</p>
                            <p class="new-price">167.5 TND</p>
                            <a href="acheter.html?produit=Chaussures%20de%20randonnée&prix=167.5" class="register-btn">Acheter maintenant</a>
                        </div>
                    </div>
                    <div class="promo-card">
                        <img src="images/sportt5.jpg" alt="Pack Microfibre">
                        <div class="promo-text">
                            <h5>Pack Microfibre</h5>
                            <p class="promo-label"><strong>Pack Microfibre</strong></p>
                            <p class="old-price">16.75 TND</p>
                            <p class="new-price">13.4 TND</p>
                            <a href="acheter.html?produit=Barres%20protéinées&prix=13.4" class="register-btn">Acheter maintenant</a>
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
                        <a href="acheter.html?produit=Tapis%20de%20yoga&prix=67" class="best-seller-btn">Acheter</a>
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
                        <a href="acheter.html?produit=Coussin%20voyage&prix=50.25" class="best-seller-btn">Acheter</a>
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
                        <a href="acheter.html?produit=Jeux%20de%20table&prix=67" class="best-seller-btn">Acheter</a>
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
                        <a href="acheter.html?produit=Serviette&prix=50.25" class="best-seller-btn">Acheter</a>
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
                        <a href="acheter.html?produit=Crème%20post-effort&prix=40.2" class="best-seller-btn">Acheter</a>
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
                        <a href="acheter.html?produit=Jeu%20de%20mémoire%20visuelle&prix=33.5" class="best-seller-btn">Acheter</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Avis Clients -->
        <div class="reviews-section">
            <h2 class="subtitle">Avis de nos clients</h2>
            <!-- Formulaire pour laisser un avis -->
            <div class="review-form">
                <h3>Laissez votre avis</h3>
                <form id="reviewForm">
                    <select id="productSelect" required>
                        <option value="" disabled selected>Choisissez un produit</option>
                        <!-- Équipements Sportifs -->
                        <option value="Raquette+Balle de Tenis">Raquette+Balle de Tenis</option>
                        <option value="Ballons">Ballons</option>
                        <option value="Corde à sauter">Corde à sauter</option>
                        <option value="Haltères réglables">Haltères réglables</option>
                        <option value="Planche de surf">Planche de surf</option>
                        <option value="Lunettes de natation">Lunettes de natation</option>
                        <option value="Chaussures de plage">Chaussures de plage</option>
                        <!-- Vêtements et Accessoires -->
                        <option value="Bouteille d'eau de sport en plastique">Bouteille d'eau de sport en plastique</option>
                        <option value="Combinaison de wingsuit">Combinaison de wingsuit</option>
                        <option value="Veste coupe-vent imperméable">Veste coupe-vent imperméable</option>
                        <option value="Leggings confort + brassière">Leggings confort + brassière</option>
                        <option value="Chaussures de randonnée">Chaussures de randonnée</option>
                        <option value="Gant de sport personnalisé">Gant de sport personnalisé</option>
                        <option value="Sacs à dos">Sacs à dos</option>
                        <!-- Gadgets & Technologies -->
                        <option value="Montre connectée">Montre connectée</option>
                        <option value="Casque VR">Casque VR</option>
                        <option value="Caméra instantanée">Caméra instantanée</option>
                        <option value="Powerbank solaire">Powerbank solaire</option>
                        <option value="Tracker de nage">Tracker de nage</option>
                        <option value="Mini Enceinte Étanche">Mini Enceinte Étanche</option>
                        <!-- Bien-être & Récupération -->
                        <option value="Tapis de yoga">Tapis de yoga</option>
                        <option value="Huiles essentielles">Huiles essentielles</option>
                        <option value="Diffuseur portatif">Diffuseur portatif</option>
                        <option value="Oreiller de voyage">Oreiller de voyage</option>
                        <option value="Crème post-effort">Crème post-effort</option>
                        <!-- Nutrition & Hydratation -->
                        <option value="Barres protéinées">Barres protéinées</option>
                        <option value="Smoothie shaker">Smoothie shaker</option>
                        <option value="Boisson énergétique">Boisson énergétique</option>
                        <option value="Fruits secs">Fruits secs</option>
                        <option value="SportFix">SportFix</option>
                        <!-- Voyage & Mobilité -->
                        <option value="Casque">Casque</option>
                        <option value="Sac USB pratique">Sac USB pratique</option>
                        <option value="Coussin voyage">Coussin voyage</option>
                        <option value="Frontale compacte">Frontale compacte</option>
                        <option value="Sac de secours">Sac de secours</option>
                        <option value="Lingette randonnée">Lingette randonnée</option>
                        <option value="Bouchons d'oreilles">Bouchons d'oreilles</option>
                        <option value="Gourde filtrante portable">Gourde filtrante portable</option>
                        <!-- Supports et accessoires d'atelier -->
                        <option value="Tapis de réparation">Tapis de réparation</option>
                        <option value="Organisateur d'outils">Organisateur d'outils</option>
                        <option value="Support vélo">Support vélo</option>
                        <!-- Univers du cerveau -->
                        <option value="BrainBox">BrainBox</option>
                        <option value="Jeu de mémoire visuelle">Jeu de mémoire visuelle</option>
                        <!-- Promotions -->
                        <option value="pack récupération">pack récupération</option>
                        <option value="Pack Sport">Pack Sport</option>
                        <option value="Pack Soleil">Pack Soleil</option>
                        <option value="duo nature">duo nature</option>
                        <option value="Pack Microfibre">Pack Microfibre</option>
                    </select>
                    <div class="star-input" id="starInput">
                        <span data-value="1">🌟</span>
                        <span data-value="2">🌟</span>
                        <span data-value="3">🌟</span>
                        <span data-value="4">🌟</span>
                        <span data-value="5">🌟</span>
                    </div>
                    <input type="text" id="reviewerName" placeholder="Votre nom" required>
                    <textarea id="reviewText" placeholder="Votre commentaire" required></textarea>
                    <button type="submit">Envoyer l'avis</button>
                </form>
            </div>

            <!-- Affichage des notes moyennes -->
            <div class="average-rating" id="averageRating"></div>

            <!-- Liste des avis -->
            <div id="reviewsContainer"></div>
        </div>
    </div>

    <script>
        // Fonction pour afficher les produits par catégorie
        function showCategory(categoryName)
         {
    fetch('../../Controller/produitcontroller.php?category=' + encodeURIComponent(categoryName))
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            const categoryDisplay = document.getElementById('category-display');
            
            // Cas d'erreur
            if (data.error) {
                categoryDisplay.innerHTML = `
                    <h2 class="category-title">${categoryName}</h2>
                    <p class="error">${data.error}</p>
                `;
                return;
            }
            
            // Cas où c'est un tableau (vide ou non)
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
            
            // Construction de l'affichage
            let html = `<h2 class="category-title">${categoryName}</h2><div class="category-products">`;
            data.forEach(product => {
                html += `
<div class="product-item">
    <img src="<?= !empty($product['image']) ? '../../' . $product['image'] : './images/logo.png'?>" style="width: 100%; height: auto;" alt="${product.name}">
    <h4>${product.name}</h4>
    <p>${product.price}</p>
    <a href="acheter.html?id=${product.id}&produit=${encodeURIComponent(product.name)}&prix=${product.price.split(' ')[0]}&image=../../${product.image}" class="register-btn">Acheter</a>
   <a href="louer.php?id=${product.id}&produit=${encodeURIComponent(product.name)}&prix=${product.price.split(' ')[0]}&image=../../${product.image}" class="register-btn">Louer</a>

</div>
`;
});
            
            html += '</div>';
            categoryDisplay.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('category-display').innerHTML = `
                <h2 class="category-title">${categoryName}</h2>
                <p class="error">Erreur lors du chargement: ${error.message}</p>
            `;
        });
}
        
        
 // Fonction pour obtenir le nom de la catégorie à partir de son ID
function getCategoryName(categoryId) {
    const categories = {
        'equipements-sportifs': 'Équipements Sportifs',
        'vetements-accessoires': 'Vêtements et Accessoires',
        'gadgets-technologies': 'Gadgets & Technologies',
        'bien-etre-recuperation': 'Articles de Bien-être & Récupération',
        'nutrition-hydratation': 'Nutrition & Hydratation',
        'voyage-mobilite': 'Accessoires de Voyage & Mobilité',
        'supports-atelier': 'Supports et accessoires d\'atelier',
        'univers-cerveau': 'Univers du cerveau'
    };
    return categoryId; // Retourne directement l'ID (si vous voulez le mapping, utilisez: return categories[categoryId] || categoryId;
}

        // Gestion des favoris et avis (stockage local pour la démo)
        let reviews = JSON.parse(localStorage.getItem('reviews')) || [
            { product: "pack récupération", rating: 4, text: "Excellent pour la récupération après le sport !", author: "Sarah M.", favorite: false, userRating: 0 },
            { product: "Pack Sport", rating: 5, text: "Tout ce dont j'avais besoin pour mes entraînements.", author: "Ahmed B.", favorite: true, userRating: 0 }
        ];

        // Gestion des étoiles dans le formulaire
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

        // Soumission du formulaire d'avis
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const product = document.getElementById('productSelect').value;
            const reviewerName = document.getElementById('reviewerName').value;
            const reviewText = document.getElementById('reviewText').value;

            if (!product || !reviewerName || !reviewText || selectedRating === 0) {
                alert("Veuillez remplir tous les champs et sélectionner une note !");
                return;
            }

            const newReview = {
                product,
                rating: selectedRating,
                text: reviewText,
                author: reviewerName,
                favorite: false,
                userRating: 0
            };

            reviews.push(newReview);
            localStorage.setItem('reviews', JSON.stringify(reviews));
            displayReviews();
            this.reset();
            starInput.querySelectorAll('span').forEach(s => s.classList.remove('active'));
            selectedRating = 0;
        });

        // Afficher les avis et la note moyenne
        function displayReviews() {
            const reviewsContainer = document.getElementById('reviewsContainer');
            reviewsContainer.innerHTML = '';

            // Calculer la note moyenne par produit
            const productRatings = {};
            reviews.forEach(review => {
                if (!productRatings[review.product]) {
                    productRatings[review.product] = { sum: 0, count: 0 };
                }
                productRatings[review.product].sum += review.rating;
                productRatings[review.product].count += 1;
            });

            // Afficher les avis
            reviews.forEach((review, index) => {
                const reviewCard = document.createElement('div');
                reviewCard.className = 'review-card';
                reviewCard.innerHTML = `
                    <h5>${review.product}</h5>
                    <div class="star-rating" data-index="${index}">
                        ${Array(5).fill(0).map((_, i) => `<span data-value="${i + 1}" class="${i < review.userRating ? '' : 'inactive'}">🌟</span>`).join('')}
                        <span>(${review.rating}/5)</span>
                    </div>
                    <p class="review-text">${review.text}</p>
                    <p class="review-author">- ${review.author}</p>
                    <span class="favorite-btn ${review.favorite ? 'active' : ''}" data-index="${index}"><i class="fas fa-heart"></i></span>
                `;
                reviewsContainer.appendChild(reviewCard);
            });

            // Gestion des favoris (cœurs)
            document.querySelectorAll('.favorite-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const index = parseInt(btn.getAttribute('data-index'));
                    reviews[index].favorite = !reviews[index].favorite;
                    localStorage.setItem('reviews', JSON.stringify(reviews));
                    displayReviews();
                });
            });

            // Gestion des étoiles cliquables
            document.querySelectorAll('.star-rating').forEach(rating => {
                const index = parseInt(rating.getAttribute('data-index'));
                rating.querySelectorAll('span').forEach(star => {
                    star.addEventListener('click', () => {
                        const value = parseInt(star.getAttribute('data-value'));
                        reviews[index].userRating = value;
                        localStorage.setItem('reviews', JSON.stringify(reviews));
                        displayReviews();
                    });
                });
            });
        }

        // Initialisation
        displayReviews();

        // Gestion du scroll vers "Nos Produits"
        window.onload = function() {
            const target = document.getElementById('nos-produits');
            target.scrollIntoView({ behavior: 'smooth' });
        };

        // Recherche de produits
        document.getElementById('searchInput').addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            const cards = document.querySelectorAll('.activity-card');
            cards.forEach(card => {
                const category = card.querySelector('h4').textContent.toLowerCase();
                card.style.display = category.includes(filter) ? 'block' : 'none';
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