<?php

class Database {
    private $username;
    private $password;
    private $host;
    private $database;

    public function __construct()
    {
        // Pobieranie danych z pliku .env udostępnionego w Dockerze 
        $this->username = getenv('POSTGRES_USER');
        $this->password = getenv('POSTGRES_PASSWORD');
        $this->host = getenv('POSTGRES_HOST');
        $this->database = getenv('POSTGRES_DB');
    }

    public function connect()
    {
        try {
            $connection = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode"  => "disable"]
            );

            // Ustawienie raportowania błędów jako wyjątków dla lepszej kontroli [cite: 18]
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $connection;
        } catch (PDOException $e) {
            // Globalna obsługa błędu połączenia [cite: 18]
            die("Connection failed: " . $e->getMessage());
        }
    }
}