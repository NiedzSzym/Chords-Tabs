<?php

require_once 'AppController.php';
require_once __DIR__.'/../middleware/AllowedMethods.php';

class DashboardController extends AppController {

    #[AllowedMethods(['GET'])]
    public function index() {
        $this->initSession();
        $this->generateCsrf(); 
        
        if (empty($_SESSION['user_id'])) {
            return $this->render('welcome');
        }

        return $this->render('dashboard');
    }
}