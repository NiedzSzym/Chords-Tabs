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

        if (!$this->isPost()) {
            $this->generateCsrf();
            return $this->render("login", ['csrf' => $_SESSION['csrf']]);;
        }

        if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
            return $this->render('login', ['messages' => ['Session has been terminated or CSRF error.']]);
        }
        
        $failures = $_SESSION['login_failures'] ?? 0;

        if ($failures > 5) {
            sleep(2);
        }

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';
        if (strlen($email) > 100) return $this->render("login", ["messages" => ["Invalid input length"]]);

        $user = UserRepository::getInstance()->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['login_failures'] = $failures + 1;
            return $this->render('login', ['messages' => ['Wrong password']]);
        }

        unset($_SESSION['login_failures']);

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_logged_in'] = true;

        header("Location: /dashboard");
        exit;
        
    }
    
    #[AllowedMethods(['POST', 'GET'])]
    public function register() {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            return $this->render('register', ['messages' => ['HTTPS is required']]);
        } 
        if (!$this->isPost()) {
            $this->generateCsrf();
            return $this->render("register", ['csrf' => $_SESSION['csrf']]);;
        }

        if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
            return $this->render('register', ['messages' => ['Session has been terminated or CSRF error.']]);
        }

        $email = $_POST["email"] ?? '';
        $password1 = $_POST["password1"] ?? '';
        $password2 = $_POST["password2"] ?? '';
        $nickname = $_POST['nickname'];

        if(strlen($password1) < 8)  return $this->render("register", ["messages" => ["Password is to weak"]]);
        if (strlen($email) > 100) return $this->render("register", ["messages" => ["Invalid input length"]]);

        if (empty($email) || empty($password1) || empty($password2) || empty($nickname)) {
            return $this->render('register', ['messages' => ['Fill all fields']]);
        }

        if ($password1 !== $password2) {
            return $this->render('register', ['messages' => ['Password aren\' the same']]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('register', ['messages' => ['Invalid email format!']]);
        }

        $hashedPassword = password_hash($password1, PASSWORD_BCRYPT);
        $user = new User($email, $hashedPassword, $nickname);

        try {
            UserRepository::getInstance()->addUser($user);
        } catch (Exception $e) {
            $errorCode =$e->getMessage();
            if ($e->getCode() == '23505') {
                return $this->render('register', ['messages' => ['If there is user with that email, message with instruction has been send.']]);
            }
            return $this->render('register', ['messages' => ["Unknown error: $errorCode"]]);
        }
        return $this->render('login', ['messages' => ['You\'ve been registered!']]); 
    }

    #[AllowedMethods(['POST', 'GET'])]
    public function logout() 
    { 

        $this->initSession();

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