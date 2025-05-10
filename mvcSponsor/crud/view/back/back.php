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
        // Update sponsor status to 'accepted'
        $stmt = $conn->prepare("UPDATE sponsor2 SET status = 'accepted' WHERE id_sponsor = ?");
        $stmt->execute([$id]);

        // Get sponsor montant and id_offre
        $stmtSponsor = $conn->prepare("SELECT montant, id_offre FROM sponsor2 WHERE id_sponsor = ?");
        $stmtSponsor->execute([$id]);
        $sponsorData = $stmtSponsor->fetch(PDO::FETCH_ASSOC);

        if ($sponsorData) {
            $sponsorMontant = (float)$sponsorData['montant'];
            $idOffre = (int)$sponsorData['id_offre'];

            // Get current montant_offre and status from offre2
            $stmtOffre = $conn->prepare("SELECT montant_offre, status FROM offre2 WHERE id_offre = ?");
            $stmtOffre->execute([$idOffre]);
            $offreData = $stmtOffre->fetch(PDO::FETCH_ASSOC);

            if ($offreData) {
                $currentMontantOffre = (float)$offreData['montant_offre'];
                $currentStatus = $offreData['status'];

                // Calculate new montant_offre
                $newMontantOffre = $currentMontantOffre - $sponsorMontant;
                if ($newMontantOffre < 0) {
                    $newMontantOffre = 0;
                }

                // Determine new status
                $newStatus = $currentStatus;
                if ($newMontantOffre == 0) {
                    $newStatus = 'occupé';
                }

                // Update offre2 with new montant_offre and status
                $stmtUpdateOffre = $conn->prepare("UPDATE offre2 SET montant_offre = ?, status = ? WHERE id_offre = ?");
                $stmtUpdateOffre->execute([$newMontantOffre, $newStatus, $idOffre]);
            }
        }

        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=accept");
        exit();
    } elseif ($action === 'refuser') {
        $stmt = $conn->prepare("UPDATE sponsor2 SET status = 'refused' WHERE id_sponsor = ?");
        $stmt->execute([$id]);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=refuse");
        exit();
    }
}

// Récupération des demandes
$stmt = $conn->query("SELECT * FROM sponsor2 ORDER BY id_sponsor DESC");
$sponsors = $stmt->fetchAll();

// Récupération des offres
$controller = new sponsorController();
$offers = $controller->listOffers();

// Récupération des sponsors acceptés groupés par offre
$stmt2 = $conn->query("SELECT id_offre, GROUP_CONCAT(nom_entreprise SEPARATOR ', ') AS sponsors FROM sponsor2 WHERE status = 'accepted' GROUP BY id_offre");
$acceptedSponsors = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<?php
// Configuration de session sécurisée
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// Vérifier si utilisateur connecté
if (!isset($_SESSION['user']['id_user'])) {
    header('Location: /login.php'); // Adjust the login path as needed
    exit();
}


$conn = config::getConnexion();

// Function to generate color from string (from indeex.php)
function stringToColor($str) {
    $Colors = [
        '#FF6B6B', '#FF8E53', '#6B5B95', '#88B04B', '#F7CAC9',
        '#92A8D1', '#955251', '#B565A7', '#DD4124', '#D65076'
    ];
    $hash = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = ord($str[$i]) + (($hash << 5) - $hash);
    }
    return $Colors[abs($hash) % count($Colors)];
}
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
            background: #f8e1f7;
            padding: 20px;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }

        .menu-item {
            padding: 12px 0;
            cursor: pointer;
            user-select: none;
            color: #6a0a6a;
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

        /* New styles for statistics buttons and charts */
        .btn-group {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-stat {
            background-color: #c122c1;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(193, 34, 193, 0.5);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
            flex: 1 1 auto;
            text-align: center;
            white-space: nowrap;
        }

        .btn-stat:hover {
            background-color: #a01aa0;
            box-shadow: 0 6px 20px rgba(160, 26, 160, 0.7);
        }

        .btn-stat.active {
            background-color: #7a0e7a;
            box-shadow: 0 6px 25px rgba(122, 14, 122, 0.8);
        }

        #tab-statistics canvas {
            max-width: 1100px !important;
            width: 100% !important;
            height: 600px !important;
            margin: 1rem auto 0 auto;
            display: block;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(193, 34, 193, 0.15);
            background: #fff;
            padding: 20px;
        }
        #tab-statistics {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #tab-statistics h2 {
            font-weight: 700;
            color: #6a0a6a;
            margin-top: 2.5rem;
            margin-bottom: 0.75rem;
            font-size: 1.3rem;
            text-align: center;
        }
        /* User Profile Styles */
