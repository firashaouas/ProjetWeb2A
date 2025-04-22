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
        $sql = "SELECT
                id, category, name, description, price, duration, date, location,
                image_url AS imageUrl,
                total_seats AS totalSeats,
                reserved_seats AS reservedSeats,
                UNIX_TIMESTAMP(created_at) AS created_at,
                UNIX_TIMESTAMP(updated_at) AS updated_at
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
        try {
            $this->db->beginTransaction();
            
            // Insert event
            $sql = "INSERT INTO evenements (
                    category, name, description, price, duration, date, location,
                    image_url, total_seats, reserved_seats, created_at, updated_at
                ) VALUES (
                    :category, :name, :description, :price, :duration, :date, :location,
                    :imageUrl, :totalSeats, :reservedSeats, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )";
            
            $query = $this->db->prepare($sql);
            $query->bindValue(':category', $event->getCategory());
            $query->bindValue(':name', $event->getName());
            $query->bindValue(':description', $event->getDescription());
            $query->bindValue(':price', $event->getPrice());
            $query->bindValue(':duration', $event->getDuration());
            $query->bindValue(':date', $event->getDate());
            $query->bindValue(':location', $event->getLocation());
            $query->bindValue(':imageUrl', $event->getImageUrl());
            $query->bindValue(':totalSeats', $event->getTotalSeats());
            $query->bindValue(':reservedSeats', $event->getReservedSeats() ?? 0);
            $query->execute();
            
            $eventId = $this->db->lastInsertId();
            
            // Add chairs using same connection
            $chaiseController = new ChaiseController($this->db);
            $chaiseController->addMultipleChaises($eventId, $event->getTotalSeats());
            
            $this->db->commit();
            return $eventId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new Exception('Erreur addEvent: ' . $e->getMessage());
        }
    }

    public function updateEvent($event, $id) {
        $oldEvent = $this->getEventById($id);
        $oldTotalSeats = $oldEvent['totalSeats'];
        $newTotalSeats = $event->getTotalSeats();

        try {
            $this->db->beginTransaction();

            $sql = "UPDATE evenements SET
                    category = :category,
                    name = :name,
                    description = :description,
                    price = :price,
                    duration = :duration,
                    date = :date,
                    location = :location,
                    image_url = :imageUrl,
                    total_seats = :totalSeats,
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
            $query->bindValue(':location', $event->getLocation());
            $query->bindValue(':imageUrl', $event->getImageUrl());
            $query->bindValue(':totalSeats', $newTotalSeats);
            $query->execute();

            $chaiseController = new ChaiseController();
            
            if ($newTotalSeats < $oldTotalSeats) {
                $chaiseController->removeLastAvailableChaises($id, $oldTotalSeats - $newTotalSeats);
            } elseif ($newTotalSeats > $oldTotalSeats) {
                $chaiseController->addMultipleChaises($id, $newTotalSeats - $oldTotalSeats, $oldTotalSeats + 1);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Erreur updateEvent: ' . $e->getMessage());
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
        $sql = "SELECT 
                    id, 
                    category, 
                    name, 
                    description, 
                    price, 
                    duration, 
                    date, 
                    location,
                    image_url AS imageUrl,
                    total_seats AS totalSeats,
                    reserved_seats AS reservedSeats
                FROM evenements 
                WHERE id = :id";
        
        try {
            $query = $this->db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEventById: ' . $e->getMessage());
        }
    }
}
?>