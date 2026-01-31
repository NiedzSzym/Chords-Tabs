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
        $response = $this->handleInitialRequest('login');
        if ($response) {
            return $response;
        }
        $this->applyLoginThrottling(); 

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        if (!$this->isValidLoginInput($email)) {
            return $this->render("login", ["messages" => ["Invalid input"]]);
        }

        $user = UserRepository::getInstance()->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->incrementLoginFailures();
            return $this->render('login', ['messages' => ['Wrong email or password']]);
        }

        $this->handleLoginSuccess($user);
    }
    
    #[AllowedMethods(['POST', 'GET'])]
    public function register() {
        $response = $this->handleInitialRequest('register');
        if ($response) {
            return $response; 
        }
        $email = $_POST["email"] ?? '';
        $passwordCmd = $_POST["password1"] ?? '';
        $passwordRepeat = $_POST["password2"] ?? '';
        $nickname = $_POST['nickname'] ?? '';

        $validationError = $this->validateRegistrationInput($email, $passwordCmd, $passwordRepeat, $nickname);
        if ($validationError) {
            return $this->render('register', ['messages' => [$validationError]]);
        }

        try {
            $this->registerNewUser($email, $passwordCmd, $nickname);
            
            return $this->render('login', ['messages' => ['Registration successful! Please login.']]);
            
        } catch (Exception $e) {
            return $this->handleRegistrationError($e);
        }
    }

    #[AllowedMethods(['POST', 'GET'])]
    public function logout() { 
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
    
        session_unset();    
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

    private function verifyCsrfToken(): bool { return isset($_POST['csrf']) && $_POST['csrf'] === ($_SESSION['csrf'] ?? ''); }

    private function applyLoginThrottling(): void {
        $failures = $_SESSION['login_failures'] ?? 0;
        if ($failures > 5) {
            sleep(2);
        }
    }

    private function incrementLoginFailures(): void {$_SESSION['login_failures'] = ($_SESSION['login_failures'] ?? 0) + 1;}
    
    private function isValidLoginInput(string $email): bool { return strlen($email) <= 100;}

    private function handleLoginSuccess(array $user): void {
        unset($_SESSION['login_failures']);
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_logged_in'] = true;
        header("Location: /dashboard");
        exit;
    }
    
    private function validateRegistrationInput(string $email, string $p1, string $p2, string $nick): ?string {
        if (empty($email) || empty($p1) || empty($p2) || empty($nick)) {
            return 'Fill all fields';
        }
        if ($p1 !== $p2) {
            return 'Passwords are not the same';
        }
        if (strlen($p1) < 8) {
            return 'Password is too weak (min 8 chars)';
        }
        if (strlen($email) > 100) {
            return 'Email is too long';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email format!';
        }

        return null;
    }

    private function registerNewUser(string $email, string $password, string $nickname): void 
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $user = new User($email, $hashedPassword, $nickname);
        UserRepository::getInstance()->addUser($user);
    }

    private function handleRegistrationError(Exception $e) 
    {
        if ($e->getCode() == '23505') {
            return $this->render('register', ['messages' => ['Email already exists!']]);
        }

        error_log($e->getMessage());
        return $this->render('register', ['messages' => ["An unexpected error occurred."]]);
    }

    private function handleInitialRequest(string $viewName)
    {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
            $this->render('login', ['messages' => ['HTTPS is required']]);
            return false;
        }

        if ($this->isGet()) {
            $this->generateCsrf();
            return $this->render($viewName, ['csrf' => $_SESSION['csrf']]);
        }

        if (!$this->verifyCsrfToken()) {
            return $this->render($viewName, ['messages' => ['Session error (CSRF).']]);
        }

        return null;
    }
}