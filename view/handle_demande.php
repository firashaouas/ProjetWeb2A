<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';

$input = json_decode(file_get_contents('php://input'), true);
error_log('Received input: ' . print_r($input, true));

$id_demande = $input['id_demande'] ?? 0;
$action = $input['action'] ?? '';

error_log("id_demande: $id_demande, action: $action");

try {
    if (!$id_demande || !$action) {
        throw new Exception('Paramètres manquants: id_demande ou action');
    }

    $controller = new DemandeCovoiturageController();
    
    if ($action === 'approve') {
        $result = $controller->updateDemandeStatus($id_demande, 'approuvée');
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Demande approuvée']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Échec de l\'approbation de la demande']);
        }
    } elseif ($action === 'reject') {
        $result = $controller->updateDemandeStatus($id_demande, 'rejetée');
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Demande rejetée']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Échec du rejet de la demande']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
    }
} catch (Exception $e) {
    error_log('Error in handle_demande.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>