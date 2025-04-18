<?php
require_once __DIR__ . '/../../model/ActivityModel.php';

// Initialiser le modèle d'activité
$activityModel = new ActivityModel();

// Récupérer toutes les activités
$allActivities = $activityModel->getAllActivities();

// Organiser les activités par catégorie
$categorizedActivities = [];
foreach ($allActivities as $activity) {
    $category = $activity['category'];
    if (!isset($categorizedActivities[$category])) {
        $categorizedActivities[$category] = [];
    }
    $categorizedActivities[$category][] = $activity;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Activités - Click'N'Go</title>
  <link rel="stylesheet" href="style.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Style pour les boutons Réserver dans les catégories */
    .category-content .register-btn {
      font-size: 14px;
      padding: 7px 15px;
      border-radius: 20px;
      display: inline-block;
      margin-top: 8px;
      background-color: #FF385C;
      color: white;
      text-decoration: none;
      transition: background-color 0.3s, transform 0.2s;
      text-align: center;
    }
    
    .category-content .register-btn:hover {
      background-color: #E4002B;
      transform: translateY(-2px);
    }
    
    /* Style pour les cartes d'activité */
    .category-content .exclusives div {
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
    }
    
    .category-content .exclusives div:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }
    
    .category-content .exclusives img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 12px 12px 0 0;
    }
    
    .category-content .exclusives .description-container {
      padding: 12px 15px;
      background: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
    }
    
    .category-content .exclusives p.description {
      margin: 0;
      color: #444;
      font-size: 14px;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      font-weight: 400;
      letter-spacing: 0.2px;
    }
    
    .category-content .exclusives span {
      padding: 12px 15px;
      background: white;
    }
    
    .category-content .exclusives span h3 {
      margin-bottom: 6px;
      color: #333;
      font-size: 16px;
    }
    
    .category-content .exclusives span p.price {
      margin-bottom: 8px;
      color: #FF385C;
      font-weight: bold;
      font-size: 15px;
    }
    
    /* Style pour améliorer l'affichage des sections de catégories */
    .category-content h3 {
      margin-bottom: 25px;
      font-size: 24px;
      color: #333;
      position: relative;
      padding-bottom: 10px;
    }
    
    .category-content h3:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background-color: #FF385C;
    }
  </style>
