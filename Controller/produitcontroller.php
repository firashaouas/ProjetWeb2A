<?php
require_once(__DIR__ . "/../model/produitmodel.php");
require_once(__DIR__ . "../../config.php");

<<<<<<< HEAD


=======
>>>>>>> 3c3f2119bb40b1bf3989c4f7e0f85b069e0926de
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the POST data to a file for debugging
file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - POST data: ' . print_r($_POST, true) . "\n", FILE_APPEND);

class ProductController {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Config::getConnexion();
            // Log successful connection
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - Database connected successfully' . "\n", FILE_APPEND);
        } catch (Exception $e) {
            // Log database connection error
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - Database connection error: ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }
    public function rentProduct($productId, $quantity) {
        try {
            // Vérifier si le stock est suffisant
            $product = $this->getProductById($productId);
            if ($product['stock'] < $quantity) {
                throw new Exception("Stock insuffisant pour la location du produit ID $productId");
            }
    
            // Réduire le stock du produit
            return $this->decreaseStock($productId, $quantity);
        } catch (Exception $e) {
            error_log("Erreur dans rentProduct: " . $e->getMessage());
            return false;
        }
    }
    
public function decreaseStock($productId, $quantity) {
    try {
        $sql = "UPDATE products SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':quantity' => $quantity,
            ':id' => $productId
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Stock insuffisant pour le produit ID $productId");
        }

        return true;
    } catch (Exception $e) {
        error_log("Erreur dans decreaseStock: " . $e->getMessage());
        return false;
    }
}



    public function getProductsByCategory($categoryName) {
        try {
            // Nettoyer et valider la catégorie
            $allowedCategories = [
                'Équipements Sportifs',
                'Vêtements et Accessoires',
                'Gadgets & Technologies',
                'Articles de Bien-être & Récupération',
                'Nutrition & Hydratation',
                'Accessoires de Voyage & Mobilité',
                'Supports et accessoires d\'atelier',
                'Univers du cerveau'
            ];
    
            if (!in_array($categoryName, $allowedCategories)) {
                return ['error' => 'Catégorie invalide'];
            }
    
            // Ajouter 'id' dans la sélection
            $stmt = $this->pdo->prepare("SELECT id, name, price, photo as image FROM products WHERE category = ?");
            $stmt->execute([$categoryName]);
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatage cohérent des données
            return array_map(function($product) {
                return [
                    'id' => $product['id'], // Inclure l'ID
                    'name' => $product['name'],
                    'price' => number_format($product['price'], 2) . ' TND',
                    'image' => $product['image'] ?? 'images/default-product.jpg'
                ];
            }, $products);
    
        } catch (Exception $e) {
            error_log("Erreur getProductsByCategory: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // Add a new product
    public function addProduct($name, $price, $stock, $category, $purchase_available, $rental_available) {
        try {

            // Gestion de l'image
            $photoPath = 'images/products/logo.png'; 
            
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->handleFileUpload('photo');
            }

            
            $purchase_available = ($purchase_available === 'yes') ? 1 : 0;
            $rental_available = ($rental_available === 'yes') ? 1 : 0;

            // Requête SQL
            $sql = "INSERT INTO products 
                    (name, price, stock, category, purchase_available, rental_available, photo, created_at) 
                    VALUES (:name, :price, :stock, :category, :purchase, :rental, :photo, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':stock' => $stock,
                ':category' => $category,
                ':purchase' => $purchase_available,
                ':rental' => $rental_available,
                ':photo' => $photoPath
            ]);

            return "Produit ajouté avec succès. Image: " . $photoPath;
            
        } catch (Exception $e) {
            error_log("Erreur dans addProduct: " . $e->getMessage());
            return "Erreur: " . $e->getMessage();
        }
    }



    private function handleFileUpload($fileInputName) {
        // 1. Vérifier si un fichier a été uploadé
        if (!isset($_FILES[$fileInputName])) {
            throw new Exception("Aucun fichier n'a été uploadé");
        }
    
        $file = $_FILES[$fileInputName];
    
        // 2. Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors de l'upload: " . $this->getUploadError($file['error']));
        }
    
        // 3. Vérifier le type MIME réel du fichier
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
    
        if (!array_key_exists($mimeType, $allowedMimeTypes)) {
            throw new Exception("Type de fichier non autorisé. Seuls JPG, PNG, GIF et WebP sont acceptés.");
        }
    
        // 4. Vérifier la taille du fichier (max 2MB)
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxFileSize) {
            throw new Exception("La taille du fichier ne doit pas dépasser 2MB");
        }
    
        // 5. Préparer le répertoire de destination
        $uploadDir = '../images/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    
        // 6. Générer un nom de fichier unique
        $extension = $allowedMimeTypes[$mimeType];
        $filename = uniqid('prod_', true) . '.' . $extension;
        $destination = $uploadDir . $filename;
    
        // 7. Déplacer le fichier uploadé
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Impossible de déplacer le fichier uploadé");
        }
    
        // 8. Retourner le chemin relatif (important pour le stockage en base)
        return 'images/products/' . $filename;
    }
    
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (php.ini)',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (formulaire)',
            UPLOAD_ERR_PARTIAL => 'Upload partiel',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture',
            UPLOAD_ERR_EXTENSION => 'Extension PHP bloquée'
        ];
        return $errors[$errorCode] ?? 'Erreur inconnue';
    }



    // Delete a product
    public function deleteProduct($id) {
        try {
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return 'Produit supprimé avec succès';
        } catch (PDOException $e) {
            return 'Erreur : ' . $e->getMessage();
        }
    }

    public function updateProduct($id, $name, $price, $stock, $category, $purchase_available, $rental_available) {
        try {
            // Conversion des valeurs booléennes
            $purchase_available = ($purchase_available === 'yes') ? 1 : 0;
            $rental_available = ($rental_available === 'yes') ? 1 : 0;
    
            // Vérifier si une nouvelle image a été uploadée
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->handleFileUpload('photo');
            } else {
                // Si aucune image uploadée, conserver l'ancienne photo
                $stmt = $this->pdo->prepare("SELECT photo FROM products WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $photoPath = $stmt->fetchColumn();
            }
    
            // Requête de mise à jour
            $sql = "UPDATE products SET 
                        name = :name, 
                        price = :price, 
                        stock = :stock, 
                        category = :category, 
                        purchase_available = :purchase_available, 
                        rental_available = :rental_available, 
                        photo = :photo 
                    WHERE id = :id";
    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':price' => $price,
                ':stock' => $stock,
                ':category' => $category,
                ':purchase_available' => $purchase_available,
                ':rental_available' => $rental_available,
                ':photo' => $photoPath
            ]);
    
            return 'Produit mis à jour avec succès';
        } catch (Exception $e) {
            error_log("Erreur dans updateProduct: " . $e->getMessage());
            return 'Erreur : ' . $e->getMessage();
        }
    }
    
    // Get all products
    public function getAllProducts() {
        try {
            $sql = "SELECT * FROM products ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'price' => (float)$row['price'],
                    'stock' => (int)$row['stock'],
                    'category' => $row['category'],
                    'purchase_available' => (bool)$row['purchase_available'],
                    'rental_available' => (bool)$row['rental_available'],
                    'photo' => $row['photo']
                ];
            }
            
            return ['success' => true, 'products' => $products];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getCategoryStats() {
        try {
            $sql = "SELECT 
                    category,
                    COUNT(*) as product_count,
                    SUM(stock) as total_stock,
                    SUM(price * stock) as total_value
                FROM products 
                GROUP BY category
                ORDER BY product_count DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $stats = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[] = [
                    'category' => $row['category'],
                    'product_count' => (int)$row['product_count'],
                    'total_stock' => (int)$row['total_stock'],
                    'total_value' => (float)$row['total_value']
                ];
            }
            
            return ['success' => true, 'stats' => $stats];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Get a single product by ID
    public function getProductById($id) {
        try {
            $sql = "SELECT * FROM products WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                throw new Exception('Produit non trouvé');
            }
            
            return [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'category' => $row['category'],
                'purchase_available' => $row['purchase_available'],
                'rental_available' => $row['rental_available'],
                'photo' => $row['photo']
            ];
        } catch (PDOException $e) {
            throw new Exception('Erreur : ' . $e->getMessage());
        }
    }

    public function getGlobalStats() {
        try {
            $sql = "SELECT 
                    SUM(stock) as total_stock,
                    SUM(price * stock) as total_value
                FROM products";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'total_stock' => (int)$row['total_stock'],
                'total_value' => (float)$row['total_value']
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getOrdersStats() {
        try {
            // Compter les commandes d'achat
            $sqlPurchases = "SELECT COUNT(*) as purchase_count FROM commandes";
            $stmtPurchases = $this->pdo->prepare($sqlPurchases);
            $stmtPurchases->execute();
            $purchaseCount = $stmtPurchases->fetch(PDO::FETCH_ASSOC)['purchase_count'];

            // Compter les locations
            $sqlRentals = "SELECT COUNT(*) as rental_count FROM louer";
            $stmtRentals = $this->pdo->prepare($sqlRentals);
            $stmtRentals->execute();
            $rentalCount = $stmtRentals->fetch(PDO::FETCH_ASSOC)['rental_count'];

            // Total des commandes
            $totalOrders = $purchaseCount + $rentalCount;

            return [
                'success' => true,
                'total_orders' => $totalOrders,
                'purchase_count' => $purchaseCount,
                'rental_count' => $rentalCount
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getTopProducts($period = 'today') {
        try {
            $query = "SELECT p.*, COUNT(c.id_commande) as total_ventes 
                     FROM products p 
                     LEFT JOIN commandes c ON p.id = c.id_produit ";

            switch($period) {
                case 'today':
                    $query .= "AND DATE(c.date_commande) = CURDATE() ";
                    break;
                case 'week':
                    $query .= "AND c.date_commande >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) ";
                    break;
                case 'month':
                    $query .= "AND c.date_commande >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ";
                    break;
                default:
                    $query .= "AND DATE(c.date_commande) = CURDATE() ";
            }

            $query .= "GROUP BY p.id, p.name, p.category, p.stock, p.price 
                      ORDER BY total_ventes DESC 
                      LIMIT 3";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            // Debug: Afficher la requête et les résultats
            error_log("Query: " . $query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Results: " . print_r($results, true));

            return [
                'success' => true,
                'products' => $results
            ];
        } catch (PDOException $e) {
            error_log("Error in getTopProducts: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getRecentActivities() {
        try {
            $query = "SELECT 
                        name as product_name,
                        stock as quantity,
                        created_at as date,
                        'add' as type
                    FROM products 
                    ORDER BY created_at DESC 
                    LIMIT 5";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'activities' => $activities
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getOutOfStockCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM products WHERE stock = 0";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return [
                'success' => true,
                'count' => (int)$stmt->fetch(PDO::FETCH_ASSOC)['count']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// Create controller instance
$controller = new ProductController();

// Handle API requests - check if it's an AJAX request (for get_products or get_product)
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || 
    (isset($_GET['action']) && ($_GET['action'] === 'get_all' || $_GET['action'] === 'get_one'))) {
    
    header('Content-Type: application/json');
    
    try {
       
        if (isset($_GET['action']) && $_GET['action'] === 'get_all') {
            $products = $controller->getAllProducts();
            echo json_encode(['success' => true, 'products' => $products]);
            exit;
        }
        
        
        if (isset($_GET['action']) && $_GET['action'] === 'get_one' && isset($_GET['id'])) {
            $product = $controller->getProductById($_GET['id']);
            echo json_encode(['success' => true, 'product' => $product]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Handle form submissions
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $result = $controller->addProduct(
                $_POST['name'],
                $_POST['price'],
                $_POST['stock'],
                $_POST['category'],
                $_POST['purchase_available'],
                $_POST['rental_available']
            );
            break;

        case 'delete':
            $message = $controller->deleteProduct($_POST['id']);
            break;

        case 'update':
            $message = $controller->updateProduct(
                $_POST['id'],
                $_POST['name'],
                $_POST['price'],
                $_POST['stock'],
                $_POST['category'],
                $_POST['purchase_available'],
                $_POST['rental_available']
            );
            break;

        default:
            $message = 'Action invalide';
            break;
    }

    if (is_array($result) && !$result['success']) {
        // Stocke les erreurs en session par exemple
        session_start();
        $_SESSION['form_errors'] = $result['errors'];
        header("Location: ../view/back%20office/indeex.php#products");
        exit;
    } else {
        $message = is_array($result) ? $result['message'] : $result;
        header("Location: ../view/back%20office/indeex.php#products?message=" . urlencode($message));
        exit;
    }
}
<<<<<<< HEAD


=======
>>>>>>> 3c3f2119bb40b1bf3989c4f7e0f85b069e0926de
if (isset($_GET['category'])) {
    $categoryName = $_GET['category'];
    $controller = new ProductController();
    $products = $controller->getProductsByCategory($categoryName);
    
    header('Content-Type: application/json');
    echo json_encode($products);
    exit();
}
?>
