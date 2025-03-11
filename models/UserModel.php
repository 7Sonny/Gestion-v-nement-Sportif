<?php
namespace Models;

use PDO;
use PDOException;

class UserModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Récupérer tous les utilisateurs avec leurs informations détaillées
    public function getAllUsers(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*,
                       (SELECT COUNT(*) FROM events WHERE user_id = u.id) as events_count,
                       (SELECT COUNT(*) FROM inscriptions WHERE user_id = u.id) as participations_count,
                       (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comments_count
                FROM users u
                ORDER BY u.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
            throw new \Exception("Une erreur est survenue lors de la récupération des utilisateurs");
        }
    }

    // Vérifier si l'email existe déjà
    public function emailExists(string $email): bool {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            die("❌ Erreur SQL (emailExists) : " . $e->getMessage());
        }
    }

    public function getUserByEmail(string $email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_OBJ); // ✅ Retourne un objet au lieu d'un tableau
        } catch (PDOException $e) {
            die("❌ Erreur SQL (getUserByEmail) : " . $e->getMessage());
        }
    }
    
    

    // Inscription d'un utilisateur
    public function register(string $pseudo, string $email, string $password): ?int {
        try {
            echo "🔍 Tentative d'insertion en base de données...<br>";
    
            $stmt = $this->db->prepare("INSERT INTO users (pseudo, email, password) VALUES (:pseudo, :email, :password)");
            $stmt->execute([
                ':pseudo' => $pseudo,
                ':email' => $email,
                ':password' => $password
            ]);
    
            $userId = (int) $this->db->lastInsertId();
    
            if ($userId) {
                echo "✅ Utilisateur inséré avec succès, ID: " . $userId . "<br>";
            } else {
                echo "❌ Erreur : Aucune insertion en base.<br>";
            }
    
            return $userId;
        } catch (PDOException $e) {
            die("❌ Erreur SQL : " . $e->getMessage());
        }
    }
    
    

    // Connexion d'un utilisateur
    public function login(string $email, string $password): ?int {
        try {
            $stmt = $this->db->prepare("SELECT id, password FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            if ($user && password_verify($password, $user->password)) {
                return $user->id;
            } else {
                die("❌ Erreur : Identifiants incorrects.");
            }
        } catch (PDOException $e) {
            die("❌ Erreur SQL (login) : " . $e->getMessage());
        }
    }

    // Récupérer un utilisateur par ID
    public function getUserById(int $id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("❌ Erreur SQL (getUserById) : " . $e->getMessage());
        }
    }

    // Récupérer les événements créés par un utilisateur
    public function getEventsCreatedByUser(int $userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM events 
                WHERE user_id = :userId 
                ORDER BY date_event DESC
            ");
            $stmt->execute([':userId' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("❌ Erreur SQL (getEventsCreatedByUser) : " . $e->getMessage());
        }
    }

    // Récupérer les événements auxquels participe un utilisateur
    public function getEventsParticipatingByUser(int $userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.* FROM events e
                JOIN inscriptions i ON e.id = i.event_id
                WHERE i.user_id = :userId
                ORDER BY e.date_event DESC
            ");
            $stmt->execute([':userId' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            die("❌ Erreur SQL (getEventsParticipatingByUser) : " . $e->getMessage());
        }
    }

    // Récupérer un événement par ID
    public function getEventById($id) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérifier si un utilisateur est admin
    public function isAdmin(int $userId): bool {
        try {
            error_log("Début de la vérification admin pour l'utilisateur $userId");

            $stmt = $this->db->prepare("
                SELECT id, role, pseudo, email 
                FROM users 
                WHERE id = :id
            ");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                error_log("Utilisateur trouvé - ID: {$user['id']}, Pseudo: {$user['pseudo']}, Role: {$user['role']}");
            } else {
                error_log("Aucun utilisateur trouvé avec l'ID: $userId");
            }

            $isAdmin = ($user && $user['role'] === 'admin');
            error_log("Résultat de la vérification admin: " . ($isAdmin ? 'true' : 'false'));

            return $isAdmin;
        } catch (PDOException $e) {
            error_log("Erreur SQL lors de la vérification admin: " . $e->getMessage());
            return false;
        }
    }

    // Mettre à jour le rôle d'un utilisateur
    public function updateUserRole(int $userId, string $role): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET role = :role 
                WHERE id = :id
            ");
            return $stmt->execute([
                ':id' => $userId,
                ':role' => $role
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du rôle : " . $e->getMessage());
            return false;
        }
    }
}
