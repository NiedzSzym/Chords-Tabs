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

        // GET: Wyświetlamy formularz i przekazujemy listę instrumentów
        if ($this->isGet()) {
            $instruments = $this->chordRepository->getInstruments();
            return $this->render('add_chord', ['instruments' => $instruments]);
        }

        // POST: Zapisujemy
        if ($this->isPost()) {
            $name = $_POST['name'];
            $instrumentId = (int)$_POST['instrument_id'];
            $tuningId = (int)$_POST['tuning_id'];

            // Pobieramy liczbę strun z ukrytego inputa (obsłużymy to w JS)
            $stringCount = (int)$_POST['string_count']; 

            // Dynamiczne zbieranie strun (np. od 1 do 4 dla Ukulele, od 1 do 6 dla Gitary)
            $diagramArray = [];
            // Pętla odwrócona, bo inputy idą od najgrubszej (stringX) do najcieńszej (string1)
            // Ale w bazie chcemy: [Bass, ..., Treble]
            for ($i = $stringCount; $i >= 1; $i--) {
                $diagramArray[] = (int)($_POST["string$i"] ?? 0);
            }

            // Walidacja (możesz tu dodać validateChordInput)
            // ...

            $jsonDiagram = json_encode($diagramArray);
            $authorId = $_SESSION['user_id'];

            $this->chordRepository->addChord($name, $jsonDiagram, $authorId, $instrumentId, $tuningId);

            header("Location: /library");
            exit;
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
        
        // 1. Pobieramy akord, żeby sprawdzić kto jest właścicielem
        $chord = $this->chordRepository->getChordById($id);
        
        // 2. SPRAWDZENIE BEZPIECZEŃSTWA:
        // Jeśli akord nie istnieje LUB id autora jest inne niż id zalogowanego
        // (To automatycznie chroni akordy systemowe, bo tam author_id jest NULL, a user_id jest liczbą)
        if (!$chord || $chord['author_id'] !== $_SESSION['user_id']) {
            // Możesz tu dodać return z błędem, np. "Nie masz uprawnień"
            header("Location: /library"); 
            exit;
        }

        // 3. Jeśli przeszło weryfikację -> usuwamy
        $this->chordRepository->deleteChord($id);

        header("Location: /library");
        exit;
    }

    #[AllowedMethods(['POST'])]
    public function getTuningsApi() {
        // Odczytujemy JSON z ciała żądania (fetch sends raw body)
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            $instrumentId = $decoded['instrument_id'];

            $tunings = $this->chordRepository->getTunings($instrumentId);

            header('Content-Type: application/json');
            echo json_encode($tunings);
            exit; // Ważne, żeby nie renderować widoku
        }
    }

    private function validateChordInput(string $name, array $strings): ?string 
    {
        // 1. Sprawdź nazwę
        if (empty($name)) {
            return "Nazwa akordu jest wymagana.";
        }
        
        // Tutaj zabezpieczamy się przed błędem "value too long"
        if (strlen($name) > 50) {
            return "Nazwa jest za długa! (Max 50 znaków)";
        }

        // 2. Sprawdź progi (czy mieszczą się w zakresie -1 do 24)
        foreach ($strings as $fret) {
            if (!is_numeric($fret) || $fret < -1 || $fret > 24) {
                return "Nieprawidłowa wartość progu.";
            }
        }

        return null; // Brak błędów
    }

    
}