<?php


require_once __DIR__.'/../../database/database.php';

class Repository {
    protected $database;

    public function __construct() {
        $this->database = new Database();
    }
}