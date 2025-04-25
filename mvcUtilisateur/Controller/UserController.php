<?php
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Model/User.php';

class UserController {
    private $userModel;


    public function handleSocialLogin(array $socialUser, string $provider) {
        // Example implementation for handling social login
        // Save or update the user in the database based on $socialUser and $provider
        // This is a placeholder; adapt it to your application's logic
        if (!isset($socialUser['facebook_id'])) {
            throw new Exception('Invalid social user data');
        }

        // Example: Log the user data (replace with actual database logic)
        error_log("Social login via {$provider}: " . json_encode($socialUser));
    }

    public function emailExists($email) {
        // Example logic to check if the email exists in the database
        $db = Config::getConnexion();
        $query = $db->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
        $query->bindParam(':email', $email);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

        // Method to check if a phone number exists
        public function phoneExists($phone) {
            // Replace with actual database logic
            $db = Config::getConnexion();
            $query = $db->prepare("SELECT COUNT(*) FROM user WHERE num_user = :num_user");
            $query->bindParam(':num_user', $phone, PDO::PARAM_STR);
            $query->execute();
            return $query->fetchColumn() > 0;
        }

        public function addUser() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $fullName = $_POST['fullName'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $dateInscription = $_POST['dateInscription'];
                $numUser = $_POST['numUser'];
                $role = $_POST['role'];
                $profilePicture = $_FILES['profilePicture'];
    
                // Upload image
                $uploadDir = 'uploads/';
                $fileName = basename($profilePicture['name']);
                $targetPath = $uploadDir . $fileName;
    
                if (move_uploaded_file($profilePicture['tmp_name'], $targetPath)) {
                    $user = new User();
                    $user->addUser($fullName, $email, $password, $dateInscription, $role, $numUser, null, null, $fileName);
    
                    // Redirection après ajout
                    header('Location: index.php');
                    exit;
                } else {
                    echo "Erreur lors de l’upload de l’image.";
                }
            }
        }

        public function debannirUser($id) {
            $db = Config::getConnexion();
            $user = User::findById($db, $id);
        
            if ($user) {
                $user->setRole('user'); // ou autre selon l'ancien rôle
                $user->setBanReason(null); // on supprime la raison
        
                if ($user->updateUser($db, $id)) {
                    header("Location: indeex.php?unban_success=1&name=" . urlencode($user->getFullName()) . "&id=" . urlencode($user->getIdUser()));
                    exit;
                } else {
                    echo "Échec de la mise à jour.";
                }
            } else {
                echo "Utilisateur introuvable.";
            }
        }
              

        public function bannirUser($id, $raison) {
            $db = Config::getConnexion();
            $user = User::findById($db, $id);
        
            header('Content-Type: application/json'); // important pour JSON
        
            if ($user) {
                $user->setRole('banni');
                $user->setBanReason($raison);
                $success = $user->updateUser($db, $id);
        
                if ($success) {
                    echo json_encode([
                        'success' => true,
                        'message' => "Utilisateur banni avec succès.",
                        'id' => $id,
                        'raison' => $raison
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => "Erreur lors de la mise à jour."
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Utilisateur non trouvé."
                ]);
            }
        
            exit;
        }
        
        
        
        
        
        
        

        
        public function changerRole($id, $nouveauRole) {
            $db = Config::getConnexion();
            $user = User::findById($db, $id);
        
            if ($user) {
                if ($user->getRole() === $nouveauRole) {
                    // Pas de changement
                    header("Location: indeex.php?role_no_change=1&name=" . urlencode($user->getFullName()) . "&role=" . urlencode($nouveauRole));
                    exit;
                }
        
                $user->setRole($nouveauRole);
        
                // Si on débannit, on vide la raison
                if ($nouveauRole !== 'banni') {
                    $user->setBanReason(null);
                }
        
                if ($user->updateUser($db, $id)) {
                    header("Location: indeex.php?role_update_success=1&name=" . urlencode($user->getFullName()) . "&id=$id&role=" . urlencode($nouveauRole));
                    exit;
                } else {
                    echo "❌ Échec de mise à jour.";
                }
            } else {
                echo "Utilisateur non trouvé.";
            }
        }
        
        



      

// This code block was removed because it was outside of a function and caused a syntax error.

public function getAllUsers() {
    $db = Config::getConnexion(); // récupère la connexion PDO
    return User::getAll($db); // appel de la méthode statique
}

public function deleteUser($id) {
    $db = Config::getConnexion(); // Get the database connection
    User::deleteUser($db, $id); // Pass the database connection as the first argument
    header("Location: index.php?action=listUsers");
    exit();
  }
  
  public static function deleteById($db, $id) {
    // Préparer la requête SQL pour supprimer un utilisateur par ID
    $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
    
    // Exécuter la requête avec l'ID
    return $stmt->execute([$id]);
}



public function index() {
    $db = Config::getConnexion();
    $users = $this->userModel->findAll($db); // méthode findAll à ajouter dans ton modèle si elle n'existe pas

    // Démarrer session si besoin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Inclure la vue et rendre $users dispo
    require_once __DIR__ . '/../View/BackOffice/indeex.php';
}



    public function __construct() {
        $this->userModel = new User();
    }

    public function register($userData) {
        $db = Config::getConnexion();
        
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    
        try {
            // Validation des données avant création de l'utilisateur
            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("L'adresse email n'est pas valide.");
            }
    
            if (strlen($userData['password']) < 8) {
                throw new Exception("Le mot de passe doit contenir au moins 8 caractères.");
            }
    
            $user = new User();
            $user->setFullName($userData['full_name']);
            $user->setEmail($userData['email']);
            $user->setPassword(password_hash($userData['password'], PASSWORD_DEFAULT));
            $user->setNumUser($userData['phone']);
            $user->setRole('user');
    
            if ($user->save($db)) {
                $_SESSION['register_success'] = "Votre compte a été créé avec succès !";
                header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
                exit;
            }
            
            throw new Exception("Erreur lors de l'enregistrement.");
    
        } catch (PDOException $e) {
            // Gestion des erreurs SQL
            if ($e->getCode() == '23000') {
                // Erreur de contrainte d'intégrité (clé dupliquée)
                if (strpos($e->getMessage(), 'email') !== false) {
                    $_SESSION['register_error'] = "Cet email est déjà utilisé.";
                } elseif (strpos($e->getMessage(), 'num_user') !== false) {
                    $_SESSION['register_error'] = "Ce numéro de téléphone est déjà utilisé.";
                } else {
                    $_SESSION['register_error'] = "Une donnée existe déjà en base.";
                }
            } else {
                $_SESSION['register_error'] = "Erreur de base de données : " . $e->getMessage();
            }
            
        } catch (Exception $e) {
            // Gestion des autres exceptions
            $_SESSION['register_error'] = $e->getMessage();
        }
    
        // Redirection en cas d'erreur
        header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
        exit;
    }

