<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Accueil</title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #fff7fb;
    }

    .dashboard-container {
      display: flex;
    }

    .sidebar {
      width: 220px;
      background-color: #ffffff;
      color: #c04e9a;
      padding: 20px;
      height: 100vh;
      box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }

    .sidebar h2 {
      color: #f72975;
      margin-bottom: 25px;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar ul li {
      margin-bottom: 15px;
    }

    .sidebar ul li a {
      text-decoration: none;
      color: #fa80d1;
      display: flex;
      align-items: center;
      padding: 8px 10px;
      border-radius: 8px;
    }

    .sidebar ul li a:hover {
      background-color: #fbe3f1;
    }

    .main-content {
      flex: 1;
      padding: 30px;
    }

    h1 {
      color: #f72975;
      margin-bottom: 15px;
      text-align: center;
    }

    .search-bar {
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .search-bar input {
      width: 100%;
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 12px;
      border: 1px solid #e0cfe6;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      outline: none;
    }

    .grid-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
    }

    .grid-card {
      background-color: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      text-align: center;
      animation: popup 0.6s ease;
      transition: background-color 0.3s ease, transform 0.2s;
      cursor: pointer;
    }

    .grid-card:hover {
      transform: scale(1.05);
      background-color: #fbe3f1;
    }

    .grid-card.clicked {
      background-color: #f3c0e8;
    }

    .grid-card i {
      color: #ba68c8;
      font-size: 40px;
      margin-bottom: 10px;
    }

    .grid-card h3 {
      color: #ba68c8;
      margin: 10px 0;
    }

    .grid-card p {
      font-weight: bold;
      color: #555;
    }

    @keyframes popup {
      0% {
        opacity: 0;
        transform: scale(0.8);
      }
      100% {
        opacity: 1;
        transform: scale(1);
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <h2>Click'N'Go</h2>
      <ul>
        <li><a href="#"><i data-lucide="home"></i>&nbsp;Dashboard</a></li>
        <li><a href="demande_list.php"><i data-lucide="message-square"></i>&nbsp;Demande</a></li>
        <li><a href="annonces.php"><i data-lucide="message-square"></i>&nbsp;Annonces</a></li>
        
      </ul>
      
    </aside>

    <main class="main-content">
      <h1>Bienvenue sur le Dashboard</h1>

      <div class="search-bar">
        <input type="text" placeholder="Rechercher... 🔍" />
      </div>

      <div class="grid-container">
        <div class="grid-card"><i data-lucide="users"></i><h3>Utilisateurs</h3><p>250 inscrits</p></div>
        <div class="grid-card"><i data-lucide="car"></i><h3>Annonces</h3><p>45 en cours</p></div>
        <div class="grid-card"><i data-lucide="alert-circle"></i><h3>Réclamations</h3><p>12 à traiter</p></div>
        <div class="grid-card"><i data-lucide="message-circle"></i><h3>Demandes</h3><p>8 nouvelles</p></div>
        <div class="grid-card"><i data-lucide="calendar"></i><h3>Calendrier</h3><p>Trajets à venir</p></div>
        <div class="grid-card"><i data-lucide="check-circle"></i><h3>Validations</h3><p>5 à valider</p></div>
        <div class="grid-card"><i data-lucide="mail"></i><h3>Courriers</h3><p>3 reçus</p></div>
        <div class="grid-card"><i data-lucide="bell"></i><h3>Alertes</h3><p>2 urgentes</p></div>
        <div class="grid-card"><i data-lucide="clock"></i><h3>Horaires</h3><p>8h à 18h</p></div>
        <div class="grid-card"><i data-lucide="globe"></i><h3>Connexion</h3><p>Live</p></div>
      </div>
    </main>
  </div>

  <script>
    lucide.createIcons();

    const cards = document.querySelectorAll('.grid-card');
    cards.forEach(card => {
      card.addEventListener('click', () => {
        card.classList.toggle('clicked');
      });
    });
  </script>
</body>
</html>
