<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../model/model.php");
require_once(__DIR__ . "/controller.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sponsor = (int)$_POST['id'];
    $nom_entreprise = htmlspecialchars($_POST['companyName']);
    $nom_personne = htmlspecialchars($_POST['contactPerson']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = (int)$_POST['phone'];
    $montant = (float)$_POST['amount'];
    $duree = htmlspecialchars($_POST['duration']);
    $avantage = htmlspecialchars($_POST['benefits']);
    $status = htmlspecialchars($_POST['status']);
    $id_offre = (int)$_POST['id_offre'];  // Added id_offre from POST


    try {
        $sponsor = new sponsor(
            $nom_entreprise,
            $nom_personne,
            $email,
            $telephone,
            $montant,
            $duree,
            $avantage,
            $status,
            $id_offre  
        );
        $sponsor->setId_sponsor($id_sponsor);

        $controller = new sponsorController();
        $success = $controller->updateSponsor($sponsor);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Sponsor mis à jour avec succès!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Échec de la mise à jour du sponsor'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>