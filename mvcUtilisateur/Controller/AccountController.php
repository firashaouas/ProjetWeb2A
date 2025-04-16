<?php
session_start();
require_once '../Config.php';
require_once '../Model/User.php';

if (!isset($_GET['action'])) {
    header("Location: /Projet%20Web/index.php");
    exit;
}

$action = $_GET['action'];
$db = Config::getConnexion();

// --- Suppression du compte ---
if ($action === 'delete') {
    if (!isset($_SESSION['user'])) {
        header("Location: /Projet%20Web/index.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = "Requête invalide.";
        header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php");
        exit;
    }

    $user = $_SESSION['user'];
    $email = $user['email'];
    $currentPassword = $_POST['current_password'] ?? '';
    $confirmDelete = isset($_POST['confirm_delete']);

    if (!$confirmDelete) {
        $_SESSION['error'] = "Vous devez confirmer la suppression de votre compte.";
        header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php");
        exit;
    }

    if (!User::verifyPassword($db, $email, $currentPassword)) {
        $_SESSION['error'] = "Mot de passe incorrect.";
        header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php");
        exit;
    }

    $deleted = User::deleteByEmail($db, $email);

    if ($deleted) {
        session_unset();
        session_destroy();
        header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/index.php?account_deleted=1");
        exit;
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de la suppression du compte.";
        header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php");
        exit;
    }
}

// ------------------------------------------------------------

if ($action === 'update') {
    if (!isset($_SESSION['user'])) {
        header("Location: /Projet%20Web/index.php");
        exit;
    }

    $user = $_SESSION['user'];
    $userId = $user['id_user'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $numUser = $_POST['num_user'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'] ?? '';
    $profilePicturePath = $user['profile_picture'] ?? null;

    // Vérification de l'ancien mot de passe
    if (!User::verifyPassword($db, $email, $currentPassword)) {
        $_SESSION['error'] = "Mot de passe actuel incorrect.";
        header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/edit_profile.php");
        exit;
    }

    // Si un nouveau mot de passe est fourni
    $passwordToUpdate = $user['password'];
    if (!empty($newPassword)) {
        $passwordToUpdate = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Gestion de la photo de profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../Public/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $tmpName = $_FILES['profile_picture']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $destination)) {
            $profilePicturePath = "/Projet%20Web/mvcUtilisateur/Public/uploads/$fileName";
        }
    }

    // Mise à jour via le modèle
    $userModel = new User();
    $userModel->setIdUser($userId);
    $userModel->setFullName($fullName);
    $userModel->setEmail($email);
    $userModel->setNumUser($numUser);
    $userModel->setPassword($passwordToUpdate);
    $userModel->setProfilePicture($profilePicturePath);

    $result = $userModel->updateUserInfo($db);

    if ($result) {
        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['num_user'] = $numUser;
        $_SESSION['user']['profile_picture'] = $profilePicturePath;

        $_SESSION['success'] = "Profil mis à jour avec succès !";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour.";
    }

    // Rediriger vers la page profile.php
    header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php");
    exit;
}
?>
