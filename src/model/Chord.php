<?php
// src/model/Chord.php

class Chord {
    private $id;
    private $name;
    private $chordDiagram; // To będzie nasz JSON string
    private $authorId;

    // Konstruktor może przyjmować dane przy tworzeniu obiektu
    public function __construct(string $name, string $chordDiagram, ?int $authorId = null) {
        $this->name = $name;
        $this->chordDiagram = $chordDiagram;
        $this->authorId = $authorId;
    }

    // Gettery
    public function getName(): string { return $this->name; }
    public function getChordDiagram(): string { return $this->chordDiagram; }
    public function getAuthorId(): ?int { return $this->authorId; }
}