<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__.'/Controller/EventController.php';
require_once __DIR__.'/Controller/ChaiseController.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
$action = $_GET['action'] ?? '';

$eventController = new EventController();
$chaiseController = new ChaiseController();

try {
    switch ($action) {
        case 'get_all':
            $reservations = $eventController->getAllReservations();
            echo json_encode([
                'success' => true,
                'reservations' => $reservations,
                'count' => count($reservations)
            ]);
            break;

        case 'get_event':
            $eventId = $_GET['event_id'] ?? null;
            if (!$eventId) throw new Exception('ID événement manquant');
            
            $event = $eventController->getEventById($eventId);
            echo json_encode(['success' => true, 'event' => $event]);
            break;

        case 'get_seats':
            $eventId = $_GET['event_id'] ?? null;
            if (!$eventId) throw new Exception('ID événement manquant');
            
            $chaises = $chaiseController->getChaisesByEvent($eventId);
            $seats = array_map(function($chaise) {
                return [
                    'number' => $chaise['numero'],
                    'status' => $chaise['statut']
                ];
            }, $chaises);
            
            echo json_encode(['success' => true, 'seats' => $seats]);
            break;

        case 'get_reservation':
            $eventId = $_GET['event_id'] ?? null;
            if (!$eventId) throw new Exception('ID événement manquant');
            
            $reservation = $eventController->getAllReservations();
            $currentReservation = array_filter($reservation, function($res) use ($eventId) {
                return $res['id'] == $eventId;
            });
            $currentReservation = array_values($currentReservation)[0] ?? null;
            echo json_encode(['success' => true, 'reservation' => $currentReservation]);
            break;

        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $eventId = $data['event_id'] ?? null;
            $seatNumbers = $data['seat_numbers'] ?? [];
            
            if (!$eventId || empty($seatNumbers)) {
                throw new Exception('Données manquantes');
            }
            
            $success = $chaiseController->updateMultipleReservations($eventId, $seatNumbers);
            echo json_encode(['success' => $success]);
            break;

        case 'cancel':
            $data = json_decode(file_get_contents('php://input'), true);
            $eventId = $data['event_id'] ?? null;
            
            if (!$eventId) {
                throw new Exception('ID événement manquant');
            }
            
            $success = $chaiseController->cancelReservation($eventId);
            echo json_encode(['success' => $success]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
}
?>