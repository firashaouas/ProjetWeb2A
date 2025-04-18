<?php
require_once(__DIR__ . "/../config.php");
class sponsorController{
    public function listSponser(){
        $db = config::getConnexion(); 
        $sql = $db->prepare("SELECT * FROM sponsor");
        $sql->execute();
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $result;
        
    }

    public function listOffers() {
        $db = config::getConnexion();
        $sql = $db->prepare("SELECT * FROM offre");
        $sql->execute();
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function addOffer($offer) {
        $db = config::getConnexion();
        $sql = "INSERT INTO offre (titre_offre, description_offre, evenement, montant_offre, status) VALUES (:titre_offre, :description_offre, :evenement, :montant_offre, :status)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':titre_offre', $offer->getTitre_offre());
        $stmt->bindValue(':description_offre', $offer->getDescription_offre());
        $stmt->bindValue(':evenement', $offer->getEvenement());
        $stmt->bindValue(':montant_offre', $offer->getMontant_offre());
        $stmt->bindValue(':status', $offer->getStatus());
        return $stmt->execute();
    }
    public function addSponsor(sponsor $sponsor)
{
    // Assuming you already have a PDO connection $pdo
    // Replace with your actual PDO instance
     $pdo = config::getConnexion(); 

    $sql = "INSERT INTO sponsor (
                nom_entreprise, 
                evenement, 
                email, 
                telephone, 
                montant, 
                duree, 
                avantage
            ) VALUES (
                :nom_entreprise, 
                :evenement, 
                :email, 
                :telephone, 
                :montant, 
                :duree, 
                :avantage
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':nom_entreprise', $sponsor->getNom_entreprise());
    $stmt->bindValue(':evenement', $sponsor->getEvenement());
    $stmt->bindValue(':email', $sponsor->getEmail());
    $stmt->bindValue(':telephone', $sponsor->getTelephone(), PDO::PARAM_INT);
    $stmt->bindValue(':montant', $sponsor->getMontant());
    $stmt->bindValue(':duree', $sponsor->getDuree());
    $stmt->bindValue(':avantage', $sponsor->getAvantage());

    if ($stmt->execute()) {
        // Optional: get the inserted ID and set it in the object
        $sponsor->setId_sponsor($pdo->lastInsertId());
        return true;
    } else {
        return false;
    }
}
public function deleteSponsor($id_sponsor) {
    $db = config::getConnexion();
    $sql = $db->prepare("DELETE FROM sponsor WHERE id_sponsor = :id");
    $sql->bindValue(':id', $id_sponsor, PDO::PARAM_INT);
    return $sql->execute();
}

public function getSponsorById($id_sponsor) {
    $db = config::getConnexion();
    $sql = $db->prepare("SELECT * FROM sponsor WHERE id_sponsor = :id");
    $sql->bindValue(':id', $id_sponsor, PDO::PARAM_INT);
    $sql->execute();
    return $sql->fetch(PDO::FETCH_ASSOC);
}

public function updateSponsor(sponsor $sponsor) {
    $db = config::getConnexion();
    $sql = $db->prepare("UPDATE sponsor SET 
        nom_entreprise = :nom_entreprise,
        evenement = :evenement,
        email = :email,
        telephone = :telephone,
        montant = :montant,
        duree = :duree,
        avantage = :avantage,
        status = :status 
        WHERE id_sponsor = :id");

    $sql->bindValue(':id', $sponsor->getId_sponsor(), PDO::PARAM_INT);
    $sql->bindValue(':nom_entreprise', $sponsor->getNom_entreprise());
    $sql->bindValue(':evenement', $sponsor->getEvenement());
    $sql->bindValue(':email', $sponsor->getEmail());
    $sql->bindValue(':telephone', $sponsor->getTelephone(), PDO::PARAM_INT);
    $sql->bindValue(':montant', $sponsor->getMontant());
    $sql->bindValue(':duree', $sponsor->getDuree());
    $sql->bindValue(':avantage', $sponsor->getAvantage());
    $sql->bindValue(':status', $sponsor->getStatus());

    return $sql->execute();
}
}
?>