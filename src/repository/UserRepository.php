<?php

require_once 'Repository.php';
require_once __DIR__.'/../model/User.php';

class UserRepository extends Repository {
    private static $instance;
    public static function getInstance() {
        return self::$instance ??= new UserRepository();
    }

    public function getUserByEmail(string $email) {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM auth_users_view WHERE email = :email
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

    public function getUserDetailsById(int $id) {
        // Używamy JOIN, aby połączyć tabelę użytkowników z profilami i rolami
        // Jeśli masz widok 'auth_users_view', możesz go użyć, ale JOIN jest pewniejszy na start
        $stmt = $this->database->connect()->prepare('
            SELECT 
                u.email, 
                u.id_role, 
                p.nickname, 
                p.bio 
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.id_user
            WHERE u.id = :id
        ');
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jeśli profil nie istnieje (np. stary user), zwracamy same dane z users
        if (!$user) {
            return null;
        }
        
        return $user;
    }
}