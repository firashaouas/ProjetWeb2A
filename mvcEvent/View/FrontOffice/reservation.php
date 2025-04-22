<?php
// Start the session (for future compatibility)
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON string from seats input
    $selectedSeatsJson = $_POST['seats'] ?? '';
    $selectedSeats = json_decode($selectedSeatsJson, true) ?? [];
    
    $errorMessages = [];
    $successCount = 0;
    
    // Check if selectedSeats is an array and not empty
    if (!is_array($selectedSeats) || empty($selectedSeats)) {
        $errorMessages[] = "Erreur : Aucune chaise sélectionnée.";
    } else {
        foreach ($selectedSeats as $seatId) {
            try {
                // Passer null pour userId car pas d'authentification requise
                $chaiseController->reserverChaise($seatId, null);
                $successCount++;
            } catch (Exception $e) {
                $errorMessages[] = "Erreur lors de la réservation de la chaise $seatId : " . $e->getMessage();
            }
        }
    }
    
    if (empty($errorMessages) && $successCount > 0) {
        $successMessage = "Réservation confirmée pour $successCount chaise(s) !";
        // Recharger les chaises pour mettre à jour l'affichage
        $chaises = $chaiseController->getChaisesByEvent($eventId);
        // Recharger l'événement pour mettre à jour reservedSeats
        $event = $eventController->getEventById($eventId);
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
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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

            <form method="POST" action="reservation.php?event_id=<?= $eventId ?>">
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
                        <i class="fas fa-check-circle"></i> Réserver
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                        confirmBtn.innerHTML = `<i class="fas fa-check-circle"></i> Confirmer (${selectedSeats.length})`;
                    } else {
                        confirmBtn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmer la réservation';
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
        });
    </script>
</body>
</html>