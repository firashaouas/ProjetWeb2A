<?php
class PromotionController {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO("mysql:host=localhost;dbname=projet web", "root", "");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function addPromotion($data) {
        try {
            // Vérifier si la photo a été uploadée
            if (empty($data['photo'])) {
                return ['success' => false, 'error' => 'La photo est requise'];
            }

            $stmt = $this->pdo->prepare("INSERT INTO promotions (nom_produit, prix_original, prix_promotion, date_debut, date_fin, photo, statut) 
                                      VALUES (:nom_produit, :prix_original, :prix_promotion, :date_debut, :date_fin, :photo, 'active')");
            
            $result = $stmt->execute([
                ':nom_produit' => $data['nom_produit'],
                ':prix_original' => $data['prix_original'],
                ':prix_promotion' => $data['prix_promotion'],
                ':date_debut' => $data['date_debut'],
                ':date_fin' => $data['date_fin'],
                ':photo' => $data['photo']
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Promotion ajoutée avec succès'];
            } else {
                return ['success' => false, 'error' => 'Erreur lors de l\'ajout de la promotion'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAllPromotions() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM promotions ORDER BY date_debut DESC");
            $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'promotions' => $promotions];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $controller = new PromotionController();
    
    // Gestion de l'upload de photo
    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../images/promotions/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $photoPath = 'images/promotions/' . $fileName;
        } else {
            $_SESSION['promo_error'] = 'Erreur lors de l\'upload de l\'image';
            header("Location: ../view/back office/indeex.php#promos");
            exit;
        }
    } else {
        $_SESSION['promo_error'] = 'Veuillez sélectionner une image';
        header("Location: ../view/back office/indeex.php#promos");
        exit;
    }
    
    $data = [
        'nom_produit' => $_POST['nom_produit'],
        'prix_original' => $_POST['prix_original'],
        'prix_promotion' => $_POST['prix_promotion'],
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin'],
        'photo' => $photoPath
    ];

    $result = $controller->addPromotion($data);
    
    if ($result['success']) {
        $_SESSION['promo_message'] = 'Promotion ajoutée avec succès';
        header("Location: ../view/back office/indeex.php#promos");
    } else {
        $_SESSION['promo_error'] = $result['error'];
        header("Location: ../view/back office/indeex.php#promos");
    }
    exit;
}
?> 