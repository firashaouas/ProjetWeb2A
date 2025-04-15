<?php
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Get the ID from the request
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        throw new Exception('ID de demande manquant');
    }

    // Create controller instance
    $controller = new DemandeCovoiturageController();
    
    // Delete the demande
    $success = $controller->deleteDemande($id);

    if ($success) {
        $response['success'] = true;
        $response['message'] = 'Demande supprimée avec succès';
    } else {
        $response['message'] = 'Échec de la suppression de la demande';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);