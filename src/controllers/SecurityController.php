<?php

require_once 'AppController.php';
require_once __DIR__.'/../model/User.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../middleware/AllowedMethods.php';

class SecurityController extends AppController {
    
    public function __construct() {}

    #[AllowedMethods(['POST', 'GET'])]
    public function login() {
        if ($this->isGet()) {
            return $this->renderStandalone('login');
        }

        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            return $this->render('welcome', ['messages' => ['HTTPS is required']]);
        }
        
        if (!$this->verifyCsrfToken()) {
            return $this->render('welcome', ['messages' => ['Session expired (CSRF). Refresh page.']]);
        }

        $this->applyLoginThrottling(); 

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        if (!$this->isValidLoginInput($email)) {
            return $this->render("welcome", ["messages" => ["Invalid input"]]);
        }

        $user = UserRepository::getInstance()->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->incrementLoginFailures();
            return $this->render('welcome', ['messages' => ['Wrong email or password']]);
        }

        $this->handleLoginSuccess($user);
    }

    #[AllowedMethods(['POST', 'GET'])]
    public function register() {
        if ($this->isGet()) {
            $this->generateCsrf();
            return $this->renderStandalone('register');
        }

        if (!$this->verifyCsrfToken()) {
            return $this->renderStandalone('register', ['messages' => ['Session expired. Refresh page.']]);
        }

        $email = $_POST["email"] ?? '';
        $passwordCmd = $_POST["password1"] ?? '';
        $passwordRepeat = $_POST["password2"] ?? '';
        $nickname = $_POST['nickname'] ?? '';

        $validationError = $this->validateRegistrationInput($email, $passwordCmd, $passwordRepeat, $nickname);
        
        if ($validationError) {
            return $this->renderStandalone('register', ['messages' => [$validationError]]);
        }

        try {
            $this->registerNewUser($email, $passwordCmd, $nickname);
            
            return $this->render('welcome', ['messages' => ['Konto utworzone! Zaloguj się w menu.']]);
            
        } catch (Exception $e) {
            if ($e->getCode() == '23505') {
                return $this->renderStandalone('register', ['messages' => ['Taki email jest już zajęty!']]);
            }
            error_log($e->getMessage());
            return $this->renderStandalone('register', ['messages' => ["Wystąpił nieoczekiwany błąd."]]);
        }
    }

    #[AllowedMethods(['POST', 'GET'])]
    public function logout() { 
        $this->initSession();
        $_SESSION = []; 
        if (ini_get("session.use_cookies")) { 
            $params = session_get_cookie_params(); 
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]); 
        } 
        session_unset();    
        session_destroy(); 
        
        header("Location: /"); 
        exit;
    }


    private function verifyCsrfToken(): bool { 
        return isset($_POST['csrf']) && isset($_SESSION['csrf']) && $_POST['csrf'] === $_SESSION['csrf']; 
    }

    private function applyLoginThrottling(): void {
        $failures = $_SESSION['login_failures'] ?? 0;
        if ($failures > 5) { sleep(2); }
    }

    private function incrementLoginFailures(): void { $_SESSION['login_failures'] = ($_SESSION['login_failures'] ?? 0) + 1; }
    
    private function isValidLoginInput(string $email): bool { return strlen($email) <= 100; }

    private function handleLoginSuccess(array $user): void {
        unset($_SESSION['login_failures']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['nickname'] = $user['nickname'];
        $_SESSION['role_id'] = (int)$user['id_role']; 
        $_SESSION['is_logged_in'] = true;
        header("Location: /songs"); 
        exit;
    }
    
    private function validateRegistrationInput(string $email, string $p1, string $p2, string $nick): ?string {
        if (empty($email) || empty($p1) || empty($p2) || empty($nick)) return 'Fill all fields';
        if ($p1 !== $p2) return 'Passwords are not the same';
        if (strlen($p1) < 8) return 'Password is too weak (min 8 chars)';
        if (strlen($email) > 100) return 'Email is too long';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email format!';
        return null;
    }

    private function registerNewUser(string $email, string $password, string $nickname): void {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $user = new User($email, $hashedPassword, $nickname);
        UserRepository::getInstance()->addUser($user);
    }
}