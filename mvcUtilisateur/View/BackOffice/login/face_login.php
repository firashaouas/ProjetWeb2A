<?php
session_start();
require_once '../../../Controller/UserController.php';

// Enable error display for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$controller = new UserController();
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$debugInfo = isset($_SESSION['debug_info']) ? $_SESSION['debug_info'] : [];

// Clear debug info to prevent stale data
unset($_SESSION['debug_info']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'faceLogin') {
    try {
        $controller->faceLogin();
    } catch (Exception $e) {
        $error = $e->getMessage();
        $debugInfo['error'] = $error;
        error_log("Face login error: " . $error);
        $_SESSION['debug_info'] = $debugInfo;
        header("Location: face_login.php?error=" . urlencode($error));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Faciale</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error {
            color: red;
            margin-bottom: 10px;
            font-weight: bold;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Connexion Faciale</h2>
        <?php if ($error): ?>
            <p class="error">Erreur: <?php echo $error; ?></p>
            <p>Veuillez vérifier la console du navigateur (F12 → Console) pour plus de détails.</p>
        <?php else: ?>
            <p>Veuillez positionner votre visage devant la caméra.</p>
        <?php endif; ?>
        <form method="POST" id="faceLoginForm">
            <input type="hidden" name="action" value="faceLogin">
            <button type="submit">Démarrer la reconnaissance faciale</button>
        </form>
        <p><a href="login.php">Retour à la connexion</a></p>
    </div>

    <script>
        // Log page load
        console.log('Face login page loaded');

        // Log form submission
        document.getElementById('faceLoginForm').addEventListener('submit', () => {
            console.log('Form submitted: Initiating facial recognition');
        });

        // Log errors
        <?php if ($error): ?>
            console.error('Face login error: <?php echo addslashes($error); ?>');
        <?php endif; ?>

        // Log debug info safely
        const debugInfo = <?php echo json_encode($debugInfo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}'; ?>;
        if (Object.keys(debugInfo).length > 0) {
            console.log('Debug info:', debugInfo);
        }

        // Only auto-submit if no error
        <?php if (!$error): ?>
            setTimeout(() => {
                console.log('Auto-submitting form...');
                document.getElementById('faceLoginForm').submit();
            }, 2000);
        <?php else: ?>
            console.warn('Auto-submission stopped due to error. Check console and retry manually.');
        <?php endif; ?>
    </script>
</body>
</html>