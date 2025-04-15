<?php
require_once '../../Controller/EventController.php';
$controller = new EventController();

// Définir les catégories fixes avec leurs images
$categories = [
    'sportif' => [
        'title' => 'Événements sportifs',
        'image' => 'images/sportif.jpg'
    ],
    'culturel' => [
        'title' => 'Festivals culturels',
        'image' => 'images/culturel.jpg'
    ],
    'culinaire' => [
        'title' => 'Festivals culinaires',
        'image' => 'images/gastro.jpg'
    ],
    'musique' => [
        'title' => 'Festivals de musique',
        'image' => 'images/festives.jpg'
    ],
    'charite' => [
        'title' => 'Galas de charité',
        'image' => 'images/vip.jpg'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>event</title>
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
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
        <a href="#" class="register-btn">Register</a>
    </nav>
    <h1 class="animated-text">Restez connecté à tous les événements</h1>
</div>
<body>
    <section class="hero">
        <div class="hero-content">
            <h2>Événement du mois : Concert live !</h2>
            <p>Rejoignez-nous le 20 avril 2025 pour une soirée inoubliable.</p>
            <a href="#" class="cta-btn">Réservez maintenant</a>
        </div>
    </section>
    <div class="container">
        <h2 class="subtitle">Pays</h2>
        <div class="exclusives-wrapper">
            <div class="exclusives">
                <!-- Premier groupe d'éléments -->
                <div class="exclusive-item">
                    <img src="images/paris.jpg" alt="Paris">
                    <h3>London</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/munchin.webp" alt="Munich">
                    <h3>Paris</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/london.jpg" alt="London">
                    <h3>New York</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/tokyo.jpg" alt="Tokyo">
                    <h3>Tokyo</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/sudny.jpg" alt="Sydney">
                    <h3>Sydney</h3>
                </div>
                <!-- Deuxième groupe d'éléments (dupliqué pour l'effet continu) -->
                <div class="exclusive-item">
                    <img src="images/paris.jpg" alt="Paris">
                    <h3>London</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/munchin.webp" alt="Munich">
                    <h3>Paris</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/london.jpg" alt="London">
                    <h3>New York</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/image-4.png" alt="Tokyo">
                    <h3>Tokyo</h3>
                </div>
                <div class="exclusive-item">
                    <img src="images/image-5.png" alt="Sydney">
                    <h3>Sydney</h3>
                </div>
            </div>
        </div>
    </div>
   
    <!-- Catégories -->
    <div class="container">
        <h2 class="subtitle">Nos tops catégories d'événements</h2>
        <div class="trending-wrapper">
            <div class="scroll-controls">
                <button class="scroll-btn scroll-left" aria-label="Défiler à gauche">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="scroll-btn scroll-right" aria-label="Défiler à droite">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
            <div class="trending">
            <?php foreach ($categories as $categoryKey => $categoryInfo): ?>
                <?php 
                    // Récupérer les événements pour cette catégorie
                    $events = $controller->getEventsByCategory($categoryKey);
                ?>
                <div class="activity-card">
                    <div class="category-header">
                        <img src="<?= $categoryInfo['image'] ?>" alt="<?= $categoryInfo['title'] ?>">
                        <h3><?= $categoryInfo['title'] ?></h3>
                    </div>
                    <div class="events-list">
                        <?php foreach ($events as $event): ?>
                            <div class="event-item">
                                <img src="<?= htmlspecialchars($event['imageUrl']) ?>" 
                                     alt="<?= htmlspecialchars($event['name']) ?>">
                                <span>
                                    <h4><?= htmlspecialchars($event['name']) ?></h4>
                                    <p><?= htmlspecialchars($event['price']) ?> DT</p>
                                    <a href="reservation.php?id=<?= $event['id'] ?>" class="register-btn">
                                        Réserver
                                    </a>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
        <div class="events-container">
            <div class="events-display" id="events-display">
                <!-- Les sous-événements seront affichés ici dynamiquement -->
            </div>
        </div>
    </div>
  <!-- Nouvelle section pour les avis -->
<div class="review-container">
    <!-- Section Titre avec animation -->
    <div class="review-header">
        <h2 class="magic-text">Votre avis fait vibrer notre communauté <i class="fas fa-heart pulse"></i></h2>
        <p class="subtitle">Partagez votre expérience et inspirez les autres explorateurs</p>
    </div>

    <!-- Carte d'avis interactive -->
    <div class="review-card glow-hover">
        <div class="review-card-header">
            <div class="event-emoji">🎪</div>
            <h3>Dites-nous tout sur votre aventure !</h3>
        </div>

        <!-- Formulaire Créatif -->
        <form class="review-form">
            <div class="form-group floating">
                <input type="text" id="event-name" required>
                <label for="event-name">Quel événement avez-vous vécu ?</label>
            </div>

            <!-- Notation Étoiles Animées -->
            <div class="rating-group">
                <p>Votre coup de cœur :</p>
                <div class="stars-rating">
                    <span class="star" data-value="1">☆</span>
                    <span class="star" data-value="2">☆</span>
                    <span class="star" data-value="3">☆</span>
                    <span class="star" data-value="4">☆</span>
                    <span class="star" data-value="5">☆</span>
                </div>
            </div>

            <!-- Zone de texte avec compteur -->
            <div class="form-group">
                <label for="review-text">Racontez-nous ces moments magiques...</label>
                <textarea id="review-text" maxlength="500" placeholder="L'émotion, l'ambiance, une rencontre inoubliable..."></textarea>
                <div class="char-counter"><span id="char-count">0</span>/500</div>
            </div>

            <!-- Upload Photo Créatif -->
            <div class="photo-upload">
                <label class="upload-btn">
                    <input type="file" accept="image/*">
                    <i class="fas fa-camera-retro"></i> Ajoutez une photo souvenir
                </label>
            </div>

            <!-- Bouton Soumission -->
            <button type="submit" class="submit-btn">
                <span class="btn-text">Partager ma pépite</span>
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <!-- Message de remerciement (caché par défaut) -->
    <div class="thank-you-message">
        <div class="confetti">🎉</div>
        <h3>Merci pour cette étincelle !</h3>
        <p>Votre avis sera bientôt visible par toute la communauté.</p>
    </div>
</div>
<!-- Section existante des catégories et événements -->
<div class="container">
    <!-- Votre contenu existant -->
</div>

<!-- Section existante pour les avis -->
<div class="review-container">
    <!-- Contenu de la section des avis -->
</div>

<!-- Nouvelle section pour la réservation de sièges -->
<div class="seat-reservation-section">
    <div class="seat-reservation-header">
        <h2 class="magic-text">Réservez votre place comme au théâtre ! <i class="fas fa-chair pulse"></i></h2>
        <p class="subtitle">Choisissez votre siège pour vivre l'événement à votre façon, que ce soit au cœur de l'action ou en hauteur pour tout voir !</p>
    </div>

    <!-- Grille de sièges avec scène -->
    <div class="seat-grid-container">
        <!-- Scène -->
        <div class="stage">
            <div class="stage-label">Scène</div>
            <div class="stage-lights">
                <span class="light"></span>
                <span class="light"></span>
                <span class="light"></span>
            </div>
        </div>
        <!-- Grille de sièges -->
        <div class="seat-grid" id="seat-grid">
            <!-- Les sièges seront générés dynamiquement via JavaScript -->
        </div>
        <div class="seat-legend">
            <div class="legend-item">
                <span class="seat available"></span> Disponible
            </div>
            <div class="legend-item">
                <span class="seat reserved"></span> Réservé
            </div>
            <div class="legend-item">
                <span class="seat selected"></span> Sélectionné
            </div>
        </div>
    </div>

    <!-- Bouton de confirmation -->
    <button class="confirm-btn" id="confirm-btn" disabled>Confirmer ma réservation</button>

    <!-- Message de confirmation (caché par défaut) -->
    <div class="confirmation-message" id="confirmation-message">
        <div class="confetti">🎉</div>
        <h3>Votre place est réservée !</h3>
        <p>Vous recevrez une confirmation par email. Profitez de l'événement !</p>
    </div>
</div>
    <script>
        // Gestion de l'ouverture/fermeture des sous-événements
       // Gestion de l'ouverture/fermeture des sous-événements
document.querySelectorAll('.activity-card').forEach(card => {
    const header = card.querySelector('.category-header');
    header.addEventListener('click', () => {
        const eventsDisplay = document.getElementById('events-display');
        const isActive = card.classList.contains('active');

        document.querySelectorAll('.activity-card').forEach(c => {
            c.classList.remove('active');
        });

        eventsDisplay.classList.remove('active');
        eventsDisplay.innerHTML = '';

        if (!isActive) {
            card.classList.add('active');
            const eventsList = card.querySelector('.events-list');

            eventsList.querySelectorAll('.event-item').forEach(item => {
                const clone = item.cloneNode(true);
                eventsDisplay.appendChild(clone);
            });

            eventsDisplay.classList.add('active');
        }
    });
});
    
        // Gestion du défilement avec les flèches
        const trending = document.querySelector('.trending');
        const scrollLeftBtn = document.querySelector('.scroll-left');
        const scrollRightBtn = document.querySelector('.scroll-right');
    
        // Fonction pour mettre à jour l'état des boutons (désactiver si au début/fin)
        function updateScrollButtons() {
            scrollLeftBtn.disabled = trending.scrollLeft <= 0;
            scrollRightBtn.disabled = trending.scrollLeft >= trending.scrollWidth - trending.clientWidth - 1; // -1 pour éviter les erreurs d'arrondi
        }
    
        // Initialiser l'état des boutons
        updateScrollButtons();
    
        // Événement de défilement pour mettre à jour les boutons
        trending.addEventListener('scroll', updateScrollButtons);
    
        // Défilement à gauche
        scrollLeftBtn.addEventListener('click', () => {
            trending.scrollLeft -= 320; // Défilement de 320px (largeur d'une carte + gap)
        });
    
        // Défilement à droite
        scrollRightBtn.addEventListener('click', () => {
            trending.scrollLeft += 320; // Défilement de 320px (largeur d'une carte + gap)
        });
    </script>
   <script>
    // Interactivité des étoiles
    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', () => {
            const value = star.getAttribute('data-value');
            document.querySelectorAll('.star').forEach(s => {
                s.classList.toggle('active', s.getAttribute('data-value') <= value);
                s.textContent = s.classList.contains('active') ? '★' : '☆';
            });
        });
    });
    
    // Compteur de caractères
    document.getElementById('review-text').addEventListener('input', function() {
        document.getElementById('char-count').textContent = this.value.length;
    });
    
    // Animation de soumission
    document.querySelector('.review-form').addEventListener('submit', function(e) {
        e.preventDefault();
        this.style.display = 'none';
        document.querySelector('.thank-you-message').style.display = 'block';
    });
    </script>
    <script>
        // Génération de la grille de sièges
        const seatGrid = document.getElementById('seat-grid');
        const confirmBtn = document.getElementById('confirm-btn');
        const confirmationMessage = document.getElementById('confirmation-message');
        const totalSeats = 50; // 5 rangées x 10 colonnes
        const reservedSeats = [5, 12, 15, 28, 42]; // Sièges déjà réservés (exemple)
        let selectedSeats = [];
        
        function generateSeatGrid() {
            for (let i = 1; i <= totalSeats; i++) {
                const seat = document.createElement('div');
                seat.classList.add('seat');
                seat.textContent = i;
        
                if (reservedSeats.includes(i)) {
                    seat.classList.add('reserved');
                } else {
                    seat.classList.add('available');
                    seat.addEventListener('click', () => toggleSeatSelection(seat, i));
                }
        
                seatGrid.appendChild(seat);
            }
        }
        
        function toggleSeatSelection(seat, seatNumber) {
            if (seat.classList.contains('selected')) {
                seat.classList.remove('selected');
                selectedSeats = selectedSeats.filter(num => num !== seatNumber);
            } else {
                seat.classList.add('selected');
                selectedSeats.push(seatNumber);
            }
        
            confirmBtn.disabled = selectedSeats.length === 0;
        }
        
        function confirmReservation() {
            seatGrid.style.display = 'none';
            confirmBtn.style.display = 'none';
            confirmationMessage.style.display = 'block';
        
            // Simuler l'envoi des sièges réservés (vous pouvez ajouter une requête vers un backend ici)
            console.log('Sièges réservés :', selectedSeats);
        }
        
        // Initialisation de la grille
        generateSeatGrid();
        
        // Gestion de la confirmation
        confirmBtn.addEventListener('click', confirmReservation);
        </script>
