<?php
namespace Models;

use PDO;

class LikeModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function addLike($event_id, $user_id) {
        try {
            $stmt = $this->db->prepare("INSERT INTO likes (event_id, user_id) VALUES (:event_id, :user_id)");
            return $stmt->execute([
                ':event_id' => $event_id,
                ':user_id' => $user_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function removeLike($event_id, $user_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM likes WHERE event_id = :event_id AND user_id = :user_id");
            return $stmt->execute([
                ':event_id' => $event_id,
                ':user_id' => $user_id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getLikeCount($event_id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE event_id = :event_id");
            $stmt->execute([':event_id' => $event_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function hasUserLiked($event_id, $user_id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->execute([
                ':event_id' => $event_id,
                ':user_id' => $user_id
            ]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }
}