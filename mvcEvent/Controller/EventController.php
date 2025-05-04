<?php 
require_once 'C:/xampp/htdocs/projetWeb/mvcEvent/Config.php';
require 'C:/xampp/htdocs/projetWeb/mvcEvent/Model/Event.php';
require_once 'ChaiseController.php';

class EventController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function checkForUpdates($lastKnownHash) {
        $sql = "SELECT 
                COUNT(*) as event_count, 
                CASE 
                    WHEN COUNT(*) = 0 THEN MD5('EMPTY_TABLE')
                    ELSE MD5(GROUP_CONCAT(
                        CONCAT_WS('|', id, name, description, price, updated_at) 
                        ORDER BY updated_at DESC
                        SEPARATOR '||'
                    ))
                END as current_hash
                FROM evenements";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
    
            // Debug logging
            error_log("LastHash: $lastKnownHash | CurrentHash: ".$result['current_hash']);
    
            return [
                'has_changes' => ($result['current_hash'] !== $lastKnownHash),
                'new_hash' => $result['current_hash'],
                'event_count' => $result['event_count']
            ];
        } catch (Exception $e) {
            error_log('Error in checkForUpdates: '.$e->getMessage());
            return ['has_changes' => false, 'new_hash' => $lastKnownHash];
        }
    }
    public function getEvents() {
        $sql = "SELECT id, category, name, description, price, duration, date, longitude, latitude, place_name,
       image_url AS imageUrl, total_seats AS totalSeats, reserved_seats AS reservedSeats,
       UNIX_TIMESTAMP(created_at) AS created_at, UNIX_TIMESTAMP(updated_at) AS updated_at
FROM evenements";
    
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEvents: ' . $e->getMessage());
        }
    }

    public function addEvent($event) {
        error_log('addEvent called with data: ' . json_encode([
            'category' => $event->getCategory(),
            'name' => $event->getName(),
            'description' => $event->getDescription(),
            'price' => $event->getPrice(),
            'duration' => $event->getDuration(),
            'date' => $event->getDate(),
            'longitude' => $event->getLongitude(),
            'latitude' => $event->getLatitude(),
            'place_name' => $event->getPlaceName(),
            'image_url' => $event->getImageUrl(),
            'total_seats' => $event->getTotalSeats(),
            'reserved_seats' => $event->getReservedSeats()
        ]));
    
        $longitude = $event->getLongitude();
        $latitude = $event->getLatitude();
        $placeName = $event->getPlaceName();
    
        if (empty($longitude) || empty($latitude) || !is_numeric($longitude) || !is_numeric($latitude)) {
            error_log('Validation failed: Invalid GPS coordinates');
            throw new Exception('Les coordonnées GPS doivent être des nombres valides.');
        }
    
        if (empty($placeName) || strlen($placeName) < 3) {
            error_log('Validation failed: Invalid place name');
            throw new Exception('Le nom du lieu est requis et doit contenir au moins 3 caractères.');
        }
    
        try {
            $this->db->beginTransaction();
            $sql = "INSERT INTO evenements (
                category, name, description, price, duration, date, longitude, latitude, place_name,
                image_url, total_seats, reserved_seats, created_at, updated_at
            ) VALUES (
                :category, :name, :description, :price, :duration, :date, :longitude, :latitude, :place_name,
                :imageUrl, :totalSeats, :reservedSeats, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )";
            $query = $this->db->prepare($sql);
            $query->bindValue(':category', $event->getCategory());
            $query->bindValue(':name', $event->getName());
            $query->bindValue(':description', $event->getDescription());
            $query->bindValue(':price', $event->getPrice());
            $query->bindValue(':duration', $event->getDuration());
            $query->bindValue(':date', $event->getDate());
            $query->bindValue(':longitude', $longitude);
            $query->bindValue(':latitude', $latitude);
            $query->bindValue(':place_name', $placeName);
            $query->bindValue(':imageUrl', $event->getImageUrl());
            $query->bindValue(':totalSeats', $event->getTotalSeats());
            $query->bindValue(':reservedSeats', $event->getReservedSeats() ?? 0);
            $query->execute();
    
            $eventId = $this->db->lastInsertId();
            $chaiseController = new ChaiseController($this->db);
            $chaiseController->addMultipleChaises($eventId, $event->getTotalSeats());
    
            $this->db->commit();
            error_log('Event added successfully with ID: ' . $eventId);
            return $eventId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error in addEvent: ' . $e->getMessage());
            throw new Exception('Erreur lors de l\'ajout de l\'événement : ' . $e->getMessage());
        }
    }

    public function updateEvent($event, $id) {
        error_log('updateEvent called with data: ' . json_encode([
            'id' => $id,
            'category' => $event->getCategory(),
            'name' => $event->getName(),
            'description' => $event->getDescription(),
            'price' => $event->getPrice(),
            'duration' => $event->getDuration(),
            'date' => $event->getDate(),
            'longitude' => $event->getLongitude(),
            'latitude' => $event->getLatitude(),
            'place_name' => $event->getPlaceName(),
            'image_url' => $event->getImageUrl(),
            'total_seats' => $event->getTotalSeats()
        ]));
    
        $longitude = $event->getLongitude();
        $latitude = $event->getLatitude();
        $placeName = $event->getPlaceName();
    
        if (empty($longitude) || empty($latitude) || !is_numeric($longitude) || !is_numeric($latitude)) {
            error_log('Validation failed: Invalid GPS coordinates');
            throw new Exception('Les coordonnées GPS doivent être des nombres valides.');
        }
    
        if (empty($placeName) || strlen($placeName) < 3) {
            error_log('Validation failed: Invalid place name');
            throw new Exception('Le nom du lieu est requis et doit contenir au moins 3 caractères.');
        }
    
        $oldEvent = $this->getEventById($id);
        $oldTotalSeats = $oldEvent['totalSeats'];
        $newTotalSeats = $event->getTotalSeats();
    
        try {
            $this->db->beginTransaction();
    
            $sql = "UPDATE evenements SET
                    category = :category, name = :name, description = :description, price = :price,
                    duration = :duration, date = :date, longitude = :longitude, latitude = :latitude,
                    place_name = :place_name, image_url = :imageUrl, total_seats = :totalSeats,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
    
            $query = $this->db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->bindValue(':category', $event->getCategory());
            $query->bindValue(':name', $event->getName());
            $query->bindValue(':description', $event->getDescription());
            $query->bindValue(':price', $event->getPrice());
            $query->bindValue(':duration', $event->getDuration());
            $query->bindValue(':date', $event->getDate());
            $query->bindValue(':longitude', $longitude);
            $query->bindValue(':latitude', $latitude);
            $query->bindValue(':place_name', $placeName);
            $query->bindValue(':imageUrl', $event->getImageUrl());
            $query->bindValue(':totalSeats', $newTotalSeats);
            $query->execute();
    
            $chaiseController = new ChaiseController($this->db);
            
            if ($newTotalSeats < $oldTotalSeats) {
                $chaiseController->removeLastAvailableChaises($id, $oldTotalSeats - $newTotalSeats);
            } elseif ($newTotalSeats > $oldTotalSeats) {
                $chaiseController->addMultipleChaises($id, $newTotalSeats - $oldTotalSeats, $oldTotalSeats + 1);
            }
    
            $this->db->commit();
            error_log('Event updated successfully with ID: ' . $id);
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error in updateEvent: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour de l\'événement : ' . $e->getMessage());
        }
    }

    public function deleteEvent($id) {
        try {
            $this->db->beginTransaction();
            
            $chaiseController = new ChaiseController();
            $chaiseController->deleteAllChaisesForEvent($id);
            
            $update = $this->db->prepare("UPDATE evenements 
                                         SET updated_at = CURRENT_TIMESTAMP 
                                         WHERE id = :id");
            $update->bindValue(':id', $id);
            $update->execute();
            
            $delete = $this->db->prepare('DELETE FROM evenements WHERE id = :id');
            $delete->bindValue(':id', $id);
            $result = $delete->execute();
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Erreur deleteEvent: ' . $e->getMessage());
        }
    }
    public function getEventsByCategory($category) {
        $sql = "SELECT 
                    id, 
                    name, 
                    description, 
                    price, 
                    duration, 
                    image_url AS imageUrl,
                    UNIX_TIMESTAMP(updated_at) AS last_updated
                FROM evenements
                WHERE category = :category
                ORDER BY date DESC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->bindValue(':category', $category);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEventsByCategory: ' . $e->getMessage());
        }
    }
    public function getEventById($id) {
        $sql = "SELECT id, category, name, description, price, duration, date, longitude, latitude, place_name,
       image_url AS imageUrl, total_seats AS totalSeats, reserved_seats AS reservedSeats
FROM evenements WHERE id = :id";
        
        try {
            $query = $this->db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEventById: ' . $e->getMessage());
        }
    }
    public function getAllReservations() {
        $sql = "SELECT e.id, e.name, e.date, e.place_name, e.price, e.image_url AS image,
       GROUP_CONCAT(c.numero ORDER BY CAST(c.numero AS UNSIGNED)) AS seats,
       e.date > DATE_ADD(NOW(), INTERVAL 1 DAY) AS can_modify,
       e.date > DATE_ADD(NOW(), INTERVAL 1 DAY) AS can_cancel
FROM chaise c JOIN evenements e ON c.event_id = e.id
WHERE c.statut = 'reserve'
GROUP BY e.id, e.name, e.date, e.place_name, e.price, e.image_url
ORDER BY e.date ASC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getAllReservations: ' . $e->getMessage());
        }
    }
}
?>