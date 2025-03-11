<?php
namespace Controllers;

use PDO;
use Models\LikeModel;

class LikeController {
    private LikeModel $likeModel;

    public function __construct(PDO $db) {
        $this->likeModel = new LikeModel($db);
    }

    public function toggleLike() {
        if (!isset($_SESSION['user_id'])) {
            die("❌ Erreur : Utilisateur non connecté.");
        }

        if (empty($_POST['event_id'])) {
            die("❌ Erreur : ID de l'événement manquant.");
        }

        $event_id = (int) $_POST['event_id'];
        $user_id = (int) $_SESSION['user_id'];

        // Toggle le like (ajoute si n'existe pas, supprime si existe)
        $result = $this->likeModel->toggleLike($event_id, $user_id);
        
        // Récupère le nouveau nombre de likes
        $likeCount = $this->likeModel->getLikeCount($event_id);
        
        // Retourne le résultat en JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'liked' => $result['action'] === 'added',
            'likeCount' => $likeCount
        ]);
        exit;
    }

    public function getLikeStatus($event_id) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        return $this->likeModel->hasUserLiked($event_id, $_SESSION['user_id']);
    }

    public function getLikeCount($event_id) {
        return $this->likeModel->getLikeCount($event_id);
    }
}
