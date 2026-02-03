<?php

require_once 'Repository.php';
require_once __DIR__.'/../model/Chord.php';

class ChordRepository extends Repository {
    public function getChords(int $userId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT c.*, 
                   it.name as instrument_name, 
                   t.tuning 
            FROM chords c
            JOIN instrument_types it ON c.instrument_type_id = it.id
            JOIN tunings t ON c.tuning_id = t.id
            WHERE c.author_id = :id OR c.author_id IS NULL
            ORDER BY c.name ASC
        ');
        
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addChord(Chord $chord): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO chords (name, chord_diagram, instrument_type_id, tuning_id, author_id)
            VALUES (?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $chord->getName(),
            $chord->getDiagram(),      
            $chord->getInstrumentTypeId(),
            $chord->getTuningId(),
            $chord->getAuthorId() 
        ]);
    }

    public function deleteChord(int $id): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM chords WHERE id = :id
        ');

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getChordById(int $id): ?array {
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

    public function getInstruments(): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM instrument_types ORDER BY id
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTunings(int $instrumentId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM tunings WHERE instrument_type_id = :id
        ');
        $stmt->bindParam(':id', $instrumentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

}