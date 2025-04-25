<?php
session_start();

require_once '../../Controller/EventController.php';
require_once '../../Controller/ChaiseController.php';

$eventId = $_GET['event_id'] ?? null;
if (!$eventId) {
    header("Location: evenemant.php");
    exit;
}

$eventController = new EventController();
$chaiseController = new ChaiseController();

$event = $eventController->getEventById($eventId);
if (!$event) {
    header("Location: evenemant.php");
    exit;
}

$event['totalSeats'] = $event['totalSeats'] ?? 0;
$event['reservedSeats'] = $event['reservedSeats'] ?? 0;
$event['imageUrl'] = $event['imageUrl'] ?? 'images/default-event.jpg';

$chaises = $chaiseController->getChaisesByEvent($eventId);
$successMessage = '';

// Nouveau code pour le paiement
$paymentStep = $_POST['payment_step'] ?? 1;
$selectedSeats = [];
$totalPrice = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($paymentStep == 1) {
        // Étape 1: Sélection des sièges
        $selectedSeatsJson = $_POST['seats'] ?? '';
        $selectedSeats = json_decode($selectedSeatsJson, true) ?? [];
        
        if (!empty($selectedSeats)) {
            $_SESSION['selected_seats'] = $selectedSeats;
            $paymentStep = 2;
            $totalPrice = count($selectedSeats) * $event['price'];
        } else {
            $errorMessages[] = "Erreur : Aucune chaise sélectionnée.";
        }
    } elseif ($paymentStep == 2 && isset($_SESSION['selected_seats'])) {
        // Étape 2: Paiement
        $selectedSeats = $_SESSION['selected_seats'];
        $cardNumber = $_POST['card_number'] ?? '';
        $cardExpiry = $_POST['card_expiry'] ?? '';
        $cardCvv = $_POST['card_cvv'] ?? '';
        
        // Validation du formulaire de paiement
        $errorMessages = [];
        
        if (!preg_match('/^\d{16}$/', $cardNumber)) {
            $errorMessages[] = "Numéro de carte invalide (16 chiffres requis)";
        }
        
        if (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $cardExpiry)) {
            $errorMessages[] = "Date d'expiration invalide (MM/AA requis)";
        }
        
        if (!preg_match('/^\d{3,4}$/', $cardCvv)) {
            $errorMessages[] = "CVV invalide (3 ou 4 chiffres requis)";
        }
        
        if (empty($errorMessages)) {
            // Traitement de la réservation
            $successCount = 0;
            foreach ($selectedSeats as $seatId) {
                try {
                    $chaiseController->reserverChaise($seatId, null);
                    $successCount++;
                } catch (Exception $e) {
                    $errorMessages[] = "Erreur lors de la réservation de la chaise $seatId : " . $e->getMessage();
                }
            }
            
            if (empty($errorMessages)) {
                $successMessage = "Paiement confirmé et réservation effectuée pour $successCount chaise(s) !";
                $chaises = $chaiseController->getChaisesByEvent($eventId);
                $event = $eventController->getEventById($eventId);
                $paymentStep = 3; // Étape de confirmation finale
                unset($_SESSION['selected_seats']);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - <?= htmlspecialchars($event['name'] ?? 'Événement') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="reservation.css">
    <style>
        .success-message, .error-message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }
        .payment-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
            outline: none;
        }
        .error-field {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        .loading-spinner {
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 5px solid var(--primary);
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .payment-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .summary-total {
            font-weight: 600;
            font-size: 1.2rem;
            border-top: 1px solid #ddd;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <section class="reservation-section">
        <div class="reservation-container">
            <h1 class="magic-text">
                <i class="fas fa-ticket-alt"></i> Réservez pour <?= htmlspecialchars($event['name'] ?? 'Événement') ?>
            </h1>
            
            <?php if ($successMessage): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
                    <p><a href="evenemant.php">Retour aux événements</a></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessages)): ?>
                <div class="error-message">
                    <?php foreach ($errorMessages as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="event-details">
                <div class="event-image">
                    <img src="<?= htmlspecialchars($event['imageUrl']) ?>" 
                         alt="<?= htmlspecialchars($event['name'] ?? 'Événement') ?>">
                </div>
                <div class="event-info">
                    <h2><?= htmlspecialchars($event['name'] ?? 'Événement') ?></h2>
                    <p><i class="fas fa-calendar-alt"></i> <strong>Date :</strong> <?= !empty($event['date']) ? date('d/m/Y', strtotime($event['date'])) : 'Non spécifiée' ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Lieu :</strong> <?= htmlspecialchars($event['location'] ?? 'Non spécifié') ?></p>
                    <p><i class="fas fa-money-bill-wave"></i> <strong>Prix :</strong> <?= htmlspecialchars($event['price'] ?? '0') ?> DT</p>
                    <p><i class="fas fa-chair"></i> <strong>Places disponibles :</strong> <?= ($event['totalSeats'] - $event['reservedSeats']) ?></p>
                </div>
            </div>

            <?php if ($paymentStep == 1): ?>
                <form method="POST" action="reservation.php?event_id=<?= $eventId ?>">
                    <input type="hidden" name="payment_step" value="1">
                    <div class="seat-selection">
                        <h2><i class="fas fa-chair"></i> Sélectionnez vos sièges</h2>
                        <div class="seat-grid-container">
                            <div class="stage">
                                <div class="stage-lights">
                                    <div class="light"></div>
                                    <div class="light"></div>
                                    <div class="light"></div>
                                </div>
                                <div class="stage-label">Scène</div>
                            </div>
                            <div class="seat-grid" id="seat-grid">
                                <?php if (!empty($chaises)): ?>
                                    <?php foreach ($chaises as $chaise): ?>
                                        <div class="seat <?= $chaise['statut'] === 'libre' ? 'available' : 'reserved' ?>"
                                             data-id="<?= $chaise['id'] ?>"
                                             data-status="<?= $chaise['statut'] ?>">
                                            <i class="fas fa-chair"></i>
                                            <span><?= $chaise['numero'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="no-seats">Aucun siège disponible pour cet événement.</p>
                                <?php endif; ?>
                            </div>
                            <div class="seat-legend">
                                <div class="legend-item">
                                    <span class="seat available"><i class="fas fa-chair"></i></span> Disponible
                                </div>
                                <div class="legend-item">
                                    <span class="seat reserved"><i class="fas fa-chair"></i></span> Réservé
                                </div>
                                <div class="legend-item">
                                    <span class="seat selected"><i class="fas fa-chair"></i></span> Sélectionné
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="seats" id="selected-seats">
                        <button type="submit" class="confirm-btn" id="confirm-btn" <?= empty($chaises) ? 'disabled' : '' ?>>
                            <i class="fas fa-arrow-right"></i> Passer au paiement
                        </button>
                    </div>
                </form>
            <?php elseif ($paymentStep == 2): ?>
                <form method="POST" action="reservation.php?event_id=<?= $eventId ?>" id="payment-form">
                    <input type="hidden" name="payment_step" value="2">
                    <div class="payment-form">
                        <h2><i class="fas fa-credit-card"></i> Informations de paiement</h2>
                        
                        <div class="payment-summary">
                            <h3>Récapitulatif de votre réservation</h3>
                            <div class="summary-item">
                                <span>Nombre de sièges:</span>
                                <span><?= count($selectedSeats) ?></span>
                            </div>
                            <div class="summary-item">
                                <span>Prix unitaire:</span>
                                <span><?= $event['price'] ?> DT</span>
                            </div>
                            <div class="summary-item summary-total">
                                <span>Total à payer:</span>
                                <span><?= count($selectedSeats) * $event['price'] ?> DT</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card_number">Numéro de carte</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="16">
                            <div class="error-field" id="card_number_error">Veuillez entrer un numéro de carte valide (16 chiffres)</div>
                        </div>
                        
                        <div class="form-row" style="display: flex; gap: 1rem;">
                            <div class="form-group" style="flex: 1;">
                                <label for="card_expiry">Date d'expiration (MM/AA)</label>
                                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/AA" maxlength="5">
                                <div class="error-field" id="card_expiry_error">Format invalide (MM/AA requis)</div>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="card_cvv">CVV</label>
                                <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4">
                                <div class="error-field" id="card_cvv_error">CVV invalide (3 ou 4 chiffres)</div>
                            </div>
                        </div>
                        
                        <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
    <a href="reservation.php?event_id=<?= $eventId ?>" class="cancel-btn" style="flex: 1;">
        <i class="fas fa-arrow-left"></i> Retour à la sélection
    </a>
    <button type="submit" class="confirm-btn" style="flex: 1;">
        <i class="fas fa-lock"></i> Payer et confirmer
    </button>
</div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la sélection des sièges
    if (document.getElementById('seat-grid')) {
        const seats = document.querySelectorAll('.seat.available');
        const selectedSeatsInput = document.getElementById('selected-seats');
        const confirmBtn = document.getElementById('confirm-btn');
        let selectedSeats = [];
        
        seats.forEach(seat => {
            seat.addEventListener('click', function() {
                const seatId = this.getAttribute('data-id');
                const index = selectedSeats.indexOf(seatId);
                
                if (index === -1) {
                    selectedSeats.push(seatId);
                    this.classList.add('selected');
                } else {
                    selectedSeats.splice(index, 1);
                    this.classList.remove('selected');
                }
                
                selectedSeatsInput.value = JSON.stringify(selectedSeats);
                confirmBtn.disabled = selectedSeats.length === 0;
                
                if (selectedSeats.length > 0) {
                    confirmBtn.innerHTML = `<i class="fas fa-arrow-right"></i> Passer au paiement (${selectedSeats.length})`;
                } else {
                    confirmBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Passer au paiement';
                }
            });
        });

        setInterval(() => {
            const availableSeats = document.querySelectorAll('.seat.available:not(.selected)');
            if (availableSeats.length > 0) {
                const randomSeat = availableSeats[Math.floor(Math.random() * availableSeats.length)];
                randomSeat.style.transform = 'translateY(-3px)';
                setTimeout(() => {
                    randomSeat.style.transform = '';
                }, 500);
            }
        }, 1000);
    }

    // Validation du formulaire de paiement
    if (document.getElementById('payment-form')) {
        const form = document.getElementById('payment-form');
        const loadingOverlay = document.getElementById('loading-overlay');

        // Fonctions de validation avec la même structure que validateName
        function validateCardNumber() {
            let cardNumber = document.getElementById('card_number').value.trim();
            let cardNumberError = document.getElementById('card_number_error');
            
            if (!/^\d{16}$/.test(cardNumber)) {
                cardNumberError.textContent = "Numéro de carte invalide (16 chiffres requis)";
                cardNumberError.classList.add("error");
                cardNumberError.classList.remove("valid");
                document.getElementById('card_number').style.borderColor = '#ff4757';
                return false;
            } else {
                cardNumberError.textContent = "✔ Valide";
                cardNumberError.classList.add("valid");
                cardNumberError.classList.remove("error");
                document.getElementById('card_number').style.borderColor = '#2ed573';
                return true;
            }
        }

        function validateCardExpiry() {
            let cardExpiry = document.getElementById('card_expiry').value.trim();
            let cardExpiryError = document.getElementById('card_expiry_error');
            
            if (!/^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(cardExpiry)) {
                cardExpiryError.textContent = "Format invalide (MM/AA requis)";
                cardExpiryError.classList.add("error");
                cardExpiryError.classList.remove("valid");
                document.getElementById('card_expiry').style.borderColor = '#ff4757';
                return false;
            } else {
                cardExpiryError.textContent = "✔ Valide";
                cardExpiryError.classList.add("valid");
                cardExpiryError.classList.remove("error");
                document.getElementById('card_expiry').style.borderColor = '#2ed573';
                return true;
            }
        }

        function validateCardCvv() {
            let cardCvv = document.getElementById('card_cvv').value.trim();
            let cardCvvError = document.getElementById('card_cvv_error');
            
            if (!/^\d{3,4}$/.test(cardCvv)) {
                cardCvvError.textContent = "CVV invalide (3 ou 4 chiffres requis)";
                cardCvvError.classList.add("error");
                cardCvvError.classList.remove("valid");
                document.getElementById('card_cvv').style.borderColor = '#ff4757';
                return false;
            } else {
                cardCvvError.textContent = "✔ Valide";
                cardCvvError.classList.add("valid");
                cardCvvError.classList.remove("error");
                document.getElementById('card_cvv').style.borderColor = '#2ed573';
                return true;
            }
        }

        // Écouteurs pour validation en temps réel
        document.getElementById('card_number').addEventListener('input', validateCardNumber);
        document.getElementById('card_expiry').addEventListener('input', validateCardExpiry);
        document.getElementById('card_cvv').addEventListener('input', validateCardCvv);

        // Validation à la soumission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Réinitialiser les messages
            document.querySelectorAll('.error-field').forEach(el => {
                el.textContent = '';
                el.classList.remove('valid', 'error');
            });

            // Valider tous les champs
            if (!validateCardNumber()) isValid = false;
            if (!validateCardExpiry()) isValid = false;
            if (!validateCardCvv()) isValid = false;

            // Si tout est valide, afficher l'overlay et soumettre
            if (isValid) {
                loadingOverlay.style.display = 'flex';
                this.submit();
            }
        });

        // Formatage automatique des champs
        document.getElementById('card_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            validateCardNumber();
        });

        document.getElementById('card_expiry').addEventListener('input', function(e) {
            this.value = this.value.replace(/^(\d{2})(\d)/, '$1/$2')
                                 .replace(/[^\d\/]/g, '')
                                 .substring(0, 5);
            validateCardExpiry();
        });

        document.getElementById('card_cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            validateCardCvv();
        });
    }
    const cancelBtn = document.querySelector('.cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            // Optionnel: Confirmation avant de retourner
            if (!confirm('Voulez-vous vraiment annuler le paiement et retourner à la sélection des sièges ?')) {
                e.preventDefault();
            }
        });
    }
});
</script>
</body>
</html>