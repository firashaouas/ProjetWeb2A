<?php
session_start(); // Démarre la session pour vérifier l'état de connexion

// Fonction pour générer une couleur basée sur le nom de l'utilisateur
function stringToColor($str) {
    // Liste de couleurs inspirées du thème Funbooker (rose, violet, orange, etc.)
    $Colors = [
        '#FF6B6B', // Rose vif
        '#FF8E53', // Orange clair
        '#6B5B95', // Violet moyen
        '#88B04B', // Vert doux
        '#F7CAC9', // Rose pâle
        '#92A8D1', // Bleu pastel
        '#955251', // Rouge bordeaux
        '#B565A7', // Violet rose
        '#DD4124', // Rouge-orange vif
        '#D65076', // Rose foncé
    ];
    
    // Générer un index déterministe basé sur la chaîne
    $hash = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = ord($str[$i]) + (($hash << 5) - $hash);
    }
    
    // Sélectionner une couleur du tableau
    $index = abs($hash) % count($Colors);
    return $Colors[$index];
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script type="module" crossorigin src="https://panorama-slider.uiinitiative.com/assets/index.d2ce9dca.js"></script>
    <link rel="modulepreload" href="https://panorama-slider.uiinitiative.com/assets/vendor.dba6b2d2.js">
    <link rel="stylesheet" href="https://panorama-slider.uiinitiative.com/assets/index.c1d53924.css">


    <script src="../FrontOffice/main.js"></script>
       
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
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
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
        box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
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

    <div class="header">
        <nav>
            <img src="images/logo.png" class="logo">
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="activite.html">Activités</a></li>
                <li><a href="events.html">Événements</a></li>
                <li><a href="Produits.html">Produits</a></li>
                <li><a href="transports.html">Transports</a></li>
                <li><a href="sponsors.html">Sponsors</a></li>
            </ul>
        <!-- Vérification de l'état de connexion -->
            <?php if (!isset($_SESSION['user'])): ?>
                <!-- Si non connecté : icône utilisateur vers login/signup -->
                <a href="../BackOffice/login/login.php" class="register-btn" title="Connexion/Inscription">
                    <i class="fas fa-user"></i>
                </a>
                <?php else: ?>
    <!-- Si connecté -->
    <div class="user-profile">
        <?php if (!empty($_SESSION['user']['profile_picture']) && file_exists($_SESSION['user']['profile_picture'])): ?>
            <!-- Afficher la photo de profil -->
            <img src="<?= $_SESSION['user']['profile_picture'] ?>" alt="fake" class="profile-photo">
        <?php else: ?>
            <!-- Sinon : cercle coloré avec initiale -->
            <div class="profile-circle" style="background-color: <?= stringToColor($_SESSION['user']['full_name']) ?>;" onclick="toggleDropdown()">
                <?= strtoupper(substr($_SESSION['user']['full_name'], 0, 1)) ?>
            </div>
        <?php endif; ?>

        <!-- Menu déroulant -->
        <div class="dropdown-menu" id="dropdownMenu">
        <a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
        <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
    </div>
    </div>
