<?php

require_once __DIR__ . '/../src/controllers/SecurityController.php';
require_once __DIR__ . '/../src/attributes/Options.php';
require_once __DIR__ . '/../src/middleware/CheckRequestAllowed.php';
require_once __DIR__ . '/../src/controllers/DefaultController.php';
require_once __DIR__ . '/../src/controllers/ChordController.php';
require_once __DIR__ . '/../src/controllers/SongController.php';
require_once __DIR__ . '/../src/controllers/ProfileController.php';

class Routing {
    public static $routes = [
        '' => [
            'controller' => 'DefaultController',
            'action' => 'index'
        ],
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
        'library' => [
            'controller' => 'ChordController',
            'action' => 'library'
        ],
        'add-chord' => [
            'controller' => 'ChordController',
            'action' => 'addChord'
        ],
        'delete-chord' => [
            'controller' => 'ChordController',
            'action' => 'deleteChord'
        ],
        'api-get-tunings' => [
            'controller' => 'ChordController',
            'action' => 'getTuningsApi'
        ],
        'songs' => [
            'controller' => 'SongController',
            'action' => 'songs'
        ],
        'add-song' => [
            'controller' => 'SongController',
            'action' => 'addSong'
        ],
        'song' => [
            'controller' => 'SongController',
            'action' => 'viewSong'
        ],
        'profile' => [
            'controller' => 'ProfileController',
            'action' => 'show'
        ]


    ];

    public static function run(string $path) {
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