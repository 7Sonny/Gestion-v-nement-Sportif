<?php
namespace Models;

use PDO;
use PDOException;

class CommentModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Récupérer le pseudo d'un utilisateur à partir de son ID
    public function getUserPseudo(int $user_id): string {
        try {
            $stmt = $this->db->prepare("SELECT pseudo FROM users WHERE id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user['pseudo'] ?? 'Utilisateur inconnu';
        } catch (PDOException $e) {
            die("Erreur SQL (getUserPseudo) : " . $e->getMessage());
        }
    }

    // Ajouter un commentaire avec pseudo récupéré
    public function addComment(int $event_id, int $user_id, string $content, string $user_name): int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO comments (event_id, user_id, content, user_name, created_at) 
                VALUES (:event_id, :user_id, :content, :user_name, NOW())
            ");

            $stmt->execute([
                ':event_id' => $event_id,
                ':user_id' => $user_id,
                ':content' => $content,
                ':user_name' => $user_name
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            die("Erreur SQL (addComment) : " . $e->getMessage());
        }
    }

    // Supprimer un commentaire
    public function deleteComment(int $comment_id): bool {
        try {
            $this->db->beginTransaction();

            // Vérifier si le commentaire existe
            $stmt = $this->db->prepare("SELECT id FROM comments WHERE id = :comment_id");
            $stmt->execute([':comment_id' => $comment_id]);
            if (!$stmt->fetch()) {
                throw new \Exception("Le commentaire n'existe pas");
            }

            // Supprimer le commentaire
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = :comment_id");
            $success = $stmt->execute([':comment_id' => $comment_id]);

            if ($success) {
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception("Erreur lors de la suppression du commentaire : " . $e->getMessage());
        }
    }

    public function getCommentById(int $comment_id): ?object {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       u.pseudo as user_name,
                       e.title as event_title
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN events e ON c.event_id = e.id
                WHERE c.id = :comment_id
            ");
            $stmt->execute([':comment_id' => $comment_id]);
            $comment = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$comment) {
                throw new \Exception("Le commentaire demandé n'existe pas");
            }

            return $comment;
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération du commentaire : " . $e->getMessage());
        }
    }

    // Récupérer tous les commentaires d'un événement
    public function getCommentsForEvent(int $event_id): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.pseudo as user_name 
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.event_id = :event_id 
                ORDER BY c.created_at DESC
            ");
            
            $stmt->execute([':event_id' => $event_id]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("Erreur SQL (getCommentsForEvent) : " . $e->getMessage());
        }
    }

    // Méthode pour l'administration : récupérer tous les commentaires
    public function getAllComments(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*,
                       u.pseudo as user_name,
                       e.title as event_title
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN events e ON c.event_id = e.id
                ORDER BY c.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des commentaires : " . $e->getMessage());
        }
    }

    // Méthode pour l'administration : supprimer un commentaire sans vérifier l'utilisateur
    public function adminDeleteComment(int $comment_id): bool {
        try {
            $this->db->beginTransaction();

            // Vérifier si le commentaire existe
            $stmt = $this->db->prepare("SELECT id FROM comments WHERE id = :comment_id");
            $stmt->execute([':comment_id' => $comment_id]);
            if (!$stmt->fetch()) {
                throw new \Exception("Le commentaire n'existe pas");
            }

            // Supprimer le commentaire
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = :comment_id");
            $success = $stmt->execute([':comment_id' => $comment_id]);

            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception("Erreur lors de la suppression du commentaire : " . $e->getMessage());
        }
    }

    public function getCommentsByEventId($event_id): array {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*,
                       u.pseudo as user_name
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.event_id = :event_id
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([':event_id' => $event_id]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des commentaires de l'événement : " . $e->getMessage());
        }
    }
}
