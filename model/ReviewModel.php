<?php
require_once __DIR__ . '/../Database.php';

class ReviewModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->createReviewsTable();
    }

    private function createReviewsTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                activity_id INT,
                activity_name VARCHAR(255) NOT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_email VARCHAR(255) NOT NULL,
                rating INT NOT NULL,
                comment TEXT NOT NULL,
                image_path VARCHAR(255),
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
            )";
            
            $this->conn->exec($sql);
        } catch(PDOException $e) {
            echo "Erreur lors de la création de la table reviews: " . $e->getMessage();
        }
    }

    public function getAllReviews() {
        $query = "SELECT * FROM reviews ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des avis: " . $e->getMessage());
            return [];
        }
    }

    public function getApprovedReviews() {
        $query = "SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des avis approuvés: " . $e->getMessage());
            return [];
        }
    }

    public function getReviewById($id) {
        $query = "SELECT * FROM reviews WHERE id = :id";
        $params = [':id' => $id];
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'avis #$id: " . $e->getMessage());
            return null;
        }
    }

    public function addReview($activityId, $activityName, $customerName, $customerEmail, $rating, $comment, $imagePath = null) {
        try {
            $sql = "INSERT INTO reviews (activity_id, activity_name, customer_name, customer_email, rating, comment, image_path) 
                    VALUES (:activity_id, :activity_name, :customer_name, :customer_email, :rating, :comment, :image_path)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
            $stmt->bindParam(':activity_name', $activityName, PDO::PARAM_STR);
            $stmt->bindParam(':customer_name', $customerName, PDO::PARAM_STR);
            $stmt->bindParam(':customer_email', $customerEmail, PDO::PARAM_STR);
            $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erreur lors de l'ajout d'un avis: " . $e->getMessage());
            return false;
        }
    }

    public function updateReviewStatus($id, $status) {
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            return false;
        }
        
        $query = "UPDATE reviews SET status = :status WHERE id = :id";
        $params = [':id' => $id, ':status' => $status];
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut de l'avis #$id: " . $e->getMessage());
            return false;
        }
    }

    public function deleteReview($id) {
        $query = "DELETE FROM reviews WHERE id = :id";
        $params = [':id' => $id];
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'avis #$id: " . $e->getMessage());
            return false;
        }
    }

    public function getAverageRating() {
        $query = "SELECT AVG(rating) as average FROM reviews WHERE status = 'approved'";
        
        try {
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($result) && $result['average'] !== null ? round($result['average'], 1) : 0;
        } catch (PDOException $e) {
            error_log("Erreur lors du calcul de la note moyenne: " . $e->getMessage());
            return 0;
        }
    }

    public function getRatingsDistribution() {
        $distribution = [];
        
        try {
            for ($i = 1; $i <= 5; $i++) {
                $query = "SELECT COUNT(*) as count FROM reviews WHERE rating = :rating AND status = 'approved'";
                $params = [':rating' => $i];
                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $distribution[$i] = $result['count'];
            }
            return $distribution;
        } catch (PDOException $e) {
            error_log("Erreur lors du calcul de la distribution des notes: " . $e->getMessage());
            return [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        }
    }

    public function getDb() {
        return $this->conn;
    }
}
?> 