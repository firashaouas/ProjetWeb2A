<?php
require_once __DIR__ . '/../model/ReviewModel.php';
// Ajouter l'inclusion du fichier d'aide pour l'upload d'images
require_once __DIR__ . '/../utils/upload_helper.php';

class ReviewController {
    private $model;

    public function __construct() {
        $this->model = new ReviewModel();
    }

    public function index() {
        // Récupérer tous les avis approuvés pour l'affichage public
        return $this->model->getApprovedReviews();
    }

    public function getAverageRating() {
        return $this->model->getAverageRating();
    }

    public function getRatingsDistribution() {
        return $this->model->getRatingsDistribution();
    }

    public function addReview($activityId, $activityName, $customerName, $customerEmail, $rating, $comment, $image = null) {
        // Gestion de l'image
        $imagePath = null;
        
        if ($image && $image['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $maxSize = 2 * 1024 * 1024; // 2 MB
            
            if (in_array($image['type'], $allowedTypes) && $image['size'] <= $maxSize) {
                // Télécharger l'image vers Cloudinary et obtenir l'URL
                $imagePath = uploadImage($image, true); // true = sauvegarder aussi en local
                
                if (empty($imagePath)) {
                    error_log("Échec du téléchargement de l'image vers Cloudinary");
                } else {
                    error_log("Image téléchargée avec succès vers Cloudinary: " . $imagePath);
                }
            } else {
                error_log("Type ou taille d'image non valide. Type: " . $image['type'] . ", Taille: " . $image['size']);
            }
        }
        
        $success = $this->model->addReview($activityId, $activityName, $customerName, $customerEmail, $rating, $comment, $imagePath);
        
        return [
            'success' => $success,
            'message' => $success ? 'Votre avis a été enregistré et sera publié après validation.' : 'Une erreur est survenue lors de l\'enregistrement de votre avis.'
        ];
    }

    public function getAllReviews() {
        // Pour l'administration, récupérer tous les avis
        return $this->model->getAllReviews();
    }

    public function approveReview($id) {
        $review = $this->model->getReviewById($id);
        
        if (!$review) {
            return [
                'success' => false,
                'message' => 'Avis non trouvé'
            ];
        }
        
        $result = $this->model->updateReviewStatus($id, 'approved');
        
        return [
            'success' => $result,
            'message' => $result ? 'Avis approuvé avec succès' : 'Erreur lors de l\'approbation de l\'avis'
        ];
    }

    public function rejectReview($id) {
        $review = $this->model->getReviewById($id);
        
        if (!$review) {
            return [
                'success' => false,
                'message' => 'Avis non trouvé'
            ];
        }
        
        $result = $this->model->updateReviewStatus($id, 'rejected');
        
        return [
            'success' => $result,
            'message' => $result ? 'Avis rejeté avec succès' : 'Erreur lors du rejet de l\'avis'
        ];
    }

    public function deleteReview($id) {
        $review = $this->model->getReviewById($id);
        
        if (!$review) {
            return [
                'success' => false,
                'message' => 'Avis non trouvé'
            ];
        }
        
        $result = $this->model->deleteReview($id);
        
        return [
            'success' => $result,
            'message' => $result ? 'Avis supprimé avec succès' : 'Erreur lors de la suppression de l\'avis'
        ];
    }
}
?> 