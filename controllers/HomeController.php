<?php
namespace Controllers;

use Models\EventModel;
use PDO;

class HomeController extends Controller
{
    private EventModel $eventModel;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->eventModel = new EventModel($db);
    }

    public function presentation()
    {
        // Récupérer les événements à venir
        $upcomingEvents = $this->eventModel->getUpcomingEvents(3);
        
        // Rendre la vue avec les données
        $this->render('presentation.html.twig', [
            'upcomingEvents' => $upcomingEvents
        ]);
    }

    public function index()
    {
        $searchTerm = $_GET['search'] ?? '';
        
        if (!empty($searchTerm)) {
            $events = $this->eventModel->searchEventsByTitle($searchTerm);
        } else {
            $events = $this->eventModel->getAllEvents();
        }
        
        $this->render('home.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm
        ]);
    }

    public function showEvent(int $event_id) {
        $event = $this->eventModel->getEventById($event_id);
        if ($event) {
            $this->render('thisevent.html.twig', ['event' => $event]);
        } else {
            header('Location: /sporteventultimate/home');
            exit;
        }
    }
}
