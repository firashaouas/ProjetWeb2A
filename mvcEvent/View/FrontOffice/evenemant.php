<?php
session_start();


require_once '../../Controller/EventController.php';
$controller = new EventController();

// Hardcode user_id to match global_user_id in controllers
if (isset($_SESSION['user']) && isset($_SESSION['user']['id_user'])) {
    $user_id = $_SESSION['user']['id_user'];
} else {
    // Redirection ou message d'erreur si l'utilisateur n'est pas connecté
    header("Location: /Projet Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit;
}

// Vérifier si l'utilisateur est nouveau
$is_new_user = true; // Par défaut, considérer comme nouveau
if ($user_id !== null && is_int($user_id) && $user_id > 0) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=click\'n\'go;charset=utf8mb4', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier les réservations
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM chaise WHERE id_user = :user_id AND statut = 'reserve'");
        $stmt->execute([':user_id' => $user_id]);
        $reservationsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Vérifier les clics
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_clicks WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $clicksCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $is_new_user = ($reservationsCount == 0 && $clicksCount == 0);
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification de l'utilisateur : " . $e->getMessage());
    }
}
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

$python_exec = 'C:/Users/Mega-PC/AppData/Local/Programs/Python/Python312/python.exe';
$python_script = 'C:/xampp/htdocs/Projet Web/mvcEvent/recommend_events.py';
$recommendations = [];

if (!file_exists($python_exec)) {
    error_log("Python executable not found: " . $python_exec);
} elseif (!file_exists($python_script)) {
    error_log("Python script not found: " . $python_script);
} else {
    $command = escapeshellcmd("\"$python_exec\" \"$python_script\" " . $user_id);
    $output = shell_exec($command);
    error_log("Python script command: " . $command);
    error_log("Python script output: " . var_export($output, true));
    $recommendations_data = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
    } elseif (is_array($recommendations_data) && isset($recommendations_data['success']) && $recommendations_data['success']) {
        $recommendations = $recommendations_data['recommendations'];
    } else {
        error_log("Invalid recommendations data: " . var_export($recommendations_data, true));
    }
}


