<?php
session_start();
require_once '../../config.php';

// Handle Stripe payment success
if (isset($_GET['session_id'])) {
    try {
        $stripe = Config::getStripe();
        $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);

        if ($session->payment_status === 'paid') {
            $_SESSION['commande_success'] = true;
            // Store order details for receipt
            $_SESSION['derniere_commande'] = [
                'client' => [
                    'nom' => $_SESSION['order_details']['nom'] ?? 'N/A',
                    'prenom' => $_SESSION['order_details']['prenom'] ?? 'N/A',
                    'telephone' => $_SESSION['order_details']['telephone'] ?? 'N/A',
                ],
                'produits' => $_SESSION['order_details']['panier'] ?? [],
                'total' => $session->amount_total / 1000, // Convert millimes to TND
                'paiement' => 'Carte'
            ];
        } else {
            header("Location: panier.php?error=payment_failed");
            exit;
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        error_log('Stripe API Error: ' . $e->getMessage());
        header("Location: panier.php?error=invalid_session");
        exit;
    }
}

// Check for non-Stripe payment success (set by commandeController.php)
if (!isset($_SESSION['commande_success'])) {
    header("Location: produit.php");
    exit;
}

// Create a session for the receipt
$_SESSION['allow_recu'] = true;

// Clear the cart and session data
unset($_SESSION['commande_success']);
unset($_SESSION['order_details']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de Commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 50px;
            text-align: center;
            background-color: #f2f2f2;
        }
        .message {
            padding: 30px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #28a745;
        }
        a {
            display: inline-block;
            margin: 10px;
            text-decoration: none;
            color: #007bff;
        }
        .btn-recu {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn-recu:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 143, 163, 0.3);
        }
        #recu-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            position: relative;
        }
        .close-btn {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .recu-details {
            text-align: left;
            margin-top: 20px;
        }
        .recu-details h3 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .recu-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="message">
        <h2>✅ Commande enregistrée avec succès !</h2>
        <p>Votre achat a été traité avec succès. Merci pour votre confiance.</p>
        <a href="recu.php" class="btn-recu">Reçu</a>
        <br>
        <a href="produit.php">Retour à la boutique</a>
    </div>

    <!-- Modal pour le reçu -->
    <div id="recu-modal">
        <div class="modal-content">
            <span class="close-btn" onclick="fermerRecu()">×</span>
            <h2>Reçu de commande</h2>
            <div class="recu-details">
                <h3>Détails de la commande</h3>
                <div id="recu-contenu"></div>
            </div>
        </div>
    </div>

    <script>
        // Clear the cart from localStorage
        localStorage.removeItem('panier');
        const commandeDetails = JSON.parse(localStorage.getItem('derniere_commande') || '{}');

        // Update cart count if function exists
        if (typeof updateCartCount === 'function') {
            updateCartCount(0);
        }

        function afficherRecu() {
            const modal = document.getElementById('recu-modal');
            const contenu = document.getElementById('recu-contenu');
            
            if (commandeDetails && Object.keys(commandeDetails).length > 0) {
                let html = `
                    <div class="recu-row">
                        <span>Date:</span>
                        <span>${new Date().toLocaleDateString()}</span>
                    </div>
                    <div class="recu-row">
                        <span>Numéro de commande:</span>
                        <span>#${Math.random().toString(36).substr(2, 9).toUpperCase()}</span>
                    </div>
                    <div class="recu-row">
                        <span>Mode de paiement:</span>
                        <span>${commandeDetails.paiement || 'N/A'}</span>
                    </div>
                `;

                if (commandeDetails.client) {
                    html += `
                        <div class="recu-row">
                            <span>Nom:</span>
                            <span>${commandeDetails.client.nom || ''}</span>
                        </div>
                        <div class="recu-row">
                            <span>Prénom:</span>
                            <span>${commandeDetails.client.prenom || ''}</span>
                        </div>
                        <div class="recu-row">
                            <span>Téléphone:</span>
                            <span>${commandeDetails.client.telephone || ''}</span>
                        </div>
                    `;
                }

                if (commandeDetails.produits && commandeDetails.produits.length > 0) {
                    html += '<h3>Produits</h3>';
                    commandeDetails.produits.forEach(produit => {
                        html += `
                            <div class="recu-row">
                                <span>${produit.nom} x${produit.quantite}</span>
                                <span>${(produit.prix * produit.quantite).toFixed(2)} TND</span>
                            </div>
                        `;
                    });
                }

                if (commandeDetails.total) {
                    html += `
                        <div class="recu-row" style="font-weight: bold; margin-top: 20px;">
                            <span>Total:</span>
                            <span>${commandeDetails.total.toFixed(2)} TND</span>
                        </div>
                    `;
                }

                contenu.innerHTML = html;
            } else {
                contenu.innerHTML = '<p>Aucun détail de commande disponible.</p>';
            }

            modal.style.display = 'block';
        }

        function fermerRecu() {
            document.getElementById('recu-modal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('recu-modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Store commandeDetails in localStorage for receipt
        localStorage.setItem('derniere_commande', JSON.stringify(<?php echo json_encode($_SESSION['derniere_commande'] ?? []); ?>));
    </script>
</body>
</html>