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
            die("Erreur : Utilisateur non connecté.");
        }

        $content = $_POST['content'] ?? '';
        if (empty($content)) {
            die("Erreur : Tous les champs doivent être remplis.");
        }

        $event_id = $_POST['event_id'] ?? null;
        $user_id = $_SESSION['user_id'];

        // Vérifier et récupérer le pseudo de l'utilisateur
        $user_name = $this->commentModel->getUserPseudo($user_id);
        if (!$user_name) {
            $user_name = "Utilisateur";  // Valeur par défaut
        }

        // Ajouter le commentaire en base de données
        if ($this->commentModel->addComment($event_id, $user_id, $content, $user_name)) {
            // Redirection propre vers la page de l'événement
            header('Location: /sporteventultimate/event/' . $event_id);
            exit;
        } else {
            die("Erreur : Impossible d'ajouter le commentaire.");
        }
    }

    public function deleteComment() {
        if (isset($_POST['comment_id']) && isset($_POST['event_id'])) {
            $comment_id = $_POST['comment_id'];
            $event_id = $_POST['event_id'];

            if ($this->commentModel->deleteComment($comment_id)) {
                header('Location: /sporteventultimate/event/' . $event_id);
                exit;
            } else {
                die("Erreur lors de la suppression.");
            }
        } else {
            die("Erreur : Informations manquantes.");
        }
    }

    public function getComments(int $event_id) {
        return $this->commentModel->getCommentsForEvent($event_id);
    }
}
