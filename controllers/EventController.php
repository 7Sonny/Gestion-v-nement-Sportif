<?php
namespace Controllers;

use Models\EventModel;
use Models\CommentModel;
use Models\LikeModel;
use Models\InscriptionModel;
use PDO;

class EventController extends Controller
{
    private EventModel $eventModel;
    private CommentModel $commentModel;
    private LikeModel $likeModel;
    private InscriptionModel $inscriptionModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->eventModel = new EventModel($db);
        $this->commentModel = new CommentModel($db);
        $this->likeModel = new LikeModel($db);
        $this->inscriptionModel = new InscriptionModel($db);
    }

    public function createEvent() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $date_event = $_POST['date_event'] ?? '';
            $time_event = $_POST['time_event'] ?? '';
            $location = $_POST['location'] ?? '';
            $user_id = $_SESSION['user_id'] ?? null;
    
            if (!$title || !$description || !$date_event || !$time_event || !$location || !$user_id) {
                die("❌ Tous les champs sont obligatoires !");
            }
    
            $eventModel = new EventModel($this->db);
            $success = $eventModel->createEvent($title, $description, $date_event, $time_event, $location, $user_id);
    
            if ($success) {
                // 🔄 **Redirection correcte**
                header("Location: /sporteventultimate/home");
                exit;
            } else {
                die("❌ Erreur lors de la création de l'événement.");
            }
        }
    
        // ✅ Si c'est une requête GET, affiche le formulaire de création d'événement
        $this->render("event.html.twig");
    }
    
    
    



    // Modifier un événement
    public function updateEvent() {
        echo "✅ Fonction updateEvent() appelée !<br>";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $event_id = $_POST['event_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $date_event = $_POST['date_event'] ?? '';
            $location = $_POST['location'] ?? '';
            $user_id = $_SESSION['user_id'] ?? 0;

            if (empty($event_id) || empty($title) || empty($description) || empty($date_event) || empty($location)) {
                die("❌ Erreur : Tous les champs doivent être remplis.");
            }

            if ($this->eventModel->updateEvent($event_id, $title, $description, $date_event, $location, $user_id)) {
                echo "✅ Événement mis à jour avec succès.<br>";
            } else {
                die("❌ Erreur lors de la mise à jour de l'événement.");
            }
        }
    }

    // Supprimer un événement
    public function deleteEvent() {
        echo "✅ Fonction deleteEvent() appelée !<br>";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $event_id = $_POST['event_id'] ?? 0;
            $user_id = $_SESSION['user_id'] ?? 0;

            if (empty($event_id)) {
                die("❌ Erreur : L'ID de l'événement est requis.");
            }

            if ($this->eventModel->deleteEvent($event_id, $user_id)) {
                echo "✅ Événement supprimé avec succès.<br>";
            } else {
                die("❌ Erreur lors de la suppression de l'événement.");
            }
        }
    }

    // Afficher un événement
    public function showEvent($id)
    {
        $event = $this->eventModel->getEventById($id);
        if (!$event) {
            echo "❌ Événement introuvable.";
            return;
        }

        // Récupérer les commentaires associés
        $comments = $this->commentModel->getCommentsForEvent($id);
        
        // Récupérer le nombre de likes
        $likes = $this->likeModel->getLikeCount($id);

        // Vérifier si l'utilisateur courant a liké l'événement
        $is_liked = false;
        if (isset($_SESSION['user_id'])) {
            $is_liked = $this->likeModel->hasUserLiked($id, $_SESSION['user_id']);
        }

        // Récupérer la liste des inscrits
        $inscriptions = $this->inscriptionModel->getInscriptions($id);

        // Vérifier si l'utilisateur est inscrit
        $is_registered = false;
        if (isset($_SESSION['user_id'])) {
            $is_registered = $this->inscriptionModel->isUserRegistered($id, $_SESSION['user_id']);
        }

        $this->render('thisevent.html.twig', [
            'event' => $event,
            'comments' => $comments,
            'likes' => $likes,
            'is_liked' => $is_liked,
            'inscriptions' => $inscriptions,
            'is_registered' => $is_registered
        ]);
    }

    public function toggleInscription() {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => '❌ Erreur : Utilisateur non connecté']));
        }

        if (empty($_POST['event_id'])) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => '❌ Erreur : ID de l\'événement manquant']));
        }

        $event_id = (int) $_POST['event_id'];
        $user_id = (int) $_SESSION['user_id'];

        $is_registered = $this->inscriptionModel->addInscription($event_id, $user_id);
        $inscriptions = $this->inscriptionModel->getInscriptions($event_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'inscrit' => $is_registered,
            'inscrits' => $inscriptions
        ]);
        exit;
    }

    public function addComment()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['event_id'])) {
        $comment = trim($_POST['comment']);
        $event_id = intval($_POST['event_id']);
        $user_id = $_SESSION['user_id'] ?? null;

        if ($user_id && !empty($comment)) {
            $this->commentModel->addComment($event_id, $user_id, $comment);
        }
    }

    // Redirige vers l'événement après ajout
    header("Location: /event/$event_id");
    exit();
}

    

    // Lister tous les événements
    public function listEvents() {
        $events = $this->eventModel->getAllEvents();
        if (!empty($events)) {
            echo "✅ Liste des événements : ";
            print_r($events);
        } else {
            die("❌ Aucun événement trouvé.");
        }
    }
}
