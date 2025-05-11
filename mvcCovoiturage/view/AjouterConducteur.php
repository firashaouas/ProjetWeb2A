<?php
// clickngo/view/AjouterConducteur.php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$successMessage = '';
$errorMessage = '';

if (isset($_POST['submit'])) {
    error_log("Données POST reçues : " . print_r($_POST, true));
    $pdo = config::getConnexion();
    $testUserId = 12345; // ID utilisateur à tester
    $controller = new AnnonceCovoiturageController($pdo, $testUserId);
    
    // Filtrer et reformater les données
    $filteredData = [
        'prenom_conducteur' => $_POST['prenom_conducteur'] ?? '',
        'nom_conducteur' => $_POST['nom_conducteur'] ?? '',
        'tel_conducteur' => $_POST['tel_conducteur'] ?? '',
        'date_depart' => date('Y-m-d H:i:s', strtotime($_POST['date_depart'] ?? '')),
        'lieu_depart' => $_POST['lieu_depart'] ?? '',
        'lieu_arrivee' => $_POST['lieu_arrivee'] ?? '',
        'nombre_places' => (int)($_POST['nombre_places'] ?? 0),
        'type_voiture' => $_POST['type_voiture'] ?? '',
        'prix_estime' => (float)($_POST['prix_estime'] ?? 0),
        'description' => $_POST['description'] ?? ''
    ];
    error_log("Données filtrées : " . print_r($filteredData, true));
    
    try {
        $message = $controller->ajouterAnnonce($filteredData);
        error_log("Résultat ajouterAnnonce : " . $message);
        $successMessage = $message ?: 'Annonce ajoutée avec succès !';
        // Rediriger vers ListConducteurs.php après succès
        header('Location: ListConducteurs.php');
        exit;
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        error_log("Erreur dans ajouterAnnonce : " . $e->getMessage() . "\nTrace : " . $e->getTraceAsString());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire Conducteur - Click'N'go</title>
    <!-- Leaflet CSS and JS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 80px;
        }

        .container {
            display: flex;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 900px;
            width: 85%;
            overflow: hidden;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .preview {
            width: 40%;
            background: linear-gradient(135deg, #fff0f5, #f0f7ff);
            color: #333;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><circle cx="10" cy="10" r="2" fill="%23ff8fa3"/><circle cx="90" cy="90" r="3" fill="%23c084fc"/><circle cx="50" cy="20" r="2" fill="%23ff8fa3"/><circle cx="80" cy="50" r="2" fill="%23c084fc"/></svg>') repeat;
            z-index: 0;
        }

        .preview::after {
            content: '✨';
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 20px;
            opacity: 0.5;
            z-index: 1;
        }

        .preview img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            z-index: 1;
            border: 2px solid #fff;
        }

        .preview img:hover {
            transform: scale(1.08) rotate(1deg);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .preview h3 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            margin: 8px 0;
            text-align: center;
            color: #2d2d2d;
            z-index: 1;
            position: relative;
        }

        .preview h3::after {
            content: '';
            display: block;
            width: 30px;
            height: 2px;
            background: #ff8fa3;
            margin: 6px auto;
            border-radius: 1px;
        }

        .preview .price {
            font-size: 18px;
            font-weight: 500;
            color: #fff;
            background: linear-gradient(45deg, #ff8fa3, #c084fc);
            padding: 6px 15px;
            border-radius: 15px;
            z-index: 1;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .preview .price:hover {
            transform: translateY(-2px);
        }

        .location-form {
            width: 60%;
            padding: 30px;
            background: #fff;
            transition: transform 0.3s ease;
        }

        .location-form:hover {
            transform: scale(1.02);
        }

        .location-form h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #2d2d2d;
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .location-form h2::after {
            content: '';
            width: 50px;
            height: 2px;
            background: #ff8fa3;
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .location-form label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .location-form input[type="text"],
        .location-form input[type="datetime-local"],
        .location-form input[type="tel"],
        .location-form input[type="number"],
        .location-form select,
        .location-form textarea {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 14px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        .location-form input:focus,
        .location-form select:focus,
        .location-form textarea:focus {
            border-color: #ff8fa3;
            box-shadow: 0 0 0 2px rgba(255, 143, 163, 0.2);
            outline: none;
            background: #fff;
        }

        .location-form textarea {
            min-height: 80px;
            resize: vertical;
        }

        .location-form button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .register-btn {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 143, 163, 0.4);
        }

        .error {
            color: #e63946;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .invalid {
            border-color: #e63946;
            box-shadow: 0 0 0 2px rgba(230, 57, 70, 0.2);
        }

        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 1000;
            display: none;
            border: 2px solid #4CAF50;
        }

        .popup.show {
            display: block;
        }

        .popup .icon {
            font-size: 30px;
            color: #4CAF50;
            margin-bottom: 8px;
        }

        .popup h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 20px;
            color: #333;
            margin-bottom: 8px;
        }

        .popup p {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .popup button {
            padding: 8px 15px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .popup button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(255, 143, 163, 0.3);
        }

        .popup.error {
            border: 2px solid #e63946;
        }

        .popup.error .icon {
            color: #e63946;
        }

        .popup.error button {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
        }

        .popup.error button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(255, 143, 163, 0.3);
        }

        #mapModal {
            position: fixed;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            z-index: 9999;
            background: white;
            border: 2px solid #ccc;
            border-radius: 8px;
            display: none;
        }

        #map {
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }

        #mapSearchInput {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 200px;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
        }

        #mapSearchSuggestions {
            position: absolute;
            top: 30px;
            left: 8px;
            width: 200px;
            max-height: 16px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
            display: none;
        }

        #mapSearchSuggestions div {
            border-bottom: 1px solid #eee;
            padding: 6px;
            cursor: pointer;
        }

        #mapSearchSuggestions div:hover {
            background: #f0f0f0;
        }

        #mapSearchSuggestions div:last-child {
            border-bottom: none;
        }

        #mapSearchInput:focus + #mapSearchSuggestions,
        #mapSearchSuggestions:hover {
            display: block;
        }

        #loadingSpinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10000;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ff8fa3;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .close-map-button {
            background: #ff6666;
            color: white;
            width: 25px;
            height: 25px;
            text-align: center;
            line-height: 25px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 4px rgba(0,0,0,0.3);
            z-index: 1000;
            margin-right: 8px;
            margin-top: 8px;
        }

        .close-map-button:hover {
            background: #e55a5a;
            transform: scale(1.1);
            transition: all 0.2s ease;
        }

        .location-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .location-icon {
            position: absolute;
            right: 8px;
            cursor: pointer;
            font-size: 18px;
            color: #ff8fa3;
        }

        .price-suggestion {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .comfort-option {
            display: flex;
            align-items: center;
            margin-top: 8px;
        }

        .comfort-option label {
            margin: 0 0 0 8px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 95%;
            }

            .preview,
            .location-form {
                width: 100%;
            }

            .preview img {
                width: 150px;
                height: 150px;
            }

            #mapModal {
                top: 5%;
                left: 5%;
                width: 90%;
                height: 90%;
            }
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="container">
            <div class="preview">
                <img src="369239694_e90328ae-f885-42c1-a65b-731ecc49cc7d.jpg" alt="Car Image">
                <h3>Aperçu de l'annonce</h3>
                <div class="price" id="preview-prix_estime">-</div>
                <link rel="stylesheet" href="/clickngo/public/css/style.css">
            </div>
            <div class="location-form">
                <h2>Proposer un covoiturage</h2>
                <form method="post" action="" id="conducteurForm">
                    <label for="prenom_conducteur">Prénom Conducteur</label>
                    <input type="text" name="prenom_conducteur" id="prenom_conducteur" value="<?php echo htmlspecialchars($_POST['prenom_conducteur'] ?? ''); ?>" required>
                    <div class="error" id="prenom_conducteur-error">Prénom invalide (minimum 3 caractères alphabétiques)</div>

                    <label for="nom_conducteur">Nom Conducteur</label>
                    <input type="text" name="nom_conducteur" id="nom_conducteur" value="<?php echo htmlspecialchars($_POST['nom_conducteur'] ?? ''); ?>" required>
                    <div class="error" id="nom_conducteur-error">Nom invalide (minimum 3 caractères alphabétiques)</div>

                    <label for="tel_conducteur">Téléphone Conducteur</label>
                    <input type="tel" name="tel_conducteur" id="tel_conducteur" value="<?php echo htmlspecialchars($_POST['tel_conducteur'] ?? ''); ?>" required>
                    <div class="error" id="tel_conducteur-error">Numéro de téléphone invalide (8 chiffres requis)</div>

                    <label for="type_voiture">Type de Voiture</label>
                    <input type="text" name="type_voiture" id="type_voiture" value="<?php echo htmlspecialchars($_POST['type_voiture'] ?? ''); ?>" required>
                    <div class="error" id="type_voiture-error">Type de voiture invalide (minimum 2 caractères alphabétiques)</div>

                    <label for="lieu_depart">Lieu de Départ</label>
                    <div class="location-input-container">
                        <input type="text" name="lieu_depart" id="lieu_depart" value="<?php echo htmlspecialchars($_POST['lieu_depart'] ?? ''); ?>" required readonly>
                        <input type="hidden" name="lieu_depart_coords" id="lieu_depart_coords">
                        <span class="location-icon" onclick="showMap('lieu_depart')">📍</span>
                    </div>
                    <div class="error" id="lieu_depart-error">Le lieu de départ est requis (minimum 2 caractères)</div>

                    <label for="lieu_arrivee">Lieu d'Arrivée</label>
                    <div class="location-input-container">
                        <input type="text" name="lieu_arrivee" id="lieu_arrivee" value="<?php echo htmlspecialchars($_POST['lieu_arrivee'] ?? ''); ?>" required readonly>
                        <input type="hidden" name="lieu_arrivee_coords" id="lieu_arrivee_coords">
                        <span class="location-icon" onclick="showMap('lieu_arrivee')">📍</span>
                    </div>
                    <div class="error" id="lieu_arrivee-error">Le lieu d'arrivée est requis (minimum 2 caractères)</div>

                    <label for="date_depart">Date de Départ</label>
                    <input type="datetime-local" name="date_depart" id="date_depart" value="<?php echo htmlspecialchars($_POST['date_depart'] ?? ''); ?>" required>
                    <div class="error" id="date_depart-error">La date de départ doit être dans le futur</div>

                    <label for="nombre_places">Nombre de Places</label>
                    <input type="number" name="nombre_places" id="nombre_places" min="1" max="4" value="<?php echo htmlspecialchars($_POST['nombre_places'] ?? ''); ?>" required>
                    <div class="error" id="nombre_places-error">Nombre de places invalide (1-4)</div>

                    <label for="prix_estime">Prix Estimé (TND)</label>
                    <input type="number" name="prix_estime" id="prix_estime" step="0.01" value="<?php echo htmlspecialchars($_POST['prix_estime'] ?? ''); ?>" required>
                    <div class="error" id="prix_estime-error">Prix estimé invalide (doit être positif)</div>
                    <div class="price-suggestion" id="price-suggestion">Prix suggéré : - TND</div>

                    <div class="comfort-option">
                        <input type="checkbox" name="comfort_extra" id="comfort_extra">
                        <label for="comfort_extra">Confort supplémentaire (+5 TND)</label>
                    </div>

                    <label for="description">Description</label>
                    <textarea name="description" id="description" placeholder="Description (optionnel)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

                    <button type="submit" name="submit" class="register-btn">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
    
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
                    <img src="images/mastercard-v2.webp" alt="Mastercard">
                    <img src="images/logo-cb.webp" alt="CB" class="cb-logo">
                    <img src="images/paypal.webp" alt="Paypal" class="paypal">
                </div>
            </div>

            <div class="links">
                <p>À propos</p>
                <a href="/clickngo/view/about.php">À propos </a>
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

    <div class="popup" id="successPopup">
        <div class="icon">✔</div>
        <h3>Succès!</h3>
        <p id="successMessage"><?php echo htmlspecialchars($successMessage); ?></p>
        <button onclick="redirectToDisplay()">Fermer</button>
    </div>

    <div class="popup error" id="errorPopup">
        <div class="icon">❌</div>
        <h3>Erreur</h3>
        <p id="errorMessage"><?php echo htmlspecialchars($errorMessage); ?></p>
        <button onclick="closePopup()">Fermer</button>
    </div>

    <div id="mapModal">
        <input id="mapSearchInput" type="text" placeholder="Rechercher une ville en Tunisie">
        <div id="mapSearchSuggestions"></div>
        <div id="map"></div>
    </div>

    <div id="loadingSpinner"></div>

    <script>
        let map;
        let marker;
        let locationMarker;
        let currentInputId = null;

        function initializeMap() {
            const tunisiaBounds = [
                [30.2, 7.5],
                [37.4, 11.6]
            ];

            map = L.map('map', {
                center: [36.8065, 10.1815],
                zoom: 7,
                maxBounds: tunisiaBounds,
                maxBoundsViscosity: 1.0,
                minZoom: 6,
                maxZoom: 12
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const redPin = L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            const tunisianLocations = [
                {name: "Tunis", lat: 36.8065, lng: 10.1815},
                {name: "La Marsa", lat: 36.8782, lng: 10.3247},
                {name: "Sidi Bou Saïd", lat: 36.8687, lng: 10.3416},
                {name: "Carthage", lat: 36.8545, lng: 10.3306},
                {name: "Le Bardo", lat: 36.8092, lng: 10.1333},
                {name: "La Goulette", lat: 36.8185, lng: 10.3052},
                {name: "Hammam-Lif", lat: 36.7333, lng: 10.3333},
                {name: "Radès", lat: 36.7667, lng: 10.2833},
                {name: "El Omrane", lat: 36.8167, lng: 10.1667},
                {name: "El Kabaria", lat: 36.8333, lng: 10.2000},
                {name: "Sidi Hassine", lat: 36.8000, lng: 10.1000},
                {name: "Ezzouhour", lat: 36.8167, lng: 10.1333},
                {name: "Sebkhet Sejoumi", lat: 36.7833, lng: 10.1333},
                {name: "Ariana", lat: 36.8665, lng: 10.1647},
                {name: "Raoued", lat: 36.9333, lng: 10.1500},
                {name: "Kalâat el-Andalous", lat: 37.0667, lng: 10.1167},
                {name: "Sidi Thabet", lat: 36.9000, lng: 10.0333},
                {name: "La Soukra", lat: 36.8833, lng: 10.2500},
                {name: "Ettadhamen", lat: 36.8500, lng: 10.1000},
                {name: "Mnihla", lat: 36.8500, lng: 10.1667},
                {name: "Ben Arous", lat: 36.7435, lng: 10.2319},
                {name: "Boumhel", lat: 36.7333, lng: 10.2833},
                {name: "El Mourouj", lat: 36.7333, lng: 10.2000},
                {name: "Ezzahra", lat: 36.7333, lng: 10.2500},
                {name: "Hammam Chott", lat: 36.7000, lng: 10.3000},
                {name: "Mégrine", lat: 36.7667, lng: 10.2333},
                {name: "Mohamedia", lat: 36.6833, lng: 10.1500},
                {name: "Mornag", lat: 36.6833, lng: 10.2833},
                {name: "Manouba", lat: 36.8081, lng: 10.0972},
                {name: "Borj El Amri", lat: 36.7167, lng: 9.9333},
                {name: "Den Den", lat: 36.8667, lng: 10.0167},
                {name: "Douar Hicher", lat: 36.8333, lng: 10.0500},
                {name: "El Battan", lat: 36.8000, lng: 9.8333},
                {name: "Jedaida", lat: 36.8500, lng: 10.0167},
                {name: "Mornaguia", lat: 36.7500, lng: 10.0167},
                {name: "Oued Ellil", lat: 36.8333, lng: 10.0333},
                {name: "Tebourba", lat: 36.8167, lng: 9.8500},
                {name: "Bizerte", lat: 37.2744, lng: 9.8739},
                {name: "Menzel Bourguiba", lat: 37.1536, lng: 9.7878},
                {name: "Menzel Jemil", lat: 37.2333, lng: 9.9167},
                {name: "Mateur", lat: 37.0500, lng: 9.6667},
                {name: "Ras Jebel", lat: 37.2167, lng: 10.1167},
                {name: "Sejnane", lat: 37.0500, lng: 9.2333},
                {name: "Tinja", lat: 37.1667, lng: 9.7500},
                {name: "Ghar El Melh", lat: 37.1667, lng: 10.1833},
                {name: "Utique", lat: 37.0500, lng: 10.0500},
                {name: "El Alia", lat: 37.1667, lng: 10.0333},
                {name: "Raf Raf", lat: 37.1833, lng: 10.1833},
                {name: "Nabeul", lat: 36.4561, lng: 10.7376},
                {name: "Hammamet", lat: 36.4000, lng: 10.6167},
                {name: "Kelibia", lat: 36.8475, lng: 11.0939},
                {name: "Korba", lat: 36.5786, lng: 10.8586},
                {name: "Menzel Temime", lat: 36.7833, lng: 10.9833},
                {name: "Beni Khalled", lat: 36.6500, lng: 10.6000},
                {name: "Beni Khiar", lat: 36.4667, lng: 10.7833},
                {name: "Bou Argoub", lat: 36.5333, lng: 10.5500},
                {name: "Dar Chaabane", lat: 36.4667, lng: 10.7500},
                {name: "El Haouaria", lat: 37.0500, lng: 11.0167},
                {name: "Soliman", lat: 36.7000, lng: 10.4833},
                {name: "Takelsa", lat: 36.7833, lng: 10.6333},
                {name: "Grombalia", lat: 36.6000, lng: 10.5000},
                {name: "Béja", lat: 36.7256, lng: 9.1817},
                {name: "Amdoun", lat: 36.8167, lng: 9.1167},
                {name: "Goubellat", lat: 36.5333, lng: 9.6667},
                {name: "Medjez el-Bab", lat: 36.6500, lng: 9.6000},
                {name: "Nefza", lat: 36.9333, lng: 9.0500},
                {name: "Téboursouk", lat: 36.4500, lng: 9.2500},
                {name: "Testour", lat: 36.5500, lng: 9.4500},
                {name: "Thibar", lat: 36.5000, lng: 9.1333},
                {name: "Jendouba", lat: 36.5012, lng: 8.7804},
                {name: "Ain Draham", lat: 36.7667, lng: 8.6833},
                {name: "Balta-Bou Aouane", lat: 36.5833, lng: 8.5000},
                {name: "Bou Salem", lat: 36.6167, lng: 8.9667},
                {name: "Fernana", lat: 36.6500, lng: 8.7000},
                {name: "Ghardimaou", lat: 36.4500, lng: 8.4333},
                {name: "Oued Mliz", lat: 36.4667, lng: 8.5667},
                {name: "Tabarka", lat: 36.9544, lng: 8.7581},
                {name: "Zaghouan", lat: 36.4029, lng: 10.1429},
                {name: "Bir Mcherga", lat: 36.5000, lng: 10.0667},
                {name: "El Fahs", lat: 36.3667, lng: 9.9000},
                {name: "Nadhour", lat: 36.3167, lng: 10.1500},
                {name: "Saouaf", lat: 36.2833, lng: 10.1167},
                {name: "Zriba", lat: 36.3333, lng: 10.0833},
                {name: "Siliana", lat: 36.0849, lng: 9.3708},
                {name: "Bargou", lat: 36.0833, lng: 9.6167},
                {name: "Bou Arada", lat: 36.3500, lng: 9.6167},
                {name: "El Aroussa", lat: 36.2667, lng: 9.4667},
                {name: "Gaâfour", lat: 36.3333, lng: 9.3167},
                {name: "Kesra", lat: 35.8167, lng: 9.3667},
                {name: "Makthar", lat: 35.8500, lng: 9.2000},
                {name: "Rouhia", lat: 35.7667, lng: 9.2833},
                {name: "El Kef", lat: 36.1822, lng: 8.7147},
                {name: "Dahmani", lat: 35.9500, lng: 8.8333},
                {name: "Jérissa", lat: 35.9167, lng: 8.5833},
                {name: "El Ksour", lat: 35.8833, lng: 8.8833},
                {name: "Kalâat Khasba", lat: 35.8167, lng: 8.6667},
                {name: "Kalâat Senan", lat: 35.8167, lng: 8.4667},
                {name: "Nebeur", lat: 36.2833, lng: 8.7667},
                {name: "Sakiet Sidi Youssef", lat: 36.2167, lng: 8.3500},
                {name: "Tajerouine", lat: 35.8833, lng: 8.5500},
                {name: "Sousse", lat: 35.8254, lng: 10.6360},
                {name: "Akouda", lat: 35.8667, lng: 10.5667},
                {name: "Bouficha", lat: 36.2667, lng: 10.4500},
                {name: "Enfidha", lat: 36.1333, lng: 10.3833},
                {name: "Hammam Sousse", lat: 35.8500, lng: 10.5833},
                {name: "Hergla", lat: 36.0333, lng: 10.5000},
                {name: "Kalâa Kebira", lat: 35.8667, lng: 10.5333},
                {name: "Kalâa Seghira", lat: 35.8167, lng: 10.5667},
                {name: "Kondar", lat: 35.8833, lng: 10.5833},
                {name: "Msaken", lat: 35.7333, lng: 10.5833},
                {name: "Sidi Bou Ali", lat: 35.9500, lng: 10.4167},
                {name: "Sidi El Hani", lat: 35.6667, lng: 10.3167},
                {name: "Monastir", lat: 35.7643, lng: 10.8113},
                {name: "Bekalta", lat: 35.6167, lng: 10.9833},
                {name: "Bembla", lat: 35.6833, lng: 10.8000},
                {name: "Beni Hassen", lat: 35.5667, lng: 10.8167},
                {name: "Jemmal", lat: 35.6333, lng: 10.7667},
                {name: "Ksar Hellal", lat: 35.6500, lng: 10.9000},
                {name: "Ksibet el-Médiouni", lat: 35.6833, lng: 10.8500},
                {name: "Moknine", lat: 35.6333, lng: 10.9667},
                {name: "Ouerdanine", lat: 35.6667, lng: 10.6667},
                {name: "Sayada", lat: 35.6667, lng: 10.8833},
                {name: "Téboulba", lat: 35.6333, lng: 10.9333},
                {name: "Zéramdine", lat: 35.5667, lng: 10.7333},
                {name: "Mahdia", lat: 35.5047, lng: 11.0622},
                {name: "Bou Merdes", lat: 35.3833, lng: 10.9833},
                {name: "Chebba", lat: 35.2333, lng: 11.1167},
                {name: "Chorbane", lat: 35.2833, lng: 10.3833},
                {name: "El Jem", lat: 35.3000, lng: 10.7167},
                {name: "Hbira", lat: 35.5000, lng: 11.0333},
                {name: "Ksour Essef", lat: 35.4167, lng: 10.9833},
                {name: "Melloulèche", lat: 35.1667, lng: 11.0333},
                {name: "Ouled Chamekh", lat: 35.3667, lng: 10.3333},
                {name: "Rejiche", lat: 35.4333, lng: 10.9167},
                {name: "Sidi Alouane", lat: 35.3833, lng: 10.9333},
                {name: "Kairouan", lat: 35.6712, lng: 10.1006},
                {name: "Bou Hajla", lat: 35.6333, lng: 10.1333},
                {name: "Chebika", lat: 35.7667, lng: 9.9667},
                {name: "Echrarda", lat: 35.6333, lng: 9.7667},
                {name: "Haffouz", lat: 35.6333, lng: 9.6667},
                {name: "Hajeb El Ayoun", lat: 35.6833, lng: 9.8000},
                {name: "Nasrallah", lat: 35.3667, lng: 9.8667},
                {name: "Oueslatia", lat: 35.8667, lng: 9.5333},
                {name: "Sbikha", lat: 35.9333, lng: 10.0167},
                {name: "Kasserine", lat: 35.1676, lng: 8.8365},
                {name: "Fériana", lat: 34.9500, lng: 8.5667},
                {name: "Foussana", lat: 35.3333, lng: 8.6167},
                {name: "Haïdra", lat: 35.5667, lng: 8.4667},
                {name: "Jedelienne", lat: 35.2000, lng: 8.7500},
                {name: "Majel Bel Abbès", lat: 35.0833, lng: 8.7500},
                {name: "Sbeïtla", lat: 35.2333, lng: 9.1167},
                {name: "Sbiba", lat: 35.5333, lng: 9.0667},
                {name: "Thala", lat: 35.5667, lng: 8.6667},
                {name: "Sidi Bouzid", lat: 35.0383, lng: 9.4848},
                {name: "Bir El Hafey", lat: 34.9333, lng: 9.2000},
                {name: "Cebbala", lat: 35.2500, lng: 9.2500},
                {name: "Jilma", lat: 35.2667, lng: 9.4167},
                {name: "Meknassy", lat: 34.9833, lng: 9.5667},
                {name: "Menzel Bouzaiane", lat: 35.1667, lng: 9.4833},
                {name: "Mezzouna", lat: 34.5833, lng: 9.8333},
                {name: "Ouled Haffouz", lat: 35.1667, lng: 9.6333},
                {name: "Regueb", lat: 34.8500, lng: 9.7833},
                {name: "Sidi Ali Ben Aoun", lat: 35.0167, lng: 9.5667},
                {name: "Sfax", lat: 34.7406, lng: 10.7603},
                {name: "Agareb", lat: 34.7333, lng: 10.5333},
                {name: "Bir Ali Ben Khalifa", lat: 34.7333, lng: 10.0833},
                {name: "El Amra", lat: 34.6667, lng: 10.5833},
                {name: "El Hencha", lat: 34.4667, lng: 10.4500},
                {name: "Graïba", lat: 34.6500, lng: 10.5000},
                {name: "Jebiniana", lat: 34.6333, lng: 10.7500},
                {name: "Kerkennah Islands", lat: 34.7000, lng: 11.2000},
                {name: "Mahres", lat: 34.5333, lng: 10.5000},
                {name: "Sakiet Eddaier", lat: 34.7667, lng: 10.6833},
                {name: "Sakiet Ezzit", lat: 34.7500, lng: 10.7500},
                {name: "Skhira", lat: 34.3000, lng: 10.0667},
                {name: "Thyna", lat: 34.6667, lng: 10.7000},
                {name: "Gabès", lat: 33.8815, lng: 10.0983},
                {name: "Ghannouch", lat: 33.9333, lng: 10.0667},
                {name: "El Hamma", lat: 33.8917, lng: 9.7967},
                {name: "Matmata", lat: 33.5500, lng: 9.9667},
                {name: "Métouia", lat: 33.9667, lng: 10.0000},
                {name: "Nouvelle Matmata", lat: 33.8833, lng: 9.8500},
                {name: "Oudhref", lat: 33.8167, lng: 10.0333},
                {name: "Medenine", lat: 33.3549, lng: 10.5055},
                {name: "Ajim", lat: 33.7167, lng: 10.7500},
                {name: "Ben Gardane", lat: 33.1333, lng: 11.2167},
                {name: "Beni Khedache", lat: 33.2500, lng: 10.2000},
                {name: "Houmt Souk", lat: 33.8667, lng: 10.8500},
                {name: "Midoun", lat: 33.8167, lng: 10.9833},
                {name: "Zarzis", lat: 33.5000, lng: 11.1167},
                {name: "Sidi Makhlouf", lat: 33.3500, lng: 10.4833},
                {name: "Tataouine", lat: 32.9297, lng: 10.4510},
                {name: "Bir Lahmar", lat: 32.8000, lng: 10.6333},
                {name: "Dehiba", lat: 32.0167, lng: 10.7000},
                {name: "Ghomrassen", lat: 33.0667, lng: 10.3333},
                {name: "Remada", lat: 32.3167, lng: 10.4000},
                {name: "Smâr", lat: 33.2333, lng: 10.5000},
                {name: "Gafsa", lat: 34.4229, lng: 8.7841},
                {name: "El Guettar", lat: 34.3333, lng: 8.9500},
                {name: "El Ksar", lat: 34.4167, lng: 8.8000},
                {name: "Mdhilla", lat: 34.2833, lng: 8.7500},
                {name: "Métlaoui", lat: 34.3333, lng: 8.4000},
                {name: "Moulares", lat: 34.3167, lng: 8.2667},
                {name: "Redeyef", lat: 34.3833, lng: 8.1500},
                {name: "Sened", lat: 34.9333, lng: 10.2833},
                {name: "Tozeur", lat: 33.9197, lng: 8.1335},
                {name: "Degache", lat: 33.9833, lng: 8.2167},
                {name: "Hazoua", lat: 33.9333, lng: 7.8667},
                {name: "Nefta", lat: 33.8667, lng: 7.8833},
                {name: "Tamerza", lat: 34.2167, lng: 7.9333},
                {name: "Kebili", lat: 33.7000, lng: 8.9667},
                {name: "Douz", lat: 33.4667, lng: 9.0167},
                {name: "Faouar", lat: 33.6833, lng: 9.0167},
                {name: "Souk Lahad", lat: 33.8333, lng: 9.0167}
            ];

            const closeButton = L.control({position: 'topright'});
            closeButton.onAdd = function() {
                const div = L.DomUtil.create('div', 'close-map-button');
                div.innerHTML = '×';
                div.title = 'Close map';
                div.onclick = function() {
                    document.getElementById("mapModal").style.display = "none";
                    document.getElementById("loadingSpinner").style.display = "none";
                };
                return div;
            };
            closeButton.addTo(map);

            tunisianLocations.forEach(location => {
                const marker = L.marker([location.lat, location.lng], {
                    icon: redPin,
                    title: location.name
                }).addTo(map);

                marker.on('click', function() {
                    document.getElementById(currentInputId).value = location.name;
                    document.getElementById(currentInputId + '_coords').value = `${location.lat.toFixed(5)}, ${location.lng.toFixed(5)}`;
                    document.getElementById("mapModal").style.display = "none";
                    document.getElementById("loadingSpinner").style.display = "none";
                    suggestPrice();
                });
            });

            const loadingSpinner = document.getElementById("loadingSpinner");
            map.on('tilesloaded', function() {
                loadingSpinner.style.display = "none";
            });

            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                if (lat >= 30.2 && lat <= 37.4 && lng >= 7.5 && lng <= 11.6) {
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng], {icon: redPin}).addTo(map);
                    reverseGeocode(lat, lng, currentInputId);
                    document.getElementById("mapModal").style.display = "none";
                    loadingSpinner.style.display = "none";
                } else {
                    alert("Veuillez sélectionner un point en Tunisie.");
                    loadingSpinner.style.display = "none";
                }
            });

            map.fitBounds(tunisiaBounds);
        }

        function showMap(inputId) {
            if (!['lieu_depart', 'lieu_arrivee'].includes(inputId)) {
                console.error('Invalid inputId:', inputId);
                document.getElementById("loadingSpinner").style.display = "none";
                return;
            }

            currentInputId = inputId;
            const mapModal = document.getElementById("mapModal");
            const loadingSpinner = document.getElementById("loadingSpinner");
            mapModal.style.display = "block";
            loadingSpinner.style.display = "block";

            if (!map) {
                initializeMap();
            } else {
                loadingSpinner.style.display = "none";
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        if (lat >= 30.2 && lat <= 37.4 && lng >= 7.5 && lng <= 11.6) {
                            map.setView([lat, lng], 10);
                            if (locationMarker) map.removeLayer(locationMarker);
                            locationMarker = L.marker([lat, lng], {
                                icon: L.divIcon({
                                    className: 'location-icon',
                                    html: '<i class="fas fa-location-dot fa-2x" style="color:blue;"></i>',
                                    iconSize: [24, 24],
                                    iconAnchor: [12, 24]
                                })
                            }).addTo(map);
                        } else {
                            map.setView([36.8065, 10.1815], 7);
                        }
                        loadingSpinner.style.display = "none";
                    },
                    (error) => {
                        map.setView([36.8065, 10.1815], 7);
                        loadingSpinner.style.display = "none";
                        alert("Impossible d'obtenir votre localisation. Centrage sur Tunis.");
                    }
                );
            } else {
                map.setView([36.8065, 10.1815], 7);
                loadingSpinner.style.display = "none";
            }

            setTimeout(() => {
                loadingSpinner.style.display = "none";
            }, 2000);
        }

        function reverseGeocode(lat, lng, inputId) {
            if (!['lieu_depart', 'lieu_arrivee'].includes(inputId)) {
                console.error('Invalid inputId in reverseGeocode:', inputId);
                document.getElementById("loadingSpinner").style.display = "none";
                return;
            }

            const inputElement = document.getElementById(inputId);
            const coordsElement = document.getElementById(inputId + '_coords');
            const loadingSpinner = document.getElementById("loadingSpinner");

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=10&accept-language=fr`, {
                headers: {
                    'User-Agent': 'ClickNGo/1.0 (contact@yourdomain.com)'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.address && (data.address.city || data.address.town)) {
                    inputElement.value = data.address.city || data.address.town;
                    coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                } else {
                    inputElement.value = 'Ville non trouvée';
                    coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                }
                document.getElementById("mapModal").style.display = "none";
                loadingSpinner.style.display = "none";
                suggestPrice();
            })
            .catch(error => {
                inputElement.value = 'Erreur de recherche';
                coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                loadingSpinner.style.display = "none";
                suggestPrice();
            });
        }

        function setupSearch() {
            const searchInput = document.getElementById('mapSearchInput');
            const suggestions = document.getElementById('mapSearchSuggestions');
            let debounceTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                const query = this.value.trim();
                suggestions.innerHTML = '';

                if (query.length < 3) return;

                debounceTimeout = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=tn&q=${encodeURIComponent(query)}&place=city&accept-language=fr`, {
                        headers: {
                            'User-Agent': 'ClickNGo/1.0 (contact@yourdomain.com)'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        suggestions.innerHTML = '';
                        data.forEach(item => {
                            if (item.type === 'city' || item.type === 'town') {
                                const div = document.createElement('div');
                                div.className = 'p-2 hover:bg-gray-200 cursor-pointer';
                                div.textContent = item.display_name;
                                div.addEventListener('click', () => {
                                    const lat = parseFloat(item.lat);
                                    const lng = parseFloat(item.lon);
                                    map.setView([lat, lng], 10);
                                    if (marker) map.removeLayer(marker);
                                    marker = L.marker([lat, lng]).addTo(map);
                                    reverseGeocode(lat, lng, currentInputId);
                                    document.getElementById("mapModal").style.display = "none";
                                    document.getElementById("loadingSpinner").style.display = "none";
                                    suggestions.innerHTML = '';
                                    searchInput.value = '';
                                    suggestPrice();
                                });
                                suggestions.appendChild(div);
                            }
                        });
                        if (suggestions.innerHTML === '') {
                            suggestions.innerHTML = '<div class="p-2 text-gray-500">Aucune ville trouvée</div>';
                        }
                    })
                    .catch(error => {
                        suggestions.innerHTML = '<div class="p-2 text-red-500">Erreur lors de la recherche</div>';
                    });
                }, 300);
            });

            searchInput.addEventListener('blur', () => {
                setTimeout(() => suggestions.innerHTML = '', 200);
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 3) {
                    suggestions.style.display = 'block';
                }
            });
        }

        const prixInput = document.getElementById('prix_estime');
        const prixPreview = document.getElementById('preview-prix_estime');
        const successPopup = document.getElementById('successPopup');
        const errorPopup = document.getElementById('errorPopup');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');

        prixInput.addEventListener('input', () => {
            prixPreview.textContent = prixInput.value ? `${prixInput.value} TND` : '-';
        });

        function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function suggestPrice() {
            const lieuDepart = document.getElementById('lieu_depart').value;
            const lieuArrivee = document.getElementById('lieu_arrivee').value;
            const departCoords = document.getElementById('lieu_depart_coords').value;
            const arriveeCoords = document.getElementById('lieu_arrivee_coords').value;
            const comfortExtra = document.getElementById('comfort_extra').checked;
            const priceSuggestion = document.getElementById('price-suggestion');
            const prixPreview = document.getElementById('preview-prix_estime');

            if (!lieuDepart || !lieuArrivee || !departCoords || !arriveeCoords) {
                priceSuggestion.textContent = 'Prix suggéré : - TND';
                prixPreview.textContent = '-';
                return;
            }

            const [departLat, departLng] = departCoords.split(',').map(coord => parseFloat(coord.trim()));
            const [arriveeLat, arriveeLng] = arriveeCoords.split(',').map(coord => parseFloat(coord.trim()));

            if (isNaN(departLat) || isNaN(departLng) || isNaN(arriveeLat) || isNaN(arriveeLng)) {
                priceSuggestion.textContent = 'Prix suggéré : - TND (coordonnées invalides)';
                prixPreview.textContent = '-';
                return;
            }

            const distanceKm = calculateHaversineDistance(departLat, departLng, arriveeLat, arriveeLng);
            const basePrice = 5.00;
            const perKmRate = 0.10;
            let totalPrice = basePrice + (distanceKm * perKmRate);

            if (comfortExtra) {
                totalPrice += 5.00;
            }

            totalPrice = Math.max(totalPrice, 5.00).toFixed(2);
            priceSuggestion.textContent = `Prix suggéré : ${totalPrice} TND (~${Math.round(distanceKm)} km)`;
            prixPreview.textContent = `${totalPrice} TND`;
            document.getElementById('prix_estime').value = totalPrice;
        }

        document.getElementById('lieu_depart').addEventListener('input', suggestPrice);
        document.getElementById('lieu_arrivee').addEventListener('input', suggestPrice);
        document.getElementById('comfort_extra').addEventListener('change', suggestPrice);

        function showSuccessPopup() {
            successPopup.classList.add('show');
            setTimeout(() => {
                redirectToDisplay();
            }, 2000);
        }

        function showErrorPopup(message) {
            errorMessage.textContent = message || 'Veuillez remplir tous les champs obligatoires correctement';
            errorPopup.classList.add('show');
        }

        function closePopup() {
            successPopup.classList.remove('show');
            errorPopup.classList.remove('show');
        }

        function redirectToDisplay() {
            window.location.href = 'ListConducteurs.php';
        }

        window.addEventListener('DOMContentLoaded', () => {
            setupSearch();
            <?php if (!empty($successMessage)): ?>
                showSuccessPopup();
            <?php elseif (!empty($errorMessage)): ?>
                showErrorPopup('<?php echo htmlspecialchars($errorMessage); ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>