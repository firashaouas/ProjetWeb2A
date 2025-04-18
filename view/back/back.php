<?php
include '../../config.php';
$conn = config::getConnexion();

require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addOffer'])) {
    $titre = trim($_POST['titre_offre'] ?? '');
    $description = trim($_POST['description_offre'] ?? '');
    $evenement = trim($_POST['evenement'] ?? '');
    $montant = floatval($_POST['montant_offre'] ?? 0);
    $status = $_POST['status'] ?? 'libre';

    $errors = [];

    if (empty($titre) || strlen($titre) < 3 || strlen($titre) > 100) {
        $errors[] = "Le titre doit contenir entre 3 et 100 caractères.";
    }
    if (empty($description) || strlen($description) < 10 || strlen($description) > 1000) {
        $errors[] = "La description doit contenir entre 10 et 1000 caractères.";
    }
    if (empty($evenement) || strlen($evenement) < 3 || strlen($evenement) > 150) {
        $errors[] = "L'événement doit contenir entre 3 et 150 caractères.";
    }
    if ($montant <= 0) {
        $errors[] = "Le montant doit être supérieur à zéro.";
    }

    // Handle image upload or selection
    $imageFileName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../view/front/images/';
        $tmpName = $_FILES['image']['tmp_name'];
        $originalName = basename($_FILES['image']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "Le format de l'image n'est pas supporté. Formats acceptés: jpg, jpeg, png, gif.";
        } else {
            // Generate unique filename to avoid overwriting
            $imageFileName = uniqid('offer_', true) . '.' . $extension;
            $destination = $uploadDir . $imageFileName;

            if (!move_uploaded_file($tmpName, $destination)) {
                $errors[] = "Erreur lors de l'upload de l'image.";
            }
        }
    } elseif (!empty($_POST['image'])) {
        // If no file uploaded, check if an image was selected from dropdown
        $selectedImage = $_POST['image'];
        $imageDir = __DIR__ . '/../../view/front/images/';
        if (file_exists($imageDir . $selectedImage)) {
            $imageFileName = $selectedImage;
        } else {
            $errors[] = "L'image sélectionnée n'existe pas.";
        }
    }

    if (empty($errors)) {
        $offer = new Offre($titre, $description, $evenement, $montant, $status, $imageFileName);
        $controller = new sponsorController();
        $success = $controller->addOffer($offer);
        if ($success) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?added=1");
            exit();
        } else {
            $error_message = "Erreur lors de l'ajout de l'offre.";
        }
    } else {
        $error_message = implode("<br>", $errors);
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
        /* Improved CSS for view/back/back.php */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f6f4f0;
            display: flex;
            color: #333;
        }

        .sidebar {
            width: 240px;
            background: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }

        .menu-item {
            padding: 12px 0;
            cursor: pointer;
            user-select: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .menu-item:hover {
            color: #a01aa0;
        }

        .menu-item.active {
            font-weight: 700;
            color: #c122c1;
        }

        .dashboard {
            margin-left: 240px;
            padding: 30px 40px;
            width: calc(100% - 240px);
            background: #fff;
            min-height: 100vh;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-weight: 600;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }

        .btn-accepter {
            background: #c122c1;
            color: white;
            box-shadow: 0 4px 12px rgba(193, 34, 193, 0.4);
        }

        .btn-accepter:hover {
            background: #a01aa0;
            box-shadow: 0 6px 20px rgba(160, 26, 160, 0.6);
        }

        .btn-refuser {
            background: #f44336;
            color: white;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
        }

        .btn-refuser:hover {
            background: #d32f2f;
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.6);
        }

        form {
            max-width: 600px;
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #444;
            font-size: 1rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            border: 1.5px solid #ccc;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #c122c1;
            box-shadow: 0 0 8px rgba(193, 34, 193, 0.4);
        }

        button.btn-accepter {
            background-color: #c122c1;
            color: white;
            border: none;
            padding: 0.85rem 1.5rem;
            cursor: pointer;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }

        button.btn-accepter:hover {
            background-color: #a01aa0;
            box-shadow: 0 4px 15px rgba(160, 26, 160, 0.5);
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
            <form method="post" action="" id="offerForm" novalidate enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titre_offre">Titre de l'offre</label>
                    <input type="text" id="titre_offre" name="titre_offre" required minlength="3" maxlength="100" />
                    <small class="error-message"></small>
                </div>
                <div class="form-group">
                    <label for="description_offre">Description</label>
                    <textarea id="description_offre" name="description_offre" rows="4" required minlength="10" maxlength="1000"></textarea>
                    <small class="error-message"></small>
                </div>
                <div class="form-group">
                    <label for="evenement">Événement</label>
                    <input type="text" id="evenement" name="evenement" required minlength="3" maxlength="150" />
                    <small class="error-message"></small>
                </div>
                <div class="form-group">
                    <label for="montant_offre">Montant (dt)</label>
                    <input type="number" id="montant_offre" name="montant_offre" min="1" step="0.01" required />
                    <small class="error-message"></small>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="libre" selected>Libre</option>
                        <option value="occupé">Occupé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image">Image de l'offre</label>
                    <select id="image" name="image">
                        <option value="">-- Sélectionnez une image --</option>
                        <?php
                        $imageDir = __DIR__ . '/../../view/front/images/';
                        $images = array_diff(scandir($imageDir), array('.', '..'));
                        foreach ($images as $img) {
                            echo '<option value="' . htmlspecialchars($img) . '">' . htmlspecialchars($img) . '</option>';
                        }
                        ?>
                    </select>
                    <small class="error-message"></small>
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
                    <?php if (!empty($offer['image'])): ?>
                        <img src="../../view/front/images/<?= htmlspecialchars($offer['image']) ?>" alt="Image de l'offre" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px;" />
                    <?php endif; ?>
                    <div class="card-actions">
                        <a href="../front/modifierOffer.php?id=<?= $offer['id_offre'] ?>" class="btn btn-accepter">Modifier</a>
                        <a href="../front/deleteOffer.php?id=<?= $offer['id_offre'] ?>" class="btn btn-refuser" onclick="return confirm('Supprimer cette offre ?')">Supprimer</a>
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
    <script>
        // Client-side validation for offer form
        document.getElementById('offerForm').addEventListener('submit', function(event) {
            let form = event.target;
            let errors = false;

            // Clear previous error messages
            form.querySelectorAll('.error-message').forEach(el => el.textContent = '');

            // Validate titre_offre
            let titre = form.titre_offre.value.trim();
            if (titre.length < 3 || titre.length > 100) {
                form.titre_offre.nextElementSibling.textContent = 'Le titre doit contenir entre 3 et 100 caractères.';
                errors = true;
            }

            // Validate description_offre
            let description = form.description_offre.value.trim();
            if (description.length < 10 || description.length > 1000) {
                form.description_offre.nextElementSibling.textContent = 'La description doit contenir entre 10 et 1000 caractères.';
                errors = true;
            }

            // Validate evenement
            let evenement = form.evenement.value.trim();
            if (evenement.length < 3 || evenement.length > 150) {
                form.evenement.nextElementSibling.textContent = "L'événement doit contenir entre 3 et 150 caractères.";
                errors = true;
            }

            // Validate montant_offre
            let montant = parseFloat(form.montant_offre.value);
            if (isNaN(montant) || montant <= 0) {
                form.montant_offre.nextElementSibling.textContent = 'Le montant doit être un nombre supérieur à zéro.';
                errors = true;
            }

            if (errors) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
