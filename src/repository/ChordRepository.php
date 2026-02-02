<?php

require_once 'Repository.php';
require_once __DIR__.'/../model/Chord.php';

class ChordRepository extends Repository
{
    public function getChords(int $userId): array
    {

        $stmt = $this->database->connect()->prepare('
            SELECT * FROM chords 
            WHERE author_id = :id OR author_id IS NULL
            ORDER BY name ASC
        ');

        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}