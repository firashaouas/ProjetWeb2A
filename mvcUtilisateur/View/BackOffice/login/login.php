
<?php if (isset($_GET['error'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
      icon: 'error',
      title: 'Erreur',
      text: <?= json_encode($_GET['error']) ?>,
      confirmButtonColor: '#6c63ff'
    });
  });
</script>
<?php endif; ?>



<?php
session_start();
require_once '../../../Controller/UserController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            $userController = new UserController();

            // Vérifier format numéro (8 chiffres exactement)
            if (!preg_match('/^\d{8}$/', $phone)) {
                $_SESSION['register_error'] = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            }

            // Vérifier si le numéro de téléphone existe déjà
            if ($userController->phoneExists($phone)) {
                $_SESSION['register_error'] = "Ce numéro de téléphone est déjà utilisé.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            }

            // Vérifier si l'email existe déjà
            if ($userController->emailExists($email)) {
                $_SESSION['register_error'] = "Cet email est déjà utilisé. Veuillez en choisir un autre.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            }

            // Vérifier force du mot de passe
            if (!isStrongPassword($password)) {
                $_SESSION['register_error'] = "Votre mot de passe n'est pas assez fort.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            }

            // Si tout est bon, inscription
            $userController->register($full_name, $phone, $email, $password);

            // Succès
            $_SESSION['register_success'] = "Inscription réussie ! Vous pouvez vous connecter.";
            header("Location: /View/BackOffice/login/login.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['register_error'] = "Une erreur s'est produite lors de l'inscription. Veuillez réessayer.";
            header("Location: /View/BackOffice/login/login.php");
            exit();
        }
    } elseif ($action === 'login') {
        // Connexion
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $userController = new UserController();
        if ($userController->login($email, $password)) {
            header("Location: /View/BackOffice/dashboard.php");
            exit();
        } else {
            $_SESSION['register_error'] = "Email ou mot de passe incorrect.";
            header("Location: /View/BackOffice/login/login.php");
            exit();
        }
    }
}

// Fonction pour vérifier la force du mot de passe
function isStrongPassword($password) {
    $hasUpper = preg_match('@[A-Z]@', $password);
    $hasLower = preg_match('@[a-z]@', $password);
    $hasNumber = preg_match('@[0-9]@', $password);
    $hasSpecial = preg_match('@[!@#$%^&*(),.?":{}|<> ]@', $password);
    $longEnough = strlen($password) >= 8;

    return $hasUpper && $hasLower && $hasNumber && $hasSpecial && $longEnough;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Click'N'Go/login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<?php if (isset($_SESSION['register_success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Succès !',
        text: <?= json_encode($_SESSION['register_success']) ?>,
        confirmButtonText: 'OK',
        confirmButtonColor: '#6c63ff'
    });
});
</script>
<?php unset($_SESSION['register_success']); endif; ?>

<?php if (isset($_SESSION['register_error'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Erreur !',
        text: <?= json_encode($_SESSION['register_error']) ?>,
        confirmButtonText: 'OK',
        confirmButtonColor: '#6c63ff'
    });
});
</script>
<?php unset($_SESSION['register_error']); endif; ?>


    <div class="section">
        <div class="container">
            <div class="row full-height justify-content-center">
                <div class="col-12 text-center align-self-center py-5">
                    <div class="section pb-5 pt-5 pt-sm-2 text-center">
                        <h6 class="mb-0 pb-3"><span>Se connecter</span><span>S’inscrire</span></h6>
                        <input class="checkbox" type="checkbox" id="reg-log" name="reg-log"/>
                        <label for="reg-log"></label>
                        <div class="card-3d-wrap mx-auto">
                            <div class="card-3d-wrapper">
                                <!-- Section "Se connecter" -->
                                <div class="card-front">
                                    <div class="center-wrap">
                                        <div class="section text-center">
                                            <h4 class="pb-3">Se connecter</h4>
                                            <form method="POST" action="login.php">
                                                <div class="form-group">
                                                    <input type="email" class="form-style" name="email" placeholder="Email" required>
                                                    <i class="input-icon uil uil-at"></i>
                                                </div>
                                                <div class="form-group mt-2">
                                                    <input type="password" class="form-style" name="password" placeholder="Password" required>
                                                    <i class="input-icon uil uil-lock-alt"></i>
                                                </div>
                                                <div class="text-right mt-1">
                                                    <a href="#" class="link">Mot de passe oublié ?</a>
                                                </div>

                                                <div class="btn-login-zone">
  <button type="submit" class="btn mt-4" name="action" value="login" id="login-btn">SE CONNECTER</button>
</div>

<br>
                                                <div class="form-group mt-2">
                                                    <p>Ou</p>
                                                    <a href="../../../auth/facebook.php" class="btn"><i class="fa-brands fa-facebook-f"></i></a>
                                                    <a href="/Projet Web/mvcUtilisateur/auth/google.php" class="btn"><i class="fa-brands fa-google"></i></a>
                                                    <a href="#" class="btn"><i class="fa-brands fa-github"></i></a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Section "S’inscrire" -->
                                <div class="card-back">
                                    <div class="center-wrap">
                                        <div class="section text-center">
                                            <h4 class="mb-3 pb-3">S’inscrire</h4>
                                            <form method="POST" action="login.php">
                                                <div class="form-group">
                                                    <input type="text" class="form-style" name="full_name" placeholder="Full Name" required>
                                                    <i class="input-icon uil uil-user"></i>
                                                </div>
                                                <div class="form-group mt-2">
                                                    <input type="tel" class="form-style" name="phone" placeholder="Phone Number" required>
                                                    <i class="input-icon uil uil-phone"></i>
                                                </div>
                                                <div class="form-group mt-2">
                                                    <input type="email" class="form-style" name="email" placeholder="Email" required>
                                                    <i class="input-icon uil uil-at"></i>
                                                </div>
                                                <div class="form-group mt-2" style="position: relative;">
  <input type="password" class="form-style" name="password" id="password" placeholder="Password" required>
  <i class="input-icon uil uil-lock-alt"></i>
  <div id="passwordHint" class="password-hint"></div>
  <div class="password-strength-bar">
    <div id="passwordStrength" class="strength-bar-inner"></div>
  </div>
</div>


                                                <button type="submit" class="btn mt-4" name="action" value="register">S’inscrire</button>
                                                <div class="form-group mt-2">
                                                    <p>Ou</p>
                                                    <a href="../../../auth/facebook.php" class="btn"><i class="fa-brands fa-facebook-f"></i></a>
                                                    <a href="/Projet Web/mvcUtilisateur/auth/google.php" class="btn"><i class="fa-brands fa-google"></i></a>
                                                    <a href="#" class="btn"><i class="fa-brands fa-github"></i></a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>