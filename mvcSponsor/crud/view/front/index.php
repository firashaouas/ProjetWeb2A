<?php
session_start();
require_once(__DIR__ . "/../../controller/controller.php");

// Vérification immédiate de la connexion
$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['user']['id_user']);
if (!$isLoggedIn) {
    header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit();
}

// Charger les données seulement si l'utilisateur est connecté
$controller = new sponsorController();
$propositions = $controller->listSponser(); // Will only return sponsors for the logged-in user
?>
<?php
if (isset($_SESSION['sponsor_error'])) {
    echo '<div style="color: #e74c3c; background: #ffe6e6; padding: 10px; margin: 10px; border-radius: 5px;">' . htmlspecialchars($_SESSION['sponsor_error']) . '</div>';
    unset($_SESSION['sponsor_error']);
}
if (isset($_SESSION['sponsor_success'])) {
    echo '<div style="color: #2ecc71; background: #e6ffed; padding: 10px; margin: 10px; border-radius: 5px;">' . htmlspecialchars($_SESSION['sponsor_success']) . '</div>';
    unset($_SESSION['sponsor_success']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <link rel="stylesheet" href="st.css">
    <style>
        /* Modal styles */
        .event-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            overflow-y: auto;
        }
        .event-modal.show {
            display: block;
        }
        .event-modal-content {
            background: white;
            margin: 2% auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            background: none;
            border: none;
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.8em;
            display: none;
        }
        input:invalid, textarea:invalid {
            border-color: #e74c3c;
        }
        input:valid, textarea:valid {
            border-color: #2ecc71;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <nav class="navbar">
                <div class="logo-container">
                    <img src="images/logo.png" alt="Logo Click'N'Go" class="logo">
                </div>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/index.php" class="nav-link">Accueil</a></li>
                    <li class="nav-item"><a href="activite.html" class="nav-link">Activités</a></li>
                    <li class="nav-item"><a href="/Projet%20Web/mvcEvent/View/FrontOffice/evenemant.php" class="nav-link">Événements</a></li>
                    <li class="nav-item"><a href="Produits.html" class="nav-link">Produits</a></li>
                    <li class="nav-item"><a href="transports.html" class="nav-link">Transports</a></li>
                    <li class="nav-item"><a href="sponsors.html" class="nav-link active">Sponsors</a></li>
                </ul>
                <div class="auth-section">
                    <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/login/logout.php" class="register-btn">Déconnexion</a>
                </div>
            </nav>
        </div>
        <div class="header-title">
            <h1>Donnez de la visibilité à votre marque dès aujourd'hui !</h1>
        </div>
    </header>

    <!-- Main Sections -->
    <div class="container">
        <div class="options" id="options" style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 3rem;">
            <button id="btnShowSponsorships" class="option-button" type="button" style="flex:1; min-width: 120px;">Opportunités</button>
            <button id="btnShowTracking" class="option-button" type="button" style="flex:1; min-width: 120px;">Suivis</button>
            <button id="btnShowPayment" class="option-button" type="button" style="flex:1; min-width: 120px;">Paiement</button>
        </div>

        <!-- Tracking Section -->
        <div class="tracking-section" id="tracking-section" style="display:none;">
            <h2>Suivi de vos Propositions</h2>
            <?php if (!empty($propositions)): ?>
                <?php foreach ($propositions as $p): ?>
                    <div class="proposal-card">
                        <h3><?= htmlspecialchars($p['nom_entreprise']) ?></h3>
                        <div class="proposal-details">
                            <p><strong>Email:</strong> <?= htmlspecialchars($p['email']) ?></p>
                            <p><strong>Téléphone:</strong> <?= htmlspecialchars($p['telephone']) ?></p>
                            <p><strong>Montant:</strong> <?= htmlspecialchars($p['montant']) ?> dt</p>
                            <p><strong>Durée:</strong> <?= htmlspecialchars($p['duree']) ?></p>
                            <p><strong>Avantage proposé:</strong> <?= htmlspecialchars($p['avantage']) ?></p>
                            <p><strong>Résultat:</strong> <?= htmlspecialchars($p['status']) ?></p>
                            <?php if (!empty($p['logo'])): ?>
                                <img src="images/sponsors/<?= htmlspecialchars($p['logo']) ?>" alt="Logo de <?= htmlspecialchars($p['nom_entreprise']) ?>" style="max-width: 150px; height: auto; margin-top: 1rem; border-radius: 8px;">
                            <?php endif; ?>
                        </div>
                        <div class="proposal-actions">
                            <a class="button-secondary" href="modifier.php?id=<?= $p['id_sponsor'] ?>">Modifier</a>
                            <a class="button-secondary" href="delete.php?id=<?= $p['id_sponsor'] ?>" onclick="return confirm('Supprimer cette proposition ?');">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Vous n'avez aucune proposition de sponsoring pour le moment.</p>
            <?php endif; ?>
        </div>

        <!-- Payment Section -->
        <div class="payment-section" id="payment-section" style="display:none; max-width: 500px; margin: 0 auto; background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>Formulaire de Paiement</h2>
            <form id="paymentForm">
                <div class="form-group">
                    <label for="card-element">Détails de la carte</label>
                    <div id="card-element" class="StripeElement"></div>
                    <div id="card-errors" role="alert"></div>
                </div>
                <div class="form-group">
                    <label for="paymentCode">Code de paiement</label>
                    <input type="text" id="paymentCode" name="paymentCode" required placeholder="Entrez le code reçu par email">
                </div>
                <button type="submit" id="submitButton">Valider le paiement</button>
            </form>
        </div>

        <!-- Sponsorships Section -->
        <div class="sponsorships-section" id="sponsorships-section">
            <h2>Opportunités de Sponsoring Disponibles</h2>
            <input type="text" id="searchInput" placeholder="Rechercher une offre par titre...">
            <div class="sponsorship-grid" id="events-grid">
                <?php
                $offers = $controller->listOffers();
                $displayedOffers = [];
                foreach ($offers as $offer) {
                    $key = $offer['titre_offre'] . '|' . $offer['evenement'];
                    if (in_array($key, $displayedOffers)) {
                        continue;
                    }
                    $displayedOffers[] = $key;
                    echo '<div class="sponsorship-card" data-evenement="' . htmlspecialchars($offer['evenement']) . '" data-id-offre="' . htmlspecialchars($offer['id_offre']) . '">';
                    echo '<h3>' . htmlspecialchars($offer['titre_offre']) . '</h3>';
                    if (!empty($offer['image'])) {
                        echo '<img src="images/' . htmlspecialchars($offer['image']) . '" alt="Image de l\'offre" style="max-width: 100%; height: 60%; object-fit: cover; border-radius: 8px; margin-bottom: 12px;" />';
                    } else {
                        echo '<img src="images/default.png" alt="Image par défaut" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px;" />';
                    }
                    echo '<p>' . htmlspecialchars($offer['description_offre']) . '</p>';
                    echo '<div class="sponsorship-footer">';
                    echo '<span class="amount">' . htmlspecialchars($offer['montant_debut']) . ' dt</span>';
                    if ($offer['montant_offre'] <= 0) {
                        echo '<img src="images/sold.png" alt="Événement occupé" style="max-width: 100px; height: auto; margin-top: 0.5rem; display: block;" />';
                    }
                    echo '</div>';
                    if ($offer['montant_offre'] <= 0) {
                        echo '<button class="request-sponsor-btn" type="button" disabled style="cursor: not-allowed; opacity: 0.6;" title="Offre épuisée">Demander ce sponsoring</button>';
                    } else {
                        echo '<button class="request-sponsor-btn" type="button">Demander ce sponsoring</button>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Sponsor Request Modal -->
        <div id="sponsorRequestModal" class="event-modal" aria-hidden="true" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
            <div class="event-modal-content">
                <button class="close-modal" aria-label="Fermer">×</button>
                <div id="offerDetailsSection">
                    <h2 id="offerTitle"></h2>
                    <img id="offerImage" src="" alt="" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px;"/>
                    <p id="offerDescription"></p>
                    <p><strong>Événement:</strong> <span id="offerEvent"></span></p>
                    <p><strong>Montant:</strong> <span id="offerAmount"></span> dt</p>
                    <ul id="offerBenefits" class="benefits-list"></ul>
                    <button id="btnGoToForm" type="button" style="background:#c122c1; color:white; padding:0.75rem 1.5rem; border:none; border-radius:0.375rem; cursor:pointer; margin-top:1rem;">Demander ce sponsoring</button>
                </div>
                <form method="post" action="addSponsor.php" id="modalSponsorForm" novalidate style="display:none; margin-top: 1rem;" enctype="multipart/form-data">
                    <h2 id="modalTitle">Formulaire de demande de sponsoring</h2>
                    <div class="form-group">
                        <label for="modalCompanyName">Nom de l'entreprise</label>
                        <input type="text" id="modalCompanyName" name="companyName" pattern="[A-Za-z0-9\u00C0-\u017F\s\-&]{2,100}" title="2-100 caractères alphanumériques" required>
                        <small class="error-message"></small>
                    </div>
                    <div class="form-group">
    <label for="modalEmail">Email</label>
    <input type="email" id="modalEmail" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required readonly>
    <small class="error-message"></small>
</div>
<div class="form-group">
    <label for="modalPhone">Téléphone</label>
    <input type="tel" id="modalPhone" name="phone" pattern="^(\+216\s)?[0-9]{8}$" title="Format: +216 XXXXXXXX ou XXXXXXXX" required readonly value="+216 " maxlength="13" />
    <small class="error-message"></small>
</div>
                    <div class="form-group">
                        <label for="modalIdOffre">Sélectionnez une offre</label>
                        <select id="modalIdOffre" name="id_offre" required>
                            <option value="">-- Choisissez une offre --</option>
                            <?php foreach ($offers as $offer): ?>
                                <option value="<?= htmlspecialchars($offer['id_offre']) ?>"><?= htmlspecialchars($offer['titre_offre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="error-message"></small>
                    </div>
                    <div class="form-group">
                        <label for="modalDescription">Description du sponsoring</label>
                        <textarea id="modalDescription" name="description" rows="4" minlength="20" maxlength="1000" required></textarea>
                        <small class="error-message"></small>
                    </div>
                    <div class="form-group">
                        <label for="modalAmount">Montant proposé (dt)</label>
                        <input type="number" id="modalAmount" name="amount" min="100" step="1" required>
                        <small class="error-message"></small>
                    </div>
                    <div class="form-group">
                        <label for="modalDuration">Durée du sponsoring</label>
                        <input type="text" id="modalDuration" name="duration" pattern="[0-9]+\s*(mois|an|ans|jours|semaines)" placeholder="Ex: 3 mois, 1 an..." required>
                        <small class="error-message"></small>
                    </div>
                    <div class="form-group">
                        <label for="modalBenefits">Avantages souhaités</label>
                        <textarea id="modalBenefits" name="benefits" rows="4" minlength="10" maxlength="500" placeholder="Ex: Logo sur les affiches, mentions sur les réseaux sociaux..." required></textarea>
                        <small class="error-message"></small>
                    </div>
                    <div class="form-group">
                        <label for="modalLogo">Logo de l'entreprise</label>
                        <input type="file" id="modalLogo" name="logo" accept="image/*" required>
                        <small class="error-message"></small>
                    </div>
                    <button type="submit">Envoyer la proposition</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="newsletter-section">
                <h2>Inscrivez-vous à notre newsletter</h2>
                <form class="newsletter-form">
                    <input type="email" placeholder="Votre email" required>
                    <button class="newsletter-btn" type="submit">S'inscrire</button>
                </form>
            </div>
            <div class="footer-grid">
                <div class="footer-links">
                    <h3>Liens rapides</h3>
                    <ul>
                        <li><a href="#">Accueil</a></li>
                        <li><a href="#">Activités</a></li>
                        <li><a href="#">Événements</a></li>
                        <li><a href="#">Produits</a></li>
                        <li><a href="#">Transports</a></li>
                        <li><a href="#">Sponsors</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Contactez-nous</a></li>
                        <li><a href="#">Aide</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Suivez-nous</h3>
                    <div class="social-links">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Paiements acceptés</h3>
                    <div class="payment-methods">
                        <img src="images/visa.png" alt="Visa">
                        <img src="images/mastercard.png" alt="Mastercard">
                        <img src="images/paypal.png" alt="Paypal">
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="legal-links">
                    <a href="#">Conditions d'utilisation</a>
                    <a href="#">Politique de confidentialité</a>
                </div>
                <p>© 2025 Click'N'Go. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
    // Pass PHP variables to JavaScript
    const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
const loginUrl = '/Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php';
const userEmail = <?php echo json_encode($_SESSION['user']['email'] ?? ''); ?>;
const userPhone = <?php echo json_encode($_SESSION['user']['num_user'] ?? ''); ?>;
    // Stripe Initialization
    const stripe = Stripe('pk_test_51RLtBORvSkgkxHMRC9pvstztm4myG6sE7n04iYjq8BfaQJKxNp1dtd5dWzLFRSruZTCQpQsyUSlHYnVKI88h8C2F00mmuKWGO3');
    const elements = stripe.elements();
    const card = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#32325d',
                '::placeholder': { color: '#aab7c4' }
            },
            invalid: { color: '#e74c3c' }
        }
    });
    card.mount('#card-element');

    card.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
            displayError.style.display = 'block';
        } else {
            displayError.textContent = '';
            displayError.style.display = 'none';
        }
    });

    // Main JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Section toggling
        const btnShowSponsorships = document.getElementById('btnShowSponsorships');
        const btnShowTracking = document.getElementById('btnShowTracking');
        const btnShowPayment = document.getElementById('btnShowPayment');
        const sponsorshipsSection = document.getElementById('sponsorships-section');
        const trackingSection = document.getElementById('tracking-section');
        const paymentSection = document.getElementById('payment-section');

        btnShowSponsorships.addEventListener('click', () => {
            sponsorshipsSection.style.display = 'block';
            trackingSection.style.display = 'none';
            paymentSection.style.display = 'none';
        });

        btnShowTracking.addEventListener('click', () => {
            sponsorshipsSection.style.display = 'none';
            trackingSection.style.display = 'block';
            paymentSection.style.display = 'none';
        });

        btnShowPayment.addEventListener('click', () => {
            sponsorshipsSection.style.display = 'none';
            trackingSection.style.display = 'none';
            paymentSection.style.display = 'block';
        });

        // Handle URL parameters for payment section
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        if (getQueryParam('payment') === '1') {
            sponsorshipsSection.style.display = 'none';
            trackingSection.style.display = 'none';
            paymentSection.style.display = 'block';
            const code = getQueryParam('code');
            if (code) {
                document.getElementById('paymentCode').value = code;
            }
        }

        // Payment form submission
        const paymentForm = document.getElementById('paymentForm');
        const submitButton = document.getElementById('submitButton');
        paymentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            submitButton.disabled = true;

            const paymentCode = document.getElementById('paymentCode').value.trim();
            const sponsorId = getQueryParam('id');

            if (!paymentCode) {
                alert('Veuillez entrer le code de paiement.');
                submitButton.disabled = false;
                return;
            }

            try {
                const createResponse = await fetch('payment_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'create',
                        id_sponsor: sponsorId,
                        payment_code: paymentCode
                    })
                });

                if (!createResponse.ok) {
                    throw new Error(`HTTP error! Status: ${createResponse.status}`);
                }

                const createData = await createResponse.json();
                if (!createData.success) {
                    alert(createData.message);
                    submitButton.disabled = false;
                    return;
                }

                const result = await stripe.confirmCardPayment(createData.clientSecret, {
                    payment_method: { card: card, billing_details: {} }
                });

                if (result.error) {
                    document.getElementById('card-errors').textContent = result.error.message;
                    document.getElementById('card-errors').style.display = 'block';
                    submitButton.disabled = false;
                } else if (result.paymentIntent.status === 'succeeded') {
                    const verifyResponse = await fetch('payment_handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'verify',
                            id_sponsor: sponsorId,
                            payment_code: paymentCode,
                            payment_intent_id: result.paymentIntent.id
                        })
                    });

                    if (!verifyResponse.ok) {
                        throw new Error(`HTTP error! Status: ${verifyResponse.status}`);
                    }

                    const verifyData = await verifyResponse.json();
                    if (verifyData.success) {
                        alert('Paiement effectué avec succès !');
                    } else {
                        alert('Erreur lors de la vérification du paiement : ' + verifyData.message);
                    }
                    submitButton.disabled = false;
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                alert('Une erreur est survenue. Veuillez vérifier votre connexion et réessayer.');
                submitButton.disabled = false;
            }
        });

        // Modal and Sponsorship Request Logic
        const modal = document.getElementById('sponsorRequestModal');
        const closeModalBtn = modal.querySelector('.close-modal');
        const requestButtons = document.querySelectorAll('.request-sponsor-btn');
        const modalIdOffre = document.getElementById('modalIdOffre');
        const offerDetailsSection = document.getElementById('offerDetailsSection');
        const modalSponsorForm = document.getElementById('modalSponsorForm');
        const btnGoToForm = document.getElementById('btnGoToForm');

        function clearForm() {
            modalSponsorForm.reset();
            modalIdOffre.value = '';
            document.querySelectorAll('#modalSponsorForm .error-message').forEach(msg => msg.style.display = 'none');
        }

        function fillFormWithOffer(offerId) {
            modalIdOffre.value = offerId;
        }

        function showOfferDetails() {
            offerDetailsSection.style.display = 'block';
            modalSponsorForm.style.display = 'none';
        }

        function showForm() {
    offerDetailsSection.style.display = 'none';
    modalSponsorForm.style.display = 'block';
    
    // Pre-fill email and phone fields
    document.getElementById('modalEmail').value = userEmail;
    document.getElementById('modalPhone').value = userPhone;
    
    // Make fields read-only
    document.getElementById('modalEmail').readOnly = true;
    document.getElementById('modalPhone').readOnly = true;
}

        function populateOfferDetails(card) {
            const title = card.querySelector('h3').textContent;
            const description = card.querySelector('p').textContent;
            const event = card.getAttribute('data-evenement') || 'Non spécifié';
            const amount = card.querySelector('.amount').textContent.replace(' dt', '');
            const img = card.querySelector('img');
            const imageSrc = img ? img.src : 'images/default.png';
            const imageAlt = img ? img.alt : 'Image par défaut';

            document.getElementById('offerTitle').textContent = title;
            document.getElementById('offerImage').src = imageSrc;
            document.getElementById('offerImage').alt = imageAlt;
            document.getElementById('offerDescription').textContent = description;
            document.getElementById('offerEvent').textContent = event;
            document.getElementById('offerAmount').textContent = amount;
            document.getElementById('offerBenefits').innerHTML = ''; // Clear benefits as not provided in data
        }

        requestButtons.forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('.sponsorship-card');
                if (!card) {
                    console.error('No sponsorship card found for button');
                    return;
                }

                populateOfferDetails(card);
                clearForm();
                fillFormWithOffer(card.getAttribute('data-id-offre'));
                showOfferDetails();

                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
            });
        });

        btnGoToForm.addEventListener('click', () => {
            showForm();
        });

        closeModalBtn.addEventListener('click', () => {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            clearForm();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                clearForm();
            }
        });

        // Search filter
        const searchInput = document.getElementById('searchInput');
        const sponsorshipCards = document.querySelectorAll('.sponsorship-card');
        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase();
            sponsorshipCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = title.includes(filter) ? '' : 'none';
            });
        });

        // Form validation
        modalSponsorForm.addEventListener('submit', function(e) {
            let isValid = true;
            document.querySelectorAll('#modalSponsorForm [required]').forEach(field => {
                const errorMsg = field.nextElementSibling;
                if (!field.checkValidity()) {
                    errorMsg.textContent = field.validationMessage || 'Ce champ est invalide';
                    errorMsg.style.display = 'block';
                    isValid = false;
                } else {
                    errorMsg.style.display = 'none';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Veuillez corriger les erreurs avant soumission');
            }
        });

        document.querySelectorAll('#modalSponsorForm input, #modalSponsorForm textarea, #modalSponsorForm select').forEach(field => {
            field.addEventListener('input', function() {
                const errorMsg = field.nextElementSibling;
                if (!this.checkValidity()) {
                    errorMsg.textContent = this.validationMessage || 'Valeur invalide';
                    errorMsg.style.display = 'block';
                } else {
                    errorMsg.style.display = 'none';
                }
            });
        });
    });
</script>

</body>
</html>