<script>
class EventNotifier {
    constructor() {
        this.lastState = null;
        this.initUI();
        this.startChecking();
    }

    initUI() {
        this.notification = document.createElement('div');
        this.notification.className = 'update-notification';
        this.notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-calendar-check"></i>
                <span>Nouveaux événements disponibles</span>
                <button class="refresh-btn">Voir</button>
            </div>
        `;
        document.body.appendChild(this.notification);

        // Style intégré
        const style = document.createElement('style');
        style.textContent = `
            .update-notification {
                position: fixed;
                bottom: -100px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 12px 16px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 1000;
                transition: bottom 0.3s ease-out;
            }
            .update-notification.visible {
                bottom: 20px;
            }
            .refresh-btn {
                background: white;
                color: #4CAF50;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
        `;
        document.head.appendChild(style);

        this.notification.querySelector('.refresh-btn').addEventListener('click', () => {
            location.reload();
        });
    }

    async checkForUpdates() {
        try {
            const response = await fetch(`check_updates.php?t=${Date.now()}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                // Seulement si le statut a changé
                if (this.lastState !== data.has_changes && data.has_changes) {
                    this.showNotification();
                }
                this.lastState = data.has_changes;
            }
        } catch (error) {
            console.error('Update check failed:', error);
        }
    }

    showNotification() {
        this.notification.classList.add('visible');
        setTimeout(() => {
            this.notification.classList.remove('visible');
        }, 8000); // Disparaît après 8 secondes
    }

    startChecking() {
        setInterval(() => this.checkForUpdates(), 5000); // Vérifie toutes les 5 secondes
        this.checkForUpdates(); // Premier check immédiat
    }
}

// Initialisation
new EventNotifier();
</script>
</body>
<footer class="footer">
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
            <div class="payment-icons">
                <img src="images/visa.webp" alt="Visa" style="height: 50px;">
                <img src="images/paypal.webp" alt="PayPal" style="margin-bottom: 11px;">
                <img src="images/mastercard.webp" alt="MasterCard" style="height: 50px;">

            </div>
        </div>

        <div class="links">
            <p>À propos</p>
            <a href="" class="link">À propos de click'N'go</a>
            <a href="" class="link">Presse</a>
            <a href="" class="link">Nous rejoindre</a>
        </div>

        <div class="links">
            <p>Liens utiles</p>
            <a href="" class="link">Devenir partenaire</a>
            <a href="" class="link">FAQ - Besoin d'aide ?</a>
            <a href="" class="link">Tous les avis click'N'go</a>
        </div>
    </div>
</div>
</div>

<div class="footer-section">
<hr>
<div class="footer-separator"></div>
<pre>© click'N'go 2025 - tous droits réservés                                                                  <a href="#">Conditions générales</a>                                                   <a href="#">Mentions légales</a></pre>
</div>
</footer>

</html>
