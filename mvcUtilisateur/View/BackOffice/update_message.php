<?php
require_once __DIR__ . '/../../Config.php'; // adapte le chemin si besoin
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Config::getConnexion();

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $text = $data['text'];

        if (empty($id) || empty($text)) {
            http_response_code(400);
            echo 'ID ou texte manquant.';
            exit();
        }

        $stmt = $db->prepare('UPDATE chat_messages SET message = :text WHERE id = :id');
        $stmt->execute([
            'text' => $text,
            'id' => $id
        ]);

        echo 'Message mis à jour avec succès.';
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Erreur serveur : ' . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo 'Méthode non autorisée.';
}
?>
