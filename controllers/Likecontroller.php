<?php
namespace Controllers;

use PDO;
use Models\LikeModel;
use Exception;

class LikeController {
    private LikeModel $likeModel;

    public function __construct(PDO $db) {
        $this->likeModel = new LikeModel($db);
    }

    public function toggleLike() {
        if (!isset($_SESSION['user_id'])) {
            die("Erreur : Utilisateur non connecté.");
        }

        if (empty($_POST['event_id'])) {
            die("Erreur : ID de l'événement manquant.");
        }

        $event_id = (int) $_POST['event_id'];
        $user_id = (int) $_SESSION['user_id'];

        try {
            $hasLiked = $this->likeModel->hasUserLiked($event_id, $user_id);
            
            if ($hasLiked) {
                $success = $this->likeModel->removeLike($event_id, $user_id);
            } else {
                $success = $this->likeModel->addLike($event_id, $user_id);
            }

            if ($success) {
                $newLikeCount = $this->likeModel->getLikeCount($event_id);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'liked' => !$hasLiked,
                    'likeCount' => $newLikeCount
                ]);
            } else {
                throw new Exception("Erreur lors de la mise à jour du like");
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
