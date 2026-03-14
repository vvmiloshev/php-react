<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Models\User;

class AuthController extends AppController
{
    private User $users;

    public function __construct(Database $database)
    {
        parent::__construct($database);

        $this->users = new User($database);
    }

    public function login(Request $request): void
    {
        $email = trim((string) ($request->input('email') ?? ''));
        $password = (string) ($request->input('password') ?? '');

        if ($email === '' || $password === '') {
            $this->error('Email and password are required.', 422);
            return;
        }

        $user = $this->users->findByEmail($email);

        if ($user === false) {
            $this->error('Invalid credentials.', 401);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            $this->error('Invalid credentials.', 401);
            return;
        }

        unset($user['password']);

        $this->success($user, 'Login successful.');
    }

    public function register(Request $request): void
    {
        $name = trim((string) ($request->input('name') ?? ''));
        $email = trim((string) ($request->input('email') ?? ''));
        $password = (string) ($request->input('password') ?? '');
        $passwordConfirmation = (string) ($request->input('password_confirmation') ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $this->error('Name, email and password are required.', 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.', 422);
            return;
        }

        if (mb_strlen($password) < 6) {
            $this->error('Password must be at least 6 characters long.', 422);
            return;
        }

        if ($passwordConfirmation !== '' && $password !== $passwordConfirmation) {
            $this->error('Password confirmation does not match.', 422);
            return;
        }

        $existingUser = $this->users->findByEmail($email);

        if ($existingUser !== false) {
            $this->error('User with this email already exists.', 409);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->users->create($name, $email, $passwordHash);
        $user = $this->users->findById($userId);

        if ($user === false) {
            $this->error('User created, but could not be loaded.', 500);
            return;
        }

        unset($user['password']);

        $this->created($user, 'User registered successfully.');
    }
}