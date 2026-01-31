<?php

require_once __DIR__ . '/../src/controllers/SecurityController.php';

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
        switch ($path) {
            case 'dashboard':
            case 'register':
            case 'logout':
            case 'login':
                $controller = Routing::$routes[$path]['controller'];
                $action = Routing::$routes[$path]['action'];

                $controllerObj = new $controller;
                $controllerObj->$action();
                break;
            default:
                include __DIR__ . '/../public/views/404.html';
                break;
        }
        
    }
}