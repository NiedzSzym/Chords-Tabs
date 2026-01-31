<?php

require_once 'AppController.php';
require_once __DIR__.'/../model/User.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../middleware/AllowedMethods.php';

class SecurityController extends AppController {
    public function __construct() {
    }

    #[AllowedMethods(['POST', 'GET'])]
    public function login() {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
        return $this->render('login', ['messages' => ['HTTPS is required']]);
    }


        if($this->isGet()) {
            return $this->render("login");
        } 

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        $user = UserRepository::getInstance()->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->render('login', ['messages' => ['Wrong password']]);
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_logged_in'] = true;

        header("Location: /dashboard");
        var_dump($email, $password);
        exit;
    }
    
    #[AllowedMethods(['POST', 'GET'])]
    public function register() {
        if (!$this->isPost()) {
            return $this->render('register');
        }

        $email = $_POST["email"] ?? '';
        $password1 = $_POST["password1"] ?? '';
        $password2 = $_POST["password2"] ?? '';
        $nickname = $_POST['nickname'];

        if (empty($email) || empty($password1) || empty($password2) || empty($nickname)) {
            return $this->render('register', ['messages' => ['Fill all fields']]);
        }

        if ($password1 !== $password2) {
            return $this->render('register', ['messages' => ['Password aren\' the same']]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('register', ['messages' => ['Invalid email format!']]);
        }


        $user = new User($email, $password1, $nickname);

        try {
            UserRepository::getInstance()->addUser($user);
        } catch (Exception $e) {
            // Obsługa błędu, np. gdy e-mail jest już zajęty
            // Sprawdzenie kodu błędu PostgreSQL dla duplikatu (23505)
            $errorCode =$e->getMessage();
            if ($e->getCode() == '23505') {
                return $this->render('register', ['messages' => ['Użytkownik o takim e-mailu już istnieje!']]);
            }
            // Obsługa innych błędów (np. logowanie błędu i ogólny komunikat)
            return $this->render('register', ['messages' => ["Wystąpił nieoczekiwany błąd $errorCode"]]);
        }
        return $this->render('login', ['messages' => ['Zarejestrowano pomyślnie!']]); 
    }
    
    #[AllowedMethods(['POST', 'GET'])]
    public function logout() 
    { 

        if (session_status() === PHP_SESSION_NONE) { 
            session_start(); 
        } 

        $_SESSION = []; 
    
        if (ini_get("session.use_cookies")) { 
            $params = session_get_cookie_params(); 
            setcookie( 
                session_name(), 
                '', 
                time() - 42000, 
                $params["path"], 
                $params["domain"], 
                $params["secure"], 
                $params["httponly"] 
            ); 
        } 
    

        session_destroy(); 

        header("Location: /login");
    }

    public function dashboard() {
        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
            header("Location: /login");
            exit;
        }
        return $this->render('dashboard', ['email' => $_SESSION['user_email']]);
    }
}