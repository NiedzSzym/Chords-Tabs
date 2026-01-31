<?php

require_once 'AppController.php';
require_once __DIR__.'/../model/User.php';
require_once __DIR__.'/../repository/UserRepository.php';

class SecurityController extends AppController {
    public function __construct() {
    }

    public function login() {
        if($this->isGet()) {
            return $this->render("login");
        } 

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        $userRepository = new UserRepository();
        $user = $userRepository->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_firstname'] = $user['firstname'];
        $_SESSION['is_logged_in'] = true;

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/dashboard");
        var_dump($email, $password);
        exit;
    }

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
            return $this->render('register', ['messages' => ['Błędny format adresu e-mail!']]);
        }


        $user = new User($email, $password1, $nickname);
        $userRepository = new UserRepository();

        try {
            $userRepository->addUser($user);
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

    public function dashboard() {
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        header("Location: /login");
        exit;
    }
    echo "Witaj, Twój email to: " . $_SESSION['user_email'];
}
}