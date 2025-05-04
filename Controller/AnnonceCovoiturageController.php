<?php
require_once '../config.php';
require_once '../Model/AnnonceCovoiturage.php';

// Set PHP time zone to Tunisia (UTC+1)
date_default_timezone_set('Africa/Tunis');

class AnnonceCovoiturageController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function ajouterAnnonce($data) {
        $errors = $this->validateInputs($data, true);
        if (!empty($errors)) {
            throw new Exception(implode("\n", $errors));
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
                $data['description'] ?? ''
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
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'ajout de l'annonce: " . $e->getMessage());
        }
    }

    public function searchAnnonces($depart, $arrivee) {
        try {
            $query = "SELECT * FROM annonce_covoiturage 
                    WHERE LOWER(lieu_depart) LIKE LOWER(:depart) 
                    AND LOWER(lieu_arrivee) LIKE LOWER(:arrivee) 
                    AND status = 'disponible'
                    AND date_depart >= NOW()
                    ORDER BY date_depart ASC";
    
            $stmt = $this->pdo->prepare($query);
            $params = [
                'depart' => "%$depart%",
                'arrivee' => "%$arrivee%"
            ];
    
            $stmt->execute($params);
    
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
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible'
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                $annonces[] = $annonce;
            }
    
            return $annonces;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche des annonces: " . $e->getMessage());
        }
    }

    private function validateInputs($data, $isInsert = false) {
        $errors = [];

        if (empty($data['prenom_conducteur']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['prenom_conducteur'])) {
            $errors[] = "Prénom invalide (minimum 2 caractères alphabétiques)";
        }
        if (empty($data['nom_conducteur']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['nom_conducteur'])) {
            $errors[] = "Nom invalide (minimum 2 caractères alphabétiques)";
        }
        if (empty($data['tel_conducteur']) || !preg_match("/^[0-9]{8}$/", $data['tel_conducteur'])) {
            $errors[] = "Numéro de téléphone invalide (8 chiffres requis)";
        }
        if (empty($data['date_depart'])) {
            $errors[] = "Date de départ requise";
        } else {
            try {
                $departDate = new DateTime($data['date_depart']);
                if ($isInsert && $departDate <= new DateTime()) {
                    $errors[] = "La date de départ doit être dans le futur";
                }
            } catch (Exception $e) {
                $errors[] = "Format de date invalide";
            }
        }
        if (empty($data['nombre_places']) || $data['nombre_places'] < 1 || $data['nombre_places'] > 8) {
            $errors[] = "Nombre de places invalide (1-8)";
        }
        if ($isInsert && (empty($data['prix_estime']) || $data['prix_estime'] <= 0)) {
            $errors[] = "Prix estimé invalide (doit être positif)";
        } elseif (!$isInsert && isset($data['prix_estime']) && $data['prix_estime'] !== '' && $data['prix_estime'] < 0) {
            $errors[] = "Prix estimé invalide (ne peut pas être négatif)";
        }
        if (empty($data['lieu_depart']) || strlen(trim($data['lieu_depart'])) < 2) {
            $errors[] = "Le lieu de départ est requis (minimum 2 caractères)";
        }
        if (empty($data['lieu_arrivee']) || strlen(trim($data['lieu_arrivee'])) < 2) {
            $errors[] = "Le lieu d'arrivée est requis (minimum 2 caractères)";
        }
        if (empty($data['type_voiture']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['type_voiture'])) {
            $errors[] = "Type de voiture invalide (minimum 2 caractères alphabétiques)";
        }

        return $errors;
    }

    // Update status of archived annonces
    private function updateArchivedAnnonces() {
        try {
            // Set MySQL time zone to UTC+1 (Tunisia)
            $this->pdo->exec("SET time_zone = '+01:00';");

            $currentDate = new DateTime();
            $query = "UPDATE annonce_covoiturage SET status = 'archivée', date_modification = NOW() WHERE date_depart < :currentDate AND status = 'disponible'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['currentDate' => $currentDate->format('Y-m-d H:i:s')]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour des annonces archivées: " . $e->getMessage());
        }
    }

    public function getAllAnnonces($forPassenger = false) {
        try {
            // Update archived annonces before fetching
            $this->updateArchivedAnnonces();

            $query = "SELECT * FROM annonce_covoiturage ORDER BY date_depart ASC";
            if ($forPassenger) {
                $query = "SELECT * FROM annonce_covoiturage WHERE status = 'disponible' AND date_depart >= NOW() ORDER BY date_depart ASC";
            }
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
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible'
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                $annonces[] = $annonce;
            }

            return $annonces;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des annonces: " . $e->getMessage());
        }
    }

    public function getAnnonceById($id) {
        try {
            $query = "SELECT * FROM annonce_covoiturage WHERE id_conducteur = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible'
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                return $annonce;
            }

            return null;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'annonce: " . $e->getMessage());
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

    public function getAnnoncesByConducteurId($id_conducteur) {
        try {
            $sql = "SELECT * FROM annonce_covoiturage WHERE id_conducteur = :id_conducteur";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_conducteur', $id_conducteur, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function archiverAnnonce($id) {
        try {
            // Mise à jour du statut de l'annonce pour la marquer comme archivée
            $query = "UPDATE annonce_covoiturage SET status = 'archivée', date_modification = NOW() WHERE id_conducteur = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'archivage de l'annonce: " . $e->getMessage());
        }
    }
    
    public function updateAnnonce($data) {
        // Add validation
        $errors = $this->validateInputs($data, false);
        if (!empty($errors)) {
            throw new Exception(implode("\n", $errors));
        }

        try {
            $sql = "UPDATE annonce_covoiturage SET 
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

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':prenom_conducteur' => $data['prenom_conducteur'],
                ':nom_conducteur' => $data['nom_conducteur'],
                ':tel_conducteur' => $data['tel_conducteur'],
                ':date_depart' => $data['date_depart'],
                ':lieu_depart' => $data['lieu_depart'],
                ':lieu_arrivee' => $data['lieu_arrivee'],
                ':nombre_places' => $data['nombre_places'],
                ':type_voiture' => $data['type_voiture'],
                ':prix_estime' => $data['prix_estime'],
                ':description' => $data['description'] ?? '',
                ':id_conducteur' => $data['id_conducteur']
            ]);

            // Check if any rows were updated
            if ($stmt->rowCount() === 0) {
                throw new Exception("Aucune modification effectuée - l'annonce n'existe peut-être pas");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour: " . $e->getMessage());
        }
    }
}
?>