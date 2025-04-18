<?php
include '../../config.php';
$conn = config::getConnexion();

require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");

// Handle form submission before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addOffer'])) {
    $titre = $_POST['titre_offre'] ?? '';
    $description = $_POST['description_offre'] ?? '';
    $evenement = $_POST['evenement'] ?? '';
    $montant = floatval($_POST['montant_offre'] ?? 0);
    $status = $_POST['status'] ?? 'libre';

    if ($titre && $description && $evenement && $montant > 0) {
        $offer = new Offre($titre, $description, $evenement, $montant, $status);
        $controller = new sponsorController();
        $success = $controller->addOffer($offer);
        if ($success) {
            // Redirect to avoid form resubmission
            header("Location: " . $_SERVER['PHP_SELF'] . "?added=1");
            exit();
        } else {
            $error_message = "Erreur lors de l'ajout de l'offre.";
        }
    } else {
        $error_message = "Veuillez remplir tous les champs correctement.";
    }
}

// Traitement Accepter / Refuser
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'accepter') {
        $stmt = $conn->prepare("UPDATE sponsor SET status = 'accepté' WHERE id_sponsor = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'refuser') {
        $stmt = $conn->prepare("UPDATE sponsor SET status = 'refusé' WHERE id_sponsor = ?");
        $stmt->execute([$id]);
    }
}

// Récupération des demandes
$stmt = $conn->query("SELECT * FROM sponsor ORDER BY id_sponsor DESC");
$sponsors = $stmt->fetchAll();

// Récupération des offres
$controller = new sponsorController();
$offers = $controller->listOffers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Sponsors</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body {
            background: #f6f4f0;
            display: flex;
        }
        .sidebar {
            width: 240px;
            background: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .menu-item {
            padding: 10px 0;
            cursor: pointer;
            user-select: none;
            color: black;
        }
        .menu-item.active {
            font-weight: bold;
            color: #c122c1;
        }
        .dashboard {
            margin-left: 240px;
            padding: 20px;
            width: calc(100% - 240px);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-weight: 500;
        }
        .btn-accepter {
            background: #4CAF50;
            color: white;
        }
        .btn-refuser {
            background: #f44336;
            color: white;
        }
        form {
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.3rem;
        }
        input, textarea, select {
            width: 100%;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        button.btn-accepter {
            background-color: #c122c1;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <h1>Click'N'Go</h1>
            <div class="menu-item active" data-tab="sponsors" tabindex="0" role="button" aria-pressed="true">🏠 Sponsors</div>
            <div class="menu-item" data-tab="events" tabindex="0" role="button" aria-pressed="false">🚗 Événements</div>
            <div class="menu-item" data-tab="offers" tabindex="0" role="button" aria-pressed="false">📋 Offres</div>
            <div class="menu-item">⚙️ Paramètres</div>
        </div>
        <div class="menu-item">🚪 Déconnexion</div>
    </div>
    <main class="dashboard">
        <div id="tab-sponsors" class="tab-content active" tabindex="0" style="display:block;">
            <h1>Demandes de sponsoring</h1>
            <?php foreach($sponsors as $sponsor): ?>
            <div class="card">
                <h3><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h3>
                <p>Contact: <?= htmlspecialchars($sponsor['evenement']) ?></p>
                <p>Email: <?= htmlspecialchars($sponsor['email']) ?></p>
                <p>Téléphone: <?= htmlspecialchars($sponsor['telephone']) ?></p>
                <p>Montant: <?= htmlspecialchars($sponsor['montant']) ?> €</p>
                <div class="card-actions">
                    <a href="?action=accepter&id=<?= $sponsor['id_sponsor'] ?>" class="btn btn-accepter">Accepter</a>
                    <a href="?action=refuser&id=<?= $sponsor['id_sponsor'] ?>" class="btn btn-refuser" onclick="return confirm('Êtes-vous sûr?')">Refuser</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="tab-events" class="tab-content" tabindex="0">
            <h1>Ajouter une Offre de Sponsoring</h1>
            <form method="post" action="">
                <div class="form-group">
                    <label for="titre_offre">Titre de l'offre</label>
                    <input type="text" id="titre_offre" name="titre_offre" required />
                </div>
                <div class="form-group">
                    <label for="description_offre">Description</label>
                    <textarea id="description_offre" name="description_offre" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="evenement">Événement</label>
                    <input type="text" id="evenement" name="evenement" required />
                </div>
                <div class="form-group">
                    <label for="montant_offre">Montant (dt)</label>
                    <input type="number" id="montant_offre" name="montant_offre" min="0" step="0.01" required />
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="libre" selected>Libre</option>
                        <option value="occupé">Occupé</option>
                    </select>
                </div>
                <button type="submit" name="addOffer" class="btn btn-accepter">Ajouter l'offre</button>
            </form>
        </div>
        <div id="tab-offers" class="tab-content" tabindex="0">
            <h1>Offres de Sponsoring</h1>
            <?php if (!empty($offers)): ?>
                <?php foreach ($offers as $offer): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($offer['titre_offre']) ?></h3>
                        <p>Description: <?= htmlspecialchars($offer['description_offre']) ?></p>
                        <p>Événement: <?= htmlspecialchars($offer['evenement']) ?></p>
                        <p>Montant: <?= htmlspecialchars($offer['montant_offre']) ?> dt</p>
                        <p>Statut: <?= htmlspecialchars($offer['status']) ?></p>
                        <div class="card-actions">
                            <a href="modifier.php?id=<?= $offer['id_offre'] ?>" class="btn btn-accepter">Modifier</a>
                            <a href="delete.php?id=<?= $offer['id_offre'] ?>" class="btn btn-refuser" onclick="return confirm('Supprimer cette offre ?')">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune offre disponible.</p>
            <?php endif; ?>
        </div>
    </main>
    <script>
        const menuItems = document.querySelectorAll('.menu-item[data-tab]');
        const tabContents = document.querySelectorAll('.tab-content');

        // On page load, hide all tab contents except the active one
        tabContents.forEach(tc => {
            if (!tc.classList.contains('active')) {
                tc.style.display = 'none';
            } else {
                tc.style.display = 'block';
            }
        });

        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                menuItems.forEach(i => {
                    i.classList.remove('active');
                    i.setAttribute('aria-pressed', 'false');
                });
                tabContents.forEach(tc => {
                    tc.classList.remove('active');
                    tc.style.display = 'none';
                });
                item.classList.add('active');
                item.setAttribute('aria-pressed', 'true');
                const tab = item.getAttribute('data-tab');
                const activeTab = document.getElementById('tab-' + tab);
                activeTab.classList.add('active');
                activeTab.style.display = 'block';
            });
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    item.click();
                }
            });
        });
    </script>
</body>
</html>
