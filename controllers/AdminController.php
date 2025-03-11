<?php
namespace Controllers;

use PDO;
use Models\EventModel;
use Models\UserModel;
use Models\CommentModel;
use Models\InscriptionModel;

class AdminController extends Controller {
    private EventModel $eventModel;
    private UserModel $userModel;
    private CommentModel $commentModel;
    private InscriptionModel $inscriptionModel;

    public function __construct(PDO $db) {
        parent::__construct($db);
        $this->eventModel = new EventModel($db);
        $this->userModel = new UserModel($db);
        $this->commentModel = new CommentModel($db);
        $this->inscriptionModel = new InscriptionModel($db);
    }

    private function isAdmin(): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        return $user && $user->role === 'admin';
    }

    private function requireAdmin() {
        if (!$this->isAdmin()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                $this->sendJsonResponse(['success' => false, 'message' => 'Accès non autorisé']);
                exit;
            } else {
                $_SESSION['error_message'] = "Accès non autorisé. Vous devez être administrateur pour accéder à cette page.";
                header('Location: /sporteventultimate/home');
                exit;
            }
        }
    }

    public function index() {
        $this->requireAdmin();

        try {
            $events = $this->eventModel->getAllEvents();
            $users = $this->userModel->getAllUsers();
            $comments = $this->commentModel->getAllComments();
            $inscriptions = $this->inscriptionModel->getAllInscriptions();

            // Ajouter les statistiques pour chaque événement
            foreach ($events as $event) {
                $event->participant_count = count($this->inscriptionModel->getInscriptions($event->id));
                $event->comment_count = count($this->commentModel->getCommentsByEventId($event->id));
                // Le creator_name est déjà inclus par getAllEvents() via la jointure
            }

            $this->render('admin/dashboard.html.twig', [
                'events' => $events,
                'users' => $users,
                'comments' => $comments,
                'inscriptions' => $inscriptions,
                'success_message' => $_SESSION['success_message'] ?? null,
                'error_message' => $_SESSION['error_message'] ?? null
            ]);

            // Nettoyer les messages de session après les avoir affichés
            unset($_SESSION['success_message'], $_SESSION['error_message']);
        } catch (\Exception $e) {
            error_log("Erreur dans le tableau de bord admin : " . $e->getMessage());
            $_SESSION['error_message'] = "Une erreur est survenue lors du chargement du tableau de bord";
            header('Location: /sporteventultimate/home');
            exit;
        }
    }

    public function deleteEvent() {
        $this->requireAdmin();

        $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        if (!$event_id) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID d\'événement invalide']);
            return;
        }

        try {
            if ($this->eventModel->adminDeleteEvent($event_id)) {
                $_SESSION['success_message'] = "L'événement a été supprimé avec succès";
                $this->sendJsonResponse(['success' => true]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression de l\'événement']);
            }
        } catch (\Exception $e) {
            error_log("Erreur lors de la suppression de l'événement : " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Une erreur est survenue lors de la suppression']);
        }
    }

    public function deleteComment() {
        $this->requireAdmin();

        $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
        if (!$commentId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID de commentaire invalide']);
            return;
        }

        try {
            if ($this->commentModel->deleteComment($commentId)) {
                $_SESSION['success_message'] = "Le commentaire a été supprimé avec succès";
                $this->sendJsonResponse(['success' => true]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression du commentaire']);
            }
        } catch (\Exception $e) {
            error_log("Erreur lors de la suppression du commentaire : " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Une erreur est survenue lors de la suppression']);
        }
    }

    public function removeParticipant() {
        $this->requireAdmin();

        $inscriptionId = filter_input(INPUT_POST, 'inscription_id', FILTER_VALIDATE_INT);
        if (!$inscriptionId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID d\'inscription invalide']);
            return;
        }

        try {
            if ($this->inscriptionModel->deleteInscription($inscriptionId)) {
                $_SESSION['success_message'] = "Le participant a été retiré avec succès";
                $this->sendJsonResponse(['success' => true]);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Erreur lors du retrait du participant']);
            }
        } catch (\Exception $e) {
            error_log("Erreur lors du retrait du participant : " . $e->getMessage());
            $this->sendJsonResponse(['success' => false, 'message' => 'Une erreur est survenue lors du retrait']);
        }
    }

    protected function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}