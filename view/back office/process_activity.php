<?php
require_once __DIR__ . '/../../model/ActivityModel.php';

session_start();

$activityModel = new ActivityModel();

// Vérifier si c'est une requête GET pour la suppression
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['operation']) && $_GET['operation'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Supprimer une activité
    $activity = $activityModel->getActivityById($id);
    
    // Supprimer l'image du serveur si elle existe
    if ($activity && !empty($activity['image'])) {
        $imagePath = __DIR__ . '/../front office/' . $activity['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Supprimer l'activité de la base de données
    $success = $activityModel->deleteActivity($id);
    
    if ($success) {
        $_SESSION['success'] = "Activité supprimée avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de l'activité.";
    }
    
    // Rediriger vers le dashboard
    header('Location: dashboard.php');
    exit;
}

// Vérifier si le formulaire a été soumis en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'] ?? 'add';
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    
    // Récupérer et valider les données du formulaire
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_SANITIZE_NUMBER_INT);
    
    // Gestion de l'image
    $imagePath = '';
    
    if ($operation == 'add' || (!empty($_FILES['image']['name']) && $operation == 'edit')) {
        // Vérifier si un fichier a été téléchargé
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5 MB
            
            if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
                // Créer le dossier uploads s'il n'existe pas
                $uploadDir = __DIR__ . '/../front office/images/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Générer un nom de fichier unique
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFilePath = $uploadDir . $fileName;
                
                // Déplacer le fichier téléchargé vers le dossier de destination
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $imagePath = 'images/' . $fileName;
                } else {
                    $_SESSION['error'] = "Erreur lors du téléchargement de l'image.";
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $_SESSION['error'] = "Format d'image non valide ou taille trop grande.";
                header('Location: dashboard.php');
                exit;
            }
        } else if ($operation == 'add') {
            $_SESSION['error'] = "Veuillez sélectionner une image.";
            header('Location: dashboard.php');
            exit;
        }
    } else if ($operation == 'edit' && isset($_POST['current_image'])) {
        // Conserver l'image actuelle lors de la modification
        $imagePath = $_POST['current_image'];
    }
    
    // Exécuter l'opération
    $success = false;
    
    if ($operation == 'add') {
        // Ajouter une nouvelle activité
        $success = $activityModel->addActivity(
            $name, 
            $description, 
            $price, 
            $location, 
            $date, 
            $category, 
            $capacity, 
            $imagePath
        );
        
        if ($success) {
            $_SESSION['success'] = "Activité ajoutée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'activité.";
        }
    } else if ($operation == 'edit' && $id) {
        // Modifier une activité existante
        $success = $activityModel->updateActivity(
            $id,
            $name, 
            $description, 
            $price, 
            $location, 
            $date, 
            $category, 
            $capacity, 
            $imagePath
        );
        
        if ($success) {
            $_SESSION['success'] = "Activité mise à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'activité.";
        }
    }
    
    // Rediriger vers le dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Si ce n'est pas une requête POST ou GET valide, rediriger vers le dashboard
    header('Location: dashboard.php');
    exit;
}
?> 