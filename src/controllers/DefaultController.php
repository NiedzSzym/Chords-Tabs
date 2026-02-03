<?php

require_once 'AppController.php';

class DefaultController extends AppController {

    public function index() {
        $this->initSession();

        if (!empty($_SESSION['user_id'])) {
            header("Location: /songs");
            exit;
        }

        return $this->render('welcome');
    }
}