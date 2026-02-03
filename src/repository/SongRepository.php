<?php

require_once 'Repository.php';
require_once __DIR__ . '/../model/Song.php';

class SongRepository extends Repository
{
    public function getInstruments(): array
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM instrument_types ORDER BY id');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKeys(): array
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM keys ORDER BY id ASC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addSong(Song $song): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO songs (
                name, artist_name, song_text, capo_fret, 
                tempo, time_signature,
                instrument_type_id, tuning_id, key_id, author_id
            )
            VALUES (
                :name, :artist, :text, :capo, 
                :tempo, :time_signature,
                :instrument, :tuning, :key, :author
            )
        ');

        $stmt->bindValue(':name', $song->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':artist', $song->getArtistName(), PDO::PARAM_STR);
        $stmt->bindValue(':text', $song->getSongText(), PDO::PARAM_STR);
        $stmt->bindValue(':capo', $song->getCapoFret(), PDO::PARAM_INT);
        $stmt->bindValue(':tempo', $song->getTempo(), PDO::PARAM_INT);
        $stmt->bindValue(':time_signature', $song->getTimeSignature(), PDO::PARAM_STR);
        $stmt->bindValue(':instrument', $song->getInstrumentTypeId(), PDO::PARAM_INT);
        $stmt->bindValue(':tuning', $song->getTuningId(), PDO::PARAM_INT);
        $stmt->bindValue(':key', $song->getKeyId(), PDO::PARAM_INT);
        $stmt->bindValue(':author', $song->getAuthorId(), PDO::PARAM_INT);

        $stmt->execute();
    }

    public function getSongs(int $userId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, artist_name, tempo, time_signature 
            FROM songs 
            WHERE author_id = :id
            ORDER BY name ASC
        ');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSongById(int $id): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM song_details_view WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $song = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($song == false) {
            return null;
        }

        return $song;
    }
}