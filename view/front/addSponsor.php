<?php
require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");
require_once(__DIR__ . "/../../config.php");

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$errors = [];
$fieldErrors = [];

$controller = new SponsorController();
$offers = $controller->listOffers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug output to verify offers and submitted id_offre
    echo '<pre>Offers: '; print_r($offers); echo '</pre>';
    echo '<pre>Submitted id_offre: '; var_dump($_POST['id_offre'] ?? null); echo '</pre>';

    // Récupération et assainissement des données
    $nom_entreprise = sanitizeInput($_POST['companyName'] ?? '');
    // Removed evenement field as per user request
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $telephone = sanitizeInput($_POST['phone'] ?? '');
    $montant = (float) filter_var($_POST['amount'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $duree = sanitizeInput($_POST['duration'] ?? '');
    $avantage = sanitizeInput($_POST['benefits'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'pending');
    $description = sanitizeInput($_POST['description'] ?? '');
    $id_offre = (int)($_POST['id_offre'] ?? 0);  // Added id_offre from form

    // Validation des champs
    if (empty($nom_entreprise)) {
        $fieldErrors['companyName'] = "Le nom de l'entreprise est obligatoire";
    } elseif (!preg_match('/^[A-Za-z0-9À-ÿ\s\-&]{2,100}$/', $nom_entreprise)) {
        $fieldErrors['companyName'] = "Le nom doit contenir entre 2 et 100 caractères (lettres, chiffres, espaces ou &-)";
    }

    

    if (empty($email)) {
        $fieldErrors['email'] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = "Format d'email invalide (ex: exemple@domaine.com)";
    }

    if (empty($telephone)) {
        $fieldErrors['phone'] = "Le téléphone est obligatoire";
    } elseif (!preg_match('/^(\+216\s)?\d{8}$/', $telephone)) {
        $fieldErrors['phone'] = "Format de téléphone invalide (doit être +216 XXXXXXXX ou XXXXXXXX)";
    }

    if (empty($montant)) {
        $fieldErrors['amount'] = "Le montant est obligatoire";
    } elseif ($montant < 100 || $montant > 100000) {
        $fieldErrors['amount'] = "Le montant doit être compris entre 100 et 100 000 DT (vous avez saisi $montant DT)";
    }

    if (empty($duree)) {
        $fieldErrors['duration'] = "La durée est obligatoire";
    } elseif (!preg_match('/^[0-9]+\s*(mois|an|ans|jours|semaines)$/i', $duree)) {
        $fieldErrors['duration'] = "Format de durée invalide (ex: '3 mois', '1 an', '2 semaines')";
    }

    if (empty($avantage)) {
        $fieldErrors['benefits'] = "Les avantages sont obligatoires";
    } elseif (strlen($avantage) < 10 || strlen($avantage) > 500) {
        $fieldErrors['benefits'] = "Les avantages doivent contenir entre 10 et 500 caractères (vous avez ".strlen($avantage)." caractères)";
    }

    if (!empty($status) && !in_array($status, ['pending', 'approved', 'rejected'])) {
        $fieldErrors['status'] = "Statut invalide (doit être: pending, approved ou rejected)";
    }

    if (strlen($description) > 1000) {
        $fieldErrors['description'] = "La description ne doit pas dépasser 1000 caractères";
    }

    // Validate id_offre
    $validOfferIds = array_map('intval', array_column($offers, 'id_offre'));
    if ($id_offre === 0 || !in_array($id_offre, $validOfferIds, true)) {
        $fieldErrors['id_offre'] = "Veuillez sélectionner une offre valide.";
    }

    // Si aucune erreur, procéder à l'insertion
    if (empty($fieldErrors)) {
        try {
        $sponsor = new sponsor(
            $nom_entreprise,
            $email,
            (int)$telephone,
            $montant,
            $duree,
            $avantage,
            $status,
            $id_offre  
        );

        

        $controller = new SponsorController();
        $success = $controller->addSponsor($sponsor);

            if ($success) {
                header("Location: index.php?success=1");
                exit();
            } else {
                $errors[] = "Une erreur s'est produite lors de l'enregistrement dans la base de données";
            }
        } catch (Exception $e) {
            $errors[] = "Erreur technique: " . $e->getMessage();
        }
    }
}

// Affichage des erreurs si elles existent
if (!empty($fieldErrors) || !empty($errors)) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreurs de formulaire</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f9f9f9;
            }
            .error-container {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                padding: 25px;
            }
            .error-header {
                color: #d9534f;
                border-bottom: 2px solid #d9534f;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .error-section {
                margin-bottom: 25px;
            }
            .error-title {
                color: #d9534f;
                margin-top: 0;
                font-size: 1.2em;
            }
            .error-list {
                list-style-type: none;
                padding: 0;
                margin: 0;
            }
            .error-item {
                padding: 10px;
                margin-bottom: 8px;
                background-color: #fdf3f3;
                border-left: 4px solid #d9534f;
                display: flex;
            }
            .field-name {
                font-weight: bold;
                color: #337ab7;
                min-width: 150px;
            }
            .back-btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #5bc0de;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 20px;
                transition: background-color 0.3s;
            }
            .back-btn:hover {
                background-color: #46b8da;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1 class="error-header">Erreurs dans le formulaire</h1>
            
            <?php if (!empty($fieldErrors)): ?>
            <div class="error-section">
                <h2 class="error-title">Veuillez corriger les erreurs suivantes :</h2>
                <ul class="error-list">
                    <?php foreach ($fieldErrors as $field => $error): ?>
                        <li class="error-item">
                            <span class="field-name"><?= 
                                ucfirst(str_replace(
                                    ['_', 'Name', 'amount', 'duration', 'benefits'], 
                                    [' ', '', 'Montant', 'Durée', 'Avantages'], 
                                    $field
                                )) 
                            ?> :</span>
                            <span><?= $error ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div class="error-section">
                <h2 class="error-title">Erreurs système :</h2>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li class="error-item"><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <a href="javascript:history.back()" class="back-btn">&larr; Retour au formulaire</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Si la méthode n'est pas POST
http_response_code(405);
echo "Accès refusé : méthode non autorisée";
exit();
?>

<form method="post" action="" novalidate>
    <!-- Other form fields -->

    <div class="form-group">
        <label for="id_offre">Sélectionnez une offre</label>
        <select id="id_offre" name="id_offre" required>
            <option value="">-- Choisissez une offre --</option>
            <?php foreach ($offers as $offer): ?>
                <option value="<?= htmlspecialchars($offer['id_offre']) ?>" <?= (isset($_POST['id_offre']) && $_POST['id_offre'] == $offer['id_offre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($offer['titre_offre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($fieldErrors['id_offre'])): ?>
            <span class="error"><?= htmlspecialchars($fieldErrors['id_offre']) ?></span>
        <?php endif; ?>
    </div>

    <!-- Submit button -->
    <button type="submit" name="submitSponsor">Envoyer</button>
</form>