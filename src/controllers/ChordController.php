<?php

require_once 'AppController.php';
require_once __DIR__ .'/../repository/ChordRepository.php';

class ChordController extends AppController {

    private $chordRepository;

    public function __construct()
    {
        $this->chordRepository = new ChordRepository();
    }

    #[AllowedMethods(['GET'])]
    public function library()
    {
        $this->initSession();
        
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
        $userId = $_SESSION['user_id'];

        $chords = $this->chordRepository->getChords($userId);

        return $this->render('library', ['chords' => $chords]);
    }
}