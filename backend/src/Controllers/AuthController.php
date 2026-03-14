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
            $this->json([
                'message' => 'Email and password are required.',
            ], 422);

            return;
        }

        $user = $this->users->findByEmail($email);

        if ($user === false) {
            $this->json([
                'message' => 'Invalid credentials.',
            ], 401);

            return;
        }

        if (!password_verify($password, $user['password'])) {
            $this->json([
                'message' => 'Invalid credentials.',
            ], 401);

            return;
        }

        unset($user['password']);

        $this->json([
            'message' => 'Login successful.',
            'data' => $user,
        ]);
    }

    public function register(Request $request): void
    {
        $name = trim((string) ($request->input('name') ?? ''));
        $email = trim((string) ($request->input('email') ?? ''));
        $password = (string) ($request->input('password') ?? '');
        $passwordConfirmation = (string) ($request->input('password_confirmation') ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $this->json([
                'message' => 'Name, email and password are required.',
            ], 422);

            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'message' => 'Invalid email address.',
            ], 422);

            return;
        }

        if (mb_strlen($password) < 6) {
            $this->json([
                'message' => 'Password must be at least 6 characters long.',
            ], 422);

            return;
        }

        if ($passwordConfirmation !== '' && $password !== $passwordConfirmation) {
            $this->json([
                'message' => 'Password confirmation does not match.',
            ], 422);

            return;
        }

        $existingUser = $this->users->findByEmail($email);

        if ($existingUser !== false) {
            $this->json([
                'message' => 'User with this email already exists.',
            ], 409);

            return;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $userId = $this->users->create($name, $email, $passwordHash);
        $user = $this->users->findById($userId);

        if ($user === false) {
            $this->json([
                'message' => 'User created, but could not be loaded.',
            ], 500);

            return;
        }

        unset($user['password']);

        $this->json([
            'message' => 'User registered successfully.',
            'data' => $user,
        ], 201);
    }
}