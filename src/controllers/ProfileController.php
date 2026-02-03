<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';

class ProfileController extends AppController {
    public function show() {
        $this->initSession();
        
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        $userRepository = new UserRepository();
        $userId = (int)$_SESSION['user_id'];
        $userData = $userRepository->getUserDetailsById($userId);

        return $this->render('profile', ['user' => $userData]);
    }
}