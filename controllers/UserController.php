<?php
namespace Controllers;

use Models\UserModel;
use PDO;

class UserController extends Controller {
    private UserModel $userModel;

    public function __construct(PDO $db) {
        parent::__construct($db);
        $this->userModel = new UserModel($db);
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /sporteventultimate/connexion');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user_id']);
        $eventsCreated = $this->userModel->getEventsCreatedByUser($_SESSION['user_id']);
        $eventsParticipating = $this->userModel->getEventsParticipatingByUser($_SESSION['user_id']);

        $this->render('profile.html.twig', [
            'user' => $user,
            'eventsCreated' => $eventsCreated,
            'eventsParticipating' => $eventsParticipating
        ]);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = htmlspecialchars($_POST['pseudo'] ?? '', ENT_QUOTES, 'UTF-8');
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!$pseudo || !$email || !$password || !$confirm_password) {
                $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
            } elseif ($password !== $confirm_password) {
                $_SESSION['error_message'] = "Les mots de passe ne correspondent pas.";
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    if ($this->userModel->register($pseudo, $email, $hashedPassword)) {
                        $_SESSION['success_message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                        header('Location: /sporteventultimate/connexion');
                        exit;
                    } else {
                        $_SESSION['error_message'] = "Erreur lors de l'inscription.";
                    }
                } catch (\PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $_SESSION['error_message'] = "Cet email ou ce pseudo est déjà utilisé.";
                    } else {
                        $_SESSION['error_message'] = "Une erreur est survenue lors de l'inscription.";
                    }
                }
            }
        }

        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['error_message']);
        
        $this->render('inscription.html.twig', [
            'error' => $error
        ]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (!$email || !$password) {
                $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
            } else {
                $user = $this->userModel->getUserByEmail($email);
                if ($user && password_verify($password, $user->password)) {
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user'] = [
                        'id' => $user->id,
                        'pseudo' => $user->pseudo,
                        'email' => $user->email,
                        'role' => $user->role
                    ];
                    $_SESSION['success_message'] = "Connexion réussie !";
                    header('Location: /sporteventultimate/home');
                    exit;
                } else {
                    $_SESSION['error_message'] = "Email ou mot de passe incorrect.";
                }
            }
        }

        $error = $_SESSION['error_message'] ?? null;
        $success = $_SESSION['success_message'] ?? null;
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        $this->render('connexion.html.twig', [
            'error' => $error,
            'success' => $success
        ]);
    }

    public function logout() {
        session_destroy();
        header('Location: /sporteventultimate/presentation');
        exit;
    }

    public function getUserProfile(int $userId) {
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            return null;
        }
        return $user;
    }
}