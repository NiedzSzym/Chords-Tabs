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
    public function addChord()
    {
        $this->initSession();
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        // 1. GET: Wyświetlenie formularza
        if ($this->isGet()) {
            return $this->render('add_chord');
        }

        if ($this->isPost()) {
            $name = $_POST['name'] ?? '';
            
            // Pobieramy dane strun
            $diagramArray = [
                (int)($_POST['string6'] ?? 0),
                (int)($_POST['string5'] ?? 0),
                (int)($_POST['string4'] ?? 0),
                (int)($_POST['string3'] ?? 0),
                (int)($_POST['string2'] ?? 0),
                (int)($_POST['string1'] ?? 0)
            ];

            // --- WALIDACJA ---
            $error = $this->validateChordInput($name, $diagramArray);
            
            if ($error) {
                // Jeśli jest błąd, wyświetlamy formularz ponownie z komunikatem
                return $this->render('add_chord', [
                    'messages' => [$error],
                    // Opcjonalnie: można przekazać wpisane dane z powrotem, żeby user nie musiał wpisywać od nowa
                    // 'old_name' => $name 
                ]);
            }
            // -----------------

            $jsonDiagram = json_encode($diagramArray);
            $authorId = $_SESSION['user_id'];

            try {
                $this->chordRepository->addChord($name, $jsonDiagram, $authorId);
                header("Location: /library");
                exit;
            } catch (Exception $e) {
                // Łapiemy ewentualne inne błędy bazy
                return $this->render('add_chord', ['messages' => ['Wystąpił błąd podczas zapisu.']]);
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