<?php endif; ?>

        </nav>
       

        <div class="search-bar">
            <div class="search-inputs">
                <div class="search-location">
                    <span class="icon-bar">📍</span>
                    <span class="static-text">N'importe où</span>
                </div>
                <div class="search-keywords">
                    <span class="icon-bar">🔍</span>
                    <input type="text" placeholder="Rechercher par mots-clés" />
                </div>
            </div>
            <button class="search-btn">🔍</button>
        </div>

    </div>
    

    <div class="container">
        <h2 class="subtitle">ville</h2>
        <div class="exclusives">
            <!-- Reprise de 10 éléments identiques (Image 1) -->
            <div><img src="images/beja.jpg" alt="Image 1"><span><h3>Beja</h3><p>200DT </p></span></div>
            <div><img src="images/korbous.webp" alt="Image 1"><span><h3>Korbous</h3><p>180DT</p></span></div>
            <div><img src="images/sousse.jpg" alt="Image 1"><span><h3>Sousse</h3><p>210DT </p></span></div>
            <div><img src="images/tozeur.jpg" alt="Image 1"><span><h3>Tozeur</h3><p>400DT</p></span></div>
            <div><img src="images/kef.jpg" alt="Image 1"><span><h3>Kef</h3><p>170DT </p></span></div>
            <div><img src="images/zaghouen.jpg" alt="Image 1"><span><h3>Zaghouen</h3><p>165DT </p></span></div>
            <div><img src="images/hamamet.jpg" alt="Image 1"><span><h3>Hammamet</h3><p>250DT </p></span></div>
            <div><img src="images/bizerte.jpg" alt="Image 1"><span><h3>Bizerte</h3><p>140DT</p></span></div>
            </div>

        <h2 class="subtitle">Catégories des activités</h2>
        <div class="trending">
            <div class="activity-card"><img src="images/atelier.jpg" alt="Image 1"><h3>Ateliers</h3></div>
            <div class="activity-card"><img src="images/bien.jpg" alt="Image 2"><h3>Bien-être</h3></div>
            <div class="activity-card"><img src="images/air.jpg" alt="Image 3"><h3>Aérien</h3></div>
            <div class="activity-card"><img src="images/cro.jpg" alt="Image 4"><h3>Croisières</h3></div>
            <div class="activity-card"><img src="images/esq.jpg" alt="Image 5"><h3>Jeux & énigmes</h3></div>
            <div class="activity-card"><img src="images/pilotage.jpg" alt="Image 5"><h3>Pilotage</h3></div>
            <div class="activity-card"><img src="images/visit.jpg" alt="Image 5"><h3>Visites</h3></div>
            <div class="activity-card"><img src="images/zone.jpg" alt="Image 5"><h3>Parcs de loisirs</h3></div>
            <div class="activity-card"><img src="images/nature.jpg" alt="Image 5"><h3>Nature</h3></div>
            <div class="activity-card"><img src="images/aqu.jpg" alt="Image 5"><h3>Aquatique</h3></div>
            <div class="activity-card"><img src="images/sim.jpg" alt="Image 5"><h3>Simulateurs</h3></div>
        </div>
        <div class="see-all-events">
            <a href="activite.html" class="see-all-link">Voir Toutes nos activités &gt;</a>
            <section class="atouts">
                <div class="atout">
                  <img src="images/atout1.webp" alt="Activités" />
                  <div>
                    <h3>Des offres adaptées à votre événement</h3>
                    <p>15 000 activités</p>
                  </div>
                </div>
                <div class="atout">
                  <img src="images/atout2.webp" alt="Prix" />
                  <div>
                    <h3>Même prix qu'en direct</h3>
                    <p>Annulation gratuite</p>
                  </div>
                </div>
                <div class="atout">
                  <img src="images/atout3.webp" alt="Contact" />
                  <div>
                    <h3>Un contact dédié à votre projet</h3>
                    <p>Joignable du lundi au vendredi</p>
                  </div>
                </div>
              </section>
        </div>
        <section class="coups-de-coeur">
            <h2>Nos Coups de Cœur ❤️</h2>
            <div class="activites">
                <div class="activite">
                    <img src="images/cro.jpg" alt="Activité 1">
                    <h3>Croisière à l'Haouaria</h3>
                    
                    <a href="#">En savoir plus</a>
                </div>
                <div class="activite">
                    <img src="images/vr.jpg" alt="Activité 2">
                    <h3>Virtual Room</h3>
                    
                    <a href="#">En savoir plus</a>
                </div>
                <div class="activite">
                    <img src="images/vol.jpg" alt="Activité 3">
                    <h3>Vol à l'ULM</h3>
                    
                    <a href="#">En savoir plus</a>
                </div>
                <div class="activite">
                    <img src="images/karting.jpg" alt="Activité 3">
                    <h3>Karting à Monastir</h3>
                    <a href="#">En savoir plus</a>
                </div>
                <div class="activite">
                    <img src="images/mong.jpg" alt="Activité 3">
                    <h3>Mongolfiere à Tozeur</h3>
                    <a href="#">En savoir plus</a>
                </div>
                <div class="activite">
                    <img src="images/noir.jpg" alt="Activité 3">
                    <h3>Le diner à l'aveugle</h3>
                    <a href="#">En savoir plus</a>
                </div>
                <div class="activite">
                    <img src="images/story-2.png" alt="Activité 3">
                    <h3>Escalade à Oued Zitoun </h3>
                    <a href="#">En savoir plus</a>
                </div>
            </div>
            <script>function scrollActivities(direction) {
                const container = document.querySelector('.activites');
                const scrollAmount = 220; // Le nombre de pixels à défiler à chaque fois
            
                if (direction === 'next') {
                    container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                } else if (direction === 'prev') {
                    container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                }
            }
            </script>
        </section>
        

        <h2 class="subtitle">Nos Événements</h2>
        <div class="events">
            <div class="event-card"><img src="images/fes.jpg" alt="Événement 1"><h3>Festivals & Culture</h3></div>
            <div class="event-card"><img src="images/mus.jpg" alt="Événement 2"><h3>Concerts & Musique</h3></div>
            <div class="event-card"><img src="images/fam.jpg" alt="Événement 3"><h3>Famille & Enfants (Kids Friendly)</h3></div>
            <div class="event-card"><img src="images/groupe.jpg" alt="Événement 4"><h3>Récompenses ou Challenges</h3></div>
            <div class="event-card"><img src="images/hallo.jpg" alt="Événement 4"><h3>Saisonniers ou Thématiques</h3></div>
            <div class="event-card"><img src="images/fete.jpg" alt="Événement 4"><h3>Privés / Fêtes</h3></div>
        </div>

        <div class="see-all-events">
            <a href="/evenements" class="see-all-link">Voir tous les événements &gt;</a>
        </div>

        <h2 class="subtitle">Nos Produits</h2>
        <div class="panorama-slider">
            <div class="swiper">
            <div class="swiper-wrapper">
            <div class="swiper-slide">
            <img class="slide-image" src="images/p1.jpg" alt="">
            
            </div>
            <div class="swiper-slide">
            <img class="slide-image" src="images/p2.jpg" alt="">
            </div>
            <div class="swiper-slide">
            <img class="slide-image" src="images/p3.jpg" alt="">
            </div>
            <div class="swiper-slide">
            <img class="slide-image" src="images/p4.jpg" alt="">
            </div>
            <div class="swiper-slide">
            <img class="slide-image" src="images/p5.jpg" alt="">
            </div>
            <div class="swiper-slide">
            <img class="slide-image" src="images/p6.jpg" alt="">
            </div>
            <div class="swiper-slide">
            <img class="slide-image" src="images/p7.jpeg" alt="">
            </div>
            <div class="swiper-slide">
            <img class="slide-image" src="images/p8.jpeg" alt="">
            </div>
            </div>
            <div class="swiper-pagination"></div>
            </div>
            </div>

        <div class="see-all-events">
            <a href="/evenements" class="see-all-link">Voir tous les produits &gt;</a>
        </div>

        <!-- COVOITURAGE SECTION START -->
        <section class="carpooling-home">
            <div class="carpooling-container">
                <div class="carpooling-content">
                    <h2>Covoiturez en groupe pour vos loisirs préférés</h2> 
                    <ul class="carpooling-features">
                        <li class="feature-item">
                            <img src="images/reser.png" alt="Check" class="feature-icon">
                            Réservation simple et rapide en ligne
                        </li>
                        <li class="feature-item">
                            <img src="images/tra.png" alt="Check" class="feature-icon">
                            Flexibilité des horaires et des trajets
                        </li>
                        <li class="feature-item">
                            <img src="images/eco.png" alt="Check" class="feature-icon">
                            Économique et respectueux de l'environnement
                        </li>
                    </ul>
                    <button class="carpooling-btn">Covoiturer</button>
                </div>
                <div class="carpooling-image">
                    <img src="images/cou.jpg" alt="Service de Covoiturage">
                </div>
            </div>
        </section>


        <!-- COVOITURAGE SECTION END -->*
        <section id="activites-tunisie" class="bg-gradient-to-b from-white to-[#f3f4f6] py-12 px-6 text-gray-800">
            <div class="max-w-5xl mx-auto text-center">
              <h2 style="font-size: 2.5rem; font-weight: bold; color: #a604ab; margin-bottom: 1.5rem;">
                Les meilleures activités en Tunisie pour s’amuser à fond 
              </h2>
              <p class="text-lg mb-8">
                Vous cherchez une idée sortie ? Envie de passer un bon moment en famille, entre amis ou entre collègues ? En Tunisie, vous avez l’embarras du choix !
              </p>
          
              <div class="grid md:grid-cols-2 gap-8 text-left">
                <div>
                  <h3 class="text-xl font-semibold" style="color: #FFA500; margin-bottom: 0.5rem;">🎂 Pour chaque occasion</h3>
                  <ul class="list-disc list-inside space-y-1">
                    <li><strong>Anniversaires enfants</strong> : chasse au trésor, aire de jeux, escape game, anniversaires à thème.</li>
                    <li><strong>Cadeaux</strong> : balade à cheval, karting, ateliers créatifs ou sensations fortes.</li>
                    <li><strong>Team building</strong> : kayak, pique-nique, pétanque, journée détente ou sportive.</li>
                    <li><strong>EVG / EVJF</strong> : tir à l’arc, chasse aux trésors, fitness, randonnée, ULM...</li>
                  </ul>
                </div>
          
                <div>
                  <h3 class="text-xl font-semibold" style="color: #FFA500; margin-bottom: 0.5rem;">🎯 Activités par thème</h3>
                  <ul class="list-disc list-inside space-y-1">
                    <li><strong>Nature et sensations</strong> : accrobranche, quad, randonnée, rafting, parapente…</li>
                    <li><strong>Urbaines & créatives</strong> : escape game, VR, ateliers cuisine, artisanat, bien-être.</li>
                    <li><strong>Famille & amis</strong> : parcs, zoos, balades en calèche, excursions nature.</li>
                  </ul>
                </div>
              </div>
          
              <p class="mt-8 text-base">
                Où que vous soyez – <strong>Tunis, Sousse, Djerba, Hammamet, Bizerte ou Tozeur</strong> – il y a toujours une activité à tester !
              </p>
          
              <p class="mt-4 font-semibold" style="color: #a604ab;">
                Des expériences fun, sportives ou relaxantes à vivre en solo ou en groupe. Alors, on s’y met ? 🌞
              </p>
            </div>
          </section>
          
          

        <hr class="trait-separateur">

        <section class="recherches-populaires">
          <h3>Les recherches les plus populaires</h3>
          <p class="sous-titre">Recherches associées</p>
          <ul>
            <li><a href="#">Activités à Tunis</a></li>
            <li><a href="#">50 activités géniales à faire à Sousse</a></li>
            <li><a href="#">Activités à Djerba</a></li>
            <li><a href="#">30 activités géniales à faire à Hammamet</a></li>
            <li><a href="#">Activités en pleine nature à Bizerte</a></li>
          </ul>
        </section>
        

        
        <div class="footer-wrapper">
            <div class="newsletter">
                <div class="newsletter-left">
                    <h2>Abonnez-vous à notre</h2>
                    <h1>Click'N'Go</h1>
                </div>
                <div class="newsletter-right">
                    <div class="newsletter-input">
                        <input type="text" placeholder="Entrez votre adresse e-mail" />
                        <button class="fotter-btn">Valider</button>
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