// Fonction pour générer une couleur basée sur le nom de l'utilisateur
function stringToColor($str)
{
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
    <title>event</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="header">
        <nav>
            <img src="images/logo.png" class="logo">
            <ul class="nav-links">
                <li><a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/index.php">Accueil</a></li>
                <li><a href="/Projet Web/mvcact/view/front office/activite.php">Activités</a></li>
                <li><a href="/Projet%20Web/mvcEvent/View/FrontOffice/evenemant.php">Événements</a></li>
                <li><a href="/Projet Web/mvcProduit/view/front office/produit.php">Produits</a></li>
                <li><a href="/Projet Web/mvcCovoiturage/view/index.php">Transports</a></li>
                <li><a href="/Projet%20Web/mvcSponsor/crud/view/front/index.php">Sponsors</a></li>
            </ul>

            <!-- Vérification de l'état de connexion -->
<?php if (!isset($_SESSION['user'])): ?>
  <!-- 🔒 Utilisateur non connecté : bouton vers login -->
  <a href="/Projet Web/mvcUtilisateur/View/BackOffice/login/login.php" class="register-btn" title="Connexion/Inscription">
    <i class="fas fa-user"></i>
  </a>
<?php else: ?>
  <!-- 👤 Utilisateur connecté -->
  <div class="user-profile" style="position: relative; display: inline-block;">
    <?php
    $user = $_SESSION['user'];
    $fullName = $user['full_name'] ?? 'U';
    $initial = strtoupper(substr($fullName, 0, 1));
    $profilePicture = $user['profile_picture'] ?? '';
    $verified = isset($user['is_verified']) && $user['is_verified'] == 1;
    ?>

    <?php if (!empty($profilePicture) && file_exists($profilePicture)): ?>
      <img src="<?= htmlspecialchars($profilePicture) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
    <?php else: ?>
      <div class="profile-circle"
        style="background-color: <?= stringToColor($fullName) ?>;"
        onclick="toggleDropdown()">
        <?= $initial ?>
      </div>
    <?php endif; ?>

    <!-- ✅ Badge vérification -->
    <div class="verification-status" style="position: absolute; bottom: -5px; right: -5px;">
      <?php if ($verified): ?>
        <img src="/Projet Web/mvcUtilisateur/assets/icons/verified.png"
          alt="Compte vérifié"
          title="Compte Vérifié"
          style="width: 20px; height: 20px;">
      <?php else: ?>
        <img src="/Projet Web/mvcUtilisateur/assets/icons/not_verified.png"
          alt="Compte non vérifié"
          title="Compte Non Vérifié"
          style="width: 20px; height: 20px; cursor: pointer;"
          onclick="showVerificationPopup()">
      <?php endif; ?>
    </div>

    <!-- Menu déroulant -->
    <div class="dropdown-menu" id="dropdownMenu" style="display: none; position: absolute; top: 120%; right: 0; background-color: white; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 100;">
      <a href="/Projet Web/mvcUtilisateur/View/FrontOffice/profile.php" style="display: block; padding: 10px;">👤 Mon Profil</a>
      <a href="/Projet Web/mvcUtilisateur/View/BackOffice/login/logout.php" style="display: block; padding: 10px;">🚪 Déconnexion</a>
    </div>
  </div>
<?php endif; ?>

<script>
  function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    if (menu) {
      menu.style.display = menu.style.display === "block" ? "none" : "block";
    }
  }

  function showVerificationPopup() {
    Swal.fire({
      title: 'Vérification requise',
      text: 'Veuillez vérifier votre compte via l’email que vous avez reçu.',
      icon: 'info',
      confirmButtonText: 'OK',
      confirmButtonColor: '#6c63ff'
    });
  }

  // Fermer dropdown quand on clique en dehors
  document.addEventListener("click", function (event) {
    const dropdown = document.getElementById("dropdownMenu");
    const profile = document.querySelector(".user-profile");
    if (dropdown && profile && !profile.contains(event.target)) {
      dropdown.style.display = "none";
    }
  });
</script>
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
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
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
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
      z-index: 100;
    }

    .user-profile:hover .dropdown-menu {
      display: block;
    }

  </style>


        </nav>
        <h1 class="animated-text">Restez connecté à tous les événements</h1>
    </div>
    <section class="hero">
        <div class="hero-content">
            <h2>Événement du mois : Concert live !</h2>
            <p>Rejoignez-nous le 20 avril 2025 pour une soirée inoubliable.</p>
            <a href="#" class="cta-btn">Réservez maintenant</a>
        </div>
    </section>
