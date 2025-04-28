<?php
require_once __DIR__.'/../../Config.php';
require_once __DIR__.'/../../Model/User.php';

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit;
}

$user = $_SESSION['user'];

function stringToColor($str) {
    $Colors = ['#FF6B6B', '#FF8E53', '#6B5B95', '#88B04B', '#F7CAC9', '#92A8D1', '#955251', '#B565A7', '#DD4124', '#D65076'];
    $hash = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = ord($str[$i]) + (($hash << 5) - $hash);
    }
    return $Colors[abs($hash) % count($Colors)];
}

$relativePhotoPath = 'View/FrontOffice/' . $user['profile_picture'];
$absolutePhotoPath = realpath(__DIR__.'/../../'.$relativePhotoPath);
$photoUrl = '/Projet Web/mvcUtilisateur/' . $relativePhotoPath;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Profil</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: #f9f9f9;
  padding: 20px;
}
.container {
  max-width: 600px;
  margin: auto;
  background: white;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
  text-align: center;
}
.profile-photo {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #8e44ad;
  cursor: pointer;
  transition: 0.3s;
}
.profile-photo:hover {
  opacity: 0.7;
}
.hidden {
  display: none;
}
.form-group {
  margin-top: 20px;
  text-align: left;
}
input[type="text"], input[type="email"] {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 8px;
}
.actions {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  gap: 10px;
}
.btn {
  background: #8e44ad;
  color: white;
  padding: 12px 18px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
}
.btn:hover {
  background: #732d91;
}
small {
  font-size: 12px;
  color: #777;
}
</style>
</head>

<body>

<div class="container">
  <h2 style="color:#8e44ad;">Modifier Mon Profil</h2>

  <form id="profileForm" action="/Projet%20Web/mvcUtilisateur/Controller/AccountController.php?action=update" method="post" enctype="multipart/form-data">

    <label for="profile-picture-input">
      <?php if (!empty($user['profile_picture']) && $absolutePhotoPath && file_exists($absolutePhotoPath)): ?>
        <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Profil" class="profile-photo">
      <?php else: ?>
        <div style="width:100px;height:100px;background:<?= stringToColor($user['full_name']) ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:auto;margin-bottom:20px;font-weight:bold;font-size:24px;color:white;">
          <?= strtoupper(substr($user['full_name'],0,1)) ?>
        </div>
      <?php endif; ?> 
    </label>
    <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*" class="hidden">

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
      <input type="email" id="emailField" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      <small>Changement d'email nécessite confirmation par mot de passe</small>
    </div>

    <div class="actions">
      <button type="button" class="btn" onclick="confirmPasswordForEmail()">Enregistrer</button>
      <button type="button" class="btn" onclick="changePassword()">Changer mot de passe</button>
    </div>

    <div style="margin-top:15px;">
      <a href="#" style="color:#8e44ad;text-decoration:underline;font-size:13px;">Mot de passe oublié ?</a>
    </div>

  </form>
</div>

<script>
// Clic sur la photo = choisir fichier
document.getElementById('profile-picture-input').addEventListener('change', function() {
    if (this.files.length > 0) {
        document.getElementById('profileForm').submit();
    }
});

// Confirm password avant email change
function confirmPasswordForEmail() {
  const originalEmail = "<?= htmlspecialchars($user['email']) ?>";
  const newEmail = document.getElementById('emailField').value.trim();

  if (newEmail !== originalEmail) {
    Swal.fire({
      title: 'Confirmez votre mot de passe 🔒',
      input: 'password',
      inputPlaceholder: 'Mot de passe actuel',
      showCancelButton: true,
      confirmButtonText: 'Confirmer',
      confirmButtonColor: '#8e44ad',
      cancelButtonText: 'Annuler'
    }).then((result) => {
      if (result.isConfirmed) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = 'confirm_password_for_email';
        hiddenField.value = result.value;
        document.getElementById('profileForm').appendChild(hiddenField);
        document.getElementById('profileForm').submit();
        // Rediriger après submit
        setTimeout(() => {
          window.location.href = '/Projet Web/mvcUtilisateur/View/FrontOffice/profile.php';
        }, 1000);
      }
    });
  } else {
    document.getElementById('profileForm').submit();
    // Rediriger après submit
    setTimeout(() => {
      window.location.href = '/Projet Web/mvcUtilisateur/View/FrontOffice/profile.php';
    }, 1000);
  }
}

// Changer le mot de passe
function changePassword() {
  Swal.fire({
    title: 'Changer votre mot de passe',
    html:
      '<input id="old-password" type="password" class="swal2-input" placeholder="Mot de passe actuel">' +
      '<input id="new-password" type="password" class="swal2-input" placeholder="Nouveau mot de passe">' +
      '<input id="confirm-new-password" type="password" class="swal2-input" placeholder="Confirmer le mot de passe">',
    focusConfirm: false,
    preConfirm: () => {
      const oldPassword = document.getElementById('old-password').value.trim();
      const newPassword = document.getElementById('new-password').value.trim();
      const confirmPassword = document.getElementById('confirm-new-password').value.trim();

      if (!oldPassword || !newPassword || !confirmPassword) {
        Swal.showValidationMessage('Tous les champs sont obligatoires');
      } else if (newPassword !== confirmPassword) {
        Swal.showValidationMessage('Les nouveaux mots de passe ne correspondent pas');
      } else {
        const form = document.getElementById('profileForm');

        const oldField = document.createElement('input');
        oldField.type = 'hidden';
        oldField.name = 'old_password';
        oldField.value = oldPassword;

        const newField = document.createElement('input');
        newField.type = 'hidden';
        newField.name = 'new_password';
        newField.value = newPassword;

        form.appendChild(oldField);
        form.appendChild(newField);

        form.submit();
        // Rediriger après submit
        setTimeout(() => {
          window.location.href = '/Projet Web/mvcUtilisateur/View/FrontOffice/profile.php';
        }, 1000);
      }
    },
    confirmButtonColor: '#8e44ad'
  });
}
</script>

</body>
</html>
