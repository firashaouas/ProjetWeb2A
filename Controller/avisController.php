<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Avis.php';
require_once __DIR__ . '/../Model/DemandeCovoiturage.php';

class AvisController {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: config::getConnexion();
        if (!$this->pdo) {
            throw new Exception("Erreur de connexion à la base de données");
        }
    }

    public function createAvis($id_passager, $note, $commentaire) {
        try {
            // Récupérer la demande pour obtenir l'id_conducteur
            $query = "SELECT id_conducteur FROM demande_covoiturage WHERE id_passager = :id_passager";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_passager', $id_passager, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception("Demande non trouvée");
            }
            $id_conducteur = $row['id_conducteur'];

            // Vérifier si un avis existe déjà pour cette demande
            $query = "SELECT COUNT(*) FROM avis WHERE id_passager = :id_passager";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_passager', $id_passager, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Vous avez déjà laissé un avis pour cette demande");
            }

            // Validation
            if (!is_numeric($note) || $note < 1 || $note > 5) {
                throw new Exception("La note doit être entre 1 et 5");
            }
            $commentaire = trim($commentaire);
            if (strlen($commentaire) > 500) {
                throw new Exception("Le commentaire ne peut pas dépasser 500 caractères");
            }

            // Insérer l'avis
            $query = "INSERT INTO avis (id_passager, id_conducteur, note, commentaire, date_creation) 
                     VALUES (:id_passager, :id_conducteur, :note, :commentaire, NOW())";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_passager', $id_passager, PDO::PARAM_INT);
            $stmt->bindParam(':id_conducteur', $id_conducteur, PDO::PARAM_INT);
            $stmt->bindParam(':note', $note, PDO::PARAM_INT);
            $stmt->bindParam(':commentaire', $commentaire);
            $stmt->execute();

            return "Avis ajouté avec succès !";
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'ajout de l'avis : " . $e->getMessage());
        }
    }

    public function updateAvis($id_avis, $note, $commentaire) {
        try {
            // Validation
            if (!is_numeric($note) || $note < 1 || $note > 5) {
                throw new Exception("La note doit être entre 1 et 5");
            }
            $commentaire = trim($commentaire);
            if (strlen($commentaire) > 500) {
                throw new Exception("Le commentaire ne peut pas dépasser 500 caractères");
            }

            // Mettre à jour l'avis
            $query = "UPDATE avis SET note = :note, commentaire = :commentaire, date_modification = NOW() 
                     WHERE id_avis = :id_avis";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':note', $note, PDO::PARAM_INT);
            $stmt->bindParam(':commentaire', $commentaire);
            $stmt->bindParam(':id_avis', $id_avis, PDO::PARAM_INT);
            $stmt->execute();

            return "Avis modifié avec succès !";
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la modification de l'avis : " . $e->getMessage());
        }
    }

    public function deleteAvis($id_avis) {
        try {
            $query = "DELETE FROM avis WHERE id_avis = :id_avis";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_avis', $id_avis, PDO::PARAM_INT);
            $stmt->execute();
            return "Avis supprimé avec succès !";
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de l'avis : " . $e->getMessage());
        }
    }

    public function getAvisById($id_avis) {
        try {
            $query = "SELECT * FROM avis WHERE id_avis = :id_avis";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_avis', $id_avis, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }

            return new Avis(
                $row['id_avis'],
                $row['id_passager'],
                $row['id_conducteur'],
                $row['note'],
                $row['commentaire'],
                new DateTime($row['date_creation']),
                $row['date_modification'] ? new DateTime($row['date_modification']) : null
            );
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'avis : " . $e->getMessage());
        }
    }

    public function getAvisByPassagerId($id_passager) {
        try {
            $query = "SELECT * FROM avis WHERE id_passager = :id_passager ORDER BY date_creation DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_passager', $id_passager, PDO::PARAM_INT);
            $stmt->execute();

            $avis = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avis[] = new Avis(
                    $row['id_avis'],
                    $row['id_passager'],
                    $row['id_conducteur'],
                    $row['note'],
                    $row['commentaire'],
                    new DateTime($row['date_creation']),
                    $row['date_modification'] ? new DateTime($row['date_modification']) : null
                );
            }
            return $avis;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des avis : " . $e->getMessage());
        }
    }

    public function getAllAvis() {
        try {
            $query = "SELECT * FROM avis ORDER BY date_creation DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $avis = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avis[] = new Avis(
                    $row['id_avis'],
                    $row['id_passager'],
                    $row['id_conducteur'],
                    $row['note'],
                    $row['commentaire'],
                    new DateTime($row['date_creation']),
                    $row['date_modification'] ? new DateTime($row['date_modification']) : null
                );
            }
            return $avis;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des avis : " . $e->getMessage());
        }
    }
}
?>