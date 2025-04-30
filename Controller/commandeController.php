<?php


require_once(__DIR__ . "/../model/CommandModel.php");
require_once(__DIR__ . "../../config.php");
class CommandController {
    private $db;
    private $modelClass = 'CommandModel';

    public function __construct(PDO $db = null) {
        if ($db === null) {
            $this->db = Config::getConnexion();
        } else {
            $this->db = $db;
        }
    }

    public function create() {
        include 'views/command/create.php';
    }

  
    

 

    public function show($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo "ID de commande invalide.";
            return;
        }
        $commande = CommandModel::getById($id, $this->db);
        if ($commande) {
            include 'views/command/show.php';
        } else {
            echo "Commande non trouvée.";
        }
    }

    public function edit($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo "ID de commande invalide.";
            return;
        }
        $commande = CommandModel::getById($id, $this->db);
        if ($commande) {
            include 'views/command/edit.php';
        } else {
            echo "Commande non trouvée.";
        }
    }

    public function update($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo "Requête invalide.";
            return;
        }

        try {
            $id_user = filter_input(INPUT_POST, 'id_user', FILTER_SANITIZE_NUMBER_INT);
            $id_produit = filter_input(INPUT_POST, 'id_produit', FILTER_SANITIZE_NUMBER_INT);
            $quantite = filter_input(INPUT_POST, 'quantite', FILTER_SANITIZE_NUMBER_INT);
            $statut_commande = filter_input(INPUT_POST, 'statut_commande', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$id_user || !$id_produit || !$quantite ) {
                throw new InvalidArgumentException("Tous les champs sont requis et doivent être valides.");
            }

            $commande = new CommandModel($id_user, $id_produit, $quantite, null, $id); // Inclure l'ID pour la mise à jour

            if (CommandModel::mettreAJour($commande, $this->db)) {
                header('Location: index.php?action=show&id=' . $id . '&success=commande_mise_a_jour');
                exit();
            } else {
                echo "Erreur lors de la mise à jour de la commande.";
            }

        } catch (InvalidArgumentException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function delete($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo "ID de commande invalide.";
            return;
        }

        if (CommandModel::supprimer($id, $this->db)) {
            header('Location: index.php?action=list&success=commande_supprimee');
            exit();
        } else {
            echo "Erreur lors de la suppression de la commande.";
        }
    }

 public function ajouterCommande() {
    // Démarre la session si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Si la méthode est POST et que le panier est disponible
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sauvegarder les informations du formulaire
        $_SESSION['form_data'] = [
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telephone' => $_POST['telephone'] ?? '',
            'adresse' => $_POST['adresse'] ?? ''
        ];

        if (isset($_POST['panier_data'])) {
            $panierData = json_decode($_POST['panier_data'], true);

            if (!empty($panierData)) {
                $idUtilisateur = 1; // Adapté à ton système d'authentification
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                try {
                    // Si la commande a été créée avec succès
                    if (CommandModel::creerCommandeDepuisTableau($idUtilisateur, $panierData, $this->db)) {
                        // Sauvegarder le panier dans la session
                        $_SESSION['panier'] = $panierData;
                        
                        // Marque le succès de la commande
                        $_SESSION['commande_success'] = true;
                        
                        // Redirige vers la page de succès de commande
                        header('Location: ../view/front%20office/commande_success.php');
                        exit();
                    } else {
                        header('Location: ../../view/front office/panier.php?error=erreur_commande');
                        exit();
                    }
                } catch (Exception $e) {
                    header('Location: ../../view/front office/panier.php?error=' . urlencode($e->getMessage()));
                    error_log("Erreur lors de la création de la commande : " . $e->getMessage());
                }
            } else {
                header('Location: ../../view/front office/panier.php?error=panier_vide');
                exit();
            }
        } else {
            header('Location: ../../view/front office/panier.php');
            exit();
        }
    }
}
          
}

// Exemple d'utilisation dans votre routeur (index.php ou autre)
if (isset($_GET['action']) || isset($_POST['action'])) {
    require_once(__DIR__ . "../../config.php"); // Assurez-vous que le chemin est correct
    $db = Config::getConnexion();
    $controller = new CommandController($db);
    $action = $_GET['action'] ?? $_POST['action'];
    $id = $_GET['id'] ?? $_POST['id'] ?? null;

    switch ($action) {
        case 'create':
            $controller->create();
            break;
       
      
        case 'show':
            $controller->show($id);
            break;
        case 'edit':
            $controller->edit($id);
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'delete':
            $controller->delete($id);
            break;
        case 'ajouter_commande': // L'action appelée depuis votre formulaire panier
            $controller->ajouterCommande();
            break;
        default:
            echo "Action non reconnue.";
            break;
    }
} else {
    $db = Config::getConnexion();
    $controller = new CommandController($db);
    
}

?>