<?php

class UserEntity
{
    public ?int $id;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $password;
    public string $role;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->role = $data['role'];
    }
}
