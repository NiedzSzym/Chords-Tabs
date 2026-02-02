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

    public function addChord(string $name, string $diagramData, int $authorId): void {
        // author_id nie jest NULL, więc to będzie akord prywatny
        $stmt = $this->database->connect()->prepare('
            INSERT INTO chords (name, chord_diagram, instrument_type_id, tuning_id, author_id)
            VALUES (:name, :diagram, 1, 1, :author)
        ');

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':diagram', $diagramData, PDO::PARAM_STR);
        $stmt->bindParam(':author', $authorId, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function deleteChord(int $id): void
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM chords WHERE id = :id
        ');

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getChordById(int $id): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM chords WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $chord = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($chord == false) {
            return null;
        }

        return $chord;
    }

}