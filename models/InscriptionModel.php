<?php
namespace Models;

use PDO;
use PDOException;

class InscriptionModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAllInscriptions(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*,
                       u.pseudo as user_name,
                       e.title as event_title,
                       e.date_event as event_date
                FROM inscriptions i
                LEFT JOIN users u ON i.user_id = u.id
                LEFT JOIN events e ON i.event_id = e.id
                ORDER BY i.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des inscriptions : " . $e->getMessage());
        }
    }

    public function getInscriptions($event_id): array {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*, u.pseudo as user_name
                FROM inscriptions i
                JOIN users u ON i.user_id = u.id
                WHERE i.event_id = :event_id
                ORDER BY i.created_at DESC
            ");
            $stmt->execute([':event_id' => $event_id]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération des inscriptions de l'événement : " . $e->getMessage());
        }
    }

    public function addInscription($event_id, $user_id) {
        try {
            $this->db->beginTransaction();

            // Vérifier si l'inscription existe déjà
            $stmt = $this->db->prepare("SELECT id FROM inscriptions WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->execute([':event_id' => $event_id, ':user_id' => $user_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Si déjà inscrit, on désinscrit
                $stmt = $this->db->prepare("DELETE FROM inscriptions WHERE event_id = :event_id AND user_id = :user_id");
                $success = $stmt->execute([':event_id' => $event_id, ':user_id' => $user_id]);
                
                if ($success) {
                    $this->db->commit();
                    return false; // Indique que l'utilisateur est maintenant désinscrit
                }
            } else {
                // Si pas inscrit, on ajoute l'inscription
                $stmt = $this->db->prepare("INSERT INTO inscriptions (event_id, user_id, created_at) VALUES (:event_id, :user_id, NOW())");
                $success = $stmt->execute([':event_id' => $event_id, ':user_id' => $user_id]);
                
                if ($success) {
                    $this->db->commit();
                    return true; // Indique que l'utilisateur est maintenant inscrit
                }
            }

            $this->db->rollBack();
            return false;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception("Erreur lors de la gestion de l'inscription : " . $e->getMessage());
        }
    }

    public function getInscriptionById(int $inscription_id): ?object {
        try {
            $stmt = $this->db->prepare("
                SELECT i.*,
                       u.pseudo as user_name,
                       e.title as event_title,
                       e.date_event as event_date
                FROM inscriptions i
                LEFT JOIN users u ON i.user_id = u.id
                LEFT JOIN events e ON i.event_id = e.id
                WHERE i.id = :inscription_id
            ");
            $stmt->execute([':inscription_id' => $inscription_id]);
            $inscription = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$inscription) {
                throw new \Exception("L'inscription demandée n'existe pas");
            }

            return $inscription;
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération de l'inscription : " . $e->getMessage());
        }
    }

    public function deleteInscription(int $inscription_id): bool {
        try {
            $this->db->beginTransaction();

            // Vérifier si l'inscription existe
            $inscription = $this->getInscriptionById($inscription_id);
            if (!$inscription) {
                throw new \Exception("L'inscription n'existe pas");
            }

            // Supprimer l'inscription
            $stmt = $this->db->prepare("DELETE FROM inscriptions WHERE id = :inscription_id");
            $success = $stmt->execute([':inscription_id' => $inscription_id]);

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
            throw new \Exception("Erreur lors de la suppression de l'inscription : " . $e->getMessage());
        }
    }

    public function isUserRegistered($event_id, $user_id): bool {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM inscriptions WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->execute([':event_id' => $event_id, ':user_id' => $user_id]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la vérification de l'inscription : " . $e->getMessage());
        }
    }
}