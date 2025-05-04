<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create a log file to track what's happening
$logFile = 'sms_debug.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " Script started\n", FILE_APPEND);

require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';

// Inclure la bibliothèque Twilio
require_once '../vendor/autoload.php';
use Twilio\Rest\Client;

// Log the raw request
$raw_post = file_get_contents('php://input');
file_put_contents($logFile, date('Y-m-d H:i:s') . " Raw POST data: $raw_post\n", FILE_APPEND);

// Identifiants Twilio - EXACTEMENT comme dans test_sms.php
$twilioAccountSid = 'AC5cff2ba64b72d5a1299a7801c943bb17';
$twilioAuthToken = 'f3c914f863775b667fea26369e04b22a';
$twilioPhoneNumber = '+18559544315';

$response = ['success' => false, 'message' => '', 'sms_sent' => false, 'sms_sid' => ''];

// Log request method
file_put_contents($logFile, date('Y-m-d H:i:s') . " Request method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $jsonData = file_get_contents('php://input');
    file_put_contents($logFile, date('Y-m-d H:i:s') . " JSON data: $jsonData\n", FILE_APPEND);
    
    // Parse the JSON data
    $data = json_decode($jsonData, true);
    
    // Log the parsed data
    file_put_contents($logFile, date('Y-m-d H:i:s') . " Parsed data: " . print_r($data, true) . "\n", FILE_APPEND);
    
    $idDemande = isset($data['id_demande']) ? (int)$data['id_demande'] : 0;
    $action = isset($data['action']) ? $data['action'] : '';
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " ID Demande: $idDemande, Action: $action\n", FILE_APPEND);

    if ($idDemande <= 0 || !in_array($action, ['approve', 'reject', 'send_sms'])) {
        $response['message'] = 'Requête invalide. ID Demande: ' . $idDemande . ', Action: ' . $action;
        file_put_contents($logFile, date('Y-m-d H:i:s') . " Invalid request: {$response['message']}\n", FILE_APPEND);
    } else {
        try {
            $controller = new DemandeCovoiturageController();
            
            // Mettre à jour le statut de la demande
            if ($action === 'approve') {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " Processing approval for demande ID: $idDemande\n", FILE_APPEND);
                
                $updateResult = $controller->updateDemandeStatus($idDemande, 'approuvée');
                if (!$updateResult) {
                    $response['message'] = 'Erreur lors de la mise à jour du statut de la demande ID: ' . $idDemande;
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " Update error: {$response['message']}\n", FILE_APPEND);
                } else {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " Status updated successfully\n", FILE_APPEND);
                    
                    // Success response - but don't send SMS yet
                    $response['success'] = true;
                    $response['message'] = 'Demande approuvée avec succès! Cliquez sur "Envoyer SMS" pour notifier le passager.';
                }
            } else if ($action === 'send_sms') {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " Processing SMS sending for demande ID: $idDemande\n", FILE_APPEND);
                
                // Get the demande details to get the phone number
                $demande = $controller->getDemandeById($idDemande);
                
                if (!$demande) {
                    $response['message'] = 'Demande non trouvée avec ID: ' . $idDemande;
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " Demande not found: {$response['message']}\n", FILE_APPEND);
                } else {
                    // SEND SMS - EXACTEMENT comme dans test_sms.php
                    try {
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " Creating Twilio client\n", FILE_APPEND);
                        
                        // Créer le client Twilio exactement comme dans test_sms.php
                        $twilio = new Client($twilioAccountSid, $twilioAuthToken);
                        
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " Twilio client created successfully\n", FILE_APPEND);
                        
                        // Utiliser le numéro de téléphone du passager si disponible, sinon utiliser le numéro de test
                        $userPhoneNumber = '+21655436637'; // Numéro de test qui fonctionne
                        
                        // Si vous voulez utiliser le numéro du passager de la base de données:
                        // $userPhoneNumber = '+' . $demande->tel_passager;
                        
                        // Message - exactement comme dans test_sms.php
                        $messageBody = "ClickNgo: Your ride request has been APPROVED. Thank you for using our service!";
                        
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " Sending SMS to $userPhoneNumber with message: $messageBody\n", FILE_APPEND);
                        
                        // Envoyer le SMS - EXACTEMENT comme dans test_sms.php
                        $message = $twilio->messages->create(
                            $userPhoneNumber,
                            [
                                'from' => $twilioPhoneNumber,
                                'body' => $messageBody
                            ]
                        );
                        
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " SMS sent successfully! SID: {$message->sid}\n", FILE_APPEND);
                        
                        // Success response
                        $response['success'] = true;
                        $response['sms_sent'] = true;
                        $response['sms_sid'] = $message->sid;
                        $response['message'] = 'SMS envoyé avec succès à ' . $userPhoneNumber . '!';
                    } catch (Exception $e) {
                        file_put_contents($logFile, date('Y-m-d H:i:s') . " ERROR sending SMS: " . $e->getMessage() . "\n", FILE_APPEND);
                        
                        $response['success'] = false;
                        $response['sms_sent'] = false;
                        $response['message'] = 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage();
                    }
                }
            } else if ($action === 'reject') {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " Processing rejection for demande ID: $idDemande\n", FILE_APPEND);
                
                $updateResult = $controller->updateDemandeStatus($idDemande, 'rejetée');
                if (!$updateResult) {
                    $response['message'] = 'Erreur lors de la mise à jour du statut de la demande ID: ' . $idDemande;
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " Update error: {$response['message']}\n", FILE_APPEND);
                } else {
                    $response['success'] = true;
                    $response['message'] = 'Demande rejetée avec succès pour ID: ' . $idDemande;
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " Rejection successful\n", FILE_APPEND);
                }
            }
        } catch (Exception $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " GENERAL ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            $response['message'] = 'Erreur générale : ' . $e->getMessage();
        }
    }
}

// Ensure proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log the response
file_put_contents($logFile, date('Y-m-d H:i:s') . " Sending response: " . json_encode($response) . "\n\n", FILE_APPEND);

echo json_encode($response);
?>