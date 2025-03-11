<?php
namespace Controllers;

use Models\CommentModel;
use PDO;

class CommentController {
    private CommentModel $commentModel;

    public function __construct(PDO $db) {
        $this->commentModel = new CommentModel($db);
    }

    public function addComment() {

       

        if (!isset($_SESSION['user_id'])) {
            die("❌ Erreur : Utilisateur non connecté.");
        }

        if (empty($_POST['content']) || empty($_POST['event_id'])) {
            die("❌ Erreur : Tous les champs doivent être remplis.");
        }

        $event_id = (int) $_POST['event_id'];
        $content = trim($_POST['content']);
        $user_id = (int) $_SESSION['user_id'];

        // ✅ Vérifier et récupérer le pseudo de l'utilisateur
        if (!isset($_SESSION['pseudo'])) {
            $_SESSION['pseudo'] = $this->commentModel->getUserPseudo($user_id);
        }

        $user_name = $_SESSION['pseudo']; // Récupération du pseudo stocké en session

        // ✅ Ajouter le commentaire en base de données
        $comment_id = $this->commentModel->addComment($event_id, $user_id, $user_name, $content);

        if ($comment_id) {
            // ✅ Redirection propre vers la page de l'événement
            header("Location: /sporteventultimate/event/$event_id");
            exit;
        } else {
            die("❌ Erreur : Impossible d'ajouter le commentaire.");
        }
    }

    public function deleteComment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $comment_id = $_POST['comment_id'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;

            if (!$comment_id || !$user_id) {
                die("❌ Erreur : Informations manquantes.");
            }

            if ($this->commentModel->deleteComment($comment_id, $user_id)) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                die("❌ Erreur lors de la suppression.");
            }
        }
    }

    public function getComments(int $event_id) {
        return $this->commentModel->getCommentsForEvent($event_id);
    }
}
