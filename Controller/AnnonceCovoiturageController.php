<?php
require_once '../config.php';
require_once '../Model/AnnonceCovoiturage.php';

class AnnonceCovoiturageController {

    private $pdo;

    // Constructor that accepts the PDO connection
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

public function ajouterAnnonce($data) {
    $errors = $this->validateInputs($data);
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }

    try {
        
        $annonce = new annonce_covoiturage(
            $data['prenom_conducteur'],
            $data['nom_conducteur'],
            $data['tel_conducteur'],
            $data['date_depart'],
            $data['lieu_depart'],
            $data['lieu_arrivee'],
            $data['nombre_places'],
            $data['type_voiture'],
            $data['prix_estime'],
            $data['description']
        );

        
        $query = "INSERT INTO annonce_covoiturage 
                 (prenom_conducteur, nom_conducteur, tel_conducteur, date_depart, 
                  lieu_depart, lieu_arrivee, nombre_places, type_voiture, 
                  prix_estime, description, status, date_creation) 
                  VALUES 
                 (:prenom_conducteur, :nom_conducteur, :tel_conducteur, :date_depart, 
                  :lieu_depart, :lieu_arrivee, :nombre_places, :type_voiture, 
                  :prix_estime, :description, :status, :date_creation)";

       
        $stmt = $this->pdo->prepare($query);

       
        $stmt->execute($annonce->toArrayForInsert());
        return "Votre annonce a été ajoutée avec succès!";
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de l'ajout de l'annonce: " . $e->getMessage());
    }
}
//controle de saisie
private function validateInputs($data) {
    $errors = [];
    
    // for the first name nd last name 
    if (empty($data['prenom_conducteur']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['prenom_conducteur'])) {
        $errors[] = "Prénom invalide (minimum 2 caractères alphabétiques)";
    }
    if (empty($data['nom_conducteur']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['nom_conducteur'])) {
        $errors[] = "Nom invalide (minimum 2 caractères alphabétiques)";
    }
    
    //for the phone number
    if (empty($data['tel_conducteur']) || !preg_match("/^[0-9]{8}$/", $data['tel_conducteur'])) {
        $errors[] = "Numéro de téléphone invalide (8 chiffres requis)";
    }
    
    // for the date_depart 
    if (empty($data['date_depart'])) {
        $errors[] = "Date de départ requise";
    } else {
        $departDate = new DateTime($data['date_depart']);
        $now = new DateTime();
        if ($departDate <= $now) {
            $errors[] = "La date de départ doit être dans le futur";
        }
    }
    
    // for(1-4)places
    if (empty($data['nombre_places']) || $data['nombre_places'] < 1 || $data['nombre_places'] > 4) {
        $errors[] = "Nombre de places invalide (1-4)";
    }
    
    //for a postive price
    if (empty($data['prix_estime']) || $data['prix_estime'] <= 0) {
        $errors[] = "Prix estimé invalide (doit être positif)";
    }

    // for the places
    if (empty($data['lieu_depart']) || strlen(trim($data['lieu_depart'])) < 2) {
        $errors[] = "Le lieu de départ est requis (minimum 2 caractères)";
    }
    if (empty($data['lieu_arrivee']) || strlen(trim($data['lieu_arrivee'])) < 2) {
        $errors[] = "Le lieu d'arrivée est requis (minimum 2 caractères)";
    }

    // for the type of car
    if (empty($data['type_voiture']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['type_voiture'])) {
        $errors[] = "Type de voiture invalide (minimum 2 caractères alphabétiques)";
    }
    return $errors;
}

// Readdd one annonce by id
public function getAnnonceById($id) {
    try {
        $query = "SELECT * FROM annonce_covoiturage WHERE id_conducteur = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $annonce = new annonce_covoiturage(
                $row['prenom_conducteur'],
                $row['nom_conducteur'],
                $row['tel_conducteur'],
                $row['date_depart'],
                $row['lieu_depart'],
                $row['lieu_arrivee'],
                $row['nombre_places'],
                $row['type_voiture'],
                $row['prix_estime'],
                $row['description'],
                $row['status']
            );
            $annonce->setIdConducteur($row['id_conducteur']);
            return $annonce;
        }
        
        return null;
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la récupération de l'annonce: " . $e->getMessage());
    }
}

// Readdd all the annonces
public function getAllAnnonces() {
    try {
        $query = "SELECT * FROM annonce_covoiturage ORDER BY date_depart ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        $annonces = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $annonce = new annonce_covoiturage(
                $row['prenom_conducteur'],
                $row['nom_conducteur'],
                $row['tel_conducteur'],
                $row['date_depart'],
                $row['lieu_depart'],
                $row['lieu_arrivee'],
                $row['nombre_places'],
                $row['type_voiture'],
                $row['prix_estime'],
                $row['description'],
                $row['status']
            );
            $annonce->setIdConducteur($row['id_conducteur']);
            $annonces[] = $annonce;
        }
        
        return $annonces;
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la récupération des annonces: " . $e->getMessage());
    }
}
public function deleteAnnonce($id) {
    try {
        
        $query = "DELETE FROM annonce_covoiturage WHERE id_conducteur = :id";
        
      
        $stmt = $this->pdo->prepare($query);
        
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
      
        $success = $stmt->execute();
        
        
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
        
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la suppression de l'annonce: " . $e->getMessage());
    }
}

public function updateAnnonce($data) {
    
    $errors = $this->validateInputs($data);
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }

    try {
        $existing = $this->getAnnonceById($data['id_conducteur']);
        if (!$existing) {
            throw new Exception("Annonce non trouvée");
        }

        
        $query = "UPDATE annonce_covoiturage SET 
                 prenom_conducteur = :prenom_conducteur,
                 nom_conducteur = :nom_conducteur,
                 tel_conducteur = :tel_conducteur,
                 date_depart = :date_depart,
                 lieu_depart = :lieu_depart,
                 lieu_arrivee = :lieu_arrivee,
                 nombre_places = :nombre_places,
                 type_voiture = :type_voiture,
                 prix_estime = :prix_estime,
                 description = :description
                 WHERE id_conducteur = :id_conducteur";

      
        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':prenom_conducteur', $data['prenom_conducteur']);
        $stmt->bindParam(':nom_conducteur', $data['nom_conducteur']);
        $stmt->bindParam(':tel_conducteur', $data['tel_conducteur']);
        $stmt->bindParam(':date_depart', $data['date_depart']);
        $stmt->bindParam(':lieu_depart', $data['lieu_depart']);
        $stmt->bindParam(':lieu_arrivee', $data['lieu_arrivee']);
        $stmt->bindParam(':nombre_places', $data['nombre_places'], PDO::PARAM_INT);
        $stmt->bindParam(':type_voiture', $data['type_voiture']);
        $stmt->bindParam(':prix_estime', $data['prix_estime']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':id_conducteur', $data['id_conducteur'], PDO::PARAM_INT);

        $stmt->execute();

        return "L'annonce a été mise à jour avec succès!";
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la mise à jour de l'annonce: " . $e->getMessage());
    }
}

}
?>