    public function login($email, $password) {
        $db = Config::getConnexion();
    
        try {
            $user = User::findByEmail($db, $email);
    
            if (!$user || !password_verify($password, $user->getPassword())) {
                throw new Exception("Email ou mot de passe incorrect");
            }
    
            if ($user->getRole() === 'banni') {
                $raison = $user->getBanReason() ?? 'Aucune raison fournie.';
                throw new Exception("Votre compte a été banni. Raison : $raison");
            }
    
            // Démarrer la session si elle n’est pas déjà démarrée
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
    
            // Sauvegarder les infos utilisateur dans la session
            $_SESSION['user'] = [
                'id_user' => $user->getIdUser(),
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'num_user' => $user->getNumUser(),
                'profile_picture' => $user->getProfilePicture()
            ];
    
            // Redirection selon le rôle
            if ($user->getRole() === 'admin') {
                header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/indeex.php");
            } else {
                header("Location: /Projet%20Web/mvcUtilisateur/View/FrontOffice/index.php");
            }
            exit;
    
        } catch (Exception $e) {
            // Redirection vers la page login avec le message d’erreur
            header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php?error=" . urlencode($e->getMessage()));
            exit;
        }
    }
    
    
    

    public function handleFacebookLogin($facebookUser) {
        $db = Config::getConnexion();
        session_start();

        try {
            // Validation des données Facebook
            if (empty($facebookUser['id'])) {
                throw new Exception("Données Facebook invalides");
            }

            // 1. Recherche par Facebook ID
            $user = User::findByFacebookId($db, $facebookUser['id']);
            
            // 2. Si non trouvé, recherche par email
            if (!$user && !empty($facebookUser['email'])) {
                $user = User::findByEmail($db, $facebookUser['email']);
                
                // Si trouvé par email mais sans Facebook ID, on met à jour
                if ($user && empty($user->getFacebookId())) {
                    $user->setFacebookId($facebookUser['id']);
                    $user->save($db);
                }
            }

            // 3. Création si nécessaire
            if (!$user) {
                $newUser = new User();
                $newUser->setFullName($facebookUser['name'] ?? 'Utilisateur Facebook');
                $newUser->setEmail($facebookUser['email'] ?? '');
                $newUser->setFacebookId($facebookUser['id']);
                $newUser->setRole('user');
                
                if (!$newUser->save($db)) {
                    throw new Exception("Échec de la création du compte");
                }
                $user = $newUser;
            }

            // 4. Connexion
            $_SESSION['user'] = [
                'id_user' => $user->getIdUser(),
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'is_social' => true,
                'profile_picture' => $user->getProfilePicture()
            ];
            
            
            header("Location: /View/FrontOffice/index.php");
            exit;

        } catch (Exception $e) {
            error_log("Facebook Login Error: " . $e->getMessage());
            header("Location: /View/BackOffice/login/login.php?error=facebook_login");
            exit;
        }
    }
}

// Traitement des requêtes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new UserController();

    try {
        if (!isset($_POST['action'])) {
            throw new Exception("Action non spécifiée");
        }

        switch ($_POST['action']) {
            case 'register':
                $required = ['full_name', 'email', 'password', 'phone'];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Le champ $field est requis");
                    }
                }
                $controller->register($_POST);
                break;
                
            case 'login':
                if (empty($_POST['email']) || empty($_POST['password'])) {
                    throw new Exception("Email et mot de passe requis");
                }
                $controller->login($_POST['email'], $_POST['password']);
                break;
                
            default:
                throw new Exception("Action non reconnue");
        }
    } catch (Exception $e) {
        header("Location: /View/BackOffice/login/login.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}