<?php
// Activer les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';

if (!isset($_GET['id_conducteur']) || empty($_GET['id_conducteur'])) {
    echo "ID de l'annonce non spécifié. <a href='http://localhost/clickngooo/view/backoffice/annonces.php'>Retour à la liste</a>";
    exit;
}

$id = $_GET['id_conducteur'];
$message = '';
$success = false;

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    try {
        $pdo = config::getConnexion();

        $checkStmt = $pdo->prepare("SELECT id_conducteur FROM annonce_covoiturage WHERE id_conducteur = :id");
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() == 0) {
            $message = "L'annonce avec l'ID $id n'existe pas.";
        } else {
            $deleteStmt = $pdo->prepare("DELETE FROM annonce_covoiturage WHERE id_conducteur = :id");
            $deleteStmt->bindParam(':id', $id);

            if ($deleteStmt->execute()) {
                $success = true;
                $message = "L'annonce a été supprimée avec succès.";
            } else {
                $message = "Erreur lors de la suppression de l'annonce.";
            }
        }
    } catch (PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer une Annonce</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #fff7fb;
            padding: 20px;
        }
        h1 {
            color: #f72975;
            text-align: center;
        }
        .container {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            margin: 10px;
        }
        .btn-danger {
            background-color: #ff5252;
            color: white;
            border: none;
        }
        .btn-secondary {
            background-color: #f2f2f2;
            color: #666;
            border: 1px solid #ddd;
        }
        .btn-primary {
            background-color: #f72975;
            color: white;
            border: none;
        }
    </style>

    <?php if ($success): ?>
    <script>
        window.onload = function() {
            alert("Suppression réussie !");
            window.location.href = "http://localhost/clickngooo/view/backoffice/annonces.php";
        };
    </script>
    <?php endif; ?>
</head>
<body>
    <h1>Supprimer une Annonce</h1>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
                <?php if ($success): ?>
                    <div>Redirection vers les annonces...</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_GET['confirm'])): ?>
            <p>Êtes-vous sûr de vouloir supprimer cette annonce ?</p>
            <p>Cette action est irréversible.</p>

            <div>
                <a href="supprimer.php?id_conducteur=<?php echo $id; ?>&confirm=yes" class="btn btn-danger">Oui, supprimer</a>
                <a href="http://localhost/clickngooo/view/backoffice/annonces.php" class="btn btn-secondary">Non, annuler</a>
            </div>
        <?php elseif (!$success): ?>
            <a href="http://localhost/clickngooo/view/backoffice/annonces.php" class="btn btn-primary">Retour à la liste des annonces</a>
        <?php endif; ?>
    </div>
</body>
</html>