.user-profile {
    position: relative;
    display: inline-block;
    margin: 20px 0;
    width: 100%;
    text-align: center;
}

.profile-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    cursor: pointer;
    border: 2px solid #c122c1;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.profile-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 20px;
    cursor: pointer;
    margin: 0 auto;
}

.dropdown-menu {
    position: absolute;
    top: 60px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #f8e1f7;
    border: 1px solid #c122c1;
    border-radius: 6px;
    padding: 10px;
    display: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 100;
    width: 150px;
    text-align: left;
}

.dropdown-menu a {
    display: block;
    padding: 8px 12px;
    color: #6a0a6a;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.dropdown-menu a:hover {
    color: #a01aa0;
    background-color: #fff;
    border-radius: 4px;
}

.user-profile:hover .dropdown-menu {
    display: block;
}
    </style>
</head>
<body>
    <div class="sidebar">



    
    <div class="sidebar">
    <div>
        <h1>Click'N'Go</h1>

<a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/indeex.php" class="menu-item">🏠 Accueil</a>

        <div class="menu-item active" data-tab="sponsors" tabindex="0" role="button" aria-pressed="true">🏠 Sponsors</div>
        <div class="menu-item" data-tab="events" tabindex="0" role="button" aria-pressed="false">🚗 Événements</div>
        <div class="menu-item" data-tab="offers" tabindex="0" role="button" aria-pressed="false">📋 Offres</div>
        <div class="menu-item" data-tab="statistics">📊 Statistiques</div>
    </div>
    <!-- User Profile Section -->
    <div class="user-profile">
        <?php if (isset($_SESSION['user'])): ?>
            <?php
            $photoPath = $_SESSION['user']['profile_picture'] ?? '';
            $fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';
            // Adjust path for back.php's directory
            $photoRelativePath = '../../../../mvcUtilisateur/View/FrontOffice/' . $photoPath;
            $absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
            $showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);
            ?>
            <?php if ($showPhoto): ?>
                <img src="/Projet%20Web/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
            <?php else: ?>
                <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                    <?= strtoupper(substr($fullName, 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
                <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
            </div>
        <?php endif; ?>
    </div>
</div>
        <div class="menu-item">🚪 Déconnexion</div>
    </div>
    <main class="dashboard">
        <div id="tab-sponsors" class="tab-content active" tabindex="0" style="display:block;">
            <h1>Demandes de sponsoring</h1>
            <?php foreach($sponsors as $sponsor): ?>
            <div class="card">
            <h3><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h3>
            <?php
                // Find the event name for this sponsor's offer
                $eventName = '';
                foreach ($offers as $offer) {
                    if ($offer['id_offre'] == $sponsor['id_offre']) {
                        $eventName = $offer['evenement'];
                        break;
                    }
                }
            ?>
            <p>Événement: <?= htmlspecialchars($eventName) ?></p>
            <p>Email: <?= htmlspecialchars($sponsor['email']) ?></p>
            <p>Téléphone: <?= htmlspecialchars($sponsor['telephone']) ?></p>
            <p>Montant: <?= htmlspecialchars($sponsor['montant']) ?> €</p>
            <div class="card-actions">
                <button class="btn btn-accepter btn-action" data-id="<?= $sponsor['id_sponsor'] ?>" data-action="accept" <?= ($sponsor['status'] !== 'pending') ? 'disabled style="opacity:0.6; cursor:not-allowed;"' : '' ?>>Accepter</button>
                <button class="btn btn-refuser btn-action" data-id="<?= $sponsor['id_sponsor'] ?>" data-action="refuse" <?= ($sponsor['status'] !== 'pending') ? 'disabled style="opacity:0.6; cursor:not-allowed;"' : '' ?> onclick="return confirm('Êtes-vous sûr?')">Refuser</button>
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
                    <?php if (isset($acceptedSponsors[$offer['id_offre']])): ?>
                        <p><strong>Sponsors acceptés:</strong> <?= htmlspecialchars($acceptedSponsors[$offer['id_offre']]) ?></p>
                    <?php else: ?>
                        <p><em>Aucun sponsor accepté pour cette offre.</em></p>
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
        <div id="tab-statistics" class="tab-content" tabindex="0">
        <h1 style="text-align: center; margin-bottom: 1.5rem;">Statistiques des Événements</h1>
            <?php
                // Query to get count of accepted sponsors grouped by event
                $stmtStats = $conn->query("
                    SELECT o.evenement, COUNT(s.id_sponsor) AS sponsor_count
                    FROM offre2 o
                    LEFT JOIN sponsor2 s ON o.id_offre = s.id_offre AND s.status = 'accepted'
                    GROUP BY o.evenement
                    ORDER BY sponsor_count DESC
                ");
                $stats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

                // Find max sponsor count for highlighting
                $maxCount = 0;
                foreach ($stats as $row) {
                    if ($row['sponsor_count'] > $maxCount) {
                        $maxCount = $row['sponsor_count'];
                    }
                }
            ?>
            <?php if (!empty($stats)): ?>
                <div class="btn-group" role="group" aria-label="Choisir la statistique">
                    <button type="button" class="btn btn-stat active" data-chart="statsChart">Nombre de Sponsors</button>
                    <button type="button" class="btn btn-stat" data-chart="amountChart">Montant total sponsorisé</button>
                    <button type="button" class="btn btn-stat" data-chart="statusChart">Répartition des sponsors</button>
                </div>

                <h2 id="statsChartTitle" style="margin-top: 3rem;">Nombre de Sponsors</h2>
                <canvas id="statsChart" style="max-width: 600px; margin-top: 1rem;"></canvas>

                <?php
                // Total amount sponsored per event (accepted sponsors)
                $stmtAmount = $conn->query("
                    SELECT o.evenement, COALESCE(SUM(s.montant), 0) AS total_amount
                    FROM offre2 o
                    LEFT JOIN sponsor2 s ON o.id_offre = s.id_offre AND s.status = 'accepted'
                    GROUP BY o.evenement
                    ORDER BY total_amount DESC
                ");
                $amountStats = $stmtAmount->fetchAll(PDO::FETCH_ASSOC);

                // Number of sponsors by status
                $stmtStatus = $conn->query("
                    SELECT status, COUNT(*) AS count
                    FROM sponsor2
                    GROUP BY status
                ");
                $statusStats = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <h2 id="amountChartTitle" style="margin-top: 3rem; display:none;">Montant total sponsorisé par événement</h2>
                <canvas id="amountChart" style="max-width: 600px; margin-top: 1rem; display:none;"></canvas>

                <h2 id="statusChartTitle" style="margin-top: 3rem; display:none;">Répartition des sponsors par statut</h2>
                <canvas id="statusChart" style="max-width: 600px; margin-top: 1rem; display:none;"></canvas>

            <?php else: ?>
                <p>Aucune statistique disponible.</p>
            <?php endif; ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuItems = document.querySelectorAll('.menu-item[data-tab]');
            const tabContents = document.querySelectorAll('.tab-content');

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
        });

        // Chart.js rendering for statistics
        <?php if (!empty($stats)): ?>
            // Sponsors count per event chart
            const ctx = document.getElementById('statsChart').getContext('2d');
            const labels = <?= json_encode(array_column($stats, 'evenement')) ?>;
            const data = <?= json_encode(array_column($stats, 'sponsor_count')) ?>;

            window.statsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Nombre de Sponsors',
                        data: data,
                        backgroundColor: 'rgba(193, 34, 193, 0.7)',
                        borderColor: 'rgba(193, 34, 193, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision: 0,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#333',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            });

            // Total amount sponsored per event chart
            const ctxAmount = document.getElementById('amountChart').getContext('2d');
            const labelsAmount = <?= json_encode(array_column($amountStats, 'evenement')) ?>;
            const dataAmount = <?= json_encode(array_column($amountStats, 'total_amount')) ?>;

            window.amountChart = new Chart(ctxAmount, {
                type: 'bar',
                data: {
                    labels: labelsAmount,
                    datasets: [{
                        label: 'Montant total sponsorisé (dt)',
                        data: dataAmount,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + ' dt';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#333',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            });

            // Sponsors by status pie chart
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            const labelsStatus = <?= json_encode(array_column($statusStats, 'status')) ?>;
            const dataStatus = <?= json_encode(array_column($statusStats, 'count')) ?>;

            const backgroundColors = ['#4caf50', '#f44336', '#ff9800', '#9e9e9e']; // green, red, orange, grey fallback
            window.statusChart = new Chart(ctxStatus, {
                type: 'pie',
                data: {
                    labels: labelsStatus,
                    datasets: [{
                        label: 'Nombre de Sponsors par statut',
                        data: dataStatus,
                        backgroundColor: backgroundColors.slice(0, labelsStatus.length),
                        borderColor: '#fff',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: '#333',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            enabled: true
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>
    <script>
        // Chart toggle buttons logic
        const statButtons = document.querySelectorAll('.btn-stat');
        const charts = ['statsChart', 'amountChart', 'statusChart'];
        const chartTitles = {
            statsChart: document.getElementById('statsChartTitle'),
            amountChart: document.getElementById('amountChartTitle'),
            statusChart: document.getElementById('statusChartTitle')
        };

        function showChart(chartId) {
            charts.forEach(id => {
                const canvas = document.getElementById(id);
                const title = chartTitles[id];
                if (id === chartId) {
                    canvas.style.display = 'block';
                    title.style.display = 'block';
                    if (window[id + 'Chart']) {
                        window[id + 'Chart'].resize();
                        window[id + 'Chart'].update();
                    }
                } else {
                    canvas.style.display = 'none';
                    title.style.display = 'none';
                }
            });
        }

        statButtons.forEach(button => {
            button.addEventListener('click', () => {
                statButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const chartId = button.getAttribute('data-chart');
                showChart(chartId);
            });
        });

        // Show the first chart by default
        showChart('statsChart');

document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.btn-action');
    buttons.forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.getAttribute('data-id');
            const action = button.getAttribute('data-action');
            if (action === 'refuse') {
                if (!confirm('Êtes-vous sûr?')) {
                    return;
                }
            }
            const status = action === 'accept' ? 'accepted' : 'refused';

            try {
                const response = await fetch('../../controller/updatesponsor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: id,
                        status: status
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    let message = 'Action réussie : ' + data.message;
                    if (action === 'accept') {
                        if (data.mailSent) {
                            message += '\nEmail envoyé avec succès.';
                        } else {
                            message += '\nErreur lors de l\'envoi de l\'email : ' + (data.mailError || 'Unknown error');
                        }
                    }
                    alert(message);
                    button.disabled = true;
                    button.style.opacity = '0.6';
                    button.style.cursor = 'not-allowed';
                    const siblingBtn = button.parentElement.querySelector(`.btn-action[data-id="${id}"]`) === button
                        ? button.parentElement.querySelector('.btn-action:not([data-id="' + id + '"])')
                        : button.parentElement.querySelector('.btn-action:not([data-id="' + id + '"])');
                    if (siblingBtn) {
                        siblingBtn.disabled = true;
                        siblingBtn.style.opacity = '0.6';
                        siblingBtn.style.cursor = 'not-allowed';
                    }
                    location.reload();
                } else {
                    alert('Erreur : ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                alert('Erreur lors de la requête : ' + error.message);
            }
        });
    });
});
    // Fonction pour ouvrir/fermer le menu
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        if (menu.style.display === 'block') {
            menu.style.display = 'none';
        } else {
            menu.style.display = 'block';
        }
    }

    // Fermer le menu si on clique en dehors
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('dropdownMenu');
        const profile = document.querySelector('.user-profile');
        if (!profile.contains(event.target)) {
            menu.style.display = 'none';
        }
    });

    </script>
</body>
</html>