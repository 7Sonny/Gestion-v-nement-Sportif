<?php
require_once __DIR__ . '/vendor/autoload.php';

use Database\Database;
use AltoRouter;
use Controllers\UserController;
use Controllers\EventController;
use Controllers\HomeController;
use Controllers\CommentController;
use Controllers\LikeController;
use Controllers\AdminController;

session_start();

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}

// Création des contrôleurs
$userController = new UserController($db);
$eventController = new EventController($db);
$homeController = new HomeController($db);
$commentController = new CommentController($db);
$likeController = new LikeController($db);
$adminController = new AdminController($db);

// Initialisation du routeur
$router = new AltoRouter();
$router->setBasePath('/sporteventultimate');

// Route pour la page de présentation (page d'accueil)
$router->map('GET', '/', function () use ($homeController) {
    $homeController->presentation();
}, 'presentation');

$router->map('GET', '/presentation', function () use ($homeController) {
    $homeController->presentation();
}, 'presentation_alt');

// Route pour le profil utilisateur
$router->map('GET', '/profile', function () use ($userController) {
    $userController->profile();
}, 'profile');

// Définition des routes principales
$router->map('GET', '/home', function () use ($homeController) {
    $homeController->index();
}, 'home_page');

$router->map('GET', '/inscription', function () use ($userController) {
    $userController->register();
}, 'inscription');

$router->map('POST', '/inscription', function () use ($userController) {
    $userController->register();
}, 'register_post');

$router->map('GET', '/connexion', function () use ($userController) {
    $userController->login();
}, 'connexion');

$router->map('POST', '/connexion', function () use ($userController) {
    $userController->login();
}, 'login_post');

$router->map('GET', '/deconnexion', function () use ($userController) {
    $userController->logout();
}, 'deconnexion');

// Routes pour les événements
$router->map('GET', '/event/[i:id]', function ($id) use ($eventController) {
    $eventController->showEvent($id);
}, 'event_detail');

$router->map('GET', '/event/create', function () use ($eventController) {
    $eventController->createEvent();
}, 'event_create');

$router->map('POST', '/event/create', function () use ($eventController) {
    $eventController->createEvent();
}, 'event_create_post');

$router->map('POST', '/event/update', function () use ($eventController) {
    $eventController->updateEvent();
}, 'event_update');

$router->map('POST', '/event/delete', function () use ($eventController) {
    $eventController->deleteEvent();
}, 'event_delete');

// Routes pour les commentaires
$router->map('POST', '/comment/add', function () use ($commentController) {
    $commentController->addComment();
}, 'comment_add');

$router->map('POST', '/comment/delete', function () use ($commentController) {
    $commentController->deleteComment();
}, 'comment_delete');

// Routes pour les likes
$router->map('POST', '/like/toggle', function () use ($likeController) {
    $likeController->toggleLike();
}, 'like_toggle');

$router->map('GET', '/like/count/[i:event_id]', function ($event_id) use ($likeController) {
    $likeController->getLikeCount($event_id);
}, 'like_count');

// Route pour les inscriptions
$router->map('POST', '/inscription/toggle', function () use ($eventController) {
    $eventController->toggleInscription();
}, 'inscription_toggle');

// Routes d'administration
$router->map('GET', '/admin', function () use ($adminController) {
    try {
        $adminController->index();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
        header('Location: /sporteventultimate/home');
        exit;
    }
}, 'admin_dashboard');

$router->map('GET', '/admin/edit-event/[i:id]', function ($id) use ($adminController) {
    $adminController->editEvent($id);
}, 'admin_edit_event');

$router->map('POST', '/admin/edit-event/[i:id]', function ($id) use ($adminController) {
    $adminController->editEvent($id);
}, 'admin_edit_event_post');

$router->map('POST', '/admin/delete-event', function () use ($adminController) {
    $adminController->deleteEvent();
}, 'admin_delete_event');

$router->map('POST', '/admin/delete-comment', function () use ($adminController) {
    try {
        $adminController->deleteComment();
    } catch (Exception $e) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
            header('Location: /sporteventultimate/admin');
        }
        exit;
    }
}, 'admin_delete_comment');

$router->map('POST', '/admin/remove-participant', function () use ($adminController) {
    try {
        $adminController->removeParticipant();
    } catch (Exception $e) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } else {
            $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
            header('Location: /sporteventultimate/admin');
        }
        exit;
    }
}, 'admin_remove_participant');

// Faire correspondre l'URL demandée
$match = $router->match();

if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo "Page non trouvée";
}