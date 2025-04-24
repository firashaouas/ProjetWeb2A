<?php
require_once(__DIR__ . "/../config.php");
class sponsorController{
    public function listSponser(){
        $db = config::getConnexion(); 
        $sql = $db->prepare("SELECT * FROM sponsor2");
        $sql->execute();
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $result;
        
    }

    public function listOffers() {
        $db = config::getConnexion();
        $sql = $db->prepare("SELECT * FROM offre2");
        $sql->execute();
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function addOffer($offer) {
        $db = config::getConnexion();
        $sql = "INSERT INTO offre (titre_offre, description_offre, evenement, montant_offre, status, image) VALUES (:titre_offre, :description_offre, :evenement, :montant_offre, :status, :image)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':titre_offre', $offer->getTitre_offre());
        $stmt->bindValue(':description_offre', $offer->getDescription_offre());
        $stmt->bindValue(':evenement', $offer->getEvenement());
        $stmt->bindValue(':montant_offre', $offer->getMontant_offre());
        $stmt->bindValue(':status', $offer->getStatus());
        $stmt->bindValue(':image', $offer->getImage());
        return $stmt->execute();
    }
    public function addSponsor(sponsor $sponsor)
{
    
     $pdo = config::getConnexion(); 

    $sql = "INSERT INTO sponsor2 (
                nom_entreprise, 
                email, 
                telephone, 
                montant, 
                duree, 
                avantage,
                status,
                id_offre
            ) VALUES (
                :nom_entreprise, 
                :email, 
                :telephone, 
                :montant, 
                :duree, 
                :avantage,
                :status,
                :id_offre
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':nom_entreprise', $sponsor->getNom_entreprise());
    $stmt->bindValue(':email', $sponsor->getEmail());
    $stmt->bindValue(':telephone', $sponsor->getTelephone(), PDO::PARAM_INT);
    $stmt->bindValue(':montant', $sponsor->getMontant());
    $stmt->bindValue(':duree', $sponsor->getDuree());
    $stmt->bindValue(':avantage', $sponsor->getAvantage());
    $stmt->bindValue(':status', $sponsor->getStatus());
    $stmt->bindValue(':id_offre', $sponsor->getId_offre(), PDO::PARAM_INT);

    if ($stmt->execute()) {
        
        $sponsor->setId_sponsor($pdo->lastInsertId());
        return true;
    } else {
        return false;
    }
}
public function deleteSponsor($id_sponsor) {
    $db = config::getConnexion();
    $sql = $db->prepare("DELETE FROM sponsor2 WHERE id_sponsor = :id");
    $sql->bindValue(':id', $id_sponsor, PDO::PARAM_INT);
    return $sql->execute();
}

public function getSponsorById($id_sponsor) {
    $db = config::getConnexion();
    $sql = $db->prepare("SELECT * FROM sponsor2 WHERE id_sponsor = :id");
    $sql->bindValue(':id', $id_sponsor, PDO::PARAM_INT);
    $sql->execute();
    return $sql->fetch(PDO::FETCH_ASSOC);
}

    public function updateSponsor(sponsor $sponsor) {
        $db = config::getConnexion();
        $sql = $db->prepare("UPDATE sponsor2 SET 
            nom_entreprise = :nom_entreprise,
            email = :email,
            telephone = :telephone,
            montant = :montant,
            duree = :duree,
            avantage = :avantage,
            status = :status,
            id_offre = :id_offre
            WHERE id_sponsor = :id");

        $sql->bindValue(':id', $sponsor->getId_sponsor(), PDO::PARAM_INT);
        $sql->bindValue(':nom_entreprise', $sponsor->getNom_entreprise());
        $sql->bindValue(':email', $sponsor->getEmail());
        $sql->bindValue(':telephone', $sponsor->getTelephone(), PDO::PARAM_INT);
        $sql->bindValue(':montant', $sponsor->getMontant());
        $sql->bindValue(':duree', $sponsor->getDuree());
        $sql->bindValue(':avantage', $sponsor->getAvantage());
        $sql->bindValue(':status', $sponsor->getStatus());
        $sql->bindValue(':id_offre', $sponsor->getId_offre(), PDO::PARAM_INT);

    return $sql->execute();
}
    public function deleteOffer($id_offer) {
        $db = config::getConnexion();
        $sql = $db->prepare("DELETE FROM offre WHERE id_offre = :id");
        $sql->bindValue(':id', $id_offer, PDO::PARAM_INT);
        return $sql->execute();
    }

    public function getOfferById($id_offer) {
        $db = config::getConnexion();
        $sql = $db->prepare("SELECT * FROM offre WHERE id_offre = :id");
        $sql->bindValue(':id', $id_offer, PDO::PARAM_INT);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOffer(Offre $offer) {
        $db = config::getConnexion();
        $sql = $db->prepare("UPDATE offre SET 
            titre_offre = :titre_offre,
            description_offre = :description_offre,
            evenement = :evenement,
            montant_offre = :montant_offre,
            status = :status,
            image = :image
            WHERE id_offre = :id");

        $sql->bindValue(':id', $offer->getId_offre(), PDO::PARAM_INT);
        $sql->bindValue(':titre_offre', $offer->getTitre_offre());
        $sql->bindValue(':description_offre', $offer->getDescription_offre());
        $sql->bindValue(':evenement', $offer->getEvenement());
        $sql->bindValue(':montant_offre', $offer->getMontant_offre());
        $sql->bindValue(':status', $offer->getStatus());
        $sql->bindValue(':image', $offer->getImage());

        return $sql->execute();
    }
}
?>
