<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../Controller/AvisController.php';

$response = ['success' => false, 'message' => ''];

try {
    $pdo = config::getConnexion();
    $avisController = new AvisController($pdo);

    $data = json_decode(file_get_contents('php://input'), true);
    $id_avis = isset($data['id_avis']) ? (int)$data['id_avis'] : 0;

    if ($id_avis <= 0) {
        throw new Exception("ID d'avis invalide");
    }

    $message = $avisController->deleteAvis($id_avis);
    $response['success'] = true;
    $response['message'] = $message;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>