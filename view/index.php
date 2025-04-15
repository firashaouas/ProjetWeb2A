<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Transport</title>
<link rel="stylesheet" href="/clickngo/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>   

<!-- BACKGROUND IMAGE WITH NAVBAR -->
<div class="relative w-full h-[85vh] mt-2">
    <!-- NAVBAR SUR L'IMAGE -->
    <nav class="absolute top-0 left-0 w-full z-50 p-4">
        <div class="flex items-center justify-center max-w-7xl mx-auto">
            <div class="flex space-x-8 text-lg font-bold text-black relative">
                <a href="#home" class="hover:text-[#be3cf0]">Accueil</a>
                <a href="#about" class="hover:text-[#be3cf0]">À propos</a>
                <div class="group relative">
                    <button class="hover:text-[#be3cf0] font-bold text-lg">
                        Nos Détails ▾
                    </button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Nos trajets</a>
                    <a href="#top-conducteurs" class="block px-4 py-2 hover:bg-gray-100">Top Conducteurs</a>
                    <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Liste des Conducteurs</a>
                    <a href="ListPassager.php" class="block px-4 py-2 hover:bg-gray-100">Liste des Passagers</a>
                </div>
                </div>
                
                <!-- LISTE DÉROULANTE SERVICES -->
                <div class="relative group">
                    <button class="hover:text-[#be3cf0]">Services ▾</button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="/clickngo/view/trouver.php" class="block px-4 py-2 hover:bg-gray-100">Trouver un covoiturage</a>
                        <a href="/clickngo/view/proposer.php" class="block px-4 py-2 hover:bg-gray-100">Proposer un covoiturage</a>
                    </div>
                </div>
                
                <!-- LISTE DÉROULANTE CONTACT -->
                <div class="relative group">
                    <button class="hover:text-[#be3cf0]">Contact ▾</button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[180px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="/clickngo/view/reclamation.php" class="block px-4 py-2 hover:bg-gray-100">Réclamation</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- IMAGE DE FOND -->
    <img alt="Background image of a person driving a car" class="absolute inset-0 w-full h-full object-cover opacity-75" src="/clickngo/public/images/eco-conduite-084014-650-325.jpg"/>

    <!-- WRAPPER POUR RÉTRÉCIR LA LARGEUR -->
    <div class="relative z-10 flex flex-col items-center justify-center h-full text-center text-black max-w-4xl mx-auto px-4">
        <h1 class="text-4xl font-bold">TROUVEZ</h1>
        <h2 class="text-5xl font-bold">UN COVOITURAGE</h2>
        <p class="mt-2 text-lg">La solution accessible et durable pour tous</p>
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
            <div class="flex items-center bg-white rounded-lg shadow-md">
                <i class="fas fa-map-marker-alt text-[#be3cf0] ml-4"></i>
                <input class="p-2 pl-4 pr-8 text-gray-700 rounded-lg focus:outline-none" placeholder="Adresse de départ" type="text"/>
                <i class="fas fa-paper-plane text-gray-500 mr-4"></i>
            </div>
            <div class="flex items-center bg-white rounded-lg shadow-md">
                <i class="fas fa-map-marker-alt text-[#ff6666] ml-4"></i>
                <input class="p-2 pl-4 pr-8 text-gray-700 rounded-lg focus:outline-none" placeholder="Adresse d'arrivée" type="text"/>
                <i class="fas fa-paper-plane text-gray-500 mr-4"></i>
            </div>
            <button type="button" onclick="window.location.href='/clickngo/view/resultats.php';" class="flex items-center px-6 py-2 text-white rounded-lg shadow-md elsa-gradient-primary elsa-gradient-primary-hover focus:outline-none">
                <span>Lancer ma recherche</span>
                <i class="fas fa-search ml-2"></i>
            </button>
        </div>
    </div>
</div>

<div class="w-full max-w-4xl mx-auto mt-8">
    <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Nos sélections par âge</h2>
    <div class="flex flex-wrap justify-center items-center gap-4">
        <!-- Carte Âge (Complétée) -->
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

<!-- Conteneur des boutons (dynamique) -->
<div id="role-buttons" class="hidden absolute z-50 flex items-center gap-4">
    <a href="/clickngo/view/AjoutConducteur.php" class="role-button elsa-gradient-primary elsa-gradient-primary-hover text-white text-sm font-semibold">Conducteur</a>
    <a href="/clickngo/view/DisplayConducteur.php" class="role-button elsa-gradient-primary elsa-gradient-primary-hover text-white text-sm font-semibold">Passager</a>
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
</section>
  
<!-- CENTRÉ parfaitement en dessous -->
<div class="see-all-events">
    <a href="/clickngo/view/avis.php" class="see-all-link elsa-gradient-primary elsa-gradient-primary-hover">Voir tous les avis </a>
</div>

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
                <img src="/clickngo/public/images/visa.webp" alt="Visa">
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