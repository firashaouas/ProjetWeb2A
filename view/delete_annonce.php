<?php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

header('Content-Type: application/json');

try {
   
    $pdo = config::getConnexion();
    
    
    $controller = new AnnonceCovoiturageController($pdo);
    
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        throw new Exception("ID d'annonce invalide");
    }
    
    
    $success = $controller->deleteAnnonce($id);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Annonce supprimée avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune annonce trouvée avec cet ID']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>