</head>
<body>

  <!-- Header -->
    <header class="header header-activite" style="background-image: url('images/banner act.jpg'); background-size: cover; background-position: center; padding-top: 10px;">
        <nav style="margin-top: -90px;">
            <img src="images/logo.png" class="logo" alt="Logo ClickNGo" style="filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.9));">
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="activite.php">Activités</a></li>
                <li><a href="events.html">Événements</a></li>
                <li><a href="Produits.html">Produits</a></li>
                <li><a href="transports.html">Transports</a></li>
                <li><a href="sponsors.html">Sponsors</a></li>
            </ul>
            <a href="#" class="register-btn">Register</a>
        </nav>
        <h1>Choisissez votre style d'activité</h1>
    </header>
 

  <!-- Catégories -->
  <div class="container">
    <h2 class="subtitle">Catégories</h2>
    <div class="trending">
      <!-- 13 catégories -->
      <div class="activity-card category-toggle" data-target="#Ateliers"><img src="images/atelier.jpg" alt=""><h3>Ateliers</h3></div>
      <div class="activity-card category-toggle" data-target="#bien-etre"><img src="images/bien.jpg" alt=""><h3>Bien-être</h3></div>
      <div class="activity-card category-toggle" data-target="#Aérien"><img src="images/air.jpg" alt=""><h3>Aérien</h3></div>
      <div class="activity-card category-toggle" data-target="#Aquatique"><img src="images/image-3.png" alt=""><h3>Aquatique</h3></div>
      <div class="activity-card category-toggle" data-target="#Terestre"><img src="images/image-2.png" alt=""><h3>Terrestre</h3></div>
      <div class="activity-card category-toggle" data-target="#Insolite"><img src="images/insolite.jpg" alt=""><h3>Insolite</h3></div>
      <div class="activity-card category-toggle" data-target="#culture"><img src="images/culture.jpg" alt=""><h3>Culture</h3></div>
      <div class="activity-card category-toggle" data-target="#Détente"><img src="images/detente.jpg" alt=""><h3>Détente</h3></div>
      <div class="activity-card category-toggle" data-target="#sport"><img src="images/sport.jpg" alt=""><h3>Sport</h3></div>
      <div class="activity-card category-toggle" data-target="#nature"><img src="images/nature.jpg" alt=""><h3>Nature</h3></div>
      <div class="activity-card category-toggle" data-target="#aventure"><img src="images/aventure.jpg" alt=""><h3>Aventure</h3></div>
      <div class="activity-card category-toggle" data-target="#Famille"><img src="images/1.jpg" alt=""><h3>Famille</h3></div>
      <div class="activity-card category-toggle" data-target="#Extreme"><img src="images/extreme.jpg" alt=""><h3>Extrême</h3></div>
    </div>

    <!-- Sections dynamiques -->
    <?php
    // Liste des catégories et leurs sections
    $categories = [
        'Ateliers' => 'Activités Ateliers',
        'bien-etre' => 'Activités Bien-être',
        'Aérien' => 'Activités Aériennes',
        'Aquatique' => 'Activités Aquatiques',
        'Terestre' => 'Activités Terrestres',
        'Insolite' => 'Activités Insolites',
        'culture' => 'Activités Culturelles',
        'Détente' => 'Activités de Détente',
        'sport' => 'Activités Sportives',
        'nature' => 'Activités Nature',
        'aventure' => 'Activités d\'Aventure',
        'Famille' => 'Activités en Famille',
        'Extreme' => 'Activités Extrêmes'
    ];

    // Générer les sections pour chaque catégorie
    foreach ($categories as $categoryId => $categoryTitle):
    ?>
    <div id="<?php echo $categoryId; ?>" class="category-content" style="display:none;">
        <h3><?php echo $categoryTitle; ?></h3>
        <div class="exclusives">
            <?php
            // Si la catégorie existe dans nos données
            if (isset($categorizedActivities[$categoryId]) && !empty($categorizedActivities[$categoryId])):
                foreach ($categorizedActivities[$categoryId] as $activity):
            ?>
                <div>
                    <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
                    <div class="description-container">
                        <p class="description"><?php echo substr(htmlspecialchars($activity['description']), 0, 100); ?>...</p>
                    </div>
                    <span>
                        <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                        <p class="price"><?php echo htmlspecialchars($activity['price']); ?> DT</p>
                        <a href="reservation.html?nom=<?php echo urlencode($activity['name']); ?>&prix=<?php echo urlencode($activity['price']); ?>&duree=1h" class="register-btn">Réserver</a>
                    </span>
                </div>
            <?php
                endforeach;
            else:
                // Afficher des activités statiques par défaut si aucune activité n'est trouvée dans la base de données
            ?>
                <div>
                    <img src="images/default-activity.jpg" alt="Aucune activité disponible">
                    <div class="description-container">
                        <p class="description">Revenez plus tard pour voir nos nouvelles activités! Nous ajoutons régulièrement de nouvelles expériences passionnantes.</p>
                    </div>
                    <span>
                        <h3>Aucune activité disponible</h3>
                        <p class="price">- DT</p>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Section Nos activités près de vous -->
    <section class="activites-container">
        <div class="section-header">
          <h2>Nos activités près de vous</h2>
          <a href="#" class="voir-tout">Tout afficher &gt;</a>
        </div>
      
        <div class="activites-grid">
          <!-- Activité 1 -->
          <div class="activite-card">
            <img src="images/bougie.jpg" alt="Atelier Bougie">
            <div class="activite-content">
              <p class="categorie">ATELIER BOUGIE</p>
              <h3>Atelier bougies parfumées à El Medina</h3>
              <span class="tag">Top vente</span>
              <p class="prix">45DT <span>/ personne</span></p>
              <p class="note"><span class="etoile">★</span> 4,9 (42)</p>
            </div>
          </div>
      
          <!-- Activité 2 -->
          <div class="activite-card">
            <img src="images/savon.jpg" alt="Atelier Savon">
            <div class="activite-content">
              <p class="categorie">ATELIER SAVON</p>
              <h3>Atelier création de savons à Centre ville</h3>
              <p class="prix">50DT<span>/ personne</span></p>
              <p class="note"><span class="etoile">★</span> 4,7 (17)</p>
            </div>
          </div>
      
          <!-- Activité 3 -->
          <div class="activite-card">
            <img src="images/vide poche.jpg" alt="Vide-poche">
            <div class="activite-content">
              <p class="categorie">ATELIER MAROQUINERIE</p>
              <h3>Atelier maroquinerie "Vide-poche" à Gabes</h3>
              <p class="prix">55DT<span>/ personne</span></p>
              <p class="note"><span class="etoile">★</span> 5,0 (1)</p>
            </div>
          </div>
          <!-- Activité 5 -->
          <div class="activite-card">
            <img src="images/maro.jpg" alt="Ceinture">
            <div class="activite-content">
              <p class="categorie">ATELIER MAROQUINERIE</p>
              <h3>Atelier de maroquinerie d'une ceinture à Nabeul</h3>
              <p class="prix">95DT <span>/ personne</span></p>
              <p class="note"><span class="etoile">★</span> 5,0 (7)</p>
            </div>
          </div>
        </div>
    </section>

    <!-- Section atouts -->
    <div class="see-all-events">
        <section class="atouts">
            <div class="atout">
              <img src="images/l1.webp" alt="Activités" />
              <div>
                <h3>Des offres adaptées à votre événement</h3>
              </div>
            </div>
            <div class="atout">
              <img src="images/l2.webp" alt="Prix" />
              <div>
                <h3>Même prix qu'en direct</h3>
              </div>
            </div>
            <div class="atout">
              <img src="images/l3.webp" alt="Contact" />
              <div>
                <h3>Des devis faciles à comparer</h3>
              </div>
            </div>
            <div class="atout">
                <img src="images/l4.webp" alt="Contact" />
                <div>
                  <h3>Un contact dédié à votre projet</h3>
                </div>
            </div>
        </section>
    </div>

    <!-- Section Découvrez nos catégories d'entreprises -->
    <section class="categories-section">
        <h2 class="section-title">Découvrez nos catégories d'entreprises</h2>
        <div class="categories-buttons">
          <a href="categorie.html" class="cat-btn">Team building</a>
          <a href="categorie.html" class="cat-btn">Animation</a>
          <a href="categorie.html" class="cat-btn">Séminaire</a>
          <a href="categorie.html" class="cat-btn">Réunions</a>
          <a href="categorie.html" class="cat-btn">Soirée</a>
          <a href="categorie.html" class="cat-btn">Repas</a>
          <a href="categorie.html" class="cat-btn">Fundays</a>
        </div>
    </section>

    <!-- Section Avis clients -->
    <section class="avis-section">
        <div class="avis-header">
            <h2><span class="ecriture-plume">Nos clients adorent !</span> <span class="etoile">★</span> 4,7/5</h2>
            <p class="ecriture-plume">64 234 avis pour vous aider à choisir</p>
        </div>
        
        <div class="avis-nav">
          <button class="btn-nav" onclick="changeAvis(-1)">←</button>
          <button class="btn-nav" onclick="changeAvis(1)">→</button>
        </div>
      
        <!-- WRAPPER pour scroll horizontal -->
        <div class="avis-slider-wrapper">
          <div class="avis-slider" id="avis-slider">
            <!-- Slides ici -->
            <div class="avis-slide">
              <div class="avis-content">
                <div class="avis-image">
                  <img src="images/para2.jpg" alt="Saut en parachute">
                </div>
                <div class="avis-card">
                  <h3>Saut en parachute</h3>
                  <div class="avis-rating"><span class="etoile">★</span> 5/5</div>
                  <p>Équipe hyper sympa: au top! Sensations garanties, mais on se sent en toute confiance ! Moment inoubliable .. je n'ai qu'une envie: Recommencer !! Le kiff total</p>
                  <p class="avis-auteur">Pauline</p>
                </div>
              </div>
            </div>
      
            <div class="avis-slide">
              <div class="avis-content">
                <div class="avis-image">
                  <img src="images/mong.jpg" alt="Montgolfière">
                </div>
                <div class="avis-card">
                  <h3>Balade en montgolfière</h3>
                  <div class="avis-rating"><span class="etoile">★</span> 4.8/5</div>
                  <p>Une expérience magique au lever du soleil. Très bien organisée, je recommande pour un moment hors du temps !</p>
                  <p class="avis-auteur">Marc</p>
                </div>
              </div>
            </div>
      
            <div class="avis-slide">
              <div class="avis-content">
                <div class="avis-image">
                  <img src="images/pilote.jpg" alt="Stage de pilotage">
                </div>
                <div class="avis-card">
                  <h3>Stage de pilotage</h3>
                  <div class="avis-rating"><span class="etoile">★</span> 5/5</div>
                  <p>Génial ! L'équipe était passionnée, et les sensations sont incroyables. À refaire sans hésiter !</p>
                  <p class="avis-auteur">Chloé</p>
                </div>
              </div>
            </div>
      
            <div class="avis-slide">
              <div class="avis-content">
                <div class="avis-image">
                  <img src="images/plo.webp" alt="Plongée sous-marine">
                </div>
                <div class="avis-card">
                  <h3>Plongée sous-marine</h3>
                  <div class="avis-rating"><span class="etoile">★</span> 4.9/5</div>
                  <p>Un moment magique au cœur des fonds marins. L'équipe a su me rassurer dès le début. Inoubliable !</p>
                  <p class="avis-auteur">Yassine</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <section class="avis-form-section">
            <h3>Laissez votre avis</h3>
            <form id="avis-form">
              <div class="avis-stars" id="avis-stars">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
              </div>
        
              <input type="text" id="avis-nom" placeholder="Votre prénom" required>
              <input type="text" id="avis-activite" placeholder="Nom de l'activité" required>
              <textarea id="avis-message" placeholder="Votre avis..." required></textarea>
              <button type="submit">Envoyer</button>
            </form>
        </section>
    </section>
  </div>
  
  <!-- Script -->
  <script>
    $(document).ready(function () {
      $(".category-toggle").click(function () {
        const target = $(this).data("target");
        $(".category-content").not(target).slideUp();
        $(target).slideToggle();
      });
    });
  </script>
  
  <script>
    // Script pour les étoiles de notation
    let selectedNote = 0;
    const stars = document.querySelectorAll('.star');
    
    function updateStars(note) {
      stars.forEach((star, index) => {
        if (index < note) {
          star.classList.add('selected');
        } else {
          star.classList.remove('selected');
        }
      });
    }
    
    stars.forEach(star => {
      star.addEventListener('click', function() {
        selectedNote = parseInt(this.getAttribute('data-value'));
        updateStars(selectedNote);
      });
      
      star.addEventListener('mouseover', function() {
        const note = parseInt(this.getAttribute('data-value'));
        updateStars(note);
      });
      
      star.addEventListener('mouseout', function() {
        updateStars(selectedNote);
      });
    });
    
    document.getElementById('avis-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      alert("Merci pour votre avis !");
      this.reset();
      updateStars(0);
      selectedNote = 0;
    });
  </script>
  
  <script>
    let currentSlide = 0;
    const slider = document.getElementById("avis-slider");
    const slides = document.querySelectorAll(".avis-slide");
  
    function changeAvis(direction) {
      currentSlide = (currentSlide + direction + slides.length) % slides.length;
      const offset = -currentSlide * 100;
      slider.style.transform = `translateX(${offset}%)`;
    }
  </script>
  
  <div class="footer-wrapper">
    <div class="newsletter">
        <div class="newsletter-left">
            <h2>Abonnez-vous à notre</h2>
            <h1>Click'N'Go</h1>
        </div>
        <div class="newsletter-right">
            <div class="newsletter-input">
                <input type="text" placeholder="Entrez votre adresse e-mail" />
                <button>Submit</button>
            </div>
        </div>
    </div>

    <div class="footer-content">
        <div class="footer-main">
            <div class="footer-brand">
                <img src="images/logo.png" alt="click'N'go Logo" class="footer-logo">
            </div>
            <p>Rejoignez nous aussi sur :</p>
            <div class="social-icons">
                <a href="#" style="--color: #0072b1" class="icon"><i class="fa-brands fa-linkedin"></i></a>
                <a href="#" style="--color: #E1306C" class="icon"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" style="--color: #FF0050" class="icon"><i class="fa-brands fa-tiktok"></i></a>
                <a href="#" style="--color: #4267B2" class="icon"><i class="fa-brands fa-facebook"></i></a>
            </div>
        </div>

        <div class="links">
            <p>Moyens de paiement</p>
            <div class="payment-methods">
                <img src="images/visa.webp" alt="Visa">
                <img src="images/mastercard-v2.webp" alt="mastercard">
                <img src="images/logo-cb.webp" alt="cb" class="cb-logo">
                <img src="images/paypal.webp" alt="paypal" class="paypal">
            </div>
        </div>

        <div class="links">
            <p>À propos</p>
            <a href="#">À propos de click'N'go</a>
            <a href="#">Presse</a>
            <a href="#">Nous rejoindre</a>
        </div>

        <div class="links">
            <p>Liens utiles</p>
            <a href="#">Devenir partenaire</a>
            <a href="#">FAQ - Besoin d'aide ?</a>
            <a href="#">Tous les avis click'N'go</a>
        </div>
    </div>

    <div class="footer-section">
        <hr>
        <div class="footer-separator"></div>
        <div class="footer-bottom">
            <p>© click'N'go 2025 - tous droits réservés</p>
            <div class="footer-links-bottom">
                <a href="#">Conditions générales</a>
                <a href="#">Mentions légales</a>
            </div>
        </div>
    </div>
</div>
</body>
</html> 