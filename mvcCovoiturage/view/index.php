<?php

session_start(); // Must be at the top of the file

if (empty($_SESSION['user']['id_user'])) {
    // Redirection vers la page de login si non connecté
    header("Location: /Projet Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit();
}

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
try {
    // Query to fetch top 6 drivers (using a placeholder for rating)
    $stmt = $pdo->prepare("SELECT prenom_conducteur, nom_conducteur FROM annonce_covoiturage ORDER BY date_depart DESC LIMIT 6");
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Simulate rating (replace with actual rating logic if available)
    foreach ($drivers as &$driver) {
        // Placeholder rating (e.g., 4.0 as default; replace with real logic)
        $driver['rating'] = 4.0; // Example: Could use AVG from a reviews table
        $driver['instagram_link'] = '#'; // Default if not available
        $driver['facebook_link'] = '#'; // Default if not available
    }
    unset($driver); // Unset reference
} catch (Exception $e) {
    $drivers = [];
    echo '<div class="text-center text-red-500">Erreur lors du chargement des conducteurs: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Transport</title>
    <link rel="stylesheet" href="/clickngo/public/css/style.css">

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
    {name: "La Goulette", lat: 36.8185, lng: 10.3052},
    {name: "Hammam-Lif", lat: 36.7333, lng: 10.3333},
    {name: "Radès", lat: 36.7667, lng: 10.2833},
    {name: "El Omrane", lat: 36.8167, lng: 10.1667},
    {name: "El Kabaria", lat: 36.8333, lng: 10.2000},
    {name: "Sidi Hassine", lat: 36.8000, lng: 10.1000},
    {name: "Ezzouhour", lat: 36.8167, lng: 10.1333},
    {name: "Sebkhet Sejoumi", lat: 36.7833, lng: 10.1333},

    // Ariana Governorate
    {name: "Ariana", lat: 36.8665, lng: 10.1647},
    {name: "Raoued", lat: 36.9333, lng: 10.1500},
    {name: "Kalâat el-Andalous", lat: 37.0667, lng: 10.1167},
    {name: "Sidi Thabet", lat: 36.9000, lng: 10.0333},
    {name: "La Soukra", lat: 36.8833, lng: 10.2500},
    {name: "Ettadhamen", lat: 36.8500, lng: 10.1000},
    {name: "Mnihla", lat: 36.8500, lng: 10.1667},

    // Ben Arous Governorate
    {name: "Ben Arous", lat: 36.7435, lng: 10.2319},
    {name: "Boumhel", lat: 36.7333, lng: 10.2833},
    {name: "El Mourouj", lat: 36.7333, lng: 10.2000},
    {name: "Ezzahra", lat: 36.7333, lng: 10.2500},
    {name: "Hammam Chott", lat: 36.7000, lng: 10.3000},
    {name: "Mégrine", lat: 36.7667, lng: 10.2333},
    {name: "Mohamedia", lat: 36.6833, lng: 10.1500},
    {name: "Mornag", lat: 36.6833, lng: 10.2833},
    
    // Manouba Governorate
    {name: "Manouba", lat: 36.8081, lng: 10.0972},
    {name: "Borj El Amri", lat: 36.7167, lng: 9.9333},
    {name: "Den Den", lat: 36.8667, lng: 10.0167},
    {name: "Douar Hicher", lat: 36.8333, lng: 10.0500},
    {name: "El Battan", lat: 36.8000, lng: 9.8333},
    {name: "Jedaida", lat: 36.8500, lng: 10.0167},
    {name: "Mornaguia", lat: 36.7500, lng: 10.0167},
    {name: "Oued Ellil", lat: 36.8333, lng: 10.0333},
    {name: "Tebourba", lat: 36.8167, lng: 9.8500},

    // Bizerte Governorate
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

    // Nabeul Governorate
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

    // Béja Governorate
    {name: "Béja", lat: 36.7256, lng: 9.1817},
    {name: "Amdoun", lat: 36.8167, lng: 9.1167},
    {name: "Goubellat", lat: 36.5333, lng: 9.6667},
    {name: "Medjez el-Bab", lat: 36.6500, lng: 9.6000},
    {name: "Nefza", lat: 36.9333, lng: 9.0500},
    {name: "Téboursouk", lat: 36.4500, lng: 9.2500},
    {name: "Testour", lat: 36.5500, lng: 9.4500},
    {name: "Thibar", lat: 36.5000, lng: 9.1333},

    // Jendouba Governorate
    {name: "Jendouba", lat: 36.5012, lng: 8.7804},
    {name: "Ain Draham", lat: 36.7667, lng: 8.6833},
    {name: "Balta-Bou Aouane", lat: 36.5833, lng: 8.5000},
    {name: "Bou Salem", lat: 36.6167, lng: 8.9667},
    {name: "Fernana", lat: 36.6500, lng: 8.7000},
    {name: "Ghardimaou", lat: 36.4500, lng: 8.4333},
    {name: "Oued Mliz", lat: 36.4667, lng: 8.5667},
    {name: "Tabarka", lat: 36.9544, lng: 8.7581},

    // Zaghouan Governorate
    {name: "Zaghouan", lat: 36.4029, lng: 10.1429},
    {name: "Bir Mcherga", lat: 36.5000, lng: 10.0667},
    {name: "El Fahs", lat: 36.3667, lng: 9.9000},
    {name: "Nadhour", lat: 36.3167, lng: 10.1500},
    {name: "Saouaf", lat: 36.2833, lng: 10.1167},
    {name: "Zriba", lat: 36.3333, lng: 10.0833},

    // Siliana Governorate
    {name: "Siliana", lat: 36.0849, lng: 9.3708},
    {name: "Bargou", lat: 36.0833, lng: 9.6167},
    {name: "Bou Arada", lat: 36.3500, lng: 9.6167},
    {name: "El Aroussa", lat: 36.2667, lng: 9.4667},
    {name: "Gaâfour", lat: 36.3333, lng: 9.3167},
    {name: "Kesra", lat: 35.8167, lng: 9.3667},
    {name: "Makthar", lat: 35.8500, lng: 9.2000},
    {name: "Rouhia", lat: 35.7667, lng: 9.2833},

    // Le Kef Governorate
    {name: "El Kef", lat: 36.1822, lng: 8.7147},
    {name: "Dahmani", lat: 35.9500, lng: 8.8333},
    {name: "Jérissa", lat: 35.9167, lng: 8.5833},
    {name: "El Ksour", lat: 35.8833, lng: 8.8833},
    {name: "Kalâat Khasba", lat: 35.8167, lng: 8.6667},
    {name: "Kalâat Senan", lat: 35.8167, lng: 8.4667},
    {name: "Nebeur", lat: 36.2833, lng: 8.7667},
    {name: "Sakiet Sidi Youssef", lat: 36.2167, lng: 8.3500},
    {name: "Tajerouine", lat: 35.8833, lng: 8.5500},

    // Sousse Governorate
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

    // Monastir Governorate
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

    // Mahdia Governorate
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

    // Kairouan Governorate
    {name: "Kairouan", lat: 35.6712, lng: 10.1006},
    {name: "Bou Hajla", lat: 35.6333, lng: 10.1333},
    {name: "Chebika", lat: 35.7667, lng: 9.9667},
    {name: "Echrarda", lat: 35.6333, lng: 9.7667},
    {name: "Haffouz", lat: 35.6333, lng: 9.6667},
    {name: "Hajeb El Ayoun", lat: 35.6833, lng: 9.8000},
    {name: "Nasrallah", lat: 35.3667, lng: 9.8667},
    {name: "Oueslatia", lat: 35.8667, lng: 9.5333},
    {name: "Sbikha", lat: 35.9333, lng: 10.0167},

    // Kasserine Governorate
    {name: "Kasserine", lat: 35.1676, lng: 8.8365},
    {name: "Fériana", lat: 34.9500, lng: 8.5667},
    {name: "Foussana", lat: 35.3333, lng: 8.6167},
    {name: "Haïdra", lat: 35.5667, lng: 8.4667},
    {name: "Jedelienne", lat: 35.2000, lng: 8.7500},
    {name: "Majel Bel Abbès", lat: 35.0833, lng: 8.7500},
    {name: "Sbeïtla", lat: 35.2333, lng: 9.1167},
    {name: "Sbiba", lat: 35.5333, lng: 9.0667},
    {name: "Thala", lat: 35.5667, lng: 8.6667},

    // Sidi Bouzid Governorate
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

    // Sfax Governorate
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

    // Gabès Governorate
    {name: "Gabès", lat: 33.8815, lng: 10.0983},
    {name: "Ghannouch", lat: 33.9333, lng: 10.0667},
    {name: "El Hamma", lat: 33.8917, lng: 9.7967},
    {name: "Matmata", lat: 33.5500, lng: 9.9667},
    {name: "Métouia", lat: 33.9667, lng: 10.0000},
    {name: "Nouvelle Matmata", lat: 33.8833, lng: 9.8500},
    {name: "Oudhref", lat: 33.8167, lng: 10.0333},

    // Medenine Governorate
    {name: "Medenine", lat: 33.3549, lng: 10.5055},
    {name: "Ajim", lat: 33.7167, lng: 10.7500},
    {name: "Ben Gardane", lat: 33.1333, lng: 11.2167},
    {name: "Beni Khedache", lat: 33.2500, lng: 10.2000},
    {name: "Houmt Souk", lat: 33.8667, lng: 10.8500},
    {name: "Midoun", lat: 33.8167, lng: 10.9833},
    {name: "Zarzis", lat: 33.5000, lng: 11.1167},
    {name: "Sidi Makhlouf", lat: 33.3500, lng: 10.4833},

    // Tataouine Governorate
    {name: "Tataouine", lat: 32.9297, lng: 10.4510},
    {name: "Bir Lahmar", lat: 32.8000, lng: 10.6333},
    {name: "Dehiba", lat: 32.0167, lng: 10.7000},
    {name: "Ghomrassen", lat: 33.0667, lng: 10.3333},
    {name: "Remada", lat: 32.3167, lng: 10.4000},
    {name: "Smâr", lat: 33.2333, lng: 10.5000},

    // Gafsa Governorate
    {name: "Gafsa", lat: 34.4229, lng: 8.7841},
    {name: "El Guettar", lat: 34.3333, lng: 8.9500},
    {name: "El Ksar", lat: 34.4167, lng: 8.8000},
    {name: "Mdhilla", lat: 34.2833, lng: 8.7500},
    {name: "Métlaoui", lat: 34.3333, lng: 8.4000},
    {name: "Moulares", lat: 34.3167, lng: 8.2667},
    {name: "Redeyef", lat: 34.3833, lng: 8.1500},
    {name: "Sened", lat: 34.9333, lng: 10.2833},

    // Tozeur Governorate
    {name: "Tozeur", lat: 33.9197, lng: 8.1335},
    {name: "Degache", lat: 33.9833, lng: 8.2167},
    {name: "Hazoua", lat: 33.9333, lng: 7.8667},
    {name: "Nefta", lat: 33.8667, lng: 7.8833},
    {name: "Tamerza", lat: 34.2167, lng: 7.9333},

    // Kebili Governorate
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
}  /* Center the container */
.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

/* Ensure the section itself is centered */
#top-conducteurs {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Slider container */
.slider-wrapper {
    overflow: hidden;
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

/* Slider track to hold items */
.slider-track {
    display: flex;
    justify-content: center;
}

/* Individual driver card */
.top-driver {
    flex: 0 0 auto;
    width: 200px;
    margin-right: 20px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
}

/* Animate only the first 4 items with a WOW effect */
.top-driver.animate {
    animation: wowAnimation 4s ease-in-out infinite;
}

/* WOW Animation: Scale, Rotate, and Pulse */
@keyframes wowAnimation {
    0% {
        transform: scale(1) rotate(0deg);
        box-shadow: 0 0 0 rgba(190, 60, 240, 0.3);
    }
    25% {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 0 15px rgba(190, 60, 240, 0.6);
    }
    50% {
        transform: scale(1.2) rotate(0deg);
        box-shadow: 0 0 25px rgba(190, 60, 240, 0.8);
    }
    75% {
        transform: scale(1.1) rotate(-5deg);
        box-shadow: 0 0 15px rgba(190, 60, 240, 0.6);
    }
    100% {
        transform: scale(1) rotate(0deg);
        box-shadow: 0 0 0 rgba(190, 60, 240, 0.3);
    }
}

/* Hover effect for driver cards */
.top-driver img {
    transition: transform 0.3s ease;
}

.top-driver:hover img {
    transform: scale(1.1);
}

.top-driver .overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 50%;
}

.top-driver:hover .overlay {
    opacity: 1;
}

.reaction-btn {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.reaction-btn.active {
    color: #be3cf0;
}

.reaction-btn.loading::after {
    content: "...";
    animation: dots 1s infinite;
}

@keyframes dots {
    0% { content: "."; }
    33% { content: ".."; }
    66% { content: "..."; }
}

/* Pause animation on hover */
.slider-wrapper:hover .top-driver.animate {
    animation-play-state: paused;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .top-driver {
        width: 150px;
        margin-right: 10px;
    }

    .slider-wrapper {
        max-width: 600px;
    }
}

    </style>
</head>
<body>
    <!-- Indicateur de chargement -->
    <div id="loadingSpinner"></div>

    <!-- BACKGROUND IMAGE WITH NAVBAR -->
    <div class="relative w-full h-[85vh]">
    <!-- NAVBAR SUR LA VIDÉO -->
    <nav class="absolute top-0 left-0 w-full z-50 p-4">
        <div class="flex items-center justify-center max-w-7xl mx-auto">
            <!-- Menu Transport centré -->
            <div class="flex space-x-8 text-lg font-bold text-white relative">

                <a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/index.php">Accueil</a>
                <a href="/Projet Web/mvcact/view/front office/activite.php">Activités</a>
                <a href="/Projet%20Web/mvcEvent/View/FrontOffice/evenemant.php">Événements</a>
                <a href="/Projet Web/mvcProduit/view/front office/produit.php">Produits</a>
                                                                <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">Sponsors</a> 

                <div class="group relative">
                    <button class="hover:text-pink-300 font-bold text-lg">
                        Transport  <span style="display: inline-block; transform: translateY(-2px);">▾</span>
                    </button>



                    <div class="absolute left-0 mt-2 bg-transparent rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <div class="relative group/sub">
                            <a href="DisplayConducteur.php" class="block px-4 py-2 text-white hover:text-pink-300">Nos trajets</a>
                            <div class="absolute top-0 left-full bg-transparent rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover/sub:opacity-100 invisible group-hover/sub:visible transition-all duration-200">
                                <a href="ListPassager.php" class="block px-4 py-2 text-white hover:text-pink-300">Mes demandes</a>
                            </div>
                        </div>
                        <div class="relative group/sub">
                            <a href="AjouterConducteur.php" class="block px-4 py-2 text-white hover:text-pink-300">Proposer un trajet</a>
                            <div class="absolute top-0 left-full bg-transparent rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover/sub:opacity-100 invisible group-hover/sub:visible transition-all duration-200">
                                <a href="ListConducteurs.php" class="block px-4 py-2 text-white hover:text-pink-300">Mes annonces</a>
                            </div>
                        </div>
                        <a href="#faq" class="block px-4 py-2 text-white hover:text-pink-300">Chatbot</a>
                    </div>

                </div>
            </div>
        </div>
    </nav>
</div>

        
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
    </script><section id="trajets" class="bg-[#f9f9fb] py-12 px-4 sm:px-8 lg:px-16">
    <h2 class="text-3xl font-bold text-center text-[#be3cf0] mb-10 animate-title">Nos trajets récents</h2>
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4" id="trajets-grid">
        <?php 
        require_once '../Model/ImageGenerator.php';
        
        // Clear session indices for fresh images on each page load
        $_SESSION['used_indices'] = [];
        $usedIndices = [];
        $imageGenerator = new ImageGenerator($usedIndices);
        
        try {
            $recentAnnonces = annonce_covoiturage::getRecentAnnonces(8);
            
            if (empty($recentAnnonces)) {
                echo '<div class="col-span-full text-center text-gray-600">Aucun trajet récent disponible pour le moment.</div>';
            } else {
                foreach ($recentAnnonces as $index => $annonce) {
                    $imageUrl = $imageGenerator->getImageForLocation($annonce->getLieuArrivee(), $usedIndices);
                    $_SESSION['used_indices'] = $usedIndices;
                    
                    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
                    $dateFormatted = strftime('%d %B %Y', $annonce->getDateDepart()->getTimestamp());
                    
                    echo '
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden transition-all duration-300 animate-sway" data-location="' . htmlspecialchars($annonce->getLieuArrivee()) . '" data-index="' . $index . '">
                        <img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($annonce->getLieuDepart() . ' vers ' . $annonce->getLieuArrivee()) . '" class="w-full h-48 object-cover trajet-image" loading="lazy" onload="this.style.opacity=1" style="opacity:0;transition:opacity 0.5s">
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">' . 
                                htmlspecialchars($annonce->getLieuDepart()) . ' ➝ ' . htmlspecialchars($annonce->getLieuArrivee()) . 
                            '</h3>
                            <p class="text-gray-600 mt-2 transition-colors duration-300 text-sm">' . 
                                htmlspecialchars($annonce->getTypeVoiture()) . ' · ' . 
                                htmlspecialchars($annonce->getNombrePlaces()) . ' personnes · ' . 
                                htmlspecialchars($dateFormatted) . 
                            '</p>
                        </div>
                    </div>';
                }
            }
        } catch (Exception $e) {
            echo '<div class="col-span-full text-center text-red-500">Erreur lors du chargement des trajets récents: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

    <style>
        /* Title bounce-in animation */
        .animate-title {
            opacity: 0;
            transform: translateY(-20px);
            animation: bounceIn 0.8s ease-out forwards;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            60% {
                opacity: 1;
                transform: translateY(5px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Swaying motion for cards */
        .animate-sway {
            animation: sway 4s ease-in-out infinite;
            transform-origin: center;
        }

        @keyframes sway {
            0%, 100% {
                transform: translateX(0) rotate(0deg);
            }
            25% {
                transform: translateX(5px) rotate(1deg);
            }
            75% {
                transform: translateX(-5px) rotate(-1deg);
            }
        }

        /* Staggered start for sway */
        .animate-sway[data-index="0"] { animation-delay: 0s; }
        .animate-sway[data-index="1"] { animation-delay: 0.2s; }
        .animate-sway[data-index="2"] { animation-delay: 0.4s; }
        .animate-sway[data-index="3"] { animation-delay: 0.6s; }
        .animate-sway[data-index="4"] { animation-delay: 0.8s; }
        .animate-sway[data-index="5"] { animation-delay: 1s; }
        .animate-sway[data-index="6"] { animation-delay: 1.2s; }
        .animate-sway[data-index="7"] { animation-delay: 1.4s; }

        /* Hover tilt effect */
        .animate-sway:hover {
            animation-play-state: paused; /* Pause sway on hover */
            transform: rotate(3deg) scale(1.05);
            box-shadow: 0 8px 20px rgba(190, 60, 240, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Smooth image transition */
        .trajet-image {
            transition: opacity 0.5s ease;
        }
    </style>

    <script>
        // Function to refresh images
        function refreshImages() {
            const trajets = document.querySelectorAll('#trajets-grid > div');
            if (trajets.length === 0) return;
            const locations = Array.from(trajets).map(trajet => trajet.dataset.location);

            fetch('fetch_images.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(locations),
            })
            .then(response => response.json())
            .then(data => {
                trajets.forEach(trajet => {
                    const location = trajet.dataset.location;
                    const img = trajet.querySelector('.trajet-image');
                    if (data[location] && img.src !== data[location]) {
                        img.style.opacity = 0;
                        img.src = data[location];
                        img.onload = () => img.style.opacity = 1;
                    }
                });
            })
            .catch(error => console.error('Error refreshing images:', error));
        }

        // Initial refresh to ensure all images load
        window.addEventListener('load', refreshImages);
    </script>
</section>
   <section id="top-conducteurs" class="mt-20 scroll-mt-20 mx-auto max-w-7xl">
    <h2 class="text-2xl font-bold mb-8 text-center text-gray-800">Top Conducteurs</h2>
    <div class="flex flex-nowrap justify-center gap-6 px-4 overflow-x-auto">
        <?php 
        // Include config file
        require_once dirname(__DIR__) . '/config.php';

        // Get PDO connection
        $pdo = config::getConnexion();

        // Get user ratings from session
        if (!isset($_SESSION['user_ratings'])) {
            $_SESSION['user_ratings'] = [];
        }
        $userRatings = $_SESSION['user_ratings'];

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    prenom_conducteur, 
                    nom_conducteur, 
                    likes AS average_rating, 
                    dislikes AS vote_count
                FROM 
                    annonce_covoiturage 
                ORDER BY 
                    likes DESC, 
                    date_depart DESC 
                LIMIT 6
            ");
            $stmt->execute();
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($drivers)) {
                echo '<div class="text-center text-gray-600">Aucun conducteur disponible pour le moment.</div>';
            } else {
                $index = 0;
                foreach ($drivers as $driver) {
                    $fullName = htmlspecialchars(trim($driver['prenom_conducteur'] . ' ' . $driver['nom_conducteur']));
                    $driverId = htmlspecialchars($driver['prenom_conducteur'] . '_' . $driver['nom_conducteur']);
                    
                    // Use identicon style for avatars
                    $avatarUrl = 'https://api.dicebear.com/7.x/identicon/svg?seed=' . urlencode($fullName) . '&backgroundColor=f7c7d7';
                    
                    $averageRating = isset($driver['average_rating']) ? (float)$driver['average_rating'] : 0;
                    $voteCount = isset($driver['vote_count']) ? (int)$driver['vote_count'] : 0;
                    
                    $userHasRated = isset($userRatings[$driverId]);
                    $userRating = $userRatings[$driverId] ?? null;

                    $instagramLink = '#';
                    $facebookLink = '#';
                    
                    // Add delay for cascading effect
                    $delay = $index * 0.15;
                    echo '
                    <div class="top-driver text-center relative group animate-cascade-drop flex-shrink-0" style="animation-delay: ' . $delay . 's;">
                        <div class="relative w-fit mx-auto">
                            <img src="' . $avatarUrl . '" alt="' . $fullName . '" class="rounded-full w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-2 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-transparent text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                                <div class="flex gap-1 rating-stars" data-driver-id="' . $driverId . '">';
                                    for ($i = 1; $i <= 5; $i++) {
                                        $starClass = $userHasRated && $i <= $userRating ? 'fas fa-star text-yellow-400 animate-sparkle' : 'far fa-star text-gray-200 animate-sparkle';
                                        echo '<button class="star-btn ' . $starClass . '" data-rating="' . $i . '" ' . ($userHasRated ? 'disabled' : '') . '></button>';
                                    }
                    echo '
                                </div>
                            </div>
                        </div>
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">' . $fullName . '</h3>
                        <div class="flex justify-center gap-3 mt-2">
                            <span class="text-yellow-400 text-sm sm:text-base">' . number_format($averageRating, 1) . ' / 5 (' . $voteCount . ' votes)</span>
                        </div>
                        <div class="flex justify-center gap-3 mt-2">
                            <a href="' . $instagramLink . '" class="text-pink-400 hover:text-[#ff69b4] transition"><i class="fab fa-instagram text-lg sm:text-xl"></i></a>
                            <a href="' . $facebookLink . '" class="text-pink-400 hover:text .text-pink-400 hover:text-[#ff69b4] transition"><i class="fab fa-facebook text-lg sm:text-xl"></i></a>
                        </div>
                        <div class="particle-container"></div>
                    </div>';
                    $index++;
                }
            }
        } catch (Exception $e) {
            echo '<div class="text-center text-red-500">Erreur lors du chargement des conducteurs: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Cascade drop entrance animation */
        .animate-cascade-drop {
            opacity: 0;
            transform: translateY(-100px) scale(0.9);
            animation: cascadeDrop 0.7s ease-out forwards;
        }

        @keyframes cascadeDrop {
            0% {
                opacity: 0;
                transform: translateY(-100px) scale(0.9);
            }
            60% {
                opacity: 0.8;
                transform: translateY(10px) scale(1.05);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Card styling */
        .top-driver {
            position: relative;
            padding: 1rem;
            background: linear-gradient(135deg, #fff0f5, #f3e8ff);
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: visible;
            width: 200px;
        }

        .top-driver:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(255, 105, 180, 0.4);
        }

        /* Particle effect on hover */
        .particle-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .top-driver:hover .particle-container::before,
        .top-driver:hover .particle-container::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background: radial-gradient(circle, #ff69b4, transparent);
            border-radius: 50%;
            opacity: 0;
            animation: particleSwirl 1.5s ease-in-out infinite;
        }

        .top-driver:hover .particle-container::before {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .top-driver:hover .particle-container::after {
            bottom: 10%;
            right: 10%;
            animation-delay: 0.5s;
        }

        @keyframes particleSwirl {
            0% {
                opacity: 0.8;
                transform: translate(0, 0) scale(1);
            }
            50% {
                opacity: 1;
                transform: translate(20px, -20px) scale(1.5);
            }
            100% {
                opacity: 0;
                transform: translate(-20px, 20px) scale(0.5);
            }
        }

        /* Sparkle effect for stars */
        .animate-sparkle {
            position: relative;
            animation: sparkle 1.5s infinite ease-in-out;
        }

        .animate-sparkle::after {
            content: '\f0c3';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: -4px;
            right: -4px;
            font-size: 8px;
            color: #FFD700;
            opacity: 0;
            animation: sparkleBlink 1.5s infinite ease-in-out;
            animation-delay: 0.3s;
        }

        @keyframes sparkle {
            0%, 100% {
                transform: scale(1);
                opacity: 0.9;
            }
            50% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        @keyframes sparkleBlink {
            0%, 100% {
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
        }

        /* Centering adjustments */
        #top-conducteurs {
            text-align: center;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .top-driver {
                padding: 0.75rem;
                width: 160px;
            }
            .top-driver img {
                width: 18vw !important;
                height: 18vw !important;
            }
            .top-driver h3 {
                font-size: 0.9rem;
            }
            .top-driver .text-yellow-400 {
                font-size: 0.8rem;
            }
            .top-driver .fab {
                font-size: 1rem;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .top-driver {
                padding: 0.875rem;
                width: 180px;
            }
            .top-driver img {
                width: 22vw !important;
                height: 22vw !important;
            }
        }
    </style>
    
    <script>
        document.querySelectorAll('.star-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (this.disabled) return;

                const driverId = this.closest('.rating-stars').dataset.driverId;
                const rating = parseInt(this.dataset.rating);
                const container = this.closest('.top-driver');
                const stars = container.querySelectorAll('.star-btn');
                const ratingDisplay = container.querySelector('.text-yellow-400');

                fetch('handle_rating.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ driver_id: driverId, rating: rating }),
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error: HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        stars.forEach((star, index) => {
                            star.className = index < rating ? 'star-btn fas fa-star text-yellow-400 animate-sparkle' : 'star-btn far fa-star text-gray-200 animate-sparkle';
                            star.disabled = true;
                        });
                        ratingDisplay.textContent = `${data.average_rating.toFixed(1)} / 5 (${data.vote_count} votes)`;
                    } else {
                        console.error('Server error:', data.error);
                        alert('Erreur: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error.message);
                    alert('Erreur réseau. Veuillez réessayer. Détails: ' + error.message);
                });
            });
        });
    </script>
</section><br><br>


<!-- Chatbot FAQ Section -->
<section id="faq" class="bg-[#f9f9fb] py-12 px-4 sm:px-8 lg:px-16">
    <h2 class="text-3xl font-bold text-center text-[#be3cf0] mb-10 animate-title">Parlez avec Click'N'Go</h2>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-md overflow-hidden border-2 border-[#be3cf0] animate-chat-window">
            <!-- Chat Header -->
            <div class="bg-gradient-to-r from-[#be3cf0] to-[#ff6666] text-white p-4 flex items-center gap-2">
                <i class="fas fa-robot text-xl"></i>
                <h3 class="text-lg font-semibold">Click'N'Go  Bot</h3>
            </div>
            <!-- Chat Messages -->
            <div id="chat-messages" class="p-4 h-64 overflow-y-auto bg-[#f3e8ff] flex flex-col gap-3">
                <div class="bot-message bg-[#be3cf0] text-white p-3 rounded-lg self-start max-w-[80%] animate-message">
                    Salut ! Je suis le bot Click'N'Go, ton guide pour des aventures épiques en Tunisie ! 🌴 Pose une question  »
                </div>
            </div>
            <!-- Chat Input -->
            <div class="p-4 bg-white border-t border-[#be3cf0]">
                <div class="flex items-center gap-2">
                    <input id="chat-input" type="text" placeholder="Karting, shopping, raquettes, sponsors... pose ta question !" class="flex-1 p-2 border border-[#be3cf0] rounded-lg focus:outline-none focus:border-[#ff6666] animate-pulse-input" aria-label="Posez une question sur Click'N'Go">
                    <button id="chat-send" class="p-2 bg-[#be3cf0] text-white rounded-lg hover:bg-[#ff6666] transition-colors duration-300" aria-label="Envoyer la question">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Title bounce-in animation */
        .animate-title {
            opacity: 0;
            transform: translateY(-20px);
            animation: bounceIn 0.8s ease-out forwards;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            60% {
                opacity: 1;
                transform: translateY(5px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Chat window entrance animation */
        .animate-chat-window {
            opacity: 0;
            transform: scale(0.95);
            animation: scaleIn 0.5s ease-out forwards;
        }

        @keyframes scaleIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Message fade-in animation */
        .animate-message {
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.3s ease-out forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pulse effect for input */
        .animate-pulse-input {
            animation: pulseBorder 2s ease-in-out infinite;
        }

        @keyframes pulseBorder {
            0%, 100% {
                border-color: #be3cf0;
            }
            50% {
                border-color: #ff6666;
            }
        }

        /* Typing animation */
        .typing {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 10px;
            background: #be3cf0;
            color: white;
            border-radius: 15px 15px 15px 5px;
            max-width: 80%;
            align-self: flex-start;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: typingDot 1s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typingDot {
            0%, 20% {
                transform: translateY(0);
                opacity: 1;
            }
            50% {
                transform: translateY(-5px);
                opacity: 0.5;
            }
            80%, 100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Chat message styles */
        .bot-message {
            background: #be3cf0;
            color: white;
            border-radius: 15px 15px 15px 5px;
        }

        .user-message {
            background: #ff69b4; /* Changed to pink */
            color: white;
            border-radius: 15px 15px 5px 15px;
            align-self: flex-end;
        }

        /* Scrollbar styling */
        #chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        #chat-messages::-webkit-scrollbar-track {
            background: #f3e8ff;
        }

        #chat-messages::-webkit-scrollbar-thumb {
            background: #be3cf0;
            border-radius: 4px;
        }
    </style>

    <script>
        // Expanded FAQ data stored client-side
        const faqs = [
            {
                question: "Qu'est-ce que Click'N'Go ?",
                answer: "Click'N'Go, c’est ta plateforme de loisirs ultime en Tunisie ! Trouve des trajets pour des aventures palpitantes à Djerba, des festivals à Sousse, ou du karting à Tunis. Shop des produits fun et embarque pour l’épopée ! 🌟",
                keywords: ["platforme", "clickngo", "loisirs", "aventure"]
            },
            {
                question: "C'est quoi le covoiturage ?",
                answer: "Le covoiturage, c’est partager une voiture pour des destinations vibrantes comme des plages ou des festivals. Économique, écolo, et super convivial ! 🚗",
                keywords: ["covoiturage", "définition", "partage"]
            },
            {
                question: "Quels sont les avantages du covoiturage ?",
                answer: "Le covoiturage, c’est génial ! Économise des dinars, réduis ton empreinte carbone, et fais des rencontres fun. Parfait pour tes escapades à Hammamet ou Kairouan ! 🌍",
                keywords: ["avantages", "covoiturage", "écolo", "économique", "bénéfices"]
            },
            {
                question: "Pourquoi utiliser le covoiturage ?",
                answer: "Pour des trajets abordables et éco-chic ! Connecte avec des aventuriers et explore Tunis ou Zaghouan. Voyage malin avec Click'N'Go ! 🎉",
                keywords: ["pourquoi", "covoiturage", "utiliser", "raison"]
            },
            {
                question: "Comment réserver un trajet ?",
                answer: "Facile ! Dans la navbar, clique sur ‘Nos trajets’ pour réserver ton aventure. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🚀",
                keywords: ["réserver", "trajet", "navbar", "nos trajets"]
            },
            {
                question: "Comment être conducteur ?",
                answer: "Rejoins l’aventure ! Dans la navbar, clique sur ‘Proposer un covoiturage’ pour transporter des voyageurs vers des plages ou festivals. Gagne des dinars et des sourires ! 😎",
                keywords: ["conducteur", "devenir", "proposer", "navbar"]
            },
            {
                question: "Est-ce que les trajets sont sûrs ?",
                answer: "Oui, 100% ! Conducteurs vérifiés et avis transparents. Pars à Sidi Bou Saïd ou Tozeur l’esprit léger ! 🛡️",
                keywords: ["sûr", "sécurité", "avis", "vérifié"]
            },
            {
                question: "Y a-t-il du shopping ici ?",
                answer: "Click'N'Go est parfait pour toi ! Visite notre partie produits, y’a un choix divers pour tous tes besoins ! Shop des raquettes, puzzles, et plus encore ! 🛍️",
                keywords: ["shopping", "shop", "produits", "achat"]
            },
            {
                question: "Quels produits puis-je trouver ?",
                answer: "Tu trouves tout sur Click'N'Go dans la section produits ! Ajoute au panier : raquette avec balle de tennis, ballons, corde à sauter, haltères réglables, planche de surf, lunettes de natation, chaussures de plage, bouteille d’eau de sport, combinaison de wingsuit, veste coupe-vent, leggings et brassière, chaussures de randonnée, caméra instantanée, casque VR, montre connectée, table inclinable, smoothie shaker, lampe loupe, support de dessin, carnet de dessin, crayons de couleurs, cube de Rubik, jeux de table, puzzle, jeu de mémoire visuelle, BrainBox. Tout pour tes aventures ! 🛒",
                keywords: ["produits", "articles", "acheter", "catalogue"]
            },
            {
                question: "Où faire du karting en Tunisie ?",
                answer: "Fonce au Karting Park à Monastir, près de l’aéroport ! Courses endiablées sur piste pro, combinaisons fournies, dès 20 TND pour 10 min. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🏎️",
                keywords: ["karting", "course", "monastir"]
            },
            {
                question: "Où jouer au paintball ?",
                answer: "Rends-toi au Zizou Paintball Club à Yasmine Hammamet ! Équipe-toi pour des batailles stratégiques, dès 30 TND par personne. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🔫",
                keywords: ["paintball", "zizou", "hammamet"]
            },
            {
                question: "Quels sont les parcs d'attractions en Tunisie ?",
                answer: "Visite le Centre Venizia à Hammamet pour manèges et karting, ou Happy Land Park à Tunis pour des jeux familiaux. Entrée dès 10 TND. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🎢",
                keywords: ["parc", "attractions", "venizia", "happy land"]
            },
            {
                question: "Où trouver des parcs aquatiques ?",
                answer: "Plonge à l’Aqua Land à Yasmine Hammamet ! Toboggans et piscines pour tous, entrée environ 25 TND. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🏊",
                keywords: ["parc aquatique", "aqua land", "toboggans"]
            },
            {
                question: "Où participer à des ateliers de peinture ?",
                answer: "Essaie les ateliers à Tunis (La Marsa) ou Sousse, souvent dans des galeries comme Nizar. Crée tes œuvres pour 50-100 TND par session. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🎨",
                keywords: ["peinture", "atelier", "art"]
            },
            {
                question: "Où trouver des ateliers de musique ?",
                answer: "Participe à des cours de oud ou darbouka à Tunis (Médina) ou Djerba, dès 40 TND par session. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🎶",
                keywords: ["musique", "atelier", "oud", "darbouka"]
            },
            {
                question: "Où surfer en Tunisie ?",
                answer: "Ride les vagues à Bizerte ou Raf Raf ! Locations de planches dès 30 TND/jour. Shop ta planche sur Click'N'Go ! Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🏄",
                keywords: ["surfer", "surf", "bizerte", "raf raf"]
            },
            {
                question: "Où faire de la randonnée ?",
                answer: "Explore les sentiers de Zaghouan ou Ichkeul, guidés pour 100-150 TND/jour. Shop des chaussures de randonnée sur Click'N'Go ! Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🥾",
                keywords: ["randonnée", "hiking", "zaghouan", "ichkeul"]
            },
            {
                question: "Où faire un safari ?",
                answer: "Pars à Douz pour un safari désertique avec chameaux, dès 150 TND/jour. Shop une veste coupe-vent sur Click'N'Go ! Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🐪",
                keywords: ["safari", "désert", "douz"]
            },
            {
                question: "Où explorer les souks ?",
                answer: "Flâne dans les souks de Tunis ou Nabeul pour des trésors artisanaux, entrée gratuite. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🛍️",
                keywords: ["souk", "marché", "artisanat"]
            },
            {
                question: "Où voir les étoiles ?",
                answer: "Admire les étoiles à Tataouine ou dans le Sahara, soirées guidées dès 80 TND. Shop une caméra instantanée sur Click'N'Go ! Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🌠",
                keywords: ["étoiles", "stargazing", "tataouine"]
            },
            {
                question: "Où faire du trampoline ?",
                answer: "Saute au Trampoline Park à Tunis (Les Berges du Lac) ! Sessions fun pour 15-20 TND/heure. Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🤸",
                keywords: ["trampoline", "sauter", "tunis"]
            },
            {
                question: "Où jouer à des jeux ninja ?",
                answer: "Tente le Ninja Warrior Course à Sousse, obstacles et défis pour 25 TND par session. Pas de transport ? Visite notre partie services ‘Nos trajets’ for a covoiturage ! 💪",
                keywords: ["ninja", "warrior", "sousse"]
            },
            {
                question: "Où apprendre la cuisine tunisienne ?",
                answer: "Participe à des ateliers culinaires à Tunis (Médina) ou Testour, dès 50 TND. Prépare du couscous et plus encore ! Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🍲",
                keywords: ["cuisine", "tunisienne", "atelier"]
            },
            {
                question: "Où faire du quad ?",
                answer: "Fonce dans le désert à Tozeur ou Douz pour des virées en quad, dès 100 TND/heure. Shop une veste coupe-vent sur Click'N'Go ! Pas de transport ? Visite notre partie services ‘Nos trajets’ pour un covoiturage ! 🏍️",
                keywords: ["quad", "désert", "tozeur"]
            },
            {
                question: "Où trouver des raquettes de tennis ou padel ?",
                answer: "Trouve des raquettes avec balle de tennis ou padel dans notre section produits ! Parfait pour jouer à Tunis ou Hammamet. Ajoute au panier ! 🏸",
                keywords: ["raquette", "tennis", "padel"]
            },
            {
                question: "Où acheter des ballons ?",
                answer: "Des ballons pour foot ou basket sont dans notre section produits ! Idéal pour les parcs à Sousse. Ajoute au panier ! ⚽",
                keywords: ["ballon", "foot", "basket"]
            },
            {
                question: "Où trouver des pulls pour le sport ?",
                answer: "Découvre des pulls stylés dans notre section produits, parfaits pour les randos ou soirées fraîches. Ajoute au panier ! 🧥",
                keywords: ["pull", "pulls", "sport"]
            },
            {
                question: "Où acheter une corde à sauter ?",
                answer: "La corde à sauter est dans notre section produits, idéale pour s’entraîner partout ! Ajoute au panier ! 🏋️",
                keywords: ["corde", "sauter", "fitness"]
            },
            {
                question: "Où trouver des haltères réglables ?",
                answer: "Shop des haltères réglables dans notre section produits pour booster ton training. Ajoute au panier ! 🏋️",
                keywords: ["haltères", "poids", "training"]
            },
            {
                question: "Où acheter une planche de surf ?",
                answer: "Trouve une planche de surf dans notre section produits pour rider à Bizerte ! Ajoute au panier ! 🏄",
                keywords: ["planche", "surf", "plage"]
            },
            {
                question: "Où trouver des lunettes de natation ?",
                answer: "Les lunettes de natation sont dans notre section produits, parfaites pour les piscines de Tunis. Ajoute au panier ! 🏊",
                keywords: ["lunettes", "natation", "piscine"]
            },
            {
                question: "Où acheter des chaussures de plage ?",
                answer: "Shop des chaussures de plage dans notre section produits pour Djerba ou Monastir. Confort garanti ! 🩴",
                keywords: ["chaussures", "plage", "sandales"]
            },
            {
                question: "Où trouver des fournitures d’art ?",
                answer: "Carnet de dessin, crayons de couleurs, support de dessin, lampe loupe : tout est dans notre section produits ! Crée à fond ! 🎨",
                keywords: ["art", "peinture", "dessin", "crayons"]
            },
            {
                question: "Où acheter des jeux de réflexion ?",
                answer: "Cube de Rubik, puzzle, jeu de mémoire visuelle, BrainBox : shop dans notre section produits pour des soirées fun ! Ajoute au panier ! 🧩",
                keywords: ["jeux", "puzzle", "rubik", "brainbox"]
            },
            {
                question: "Combien coûte un trajet ?",
    answer: "Le coût dépend du trajet ! Pour plus de détails, visitez 'Nos trajets' ou 'Nos trajets récents'. En général, les prix varient de 2 TND à 40 TND. 🌟",
    keywords: ["coût", "prix", "trajet", "tarif"]
            },
            {
                question: "Puis-je voyager en groupe ?",
                answer: "Oui ! Filtre par places pour une virée entre amis à Monastir ou le Sahara. Plus on est, plus c’est fun ! 🎈",
                keywords: ["groupe", "amis", "places"]
            },
            {
                question: "Comment payer mon trajet ?",
                answer: "Paye le conducteur en cash ou via Flouci. Bientôt des paiements en ligne pour tes escapades ! 💳",
                keywords: ["payer", "paiement", "espèces"]
            },
            {
                question: "Click'N'Go est-il éco-friendly ?",
                answer: "Oui ! Moins de CO2 grâce au covoiturage. Explore Tabarka ou El Jem éco-chic ! 🌍",
                keywords: ["éco", "environnement", "green"]
            },
            {
                question: "Puis-je emmener mon animal ?",
                answer: "Certains conducteurs disent oui ! Vérifie les détails du trajet pour ton aventure avec ton compagnon ! 🐶",
                keywords: ["animal", "chien", "chat"]
            },
            {
                question: "Quels sont les sponsors de ce site ?",
                answer: "Nos sponsors sont Ooredoo, Saida, Pathé, Dabchy, Vitalait, TravelTodo et Lella, qui soutiennent tes aventures loisirs en Tunisie ! 🌟",
                keywords: ["sponsors", "partenaires", "soutien"]
            }
        ];

        // Greetings and special responses
        const greetings = {
            "bonjour": "Salut ! Prêt pour une aventure en Tunisie ? Demande-moi sur le karting, shopping, sponsors, ou produits ! 🌟",
            "salut": "Hey ! Envie d’une escapade ? Pose une question sur les plages, raquettes, ou sponsors ! 🚗",
            "merci": "De rien ! 😊 Quelle aventure ou produit veux-tu explorer maintenant ?",
            "hello": "Hi ! Ready for Tunisian fun? Ask about paintball, shopping, or sponsors! 🌴"
        };

        // Activity-related keywords
        const activityKeywords = [
            "karting", "paintball", "parc", "aquatique", "peinture", "musique", 
            "surf", "randonnée", "hiking", "safari", "souk", "étoiles", 
            "stargazing", "cuisine", "atelier", "plongée", "diving", "camping", 
            "quad", "parapente", "pêche", "voile", "concert", "exposition", 
            "théâtre", "jeux", "manèges", "trampoline", "ninja"
        ];

        // Handle chat input
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');
        const chatMessages = document.getElementById('chat-messages');

        chatSend.addEventListener('click', () => {
            const userQuery = chatInput.value.trim();
            if (userQuery) {
                handleUserQuery(userQuery);
                chatInput.value = '';
            }
        });

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && chatInput.value.trim()) {
                handleUserQuery(chatInput.value.trim());
                chatInput.value = '';
            }
        });

        // Add message to chat
        function addMessage(question, answer) {
            // Add user question
            const userMsg = document.createElement('div');
            userMsg.className = 'user-message p-3 rounded-lg max-w-[80%] animate-message';
            userMsg.textContent = question;
            chatMessages.appendChild(userMsg);

            // Add typing animation
            const typingMsg = document.createElement('div');
            typingMsg.className = 'typing';
            typingMsg.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
            chatMessages.appendChild(typingMsg);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Show answer after 3s
            setTimeout(() => {
                typingMsg.remove();
                const botMsg = document.createElement('div');
                botMsg.className = 'bot-message p-3 rounded-lg max-w-[80%] animate-message';
                botMsg.textContent = answer;
                chatMessages.appendChild(botMsg);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 3000);
        }

        // Handle typed user query with improved matching
        function handleUserQuery(query) {
    const lowerQuery = query.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    // Check for greetings
    for (const [greet, response] of Object.entries(greetings)) {
        if (lowerQuery.includes(greet)) {
            addMessage(query, response);
            return;
        }
    }

    // Check for activity-related queries
    if (activityKeywords.some(keyword => lowerQuery.includes(keyword))) {
        addMessage(query, "Visitez notre partie activité, vous trouverez ce que vous recherchez par détails ! Besoin d’un covoiturage pour y aller ? Visite notre partie services ‘Nos trajets’ ! 🚗");
        return;
    }

    // Find the best FAQ match based on keyword scoring
    let bestMatch = null;
    let highestScore = 0;

    faqs.forEach(faq => {
        let score = 0;
        faq.keywords.forEach(keyword => {
            if (lowerQuery.includes(keyword.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''))) {
                score++;
            }
        });
        // Additional score for question similarity
        if (faq.question.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes(lowerQuery)) {
            score += 2; // Give higher weight to question similarity
        }
        if (score > highestScore) {
            highestScore = score;
            bestMatch = faq;
        }
    });

    if (bestMatch) {
        addMessage(query, bestMatch.answer);
    } else {
        addMessage(query, "Oups, je n’ai pas compris ! Tente des mots comme ‘karting’, ‘shopping’, ‘sponsors’ ou ‘raquettes’. Quelle aventure ou produit veux-tu explorer ? 😊");
    }
}
    </script>
</section>

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
                <img src="/Projet Web/mvcCovoiturage/public/images/cov.webp" alt="">
            </div>
        </div>
    </section>

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
<script>
        // Static global variable for user ID
        const id_user = 12345; // You can set any value you want here
    </script>


<style>

    .carpooling-home {
    padding: 60px 7%;
    text-align: center;
}

.carpooling-container {
    background: linear-gradient(to right, #2d2f5f, #6b248f, #0a6d9b);
    border-radius: 15px;
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

.carpooling-content {
    color: white;
    margin-bottom: 30px;
    text-align: center;
}

.carpooling-content h2 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.carpooling-features {
    list-style: none;
    margin-bottom: 25px;
}

.feature-item {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    font-size: 1.1rem;
}

.feature-icon {
    width: 30px;
    margin-right: 15px;
}

.carpooling-btn {
    display: block;
    margin: 0 auto;
    background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    width: fit-content; /* Pour un meilleur ajustement */
}

.carpooling-btn:hover {
    background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.carpooling-image img {
    width: 100%;
    max-width: 400px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

@media (min-width: 768px) {
    .carpooling-container {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 40px;
    }
    
    .carpooling-content {
        text-align: left;
        margin-bottom: 0;
        margin-right: 40px;
        flex: 1;
    }
    
    .carpooling-image {
        flex-shrink: 0;
    }
}


.footer-wrapper {
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    background-color: #f5f5f5;
    padding: 3rem 0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    margin-top: 50px;
    background-attachment: fixed;
    background-position: center;
    background-size: cover;
    color: #333;
}




.footer-logo {
    width: 150px;
    margin-bottom: 10px;
    display: block;
}

.newsletter {
    display: flex;
    width: 100%;
    position: relative;
    top: 60px;
    max-width: 1000px;
    margin: auto;
    background-color: #303035;
    justify-content: space-around;
    align-items: center;
    padding: 20px 15px;
    border-radius: 10px;
}

.newsletter-left h2 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1rem;
    opacity: 0.5;
    letter-spacing: 1px;
}

.newsletter-left h1 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1.5rem;
}

.newsletter-right {
    width: 500px;
}

.newsletter-input {
    background-color: #ffffff;
    padding: 5px;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
}

.newsletter-input input {
    border: none;
    outline: none;
    background: transparent;
    width: 80%;
    padding-left: 10px;
    font-weight: 600;
}

.newsletter-input button {
    background-color: #201e1e;
    padding: 9px 15px;
    border-radius: 15px;
    color: #ffffff;
    cursor: pointer;
    border: none;
}

.newsletter-input button:hover {
    background-color: #3a3939;
}

.footer-content {
    background-color:  #f4f4f4;
    padding: 100px 40px 40px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footer-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    margin-bottom: 20px;
}

.footer-main h2 {
    color: #ffffff;
    font-size: 1.6rem;
}

.footer-main p {
    color: 1c3f50;
    font-size: 0.8rem;
    line-height: 1.3rem;
}

.social-links {
    margin: 15px 0px;
    display: flex;
    gap: 8px;
}

.social-links a {
    padding: 5px;
    background-color: black;
    border-radius: 5px;
    transition: 0.5s;
    text-decoration: none;
}

.social-links a:hover {
    opacity: 0.7;
}

.social-links a i {
    margin: 2px;
    font-size: 1.1rem;
    color: #201e1e;
}

.links {
    display: flex;
    flex-direction: column;
    width: 200px;
    margin: 40px 20px;
}

.links p {
    color: #1c3f50;
    font-size: 1.1rem;
    margin-bottom: 10px;
    font-weight: bold;
}

.links a {
    color: #1c3f50;
    text-decoration: none;
    margin: 5px 0;
    opacity: 0.7;
    font-size: 0.9rem;
}

.links a:hover {
    opacity: 1;
}

.social-icons {
    display: flex;
    flex-direction: row; /* ✅ forcer l'affichage en ligne */
    flex-wrap: nowrap;   /* ✅ pas de retour à la ligne */
    justify-content: center; /* ✅ centrer les icônes horizontalement */
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}


@import url(https://use.fontawesome.com/releases/v5.0.8/css/all.css);

.icon {
    margin: 0 10px;
    margin-bottom: 30px;
    border-radius: 50%;
    box-sizing: border-box;
    background: transparent;
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none !important;
    transition: 0.5s;
    color: var(--color);
    font-size: 2.5em;
    -webkit-box-reflect: below 5px linear-gradient(to bottom, rgba(0, 0, 0, 0),rgba(0, 0, 0, 0.2));
}

.icon i {
    color: var(--color);
}

.icon:hover {
    background: var(--color);
    box-shadow: 0 0 5px var(--color),
                0 0 25px var(--color), 
                0 0 50px var(--color),
                0 0 200px var(--color);
}

/* ✅ changer la couleur de l’icône en noir au survol */
.icon:hover i {
    color: #050801;
}
.payment-icons img {
    height: 20px;
    margin-right: 20px;
}
/* Add this to your CSS file */
.animated-text {
    animation: fadeInUp 1.5s ease-in-out;
}

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

</body>
</html>