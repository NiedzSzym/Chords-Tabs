<?php
#[Attribute(Attribute::TARGET_METHOD)]
class Options {
    private $methods;

    public function __construct($methods) {
        $this->methods = is_array($methods) ? $methods : [$methods];
    }

    public function check() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        if (!in_array($requestMethod, $this->methods)) {
            http_response_code(405);
            die("Method $requestMethod not allowed. Allowed methods: " . implode(", ", $this->methods));
        }
    }
}