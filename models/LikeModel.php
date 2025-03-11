<?php
namespace Models;

use PDO;

class LikeModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function toggleLike($event_id, $user_id) {
        // Vérifie si l'utilisateur a déjà liké
        $stmt = $this->db->prepare("SELECT id FROM likes WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$event_id, $user_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Supprime le like
            $stmt = $this->db->prepare("DELETE FROM likes WHERE event_id = ? AND user_id = ?");
            $stmt->execute([$event_id, $user_id]);
            return ['action' => 'removed'];
        } else {
            // Ajoute le like
            $stmt = $this->db->prepare("INSERT INTO likes (event_id, user_id) VALUES (?, ?)");
            $stmt->execute([$event_id, $user_id]);
            return ['action' => 'added'];
        }
    }

    public function getLikeCount($event_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE event_id = ?");
        $stmt->execute([$event_id]);
        return $stmt->fetchColumn();
    }

    public function hasUserLiked($event_id, $user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$event_id, $user_id]);
        return $stmt->fetchColumn() > 0;
    }
}