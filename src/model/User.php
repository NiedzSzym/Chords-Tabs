<?php

class User {
    private $email;
    private $password;
    private $nickname;
    private $role;

    public function __construct(string $email, string $password, string $nickname, int $role = 2) {
        $this->email = $email;
        $this->password = $password;
        $this->nickname = $nickname;
        $this->role = $role;
    }

    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getNickname(): string { return $this->nickname; }
    public function getRole(): int { return $this->role; }
}