<div class="carousel-podium">
    <?php
    $classes = ['left-2', 'left-1', 'center', 'right-1', 'right-2'];
    for ($i = 0; $i < min(5, count($recommendations)); $i++):
        $rec = $recommendations[$i];
        $pos = $classes[$i];
    ?>
        <div class="card <?php echo $pos; ?>" style="background-image: url('<?php echo htmlspecialchars($rec['image']); ?>');">
            <div class="card-overlay">
                <h3><?php echo htmlspecialchars($rec['event']['name']); ?></h3>
                <p><?php echo htmlspecialchars($rec['event']['price']); ?> DT</p>
                <a href="reservation.php?event_id=<?php echo $rec['event']['id']; ?>" class="register1-btn">Réserver</a>
            </div>
        </div>
    <?php endfor; ?>
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
                                        <a href="reservation.php?event_id=<?= $event['id'] ?>" class="register-btn">
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
    <!-- Ajoutez cette section après la partie des avis -->
    <section class="reservations-section">
        <div class="container">
            <h2 class="magic-text">
                <i class="fas fa-ticket-alt pulse"></i> Gestion des Réservations
                <span class="badge" id="reservation-count">0</span>
            </h2>
            <p class="subtitle">Consultez, modifiez ou annulez vos réservations en toute simplicité</p>
            <div class="reservations-list" id="reservations-list">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Chargement des réservations...</p>
                </div>
            </div>
        </div>
    </section>

    <div class="reservation-modal" id="edit-modal">
        <div class="modal-content">
            <span class="close-modal" aria-label="Fermer">&times;</span>
            <h3><i class="fas fa-edit"></i> Modifier la réservation</h3>
            <div class="modal-body" id="edit-modal-body"></div>
        </div>
    </div>
    <script>
   const API_BASE_URL = '/projet%20Web/mvcEvent/reservations_api.php';
   const USER_ID = <?php echo json_encode($user_id); ?>; // Will be 4

    window.loadAllReservations = async function() {
        console.log("Loading reservations...");
        try {
            showLoadingState();
            const response = await fetch(`${API_BASE_URL}?action=get_all&user_id=${USER_ID}`, { credentials: 'same-origin' });
            console.log("API Response:", response);
            if (!response.ok) throw new Error(`Erreur serveur: ${response.status}`);
            const data = await response.json();
            console.log("API Data:", data);
            if (!data.success) throw new Error(data.message || 'Erreur inconnue');
            displayReservations(data.reservations);
            updateReservationCount(data.reservations.length);
        } catch (error) {
            console.error('Erreur:', error);
            showErrorState(error.message);
        }
    };

    function displayReservations(reservations) {
        const container = document.getElementById('reservations-list');
        if (!reservations || !reservations.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-calendar-check"></i>
                    <p>Aucune réservation trouvée</p>
                </div>`;
            return;
        }

        container.innerHTML = reservations.map(reservation => `
            <div class="reservation-card" data-id="${reservation.id}">
                <div class="reservation-header">
                    <h3>${escapeHtml(reservation.title)}</h3>
                    <span class="price">${reservation.price} DT</span>
                </div>
                <div class="reservation-details">
                    <h4><i class="fas "></i> ${escapeHtml(reservation.name)}</h4>
                    <p><i class="fas fa-calendar-alt"></i> ${formatDate(reservation.date)}</p>
                    <p><i class="fas fa-chair"></i> ${getSeatsCount(reservation.seats)} place(s)</p>
                </div>
                <div class="reservation-actions">
                    <button class="btn-modifier" onclick="openEditModal(${reservation.id})">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button class="btn-annuler" onclick="confirmCancel(${reservation.id})">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </div>
            </div>
        `).join('');
    }

window.openEditModal = async function(eventId) {
    try {
        showModalLoading();
        document.getElementById('edit-modal').classList.add('active');

        // On ne passe plus user_id dans l'URL
        const [eventResponse, seatsResponse, reservationResponse] = await Promise.all([
            fetch(`${API_BASE_URL}?action=get_event&event_id=${eventId}`, { credentials: 'same-origin' }),
            fetch(`${API_BASE_URL}?action=get_seats&event_id=${eventId}`, { credentials: 'same-origin' }),
            fetch(`${API_BASE_URL}?action=get_reservation&event_id=${eventId}`, { credentials: 'same-origin' })
        ]);

        if (!eventResponse.ok || !seatsResponse.ok || !reservationResponse.ok) {
            throw new Error('Erreur lors du chargement des données');
        }

        const [eventData, seatsData, reservationData] = await Promise.all([
            eventResponse.json(),
            seatsResponse.json(),
            reservationResponse.json()
        ]);

        if (!eventData.success || !seatsData.success || !reservationData.success) {
            throw new Error(eventData.message || seatsData.message || reservationData.message || 'Erreur inconnue');
        }

        const reservedSeats = reservationData.reservation?.seats || [];

        document.getElementById('edit-modal-body').innerHTML = `
            <div class="modal-event-info">
                <img src="${escapeHtml(eventData.event.imageUrl)}" alt="${escapeHtml(eventData.event.name)}">
                <div class="modal-event-text">
                    <h4>${escapeHtml(eventData.event.name)}</h4>
                    <p><i class="fas fa-calendar-alt"></i> ${formatDate(eventData.event.date)}</p>
                </div>
            </div>
            <div class="seat-selection">
                <h5>Choisissez vos sièges (<span id="selected-count">${reservedSeats.length}</span> sélectionné(s))</h5>
                <div class="stage-mini">SCÈNE</div>
                <div class="available-seats">
                    ${seatsData.seats.map(seat => `
                        <div class="seat-option 
                            ${seat.status === 'reserve' && !reservedSeats.includes(seat.number) ? 'reserved' : ''}
                            ${reservedSeats.includes(seat.number) ? 'selected' : 'available'}" 
                            data-seat="${seat.number}" 
                            data-status="${seat.status}">
                            <i class="fas fa-chair"></i>
                            ${seat.number}
                            ${seat.status === 'reserve' && !reservedSeats.includes(seat.number) ? '<span>(réservé)</span>' : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
            <div class="modal-actions">
                <button class="confirm-btn" onclick="updateReservation(${eventId})">
                    <i class="fas fa-check"></i> Confirmer
                </button>
            </div>
        `;
        initSeatSelection();
    } catch (error) {
        console.error('Erreur:', error);
        document.getElementById('edit-modal-body').innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${escapeHtml(error.message)}</p>
            </div>`;
    }
};


window.updateReservation = async function(eventId) {
    const selectedSeats = Array.from(document.querySelectorAll('.seat-option.selected'))
        .map(seat => seat.dataset.seat);

    if (selectedSeats.length === 0) {
        showModalError('Veuillez sélectionner au moins un siège');
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}?action=update`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                event_id: eventId,
                seat_numbers: selectedSeats
                // ⛔ Ne pas envoyer user_id ici, le back le prend depuis la session
            })
        });

        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Erreur inconnue');

        showModalSuccess('Réservation mise à jour avec succès!');
        setTimeout(() => {
            closeModal();
            loadAllReservations();
        }, 1500);
    } catch (error) {
        console.error('Erreur:', error);
        showModalError('Erreur: ' + error.message);
    }
};


    window.confirmCancel = function(eventId) {
        if (confirm('Êtes-vous sûr de vouloir annuler cette réservation?')) {
            cancelReservation(eventId);
        }
    };

    async function cancelReservation(eventId) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=cancel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ 
                    event_id: eventId,
                    user_id: USER_ID // Ajouter user_id
                })
            });

            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Erreur inconnue');
            
            showModalSuccess('Réservation annulée avec succès!');
            setTimeout(loadAllReservations, 1500);
        } catch (error) {
            console.error('Erreur:', error);
            showModalError('Erreur: ' + error.message);
        }
    }

    function showModalLoading() {
        document.getElementById('edit-modal-body').innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Chargement des détails...</p>
            </div>`;
        document.getElementById('edit-modal').classList.add('active');
    }

    function showModalError(message) {
        const modalBody = document.getElementById('edit-modal-body');
        modalBody.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${escapeHtml(message)}</p>
            </div>`;
        setTimeout(closeModal, 3000);
    }

    function showModalSuccess(message) {
        const modalBody = document.getElementById('edit-modal-body');
        modalBody.innerHTML = `
            <div class="confirmation-message">
                <div class="confetti">🎉</div>
                <h3>${escapeHtml(message)}</h3>
            </div>`;
    }

    function formatDate(dateString) {
        if (!dateString) return 'Date non spécifiée';
        return new Date(dateString).toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function getSeatsCount(seats) {
        if (!seats) return 0;
        return typeof seats === 'string' ? seats.split(',').length : seats.length;
    }

    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&")
            .replace(/</g, "<")
            .replace(/>/g, ">")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;") || '';
    }

    function showLoadingState() {
        document.getElementById('reservations-list').innerHTML = `
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Chargement en cours...</p>
            </div>`;
    }

    function showErrorState(message) {
        document.getElementById('reservations-list').innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${escapeHtml(message)}</p>
                <button onclick="loadAllReservations()" class="retry-btn">
                    <i class="fas fa-sync-alt"></i> Réessayer
                </button>
            </div>`;
    }

    function updateReservationCount(count) {
        document.getElementById('reservation-count').textContent = count || 0;
    }

    function closeModal() {
        document.getElementById('edit-modal').classList.remove('active');
    }

    function initSeatSelection() {
        const seats = document.querySelectorAll('.seat-option');
        const selectedCount = document.getElementById('selected-count');

        seats.forEach(seat => {
            if (seat.classList.contains('reserved') && !seat.classList.contains('selected')) {
                return; // Skip seats reserved by others
            }
            seat.addEventListener('click', function() {
                if (this.classList.contains('reserved') && !this.classList.contains('selected')) {
                    return; // Prevent clicking reserved seats
                }
                this.classList.toggle('selected');
                const selectedSeats = document.querySelectorAll('.seat-option.selected').length;
                selectedCount.textContent = selectedSeats;
            });
        });
    }

    function trackEventClick(eventId) {
        fetch('/projet%20Web/mvcEvent/track_click.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: USER_ID,
                event_id: eventId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Click tracked successfully');
            }
        })
        .catch(error => console.error('Error tracking click:', error));
    }

    function initEventClickTracking() {
        document.querySelectorAll('.exclusive-item').forEach(item => {
            item.addEventListener('click', () => {
                const eventId = item.getAttribute('data-event-id');
                if (eventId) {
                    trackEventClick(eventId);
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.close-modal').addEventListener('click', closeModal);
        document.getElementById('edit-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        loadAllReservations();
        initEventClickTracking();
    });
    </script>

    <script>
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
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const exclusives = document.querySelector('.exclusives');
    const items = document.querySelectorAll('.exclusive-item');
    const wrapper = document.querySelector('.exclusives-wrapper');

    if (items.length === 0) return;

    const cloneCount = Math.ceil(wrapper.offsetWidth / items[0].offsetWidth) + 1;
    items.forEach(item => {
        for (let i = 0; i < cloneCount; i++) {
            const clone = item.cloneNode(true);
            exclusives.appendChild(clone);
        }
    });

    let scrollPosition = 0;
    const scrollSpeed = 1;
    const itemWidth = items[0].offsetWidth + 20;
    const totalWidth = itemWidth * items.length;

    function scrollInfinite() {
        if (!isScrolling) return;
        scrollPosition += scrollSpeed;
        if (scrollPosition >= totalWidth) {
            scrollPosition -= totalWidth;
            exclusives.style.transition = 'none';
            exclusives.style.transform = `translateX(-${scrollPosition}px)`;
            exclusives.offsetHeight;
            exclusives.style.transition = 'transform 0.1s linear';
        }
        exclusives.style.transform = `translateX(-${scrollPosition}px)`;
        requestAnimationFrame(scrollInfinite);
    }

    let isScrolling = true;
    requestAnimationFrame(scrollInfinite);

    wrapper.addEventListener('mouseenter', () => {
        isScrolling = false;
    });
    wrapper.addEventListener('mouseleave', () => {
        isScrolling = true;
    });

    document.querySelectorAll('.exclusive-item').forEach(item => {
        item.addEventListener('click', () => {
            const eventId = item.getAttribute('data-event-id');
            if (eventId) {
                trackEventClick(eventId);
            }
        });
    });
});
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

    <div class="footer-section">
        <hr>
        <div class="footer-separator"></div>
        <pre>© click'N'go 2025 - tous droits réservés                                                                  <a href="#">Conditions générales</a>                                                   <a href="#">Mentions légales</a></pre>
    </div>
</footer>
</html>