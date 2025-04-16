<?php
require_once __DIR__.'/../../Config.php';
require_once __DIR__.'/../../Model/User.php';

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon profil</title>
    <style>
        /* styles simplifiés ici... */
        .hidden { display: none; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] {
            padding: 8px; width: 100%; max-width: 400px;
        }
        .btn { padding: 10px 15px; background-color: #007bff; color: #fff; border: none; cursor: pointer; border-radius: 5px; }
        .btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<h2>Modifier mon profil</h2>

<form action="/Projet Web/mvcUtilisateur/Controller/AccountController.php?action=edit" method="post">
    <div class="form-group">
        <label>Nom complet</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
    </div>

    <div class="form-group">
        <label>Numéro de téléphone</label>
        <input type="text" name="num_user" value="<?= htmlspecialchars($user['num_user']) ?>" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <div id="email-password-container" class="form-group">
            <label>Mot de passe actuel <small>(obligatoire pour changer l'email)</small></label>
            <input type="password" name="confirm_password_for_email" required>
        </div>
    </div>

    <button type="button" class="btn" onclick="togglePasswordChange()">Changer le mot de passe</button>

    <div id="password-change-fields" class="hidden">
        <div class="form-group">
            <label>Ancien mot de passe</label>
            <input type="password" name="old_password">
        </div>
        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" name="new_password">
        </div>
    </div>

    <button type="submit" class="btn">Enregistrer les modifications</button>
</form>

<script>
function togglePasswordChange() {
    const fields = document.getElementById('password-change-fields');
    fields.classList.toggle('hidden');
}
</script>

</body>
</html>
