<?php

require_once __DIR__ . '/../src/controllers/SecurityController.php';
require_once __DIR__ . '/../src/attributes/Options.php';

class Routing {
    public static $routes = [
        'login' => [
            'controller' => "SecurityController",
            'action' => 'login'
        ],
        'logout' => [
            'controller' => "SecurityController",
            'action' => 'logout'
        ],
        'register' => [
            'controller' => "SecurityController",
            'action' => 'register'
        ],
        'dashboard' => [
            'controller' => "SecurityController",
            'action' => 'dashboard'
        ]

    ];

    public static function run(string $path) {
        // Sprawdzamy czy ścieżka istnieje
        if (!array_key_exists($path, self::$routes)) {
             include __DIR__ . '/../public/views/404.html';
             return;
        }

        $controllerName = self::$routes[$path]['controller'];
        $action = self::$routes[$path]['action'];

        $object = new $controllerName;

        checkRequestAllowed($object, $action);

        $object->$action();
    }
}