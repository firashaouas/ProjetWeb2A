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

    $originalEmail = $user['email'];
    $profilePicturePath = $user['profile_picture'] ?? null;

    $updatePassword = false;
    $updateEmail = false;
    $newPassword = '';
    $passwordHash = $user['password'];

    // Si l'utilisateur veut changer son email
    if ($email !== $originalEmail) {
        if (empty($_POST['confirm_password_for_email'])) {
            $_SESSION['error'] = "Mot de passe requis pour changer l'email.";
            header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/edit_profile.php");
            exit;
        }
        if (!User::verifyPassword($db, $originalEmail, $_POST['confirm_password_for_email'])) {
            $_SESSION['error'] = "Mot de passe incorrect pour changer l'email.";
            header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/edit_profile.php");
            exit;
        }
        $updateEmail = true;
    }

    // Si utilisateur veut changer son mot de passe
    if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        if (!User::verifyPassword($db, $originalEmail, $_POST['old_password'])) {
            $_SESSION['error'] = "Mot de passe actuel incorrect.";
            header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/edit_profile.php");
            exit;
        }
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $updatePassword = true;
    }

    // Gestion de l'upload photo
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../View/FrontOffice/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $tmpName = $_FILES['profile_picture']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $destination)) {
            $profilePicturePath = 'uploads/' . $fileName;
        }
    }

    // Mise à jour dans la base
    $userModel = new User();
    $userModel->setIdUser($userId);
    $userModel->setFullName($fullName);
    $userModel->setNumUser($numUser);
    $userModel->setProfilePicture($profilePicturePath);

    if ($updateEmail) {
        $userModel->setEmail($email);
    } else {
        $userModel->setEmail($originalEmail);
    }

    if ($updatePassword) {
        $userModel->setPassword($newPassword);
    } else {
        $userModel->setPassword($passwordHash);
    }

    $result = $userModel->updateUserInfo($db);

    if ($result) {
        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['num_user'] = $numUser;
        $_SESSION['user']['profile_picture'] = $profilePicturePath;

        if ($updateEmail) {
            $_SESSION['user']['email'] = $email;
        }

        if ($updatePassword) {
            $_SESSION['user']['password'] = $newPassword;
        }

        $_SESSION['success'] = "Profil mis à jour avec succès !";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour.";
    }

    header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php");
    exit;
}

?>
