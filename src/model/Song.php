<?php

class Song
{
    private $id;
    private $name;
    private $artistName;
    private $songText;
    private $capoFret;
    private $tempo;
    private $timeSignature;
    private $instrumentTypeId;
    private $tuningId;
    private $keyId;
    private $authorId;


    public function __construct(
        string $name,
        string $artistName,
        string $songText,
        int $capoFret,
        int $tempo,
        string $timeSignature,
        int $instrumentTypeId,
        int $tuningId,
        ?int $keyId,
        int $authorId,
        int $id = null
    ) {
        $this->name = $name;
        $this->artistName = $artistName;
        $this->songText = $songText;
        $this->capoFret = $capoFret;
        $this->tempo = $tempo;
        $this->timeSignature = $timeSignature;
        $this->instrumentTypeId = $instrumentTypeId;
        $this->tuningId = $tuningId;
        $this->keyId = $keyId;
        $this->authorId = $authorId;
        $this->id = $id;
    }


    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getArtistName(): string { return $this->artistName; }
    public function getSongText(): string { return $this->songText; }
    public function getCapoFret(): int { return $this->capoFret; }
    public function getTempo(): int { return $this->tempo; }
    public function getTimeSignature(): string { return $this->timeSignature; }
    public function getInstrumentTypeId(): int { return $this->instrumentTypeId; }
    public function getTuningId(): int { return $this->tuningId; }
    public function getKeyId(): ?int { return $this->keyId; }
    public function getAuthorId(): int { return $this->authorId; }


    public function setName(string $name): void { $this->name = $name; }

}