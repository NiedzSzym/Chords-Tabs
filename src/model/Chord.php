<?php

class Chord {
    private $id;
    private $name;
    private $chordDiagram;
    private $authorId;


    public function __construct(string $name, string $chordDiagram, ?int $authorId = null) {
        $this->name = $name;
        $this->chordDiagram = $chordDiagram;
        $this->authorId = $authorId;
    }

    public function getName(): string { return $this->name; }
    public function getChordDiagram(): string { return $this->chordDiagram; }
    public function getAuthorId(): ?int { return $this->authorId; }
}