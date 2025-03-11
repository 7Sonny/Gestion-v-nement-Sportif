<?php
namespace Controllers;

use Models\UserModel;
use Middlewares\AuthMiddleware;
use PDO;

class UserController extends Controller
{
    private UserModel $userModel;

    public function __construct(PDO $database)
    {
        parent::__construct($database);
        $this->userModel = new UserModel($database);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = $_POST['pseudo'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($pseudo && $email && $password) {
                $userId = $this->userModel->register($pseudo, $email, $password);
                if ($userId) {
                    $this->setSession('user_id', $userId);
                    header('Location: /profile');
                    exit;
                }
            }
        }
        $this->render('inscription.html.twig');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->login($email, $password);
            if ($user) {
                $this->setSession('user_id', $user->id);
                header('Location: /profile');
                exit;
            }
        }
        $this->render('connexion.html.twig');
    }

    public function profile()
    {
        AuthMiddleware::checkAuthentication();
        
        $userId = $this->getSession('user_id');
        $user = $this->userModel->getUserById($userId);
        $this->render('profile.html.twig', ['user' => $user]);
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}