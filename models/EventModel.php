<?php
namespace Models;

use PDO;
use PDOException;

class EventModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createEvent($title, $description, $date_event, $time_event, $location, $user_id) {
        try {
            $sql = "INSERT INTO events (title, description, date_event, time_event, location, user_id) 
                    VALUES (:title, :description, :date_event, :time_event, :location, :user_id)";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':date_event', $date_event, PDO::PARAM_STR);  // Vérifie que c'est bien une date valide
            $stmt->bindParam(':time_event', $time_event, PDO::PARAM_STR);  // Vérifie que c'est bien une heure valide
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            die("❌ Erreur lors de l'insertion de l'événement : " . $e->getMessage());
        }
    }
    
    
    

    // Modifier un événement
    public function updateEvent(
        int $eventId,
        string $title,
        string $description,
        string $date,
        string $time,
        string $location
    ): bool {
        try {
            $sql = "UPDATE events SET 
                    title = :title,
                    description = :description,
                    date_event = :date,
                    time_event = :time,
                    location = :location
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $params = [
                ':id' => $eventId,
                ':title' => $title,
                ':description' => $description,
                ':date' => $date,
                ':time' => $time,
                ':location' => $location
            ];

            return $stmt->execute($params);

        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la mise à jour de l'événement : " . $e->getMessage());
        }
    }

    // Supprimer un événement
    public function deleteEvent(int $event_id, int $user_id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM events WHERE id = :event_id AND user_id = :user_id");
            return $stmt->execute([':event_id' => $event_id, ':user_id' => $user_id]);
        } catch (PDOException $e) {
            die("❌ Erreur SQL (deleteEvent) : " . $e->getMessage());
        }
    }

    // Récupérer un événement par ID
    public function getEventById(int $event_id): ?object {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, 
                       u.pseudo as creator_name,
                       (SELECT COUNT(*) FROM inscriptions WHERE event_id = e.id) as participant_count
                FROM events e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.id = :event_id
            ");
            $stmt->execute([':event_id' => $event_id]);
            $event = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$event) {
                throw new \Exception("L'événement demandé n'existe pas");
            }
            
            return $event;
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération de l'événement : " . $e->getMessage());
        }
    }

    // Récupérer tous les événements
    public function getAllEvents(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, 
                       u.pseudo as creator_name,
                       (SELECT COUNT(*) FROM inscriptions WHERE event_id = e.id) as participant_count,
                       (SELECT COUNT(*) FROM comments WHERE event_id = e.id) as comment_count
                FROM events e
                LEFT JOIN users u ON e.user_id = u.id
                ORDER BY e.date_event ASC, e.time_event ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des événements : " . $e->getMessage());
        }
    }

    // Récupérer les prochains événements
    public function getUpcomingEvents(int $limit = 3): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM events 
                WHERE date_event >= CURDATE() 
                ORDER BY date_event ASC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("❌ Erreur SQL (getUpcomingEvents) : " . $e->getMessage());
        }
    }

    public function getCommentsForEvent($eventId)
    {
        $sql = "SELECT * FROM comments WHERE event_id = :event_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchEventsByTitle(string $searchTerm): array {
        try {
            $searchTerm = "%$searchTerm%";
            $stmt = $this->db->prepare("
                SELECT * FROM events 
                WHERE title LIKE :searchTerm 
                ORDER BY date_event ASC
            ");
            $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("❌ Erreur SQL (searchEventsByTitle) : " . $e->getMessage());
        }
    }

    // Méthode de suppression pour l'administration (sans vérification du user_id)
    public function adminDeleteEvent(int $event_id): bool {
        try {
            $this->db->beginTransaction();

            // Vérifier si l'événement existe
            $event = $this->getEventById($event_id);
            if (!$event) {
                throw new \Exception("L'événement à supprimer n'existe pas");
            }

            // Supprimer d'abord les inscriptions liées
            $stmt = $this->db->prepare("DELETE FROM inscriptions WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $event_id]);

            // Supprimer les commentaires liés
            $stmt = $this->db->prepare("DELETE FROM comments WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $event_id]);

            // Supprimer les likes liés
            $stmt = $this->db->prepare("DELETE FROM likes WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $event_id]);

            // Enfin, supprimer l'événement
            $stmt = $this->db->prepare("DELETE FROM events WHERE id = :event_id");
            $success = $stmt->execute([':event_id' => $event_id]);

            if ($success) {
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;

        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new \Exception("Erreur lors de la suppression de l'événement et de ses données associées : " . $e->getMessage());
        }
    }
}
