<?php

require_once 'Repository.php'; // Stwórz pustą klasę Repository rozszerzającą Database
require_once __DIR__.'/../model/User.php';

class UserRepository extends Repository {
    private static $instance;
    public static function getInstance() {
        return self::$instance ??= new UserRepository();
    }
    // Pobieranie jednego użytkownika po emailu (np. do logowania)
    public function getUserByEmail(string $email) {
        $stmt = $this->database->connect()->prepare('
            SELECT u.id, u.email, u.password, u.id_role, p.nickname 
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.id_user
            WHERE u.email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function addUser(User $user) {
        $db = $this->database->connect();
        
        try {
            $db->beginTransaction();

            // 1. Dodanie do tabeli users [cite: 24]
            $stmt = $db->prepare('
                INSERT INTO users (email, password, id_role)
                VALUES (?, ?, ?) RETURNING id
            ');

            $stmt->execute([
                $user->getEmail(),
                $user->getPassword(),
                $user->getRole()
            ]);

            $userId = $stmt->fetch()['id'];

            $stmt = $db->prepare('
                INSERT INTO user_profiles (id_user, nickname)
                VALUES (?, ?)
            ');

            $stmt->execute([
                $userId,
                $user->getNickname()
            ]);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}