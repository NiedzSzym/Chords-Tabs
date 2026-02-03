<?php

require_once 'AppController.php';
require_once __DIR__ .'/../repository/SongRepository.php';
require_once __DIR__ . '/../model/Song.php';

class SongController extends AppController {

    private $songRepository;

    public function __construct()
    {
        $this->songRepository = new SongRepository();
    }

    #[AllowedMethods(['GET', 'POST'])]
    public function addSong()
    {
        $this->initSession();
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        if ($this->isGet()) {
            $instruments = $this->songRepository->getInstruments();
            $keys = $this->songRepository->getKeys();
            return $this->render('add_song', [
                'instruments' => $instruments,
                'keys' => $keys
            ]);
        }

        if ($this->isPost()) {
            if (empty($_POST['name']) || empty($_POST['song_text'])) {
                 $instruments = $this->songRepository->getInstruments();
                 $keys = $this->songRepository->getKeys();
                 return $this->render('add_song', [
                    'messages' => ['Song name and lyrics are required.'],
                    'instruments' => $instruments,
                    'keys' => $keys
                 ]);
            }

            $song = new Song(
                $_POST['name'],
                $_POST['artist_name'],
                $_POST['song_text'],
                (int)$_POST['capo_fret'],
                !empty($_POST['tempo']) ? (int)$_POST['tempo'] : 120,
                $_POST['time_signature'] ?? '4/4',
                (int)$_POST['instrument_type_id'],
                (int)$_POST['tuning_id'],
                !empty($_POST['key_id']) ? (int)$_POST['key_id'] : null,
                $_SESSION['user_id']
            );

            try {
                $this->songRepository->addSong($song);
                header("Location: /songs");
                exit;
            } catch (Exception $e) {
                 $instruments = $this->songRepository->getInstruments();
                 $keys = $this->songRepository->getKeys();
                 return $this->render('add_song', [
                    'messages' => ['Database error: ' . $e->getMessage()],
                    'instruments' => $instruments,
                    'keys' => $keys
                 ]);
            }
        }
    }
    #[AllowedMethods(['GET'])]
    public function songs()
    {
        $this->initSession();
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        $songs = $this->songRepository->getSongs($_SESSION['user_id']);
 
        return $this->render('songs', [
            'songs' => $songs,
            'page' => 'songs' 
        ]);
    }

    #[AllowedMethods(['GET'])]
    public function viewSong()
    {
        $this->initSession();
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        if (!isset($_GET['id'])) {
            header("Location: /songs");
            exit;
        }

        $songId = (int)$_GET['id'];
        $song = $this->songRepository->getSongById($songId);

        if (!$song) {
            header("Location: /songs"); 
            exit;
        }


        $safeText = htmlspecialchars($song['song_text']);
        
        $formattedText = preg_replace(
            '/\[(.*?)\]/', 
            '<span class="chord" data-chord="$1">$1</span>', 
            $safeText
        );

        return $this->render('song', [
            'song' => $song,
            'formattedText' => $formattedText,
            'page' => 'songs'
        ]);
    }
}