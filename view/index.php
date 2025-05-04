<?php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$annonces = [];

try {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);

    // Récupérer les paramètres de recherche
    $depart = isset($_GET['depart']) ? trim($_GET['depart']) : '';
    $arrivee = isset($_GET['arrivee']) ? trim($_GET['arrivee']) : '';

    // Log the received parameters
    error_log("resultats.php received: depart='$depart', arrivee='$arrivee'");

    if ($depart && $arrivee) {
        // Filtrer les annonces en fonction des lieux de départ et d'arrivée
        $annonces = $controller-> searchAnnonces($depart, $arrivee);
    } else {
        // Si aucun filtre n'est fourni, afficher toutes les annonces
        $annonces = $controller->getAllAnnonces();
    }

    // Log the number of announcements found
    error_log("resultats.php found " . count($annonces) . " announcements");
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Transport</title>
    <link rel="stylesheet" href="/clickngooo/public/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS et JS pour OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    let map;
    let marker;
    let locationMarker;
    let currentInputId = null;

    // Fonction pour initialiser la carte
    function initializeMap() {
    const tunisiaBounds = [
        [30.2, 7.5], // Southwest corner
        [37.4, 11.6]  // Northeast corner
    ];

    map = L.map('map', {
        center: [36.8065, 10.1815], // Tunis by default
        zoom: 7,
        maxBounds: tunisiaBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 6,
        maxZoom: 12
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Custom red pin icon
    const redPin = L.icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        shadowSize: [41, 41]
    });

    // List of Tunisian cities and towns (expanded list)
    const tunisianLocations = [
        // Tunis Governorate
        {name: "Tunis", lat: 36.8065, lng: 10.1815},
        {name: "La Marsa", lat: 36.8782, lng: 10.3247},
        {name: "Sidi Bou Saïd", lat: 36.8687, lng: 10.3416},
        {name: "Carthage", lat: 36.8545, lng: 10.3306},
        {name: "Le Bardo", lat: 36.8092, lng: 10.1333},
        
        // Other major cities
        {name: "Sfax", lat: 34.7406, lng: 10.7603},
        {name: "Sousse", lat: 35.8254, lng: 10.6360},
        {name: "Kairouan", lat: 35.6712, lng: 10.1006},
        {name: "Bizerte", lat: 37.2744, lng: 9.8739},
        {name: "Gabès", lat: 33.8815, lng: 10.0983},
        {name: "Ariana", lat: 36.8665, lng: 10.1647},
        {name: "Gafsa", lat: 34.4229, lng: 8.7841},
        {name: "Monastir", lat: 35.7643, lng: 10.8113},
        {name: "Ben Arous", lat: 36.7435, lng: 10.2319},
        {name: "Nabeul", lat: 36.4561, lng: 10.7376},
        {name: "Hammamet", lat: 36.4000, lng: 10.6167},
        {name: "Djerba", lat: 33.8078, lng: 10.8451},
        {name: "Mahdia", lat: 35.5047, lng: 11.0622},
        {name: "Zaghouan", lat: 36.4029, lng: 10.1429},
        {name: "Tozeur", lat: 33.9197, lng: 8.1335},
        {name: "Béja", lat: 36.7256, lng: 9.1817},
        {name: "Jendouba", lat: 36.5012, lng: 8.7804},
        {name: "El Kef", lat: 36.1822, lng: 8.7147},
        {name: "Siliana", lat: 36.0849, lng: 9.3708},
        {name: "Kasserine", lat: 35.1676, lng: 8.8365},
        {name: "Sidi Bouzid", lat: 35.0383, lng: 9.4848},
        {name: "Tataouine", lat: 32.9297, lng: 10.4510},
        {name: "Medenine", lat: 33.3549, lng: 10.5055},
        {name: "Manouba", lat: 36.8081, lng: 10.0972},
        
        // Add more towns as needed
        {name: "Korba", lat: 36.5786, lng: 10.8586},
        {name: "Kelibia", lat: 36.8475, lng: 11.0939},
        {name: "El Hamma", lat: 33.8917, lng: 9.7967},
        {name: "Douz", lat: 33.4667, lng: 9.0167},
        {name: "Tabarka", lat: 36.9544, lng: 8.7581},
        {name: "Hammam Lif", lat: 36.7333, lng: 10.3333},
        {name: "Radès", lat: 36.7667, lng: 10.2833},
        {name: "Menzel Bourguiba", lat: 37.1536, lng: 9.7878},
        {name: "El Jem", lat: 35.3000, lng: 10.7167},
        {name: "Ksar Hellal", lat: 35.6500, lng: 10.9000},
        {name: "Moknine", lat: 35.6333, lng: 10.9667},
        {name: "Msaken", lat: 35.7333, lng: 10.5833},
        
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
    // Add red pins for all locations
    tunisianLocations.forEach(location => {
        const marker = L.marker([location.lat, location.lng], {
            icon: redPin,
            title: location.name
        }).addTo(map);

        // Add click event to select location
        marker.on('click', function() {
            // Set the input field value
            document.getElementById(currentInputId).value = location.name;
            // Set coordinates
            document.getElementById(currentInputId + '_coords').value = `${location.lat.toFixed(5)}, ${location.lng.toFixed(5)}`;
            // Close the map
            document.getElementById("mapModal").style.display = "none";
            document.getElementById("loadingSpinner").style.display = "none";
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
            alert("Please select a point within Tunisia.");
            loadingSpinner.style.display = "none";
        }
    });

    // Fit map to show all of Tunisia
    map.fitBounds([
        [30.2, 7.5],
        [37.4, 11.6]
    ]);
}
    function showMap(inputId) {
        console.log('showMap called with inputId:', inputId);

        if (!['depart', 'arrivee'].includes(inputId)) {
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
            console.log('Map already initialized, hiding spinner');
            loadingSpinner.style.display = "none";
        }

        // Obtenir la localisation actuelle
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    console.log('Geolocation success: lat:', lat, 'lng:', lng);

                    if (lat >= 30.2 && lat <= 37.4 && lng >= 7.5 && lng <= 11.6) {
                        map.setView([lat, lng], 10); // Zoom ajusté pour les villes
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
                        console.log('Geolocation outside Tunisia, using default center');
                        map.setView([36.8065, 10.1815], 7);
                    }
                    loadingSpinner.style.display = "none";
                },
                (error) => {
                    console.error('Geolocation error:', error.message);
                    map.setView([36.8065, 10.1815], 7);
                    loadingSpinner.style.display = "none";
                    alert("Impossible d'obtenir votre localisation. Centrage sur Tunis.");
                }
            );
        } else {
            console.log('Geolocation not supported');
            map.setView([36.8065, 10.1815], 7);
            loadingSpinner.style.display = "none";
        }

        // Masquer le spinner après 2 secondes (secours)
        setTimeout(() => {
            console.log('Timeout: hiding spinner');
            loadingSpinner.style.display = "none";
        }, 2000);
    }

    function reverseGeocode(lat, lng, inputId) {
        console.log('reverseGeocode called with inputId:', inputId, 'lat:', lat, 'lng:', lng);

        if (!['depart', 'arrivee'].includes(inputId)) {
            console.error('Invalid inputId in reverseGeocode:', inputId);
            document.getElementById("loadingSpinner").style.display = "none";
            return;
        }

        const inputElement = document.getElementById(inputId);
        const coordsElement = document.getElementById(inputId + '_coords');
        const loadingSpinner = document.getElementById("loadingSpinner");

        if (!inputElement || !coordsElement) {
            console.error('DOM elements not found for inputId:', inputId, { inputElement, coordsElement });
            loadingSpinner.style.display = "none";
            return;
        }

        // Recherche inversée pour trouver la ville la plus proche
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=10&accept-language=fr`, {
            headers: {
                'User-Agent': 'ClickNGo/1.0 (contact@yourdomain.com)'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Nominatim response for', inputId, ':', data);
            if (data && data.address && data.address.city) {
                inputElement.value = data.address.city;
                coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            } else if (data && data.address && data.address.town) {
                inputElement.value = data.address.town;
                coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            } else {
                inputElement.value = 'Ville non trouvée';
                coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            }
            mapModal.style.display = "none";
            loadingSpinner.style.display = "none";
            console.log('Updated:', { inputId, inputValue: inputElement.value, coordsValue: coordsElement.value });
        })
        .catch(error => {
            console.error('Error during reverse geocoding:', error);
            inputElement.value = 'Erreur de recherche';
            coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            loadingSpinner.style.display = "none";
            console.log('Updated (error):', { inputId, inputValue: inputElement.value, coordsValue: coordsElement.value });
        });
    }

    // Fonction de recherche avec autocomplétion (villes seulement)
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
                    console.log('Search results:', data);
                    suggestions.innerHTML = '';
                    data.forEach(item => {
                        if (item.type === 'city' || item.type === 'town') {
                            const div = document.createElement('div');
                            div.className = 'p-2 hover:bg-gray-200 cursor-pointer';
                            div.textContent = item.display_name;
                            div.addEventListener('click', () => {
                                const lat = parseFloat(item.lat);
                                const lng = parseFloat(item.lon);
                                map.setView([lat, lng], 10); // Zoom ajusté pour les villes
                                if (marker) map.removeLayer(marker);
                                marker = L.marker([lat, lng]).addTo(map);
                                reverseGeocode(lat, lng, currentInputId);
                                document.getElementById("mapModal").style.display = "none";
                                document.getElementById("loadingSpinner").style.display = "none";
                                suggestions.innerHTML = '';
                                searchInput.value = '';
                            });
                            suggestions.appendChild(div);
                        }
                    });
                    if (suggestions.innerHTML === '') {
                        suggestions.innerHTML = '<div class="p-2 text-gray-500">Aucune ville trouvée</div>';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    suggestions.innerHTML = '<div class="p-2 text-red-500">Erreur lors de la recherche</div>';
                });
            }, 300);
        });

        // Masquer les suggestions lors de la perte de focus
        searchInput.addEventListener('blur', () => {
            setTimeout(() => suggestions.innerHTML = '', 200);
        });

        // Afficher les suggestions au focus
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 3) {
                suggestions.style.display = 'block';
            }
        });
    }

    // Initialiser la recherche après le chargement du DOM
    document.addEventListener('DOMContentLoaded', () => {
        setupSearch();
    });
</script>

      
         
    <style>
        /* Gradient for buttons */
        .elsa-gradient-primary {
            background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
        }

        .elsa-gradient-primary-hover:hover {
            background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
        }

        /* Styling for the video background */
        .background-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.75;
        }/* Close Map Button Styles */
.close-map-button {
    background: #ff6666;
    color: white;
    width: 30px;
    height: 30px;
    text-align: center;
    line-height: 30px;
    font-size: 20px;
    font-weight: bold;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 5px rgba(0,0,0,0.3);
    z-index: 1000;
    margin-right: 10px;
    margin-top: 10px;
}

.close-map-button:hover {
    background: #e55a5a;
    transform: scale(1.1);
    transition: all 0.2s ease;
}

/* Make sure the button appears above map controls */
.leaflet-top.leaflet-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

        /* Navbar text color */
        nav .text-white {
            color: black !important;
        }

        nav .hover\:text-pink-300:hover {
            color: rgb(203, 55, 191) !important;
        }

        /* Spinner de chargement */
        #loadingSpinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10000;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #be3cf0;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Style pour le modal */
        #mapModal {
            position: fixed;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            z-index: 9999;
            background: white;
            border: 2px solid #ccc;
            border-radius: 10px;
        }

        /* Bouton de fermeture */
        #closeMapButton {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff6666;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #closeMapButton:hover {
            background: #e55a5a;
        }

        /* Barre de recherche */
        #mapSearchInput {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 250px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
        }

        #mapSearchSuggestions {
            position: absolute;
            top: 40px;
            left: 10px;
            width: 250px;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
            display: none;
        }

        #mapSearchSuggestions div {
            border-bottom: 1px solid #eee;
        }

        #mapSearchSuggestions div:last-child {
            border-bottom: none;
        }

        #mapSearchInput:focus + #mapSearchSuggestions,
        #mapSearchSuggestions:hover {
            display: block;
        }
        .animate-title {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInSlideUp 1s ease-out forwards;
    }

    /* Animation for UN COVOITURAGE */
    .animate-subtitle {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInSlideUp 1s ease-out 0.3s forwards; /* Delay to stagger the animation */
    }

    /* Keyframes for the fade-in and slide-up effect */
    @keyframes fadeInSlideUp {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }/* Add this to your existing styles */
.city-marker {
    text-align: center;
    font-weight: bold;
    color: white;
    text-shadow: 0 0 3px black;
    white-space: nowrap;
}

.city-marker-inner {
    background-color: rgba(190, 60, 240, 0.8);
    padding: 2px 8px;
    border-radius: 15px;
    font-size: 12px;
    transform: translateY(-10px);
}

.city-marker:hover .city-marker-inner {
    background-color: rgba(255, 102, 102, 0.9);
    transform: translateY(-10px) scale(1.1);
    transition: all 0.2s ease;
}  3
    </style>
</head>
<body>
    <!-- Indicateur de chargement -->
    <div id="loadingSpinner"></div>

    <!-- BACKGROUND IMAGE WITH NAVBAR -->
    <div class="relative w-full h-[85vh] mt-2">
        <!-- NAVBAR SUR LA VIDÉO -->
        <nav class="absolute top-0 left-0 w-full z-50 p-4">
            <div class="flex items-center justify-center max-w-7xl mx-auto">
                <div class="flex space-x-8 text-lg font-bold text-white relative">
                    <a href="#home" class="hover:text-pink-300">Accueil</a>
                    <a href="#about" class="hover:text-pink-300">À propos</a>
                    <div class="group relative">
                        <button class="hover:text-pink-300 font-bold text-lg">
                            Nos Détails ▾
                        </button>
                        <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                            <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Nos trajets</a>
                            <a href="#top-conducteurs" class="block px-4 py-2 hover:bg-gray-100">Top Conducteurs</a>
                            <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Mes annonces</a>
                           
                            
                        </div>
                    </div>
                    
                    <!-- LISTE DÉROULANTE SERVICES -->
                    <div class="relative group">
                        <button class="hover:text-pink-300">Services ▾</button>
                        <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                            <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Trouver un covoiturage</a>
                            <a href="AjouterConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Proposer un covoiturage</a>
                        </div>
                    </div>
                    
                    <!-- LISTE DÉROULANTE CONTACT -->
                    <div class="relative group">
                        <button class="hover:text-pink-300">Contact ▾</button>
                        <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[180px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                            <a href="/clickngo/view/reclamation.php" class="block px-4 py-2 hover:bg-gray-100">chatbot</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
      <!-- IMAGE DE FOND -->
<img src="5668988_58246.Jpg" alt="Background" class="absolute inset-0 w-full h-full object-cover opacity-75">

<!-- WRAPPER POUR RÉTRÉCIR LA LARGEUR -->
<div class="relative z-10 flex flex-col items-center justify-center h-full text-center text-black max-w-4xl mx-auto px-4">
    <h1 class="text-4xl font-bold animate-title">TROUVEZ</h1>
    <h2 class="text-5xl font-bold animate-subtitle">✨UN COVOITURAGE✨ </h2>

    <form action="resultats.php" method="GET" class="mt-8 flex flex-wrap gap-4 justify-center">
        <!-- Adresse de départ -->
        <div class="flex items-center bg-white rounded-lg shadow-md">
            <span onclick="showMap('depart')" style="cursor:pointer">
                <i class="fas fa-map-marker-alt text-[#be3cf0] ml-4"></i>
            </span>
            <input id="depart" class="p-2 pl-4 pr-8 text-gray-700 rounded-lg focus:outline-none" name="depart" placeholder="Adresse de départ" type="text" required/>
            <input id="depart_coords" name="depart_coords" type="hidden"/>
            <i class="fas fa-paper-plane text-gray-500 mr-4"></i>
        </div>
   

                <!-- Adresse d'arrivée -->
                <div class="flex items-center bg-white rounded-lg shadow-md">
                    <span onclick="showMap('arrivee')" style="cursor:pointer">
                        <i class="fas fa-map-marker-alt text-[#ff6666] ml-4"></i>
                    </span>
                    <input id="arrivee" class="p-2 pl-4 pr-8 text-gray-700 rounded-lg focus:outline-none" name="arrivee" placeholder="Adresse d'arrivée" type="text" required/>
                    <input id="arrivee_coords" name="arrivee_coords" type="hidden"/>
                    <i class="fas fa-paper-plane text-gray-500 mr-4"></i>
                </div>

                <!-- Bouton de recherche -->
                <button type="submit" class="flex items-center px-6 py-2 text-white rounded-lg shadow-md elsa-gradient-primary elsa-gradient-primary-hover focus:outline-none">
                    <span>Lancer ma recherche</span>
                    <i class="fas fa-search ml-2"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal carte -->
    <div id="mapModal" style="display:none;">
        <button id="closeMapButton" onclick="document.getElementById('mapModal').style.display='none';document.getElementById('loadingSpinner').style.display='none';">×</button>
        <input id="mapSearchInput" type="text" placeholder="Rechercher une adresse en Tunisie">
        <div id="mapSearchSuggestions"></div>
        <div id="map" style="width:100%; height:100%; border-radius:10px;"></div>
    </div>

    <div class="w-full max-w-4xl mx-auto mt-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Nos sélections par âge</h2>
        <div class="flex flex-wrap justify-center items-center gap-4">
            <!-- Carte Âge -->
            <div class="bg-gray-100 rounded-lg p-4 w-24 text-center transform transition-transform duration-300 hover:scale-105 hover:bg-pink-100 relative age-card" data-age="18">
                <p class="text-pink-500 text-4xl font-pacifico">18</p>
                <p class="text-gray-700">ans</p>
            </div>
            <div class="bg-gray-100 rounded-lg p-4 w-24 text-center transform transition-transform duration-300 hover:scale-105 hover:bg-pink-100 relative age-card" data-age="20">
                <p class="text-pink-500 text-4xl font-pacifico">20</p>
                <p class="text-gray-700">ans</p>
            </div>
            <div class="bg-gray-100 rounded-lg p-4 w-24 text-center transform transition-transform duration-300 hover:scale-105 hover:bg-pink-100 relative age-card" data-age="30">
                <p class="text-pink-500 text-4xl font-pacifico">30</p>
                <p class="text-gray-700">ans</p>
            </div>
            <div class="bg-gray-100 rounded-lg p-4 w-24 text-center transform transition-transform duration-300 hover:scale-105 hover:bg-pink-100 relative age-card" data-age="40">
                <p class="text-pink-500 text-4xl font-pacifico">40</p>
                <p class="text-gray-700">ans</p>
            </div>
            <div class="bg-gray-100 rounded-lg p-4 w-24 text-center transform transition-transform duration-300 hover:scale-105 hover:bg-pink-100 relative age-card" data-age="50">
                <p class="text-pink-500 text-4xl font-pacifico">50</p>
                <p class="text-gray-700">ans</p>
            </div>
            <div class="bg-gray-100 rounded-lg p-4 w-24 text-center transform transition-transform duration-300 hover:scale-105 hover:bg-pink-100 relative age-card" data-age="60">
                <p class="text-pink-500 text-4xl font-pacifico">60</p>
                <p class="text-gray-700">ans</p>
            </div>
        </div>
    </div>
    <br><br><br>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const cards = document.querySelectorAll(".age-card");
            const buttons = document.getElementById("role-buttons");

            cards.forEach(card => {
                card.addEventListener("mouseenter", () => {
                    const rect = card.getBoundingClientRect();
                    buttons.style.left = `${rect.left + rect.width / 2}px`;
                    buttons.style.top = `${rect.bottom + window.scrollY + 5}px`;
                    buttons.style.transform = "translateX(-50%)";
                    buttons.classList.remove("hidden");
                });

                card.addEventListener("mouseleave", () => {
                    setTimeout(() => {
                        if (!buttons.matches(":hover")) {
                            buttons.classList.add("hidden");
                        }
                    }, 200);
                });
            });

            buttons.addEventListener("mouseleave", () => {
                buttons.classList.add("hidden");
            });
        });
    </script>

    <section id="trajets" class="bg-[#f9f9fb] py-12 px-4 sm:px-8 lg:px-16">
        <h2 class="text-3xl font-bold text-center text-[#be3cf0] mb-10 animate-pulse">Nos trajets récents</h2>
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Carte 1 : Tunis vers Hammamet (Paintball) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/zl0bxu6trkxrnmygfv7n.jpg" alt="Paintball" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Tunis ➝ Hammamet</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Paintball and aventure · 4 personnes · 05 Janvier 2025</p>
                </div>
            </div>
            <!-- Carte 2 : Manza vers Marsa (Game Production) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/gamepro.png" alt="Game Production" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Manzah ➝La Marsa</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Game Production and fun · 3 personnes · 1 Mars 2025</p>
                </div>
            </div>
            <!-- Carte 3 : Aliena vers Gammarth (Battle) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/padel.png" alt="Battle" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Ariana➝ Gammarth</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Padel · 4 personnes · 12 Mars 2025</p>
                </div>
            </div>
            <!-- Carte 4 : Tunis vers Sidi Bou Saïd (Randonnée) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/Sidi bou said ❤️.png" alt="Randonnée" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Tunis ➝ Sidi Bou Saïd</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Randonnée et découverte · 4 personnes · 22 fevrier 2025</p>
                </div>
            </div>
            <!-- Carte 5 : Sousse vers Kairouan (Visite culturelle) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/Dar Al-Alani for Traditional Industries Kairouan - Tunisia 🇹🇳.png" alt="Visite culturelle" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Sousse ➝ Kairouan</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Visite culturelle and historique · 3 personnes · 28 février 2025</p>
                </div>
            </div>
            <!-- Carte 6 : Tunis vers La Marsa (Détente plage) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/carousel.png" alt="Détente plage" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Tunis ➝ Sousse</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Parc Hannibal · 3 personnes · 05 Mai 2025</p>
                </div>
            </div>
            <!-- Carte 7 : Monastir vers Mahdia (Excursion à vélo) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/el jem.png" alt="Visite culturelle" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Monastir ➝ El Jem</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Visite de musée and patrimoine · 2 personnes · 01 Avril 2025</p>
                </div>
            </div>
            <!-- Carte 8 : Gabès vers Djerba (Exploration) -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:translate-y-[-10px] hover:text-[#be3cf0]">
                <img src="/clickngo/public/images/djerba.png" alt="Exploration" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">Gabès ➝ Djerba</h3>
                    <p class="text-gray-600 mt-2 transition-colors duration-300">Exploration and aventure · 4 personnes · 15 Mai 2025</p>
                </div>
            </div>
        </div>
    </section>

    <div class="flex justify-center items-center space-x-8 py-8">
        <div class="text-center">
            <img alt="Icon of a camera with a ticket" class="mx-auto mb-2 bounce" height="100" src="/clickngo/public/images/camera-photo.png.webp" width="100"/>
            <p class="text-lg font-semibold text-gray-800">
                15 000 conducteurs vérifiés
            </p>
        </div>
        <div class="text-center">
            <img alt="Icon of an on/off switch" class="mx-auto mb-2 bounce" height="100" src="/clickngo/public/images/power-switch.webp" width="100"/>
            <p class="text-lg font-semibold text-gray-800">
                Annulation gratuite
            </p>
        </div>
        <div class="text-center">
            <img alt="Icon of a phone with the word 'fun'" class="mx-auto mb-2 bounce" height="100" src="/clickngo/public/images/phone-fun.webp" width="100"/>
            <p class="text-lg font-semibold text-gray-800">
                Joignable tout le weekend
            </p>
        </div>
        <div class="text-center">
            <img alt="Icon of a euro symbol" class="mx-auto mb-2 bounce" height="100" src="/clickngo/public/images/euro-symbol.webp" width="100"/>
            <p class="text-lg font-semibold text-gray-800">
                Même prix qu'en direct
            </p>
        </div>
    </div>

    <!-- À placer quelque part dans ta page, de préférence en bas -->
    <section id="top-conducteurs" class="mt-20 scroll-mt-20">
        <h2 class="text-2xl font-bold mb-8 text-center">Top Conducteurs</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">
            <!-- Conducteur 1: Julien -->
            <div class="top-driver text-center relative group">
                <div class="relative w-fit mx-auto">
                    <img src="/clickngo/public/images/julien.webp" alt="Julien" class="rounded-full w-24 h-24 mx-auto mb-2 transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-50 text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                        <a href="/clickngo/view/avis.php" class="mt-2 px-2 py-1 bg-white text-black rounded text-xs hover:bg-gray-200">Tu veux laisser un avis ?</a>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Zackaria bm</h3>
                <div class="flex justify-center items-center gap-[2px] mt-1">
                    <span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span>
                    <span class="text-[#be3cf0] relative w-4 overflow-hidden"><span class="absolute left-0">★</span><span class="text-gray-300 absolute left-1/2">★</span></span>
                </div>
                <div class="flex justify-center gap-3 mt-2">
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-facebook text-xl"></i></a>
                </div>
            </div>
            <!-- Conducteur 2: Aziz Ghali -->
            <div class="top-driver text-center relative group">
                <div class="relative w-fit mx-auto">
                    <img src="/clickngo/public/images/Martin.webp" alt="Martin" class="rounded-full w-24 h-24 mx-auto mb-2 transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-50 text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                        <a href="/clickngo/view/avis.php" class="mt-2 px-2 py-1 bg-white text-black rounded text-xs hover:bg-gray-200">Tu veux laisser un avis ?</a>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Aziz Ghali</h3>
                <div class="flex justify-center gap-[2px] mt-1">
                    <span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span>
                </div>
                <div class="flex justify-center gap-3 mt-2">
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-facebook text-xl"></i></a>
                </div>
            </div>
            <!-- Conducteur 3: eya herchi-->
            <div class="top-driver text-center relative group">
                <div class="relative w-fit mx-auto">
                    <img src="/clickngo/public/images/laetitia.webp" alt="eyaherchi" class="rounded-full w-24 h-24 mx-auto mb-2 transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-50 text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                        <a href="/clickngo/view/avis.php" class="mt-2 px-2 py-1 bg-white text-black rounded text-xs hover:bg-gray-200">Tu veux laisser un avis?</a>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Eya Hrechi</h3>
                <div class="flex justify-center gap-[2px] mt-1">
                    <span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-gray-300 text-lg">★</span>
                </div>
                <div class="flex justify-center gap-3 mt-2">
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-facebook text-xl"></i></a>
                </div>
            </div>
            <!-- Conducteur 4: Yesmine Azouz -->
            <div class="top-driver text-center relative group">
                <div class="relative w-fit mx-auto">
                    <img src="/clickngo/public/images/Juliette.webp" alt="Yesmine Azouz" class="rounded-full w-24 h-24 mx-auto mb-2 transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-50 text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                        <a href="/clickngo/view/avis.php" class="mt-2 px-2 py-1 bg-white text-black rounded text-xs hover:bg-gray-200">Tu veux laisser un avis?</a>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Yesmine Azouz</h3>
                <div class="flex justify-center gap-[2px] mt-1">
                    <span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span>
                    <span class="text-[#be3cf0] relative w-4 overflow-hidden"><span class="absolute left-0">★</span><span class="text-gray-300 absolute left-1/2">★</span></span>
                </div>
                <div class="flex justify-center gap-3 mt-2">
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-facebook text-xl"></i></a>
                </div>
            </div>
            <!-- Conducteur 5: Sarah benYousssef -->
            <div class="top-driver text-center relative group">
                <div class="relative w-fit mx-auto">
                    <img src="/clickngo/public/images/Sarah.webp" alt="Sarah benYousssef" class="rounded-full w-24 h-24 mx-auto mb-2 transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-50 text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                        <a href="/clickngo/view/avis.php" class="mt-2 px-2 py-1 bg-white text-black rounded text-xs hover:bg-gray-200">Tu veux laisser un avis?</a>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Sarah benYousssef</h3>
                <div class="flex justify-center gap-[2px] mt-1">
                    <span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span>
                </div>
                <div class="flex justify-center gap-3 mt-2">
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-facebook text-xl"></i></a>
                </div>
            </div>
            <!-- Conducteur 6: Rimess Maghri -->
            <div class="top-driver text-center relative group">
                <div class="relative w-fit mx-auto">
                    <img src="/clickngo/public/images/Sarah_1.webp" alt="Rimess Maghri" class="rounded-full w-24 h-24 mx-auto mb-2 transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-50 text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                        <a href="/clickngo/view/avis.php" class="mt-2 px-2 py-1 bg-white text-black rounded text-xs hover:bg-gray-200">Tu veux laisser un avis?</a>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Rimess Maghri</h3>
                <div class="flex justify-center gap-[2px] mt-1">
                    <span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-[#be3cf0] text-lg">★</span><span class="text-gray-300 text-lg">★</span>
                </div>
                <div class="flex justify-center gap-3 mt-2">
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-instagram text-xl"></i></a>
                    <a href="#" class="text-pink-400 hover:text-[#be3cf0] transition"><i class="fab fa-facebook text-xl"></i></a>
                </div>
            </div>
        </div>
    </section><br><br>

    <!-- CENTRÉ parfaitement en dessous -->
    

    <section class="carpooling-home">
        <div class="carpooling-container">
            <div class="carpooling-content">
                <h2>Votre sécurité est notre priorité</h2> 
                <ul class="carpooling-features">
                    <p>Chez Click'N'Go, nous nous sommes fixé comme objectif de construire une communauté de covoiturage fiable et digne de confiance à travers le monde.
                    Rendez-vous sur notre page Confiance et sécurité pour explorer les différentes fonctionnalités disponibles pour covoiturer sereinement.</p>
                </ul>
                <button class="carpooling-btn">En savoir plus</button>
            </div>
            <div class="carpooling-image">
                <img src="/clickngo/public/images/cov.webp" alt="">
            </div>
        </div>
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
                    <img src="/clickngo/public/images/logo.png" alt="click'N'go Logo" class="footer-logo">
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
                    <img src="visa.webp" alt="Visa">
                    <img src="/clickngo/public/images/mastercard-v2.webp" alt="mastercard">
                    <img src="/clickngo/public/images/logo-cb.webp" alt="cb" class="cb-logo">
                    <img src="/clickngo/public/images/paypal.webp" alt="paypal" class="paypal">
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

</body>
</html>