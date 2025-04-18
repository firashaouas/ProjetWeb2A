<?php 
require_once(__DIR__ . "/../../controller/controller.php");
$controller = new sponsorController();
$propositions = $controller->listSponser(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Votre CSS existant reste inchangé */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ... (tout le reste de votre CSS existant) ... */
	h1 {
            text-align: center;
            color: #c122c1;
            font-size: 2.5rem;
            margin-bottom: 3rem;
        }

        .options {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 768px) {
            .options {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .option-card {
            background: rgb(220, 171, 213);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            cursor: pointer;
            transition: box-shadow 0.3s ease;
        }

        .option-card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .option-card img {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
        }

        .option-card h2 {
            color: #06090e;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .option-card p {
            color: #41043d;
        }

        .form-section, .sponsorships-section, .event-detail-section, .tracking-section {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .back-button {
            display: flex;
            align-items: center;
            color: #000000;
            text-decoration: none;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }

        .back-button:hover {
            color: #000000;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #D1D5DB;
            border-radius: 0.375rem;
            font-size: 1rem;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #000000;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        button {
            background: #c122c1;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #c122c1;
        }

        .filters {
            background: #d29ccb;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .filters {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .sponsorship-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .sponsorship-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .sponsorship-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(193, 34, 193, 0.15);
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            border: 1px solid #c122c1;
        }

        .sponsorship-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(193, 34, 193, 0.4);
        }

        .sponsorship-card h3 {
            color: #a01aa0;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .sponsorship-card p {
            color: #4b004b;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            line-height: 1.4;
        }

        .benefits-list {
            list-style-type: disc;
            margin-left: 1.5rem;
            margin-bottom: 1rem;
            color: #4b004b;
        }

        .sponsorship-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #a01aa0;
        }

        .amount {
            color: #7a007a;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .event-description {
            color: #4B5563;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .event-details {
            background: #F9FAFB;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        .event-details h4 {
            color: #1F2937;
            margin-bottom: 1rem;
        }

        .divider {
            height: 1px;
            background: #E5E7EB;
            margin: 2rem 0;
        }

        .proposal-status {
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            width: fit-content;
            box-shadow: 0 0 8px rgba(193, 34, 193, 0.3);
        }

        .status-pending {
            background: #fff4e5;
            color: #d56621;
            border: 1px solid #f0c36d;
        }

        .status-approved {
            background: #e6f4ea;
            color: #09513c;
            border: 1px solid #7ed08a;
        }

        .status-rejected {
            background: #fbeaea;
            color: #ac1515;
            border: 1px solid #f5a1a1;
        }

        .proposal-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(193, 34, 193, 0.15);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.3s ease;
        }

        .proposal-card:hover {
            box-shadow: 0 15px 30px rgba(193, 34, 193, 0.3);
        }

        .proposal-card h3 {
            color: #4b004b;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .proposal-details {
            margin-bottom: 1rem;
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.4;
        }

        .proposal-actions {
            display: flex;
            gap: 1rem;
        }

        .proposal-actions a {
            flex: 1;
            text-align: center;
            padding: 0.75rem 0;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
            cursor: pointer;
            user-select: none;
            background: #c122c1;
            color: white;
            box-shadow: 0 4px 12px rgba(193, 34, 193, 0.4);
        }

        .proposal-actions a:hover {
            background: #a01aa0;
            box-shadow: 0 6px 20px rgba(160, 26, 160, 0.6);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background-color: rgb(243, 244, 246);
            padding: 3rem 1rem;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
        }

        h1 {
            font-size: 2.25rem;
            font-weight: bold;
            color: rgb(17, 24, 39);
            margin-bottom: 3rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
        }

        .card {
            position: relative;
            border-radius: 0.5rem;
            overflow: hidden;
            aspect-ratio: 4/3;
            cursor: pointer;
        }

        .card img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card:hover img {
            transform: scale(1.1);
        }

        .overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.4);
            transition: background-color 0.3s ease;
        }

        .card:hover .overlay {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .card-content {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    padding: 0.3rem;
    box-sizing: border-box;
}


        .card-title {
            color: white;
            font-size: 1.25rem;
            font-weight: bold;
            text-align: center;
            padding: 0 0.5rem;
        }

        @media (min-width: 640px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 768px) {
            .grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .grid {
                grid-template-columns: repeat(5, 1fr);
            }
            body {
                padding: 3rem 2rem;
            }
        }
        .container {
  margin-bottom: 4rem; /* espace entre les blocs */
}
.video-container {
    display: flex;
    justify-content: center; /* centre horizontalement */
    align-items: center;     /* centre verticalement si hauteur définie */
    height: 100vh;           /* pleine hauteur de la fenêtre */
    background-color: #ffffff;  /* fond optionnel */
}

.video-container video {
    width: 80%;              /* adapte la taille à 80% de la largeur */
    max-width: 800px;        /* taille max pour éviter qu'elle soit trop grande */
    border-radius: 10px;     /* coins arrondis optionnels */
}
        /* Ajout pour le modal d'événement */
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

        .event-modal-content {
            background: white;
            margin: 2% auto;
            padding: 20px;
            width: 80%;
            max-width: 800px;
            border-radius: 10px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        /* ========== FOOTER STYLES ========== */
.main-footer {
    background-color: #f8f9fa;
    color: #333;
    padding: 3rem 0 0;
    margin-top: 4rem;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.newsletter-section {
    background-color: #e1bee7;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
}

.newsletter-form {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
}

.newsletter-form input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 300px;
}

.newsletter-btn {
    background: #c122c1;
    color: white;
    border: none;
    padding: 0 1.5rem;
    border-radius: 4px;
    cursor: pointer;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.footer-links h3 {
    color: #c122c1;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.footer-links ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-links a {
    color: #555;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-links a:hover {
    color: #c122c1;
}

.social-links {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.social-icon {
    color: #555;
    font-size: 1.5rem;
    transition: color 0.3s;
}

.social-icon:hover {
    color: #c122c1;
}

.payment-methods {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.payment-methods img {
    height: 30px;
    width: auto;
}

.footer-bottom {
    text-align: center;
    padding: 1.5rem 0;
    border-top: 1px solid #ddd;
}

.legal-links {
    margin-bottom: 1rem;
}

.legal-links a {
    color: #555;
    text-decoration: none;
    margin: 0 1rem;
    transition: color 0.3s;
}

.legal-links a:hover {
    color: #c122c1;
}
/* ========== HEADER STYLES ========== */
.main-header {
    background-color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: relative;
    z-index: 1000;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
}

.logo {
    height: 50px;
    width: auto;
}

.nav-menu {
    display: flex;
    gap: 2rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-link {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: color 0.3s;
}

.nav-link:hover, .nav-link.active {
    color: #c122c1;
}

.register-btn {
    background: #c122c1;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s;
}

.register-btn:hover {
    background: #a01aa0;
}

.header-title {
    text-align: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
}

.header-title h1 {
    color: #c122c1;
    margin: 0;
    font-size: 2.2rem;
}
    </style>
</head>
<body>

    <!-- Votre header existant -->
    
    <!-- Remplacez votre header actuel par ceci -->
<header class="main-header">
    <div class="header-container">
        <nav class="navbar">
            <div class="logo-container">
                <img src="images/logo.png" alt="Logo Click'N'Go" class="logo">
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.html" class="nav-link">Accueil</a></li>
                <li class="nav-item"><a href="activite.html" class="nav-link">Activités</a></li>
                <li class="nav-item"><a href="events.html" class="nav-link">Événements</a></li>
                <li class="nav-item"><a href="Produits.html" class="nav-link">Produits</a></li>
                <li class="nav-item"><a href="transports.html" class="nav-link">Transports</a></li>
                <li class="nav-item"><a href="sponsors.html" class="nav-link active">Sponsors</a></li>
            </ul>
            
            <div class="auth-section">
                <a href="#" class="register-btn">Register</a>
            </div>
        </nav>
    </div>
    
    <div class="header-title">
        <h1>Donnez de la visibilité à votre marque dès aujourd'hui !</h1>
    </div>
</header>
    
    <!-- Votre vidéo -->
    <div class="video-container">
        <video autoplay loop muted playsinline>
            <source src="video1.mp4" type="video/mp4">
            Votre navigateur ne supporte pas la balise vidéo.
        </video>
    </div>
    
    <!-- Vos sponsors -->
    <div class="container">
        <h1>Nos sponsors</h1>
        <div class="grid">
            <div class="card">
            <img src="saida.jpeg" alt="saida">
            <div class="overlay"></div>
            <div class="card-content">
                <h2 class="card-title">Saida</h2>
            </div>
        </div>

        <div class="card">
            <img src="ooredoo.jpg" alt="ooredoo">
            <div class="overlay"></div>
            <div class="card-content">
                <h2 class="card-title">Ooredoo</h2>
            </div>
        </div>

        <div class="card">
            <img src="atb.jpg" alt="atb">
            <div class="overlay"></div>
            <div class="card-content">
                <h2 class="card-title">ATB</h2>
            </div>
        </div>

        <div class="card">
            <img src="pathe.jpeg" alt="pathe">
            <div class="overlay"></div>
            <div class="card-content">
                <h2 class="card-title">Pathé</h2>
            </div>
        </div>

        <div class="card">
            <img src="dabchy.png" alt="dabchy">
            <div class="overlay"></div>
            <div class="card-content">
                <h2 class="card-title">Dabchy</h2>
            </div>
        </div>
        </div>
    </div> 

    <!-- Vos sections principales -->
    <div class="container">
        <div class="options" id="options">
            <a href="#form-section" class="option-card">
                <img src="icon1.png" alt="Proposer">
                <h2>Proposer un Sponsoring</h2>
                <p>Soumettez votre propre proposition de sponsoring pour un événement</p>
            </a>
            <a href="#sponsorships-section" class="option-card">
                <img src="icon2.png" alt="Choisir">
                <h2>Choisir un Sponsoring</h2>
                <p>Sélectionnez parmi nos opportunités de sponsoring existantes</p>
            </a>
            <a href="#tracking-section" class="option-card">
                <img src="icon3.png" alt="Suivi">
                <h2>Suivi des Propositions</h2>
                <p>Consultez l'état de vos propositions de sponsoring</p>
            </a>
        </div>

        <div class="form-section" id="form-section">
    <h2>Proposer un Sponsoring</h2>
    <form method="post" action="addSponsor.php" id="sponsorForm" novalidate>
        <div class="form-group">
            <label for="companyName">Nom de l'entreprise</label>
            <input type="text" id="companyName" name="companyName" 
                   pattern="[A-Za-z0-9\u00C0-\u017F\s\-&]{2,100}" 
                   title="2-100 caractères alphanumériques" required>
            <small class="error-message"></small>
        </div>
        
        <div class="form-group">
            <label for="evenement">Événement</label>
            <input type="text" id="evenement" name="evenement" 
                   pattern="[A-Za-z0-9\u00C0-\u017F\s\-\.,]{5,150}" 
                   title="5-150 caractères" required>
            <small class="error-message"></small>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" 
                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
            <small class="error-message"></small>
        </div>
        
    <div class="form-group">
        <label for="phone">Téléphone</label>
        <input type="tel" id="phone" name="phone" 
               pattern="(\+216\s)?[0-9]{8}" 
               title="Format: +216 XXXXXXXX ou XXXXXXXX" required
               value="+216 " maxlength="13" />
        <small class="error-message"></small>
    </div>
        
        <div class="form-group">
            <label for="description">Description du sponsoring</label>
            <textarea id="description" name="description" rows="4" 
                      minlength="20" maxlength="1000" required></textarea>
            <small class="error-message"></small>
        </div>
        
        <div class="form-group">
            <label for="amount">Montant proposé (dt)</label>
            <input type="number" id="amount" name="amount" 
                   min="100" step="1" required>
            <small class="error-message"></small>
        </div>
        
        <div class="form-group">
            <label for="duration">Durée du sponsoring</label>
            <input type="text" id="duration" name="duration" 
                   pattern="[0-9]+\s*(mois|an|ans|jours|semaines)" 
                   placeholder="Ex: 3 mois, 1 an..." required>
            <small class="error-message"></small>
        </div>
        
        <div class="form-group">
            <label for="benefits">Avantages souhaités</label>
            <textarea id="benefits" name="benefits" rows="4" 
                      minlength="10" maxlength="500" 
                      placeholder="Ex: Logo sur les affiches, mentions sur les réseaux sociaux..." required></textarea>
            <small class="error-message"></small>
        </div>
        
        <button type="submit">Envoyer la proposition</button>
    </form>
</div>

<style>
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

<script>
    document.getElementById('sponsorForm').addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validation manuelle pour meilleur feedback
        document.querySelectorAll('#sponsorForm [required]').forEach(field => {
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

    // Validation en temps réel
    document.querySelectorAll('#sponsorForm input, #sponsorForm textarea').forEach(field => {
        field.addEventListener('input', function() {
            const errorMsg = this.nextElementSibling;
            if (!this.checkValidity()) {
                errorMsg.textContent = this.validationMessage || 'Valeur invalide';
                errorMsg.style.display = 'block';
            } else {
                errorMsg.style.display = 'none';
            }
        });
    });
</script>

        <!-- Section des sponsorings existants  -->
        <div class="sponsorships-section" id="sponsorships-section">
            <h2>Opportunités de Sponsoring Disponibles</h2>
            
            <div class="sponsorship-grid" id="events-grid">
                <?php
                require_once(__DIR__ . "/../../controller/controller.php");
                $controller = new sponsorController();
                $offers = $controller->listOffers();
                $displayedOffers = [];
                foreach ($offers as $offer) {
                    // Avoid duplicate display by checking unique id or title+event
                    $key = $offer['titre_offre'] . '|' . $offer['evenement'];
                    if (in_array($key, $displayedOffers)) {
                        continue;
                    }
                    $displayedOffers[] = $key;
                    echo '<div class="sponsorship-card" data-evenement="' . htmlspecialchars($offer['evenement']) . '">';
                    echo '<h3>' . htmlspecialchars($offer['titre_offre']) . '</h3>';
                    if (!empty($offer['image'])) {
                        echo '<img src="images/' . htmlspecialchars($offer['image']) . '" alt="Image de l\'offre" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px;" />';
                    } else {
                        echo '<img src="images/default.png" alt="Image par défaut" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px;" />';
                    }
                    echo '<p>' . htmlspecialchars($offer['description_offre']) . '</p>';
                    echo '<div class="sponsorship-footer">';
                    echo '<span>Événement: ' . htmlspecialchars($offer['evenement']) . '</span>';
                    echo '<span class="amount">' . htmlspecialchars($offer['montant_offre']) . ' dt</span>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div> 

        <!-- Section de suivi des propositions -->
        <div class="tracking-section" id="tracking-section">
    <h2>Suivi de vos Propositions</h2>
    
    <?php foreach ($propositions as $p): ?>
        <div class="proposal-card">
            <!-- Nom de l'entreprise en premier -->
            <h3><?= htmlspecialchars($p['nom_entreprise']) ?></h3>
            
            
            
            <div class="proposal-details">
                <!-- Avantage déplacé après le nom -->
                <p><strong>resultat:</strong> <?= htmlspecialchars($p['status']) ?> </p>
                <p><strong>Avantage proposé:</strong> <?= htmlspecialchars($p['avantage']) ?></p>
                <p><strong>Montant:</strong> <?= htmlspecialchars($p['montant']) ?> dt</p>
                
            </div>
            
            <div class="proposal-actions">
                <a class="button-secondary" href="modifier.php?id=<?= $p['id_sponsor'] ?>">Modifier</a>
                <a class="button-secondary" href="delete.php?id=<?= $p['id_sponsor'] ?>" onclick="return confirm('Supprimer cette proposition ?');">Supprimer</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    <!-- Votre footer existant -->
 <!-- Remplacez votre footer actuel par ceci -->
<footer class="main-footer">
    <div class="footer-container">
        <div class="newsletter-section">
            <div class="newsletter-content">
                <h2>Abonnez-vous à notre</h2>
                <h1>Click'N'Go</h1>
                <div class="newsletter-form">
                    <input type="email" placeholder="Entrez votre adresse e-mail">
                    <button class="newsletter-btn">Submit</button>
                </div>
            </div>
        </div>
        
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="images/logo.png" alt="Logo Click'N'Go" class="footer-logo">
                <div class="social-links">
                    <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h3>Moyens de paiement</h3>
                <div class="payment-methods">
                    <img src="visa.jpg" alt="Visa">
                    <img src="paypal.jpg" alt="PayPal">
                </div>
            </div>
            
            <div class="footer-links">
                <h3>À propos</h3>
                <ul>
                    <li><a href="#">À propos de click'N'go</a></li>
                    <li><a href="#">Presse</a></li>
                    <li><a href="#">Nous rejoindre</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h3>Liens utiles</h3>
                <ul>
                    <li><a href="#">Devenir partenaire</a></li>
                    <li><a href="#">FAQ - Besoin d'aide ?</a></li>
                    <li><a href="#">Tous les avis click'N'go</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <hr>
            <div class="legal-links">
                <a href="#">Conditions générales</a>
                <a href="#">Mentions légales</a>
            </div>
            <p>© click'N'go 2025 - tous droits réservés</p>
        </div>
    </div>
</footer>

    <script>
        // Removed JavaScript for filtering and modal as per request

        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            if (!phoneInput) return;

            // Set default value if empty or does not start with +216
            if (!phoneInput.value.startsWith('+216 ')) {
                phoneInput.value = '+216 ';
            }

            phoneInput.addEventListener('keydown', function(e) {
                const allowedKeys = [
                    'Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', 'Tab'
                ];
                // Allow control keys
                if (allowedKeys.includes(e.key)) {
                    return;
                }

                // Prevent typing before prefix
                if (phoneInput.selectionStart < 5) {
                    e.preventDefault();
                    phoneInput.setSelectionRange(5, 5);
                    return;
                }

                // Allow only digits after prefix
                if (!/\d/.test(e.key)) {
                    e.preventDefault();
                }

                // Limit length to 13 characters (+216 + 8 digits)
                if (phoneInput.value.length >= 13 && phoneInput.selectionStart === phoneInput.selectionEnd) {
                    e.preventDefault();
                }
            });

            phoneInput.addEventListener('input', function(e) {
                // Remove any non-digit characters after prefix
                let value = phoneInput.value;

                // Ensure prefix is intact
                if (!value.startsWith('+216 ')) {
                    value = '+216 ';
                }

                // Remove invalid characters after prefix
                let afterPrefix = value.slice(5).replace(/\D/g, '');

                // Limit to 8 digits
                afterPrefix = afterPrefix.slice(0, 8);

                phoneInput.value = '+216 ' + afterPrefix;
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sponsorshipCards = document.querySelectorAll('.sponsorship-card');
            const evenementInput = document.getElementById('evenement');

            sponsorshipCards.forEach(card => {
                card.addEventListener('click', () => {
                    const evenement = card.getAttribute('data-evenement');
                    if (evenementInput) {
                        evenementInput.value = evenement;
                        evenementInput.focus();
                    }
                    // Optionally scroll to the form
                    document.getElementById('form-section').scrollIntoView({ behavior: 'smooth' });
                });
            });
        });
    </script>
</body>
</html>
