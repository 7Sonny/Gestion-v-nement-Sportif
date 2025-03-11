<?php
namespace Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PDO;

class Controller {
    protected Environment $twig;
    protected PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;

        // Initialisation de Twig
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Ajouter les variables globales
        $this->twig->addGlobal('session', $_SESSION);
    }

    protected function render(string $template, array $data = []) {
        echo $this->twig->render($template, $data);
    }
}
