<?php
require_once __DIR__.'/../../Config.php';
require_once __DIR__.'/../../Model/User.php';

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit;
}

$user = $_SESSION['user'];

// Fonction pour générer une couleur à partir d'un string
function stringToColor($str) {
    $funbookerColors = [
        '#FF6B6B', '#FF8E53', '#6B5B95', '#88B04B', 
        '#F7CAC9', '#92A8D1', '#955251', '#B565A7'
    ];
    $hash = crc32($str);
    return $funbookerColors[$hash % count($funbookerColors)];
}

// Fonction pour afficher la photo de profil
function displayProfilePicture($user, $size = 100) {
    if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
        return '<img src="'.$user['profile_picture'].'" style="width:'.$size.'px;height:'.$size.'px;border-radius:50%;object-fit:cover;">';
    } else {
        $initial = strtoupper(substr($user['full_name'], 0, 1));
        $color = stringToColor($user['full_name']);
        return '<div class="avatar-default" style="background-color:'.$color.';width:'.$size.'px;height:'.$size.'px;line-height:'.$size.'px;">'.$initial.'</div>';
    }
}

// Traitement de l'upload de photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $uploadDir = 'uploads/profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($extension, $allowedTypes)) {
        $filename = uniqid().'.'.$extension;
        $targetFile = $uploadDir.$filename;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            // Mise à jour en base de données via le modèle User
            $db = Config::getConnexion();
            $userModel = new User();
            $userModel->setIdUser($user['id_user']);
            $userModel->setProfilePicture($targetFile);
            
            if ($userModel->updateProfilePicture($db)) {
                // Mise à jour de la session
                $_SESSION['user']['profile_picture'] = $targetFile;
                $user = $_SESSION['user'];
                $_SESSION['success'] = "Photo de profil mise à jour avec succès!";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour en base de données";
            }
        } else {
            $_SESSION['error'] = "Erreur lors du téléchargement du fichier";
        }
    } else {
        $_SESSION['error'] = "Format de fichier non supporté. Formats acceptés: JPG, PNG, GIF";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Click'N'Go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Ajout de styles CSS ici */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .profile-picture-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .profile-picture-container:hover::after {
            content: "Changer";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            text-align: center;
            padding: 5px;
            border-radius: 0 0 50% 50%;
            font-size: 12px;
        }
        .avatar-default {
            border-radius: 50%;
            color: white;
            text-align: center;
            font-weight: bold;
            font-size: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        #profile-picture-input {
            display: none;
        }
        .header-info {
            flex-grow: 1;
        }
        .user-role {
            display: inline-block;
            padding: 4px 10px;
            background-color: #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            margin-top: 5px;
        }
        .profile-info {
            margin-bottom: 25px;
        }
        .profile-info p {
            margin: 10px 0;
            font-size: 16px;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            width: 150px;
        }
        .delete-form {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .btn-delete {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-delete:hover {
            background-color: #cc0000;
        }
        .password-container {
            margin: 20px 0;
        }
        .password-container input {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .checkbox-container {
            margin-bottom: 20px;
        }
        .error-message {
            color: #ff4444;
            margin-top: 15px;
            font-size: 14px;
        }
        .success-message {
            color: #28a745;
            margin-top: 15px;
            font-size: 14px;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
<a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/index.php" class="btn-secondary">
    <i class="fas fa-arrow-left"></i> Retour à l'accueil
</a>
    <div class="profile-container">
        <div class="profile-header">
            <form method="post" enctype="multipart/form-data" id="picture-form">
                <label class="profile-picture-container">
                    <?= displayProfilePicture($user, 70) ?>
                    <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*">
                </label>
            </form>
            
            <div class="header-info">
                <h1 style="margin: 0;"><?= htmlspecialchars($user['full_name']) ?></h1>
                <span class="user-role"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
                <p style="margin: 5px 0 0; color: #666;">
                    Membre depuis 
                    <?= !empty($user['date_inscription']) && strtotime($user['date_inscription']) ? date('d/m/Y', strtotime($user['date_inscription'])) : 'récemment' ?>
                </p>
            </div>
            <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/login/logout.php" class="btn-secondary">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
        
        <div class="profile-info">
            <p><span class="info-label">Email :</span> <?= htmlspecialchars($user['email']) ?></p>
            <p><span class="info-label">Téléphone :</span> <?= htmlspecialchars($user['num_user']) ?></p>
        </div>

        <div class="delete-form">
            <h3><i class="fas fa-exclamation-triangle"></i> Zone dangereuse</h3>
            <p>Cette action supprimera définitivement votre compte et toutes les données associées.</p>
            
            <form action="/Projet%20Web/mvcUtilisateur/Controller/AccountController.php?action=delete" method="post" onsubmit="return confirmDelete()">
                <div class="password-container">
                    <label for="current_password">Confirmez votre mot de passe :</label><br>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="checkbox-container">
                    <label>
                        <input type="checkbox" name="confirm_delete" required>
                        Je confirme la suppression de mon compte
                    </label>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn-delete">
                        <i class="fas fa-trash-alt"></i> Supprimer mon compte
                    </button>
                    <a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/edit_profile.php" class="btn-secondary">
                        <i class="fas fa-edit"></i> Modifier mon profil
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <p class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></p>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <p class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></p>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('profile-picture-input').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (validTypes.includes(this.files[0].type)) {
                document.forms['picture-form'].submit();
            } else {
                alert('Veuillez choisir un fichier image valide (JPG, PNG, GIF).');
            }
        }
    });

    function confirmDelete() {
        return confirm("Êtes-vous sûr de vouloir supprimer définitivement votre compte?");
    }
    </script>
</body>
</html>
