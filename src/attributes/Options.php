<?php
#[Attribute(Attribute::TARGET_METHOD)]
class Options {
    private $methods;

    public function __construct($methods) {
        // Jeśli podano pojedynczy string, zamień go na tablicę
        $this->methods = is_array($methods) ? $methods : [$methods];
    }

    public function check() {
        // Pobieramy aktualną metodę żądania (GET, POST itp.)
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Sprawdzamy, czy aktualna metoda jest na liście dozwolonych
        if (!in_array($requestMethod, $this->methods)) {
            http_response_code(405); // Method Not Allowed
            die("Method $requestMethod not allowed. Allowed methods: " . implode(", ", $this->methods));
        }
    }
}