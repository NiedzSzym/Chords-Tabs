<?php

require_once 'AllowedMethods.php';

function checkRequestAllowed($controller, $methodName) {
    $reflection = new ReflectionMethod($controller, $methodName);
    
    // Pobieramy atrybuty typu AllowedMethods
    $attributes = $reflection->getAttributes(AllowedMethods::class);

    if (!empty($attributes)) {
        $instance = $attributes[0]->newInstance();
        $allowed = $instance->methods;

        // Sprawdzamy czy metoda żądania (GET, POST) jest na liście dozwolonych
        if (!in_array($_SERVER['REQUEST_METHOD'], $allowed)) {
            http_response_code(405); // Kod błędu 405 Method Not Allowed
            echo "Method Not Allowed";
            exit;
        }
    }
}