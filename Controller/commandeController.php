<?php
require_once(__DIR__ . "/../model/CommandModel.php");
require_once '../config.php';

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

            if (!$id_user || !$id_produit || !$quantite) {
                throw new InvalidArgumentException("Tous les champs sont requis et doivent être valides.");
            }

            $commande = new CommandModel($id_user, $id_produit, $quantite, null, $id);

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_commande') {
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
            $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS);
            $quantite = filter_input(INPUT_POST, 'quantite', FILTER_SANITIZE_NUMBER_INT);
            $prix_total = filter_input(INPUT_POST, 'prix_total_value', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $paiement = filter_input(INPUT_POST, 'paiement', FILTER_SANITIZE_SPECIAL_CHARS);
            $panier_data = json_decode($_POST['panier_data'] ?? '[]', true);

            if (!$nom || !$prenom || !$email || !$telephone || !$quantite || !$prix_total || !$paiement || empty($panier_data)) {
                header('Location: ../../view/front office/panier.php?error=donnees_invalides');
                exit;
            }

            // Store order details in session for commande_success.php
            $_SESSION['order_details'] = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone,
                'quantite' => (int) $quantite,
                'prix_total' => (float) $prix_total,
                'paiement' => $paiement,
                'panier' => $panier_data
            ];

            // Save order to database
            try {
                $idUtilisateur = 1; // Adapté à votre système d'authentification
                if (CommandModel::creerCommandeDepuisTableau($idUtilisateur, $panier_data, $this->db)) {
                    // Store details for receipt
                    $_SESSION['derniere_commande'] = [
                        'client' => [
                            'nom' => $nom,
                            'prenom' => $prenom,
                            'email' => $email,
                            'telephone' => $telephone,
                        ],
                        'produits' => $panier_data,
                        'total' => $prix_total,
                        'paiement' => $paiement
                    ];

                    // Send confirmation email
                    try {
                        $mail = Config::getMailer();
                        $mail->addAddress($email, "$nom $prenom");
                        $mail->isHTML(true);
                        $mail->Subject = 'Confirmation de votre commande';

                        $itemsList = '';
                        foreach ($panier_data as $item) {
                            $itemsList .= "<li>{$item['nom']} x{$item['quantite']} - " . ($item['prix'] * $item['quantite']) . " TND</li>";
                        }

                        $mail->Body = "
                            <h2>Merci pour votre commande !</h2>
                            <p>Voici les détails de votre commande :</p>
                            <ul>
                                <li><strong>Nom :</strong> $nom</li>
                                <li><strong>Prénom :</strong> $prenom</li>
                                <li><strong>Email :</strong> $email</li>
                                <li><strong>Téléphone :</strong> $telephone</li>
                                <li><strong>Quantité totale :</strong> $quantite</li>
                                <li><strong>Prix total :</strong> $prix_total TND</li>
                                <li><strong>Mode de paiement :</strong> $paiement</li>
                            </ul>
                            <h3>Articles commandés :</h3>
                            <ul>$itemsList</ul>
                            <p>Nous vous contacterons bientôt pour la livraison.</p>
                        ";
                        $mail->AltBody = strip_tags($mail->Body);
                        
                        if (!$mail->send()) {
                            error_log("Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo);
                            // Ne pas arrêter le processus si l'email échoue
                        } else {
                            error_log("Email envoyé avec succès à $email");
                        }
                    } catch (Exception $e) {
                        error_log("Erreur lors de l'envoi de l'email : " . $e->getMessage());
                        // Ne pas arrêter le processus si l'email échoue
                    }

                    // Mark command success
                    $_SESSION['commande_success'] = true;

                    // Redirect to commande_success.php
                    header('Location: /projet%20web/view/front%20office/commande_success.php');
                    exit;
                } else {
                    header('Location: ../../view/front office/panier.php?error=erreur_commande');
                    exit;
                }
            } catch (Exception $e) {
                error_log("Erreur lors de la création de la commande : " . $e->getMessage());
                header('Location: ../../view/front office/panier.php?error=' . urlencode($e->getMessage()));
                exit;
            }
        } else {
            header('Location: ../../view/front office/panier.php');
            exit;
        }
    }
}

// Routeur
if (isset($_GET['action']) || isset($_POST['action'])) {
    require_once '../config.php';
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
        case 'ajouter_commande':
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