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

    #[AllowedMethods(['GET', 'POST'])]
    public function addChord(){
        $this->initSession();
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        if ($this->isGet()) {
            $instruments = $this->chordRepository->getInstruments();
            return $this->render('add_chord', ['instruments' => $instruments]);
        }

        if ($this->isPost()) {
            $name = $_POST['name'];
            $instrumentId = (int)$_POST['instrument_id'];
            $tuningId = (int)$_POST['tuning_id'];

            $stringCount = (int)$_POST['string_count']; 

            $diagramArray = [];
            for ($i = $stringCount; $i >= 1; $i--) {
                $diagramArray[] = (int)($_POST["string$i"] ?? 0);
            }

            $error = $this->validateChordInput($name, $diagramArray);

            if ($error) {
                $instruments = $this->chordRepository->getInstruments();
                
                return $this->render('add_chord', [
                    'messages' => [$error],
                    'instruments' => $instruments
                ]);
            }

            $jsonDiagram = json_encode($diagramArray);
            $authorId = $_SESSION['user_id'];
            if (isset($_SESSION['role_id']) && $_SESSION['role_id'] === 1 && !empty($_POST['is_global'])) {
                    $authorId = null;
                $this->chordRepository->addChord($name, $jsonDiagram, $authorId, $instrumentId, $tuningId);
                header("Location: /library");
                exit;
            }
        }
    }

    #[AllowedMethods(['POST'])]
    public function deleteChord() {
        $this->initSession();
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        $id = $_POST['id'];

        $chord = $this->chordRepository->getChordById($id);
        

        if (!$chord || $chord['author_id'] !== $_SESSION['user_id']) {
            header("Location: /library"); 
            exit;
        }

        $this->chordRepository->deleteChord($id);

        header("Location: /library");
        exit;
    }

    #[AllowedMethods(['POST'])]
    public function getTuningsApi() {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            $instrumentId = $decoded['instrument_id'];

            $tunings = $this->chordRepository->getTunings($instrumentId);

            header('Content-Type: application/json');
            echo json_encode($tunings);
            exit;
        }
    }

    private function validateChordInput(string $name, array $strings): ?string {
        if (empty($name)) {
            return "Nazwa akordu jest wymagana.";
        }

        if (strlen($name) > 50) {
            return "Nazwa jest za długa! (Max 50 znaków)";
        }

        foreach ($strings as $fret) {
            if (!is_numeric($fret) || $fret < -1 || $fret > 24) {
                return "Nieprawidłowa wartość progu.";
            }
        }

        return null; 
    }

    
}