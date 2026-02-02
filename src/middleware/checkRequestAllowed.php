<?php

require_once 'AllowedMethods.php';

function CheckRequestAllowed($controller, $methodName) {
    $reflection = new ReflectionMethod($controller, $methodName);
    
    $attributes = $reflection->getAttributes(AllowedMethods::class);

    if (!empty($attributes)) {
        $instance = $attributes[0]->newInstance();
        $allowed = $instance->methods;

        if (!in_array($_SERVER['REQUEST_METHOD'], $allowed)) {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
    }
}