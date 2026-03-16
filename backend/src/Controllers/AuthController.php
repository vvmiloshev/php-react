<?php

declare(strict_types=1);

namespace Src\Controllers;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Src\Core\Database;
use Src\Core\Request;
use Src\Models\AuthToken;
use Src\Models\User;

class AuthController extends AppController
{
    private User $users;
    private AuthToken $authTokens;

    public function __construct(Database $database)
    {
        parent::__construct($database);

        $this->users = new User($database);
        $this->authTokens = new AuthToken($database);
    }

    public function login(Request $request): void
    {
        $email = trim((string) ($request->input('email') ?? ''));
        $password = (string) ($request->input('password') ?? '');

        if ($email === '' || $password === '') {
            $this->error('Email and password are required.', 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.', 422);
            return;
        }

        $user = $this->users->findByEmail($email);

        if ($user === false || !password_verify($password, $user['password'])) {
            $this->error('Invalid credentials.', 401);
            return;
        }

        $this->authTokens->deleteExpired();

        try {
            $plainToken = bin2hex(random_bytes(32));
        } catch (Exception $exception) {
            $this->error('Could not generate token.', 500);
            return;
        }

        $tokenHash = hash('sha256', $plainToken);

        $expiresAt = (new DateTimeImmutable())
            ->add(new DateInterval('P7D'))
            ->format('Y-m-d H:i:s');

        $this->authTokens->create((int) $user['id'], $tokenHash, $expiresAt);

        unset($user['password']);

        $this->success([
            'token' => $plainToken,
            'user' => $user,
            'expires_at' => $expiresAt,
        ], 'Login successful.');
    }

    public function register(Request $request): void
    {
        $name = trim((string) ($request->input('name') ?? ''));
        $email = trim((string) ($request->input('email') ?? ''));
        $password = (string) ($request->input('password') ?? '');
        $passwordConfirmation = (string) ($request->input('password_confirmation') ?? '');

        if ($name === '' || $email === '' || $password === '' || $passwordConfirmation === '') {
            $this->error('Name, email, password and password confirmation are required.', 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.', 422);
            return;
        }

        $passwordValidationError = $this->validatePassword($password);

        if ($passwordValidationError !== null) {
            $this->error($passwordValidationError, 422);
            return;
        }

        if ($password !== $passwordConfirmation) {
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

        $this->created([
            'user' => $user,
        ], 'User registered successfully.');
    }

    public function me(Request $request): void
    {
        $user = $this->authenticate($request);

        if ($user === null) {
            return;
        }

        $this->success([
            'user' => $user,
        ]);
    }

    public function logout(Request $request): void
    {
        $token = $this->extractBearerToken($request);

        if ($token === null) {
            $this->error('Unauthenticated.', 401);
            return;
        }

        $tokenHash = hash('sha256', $token);
        $this->authTokens->deleteByTokenHash($tokenHash);

        $this->success([], 'Logout successful.');
    }

    protected function authenticate(Request $request): ?array
    {
        $token = $this->extractBearerToken($request);

        if ($token === null) {
            $this->error('Unauthenticated.', 401);
            return null;
        }

        $tokenHash = hash('sha256', $token);
        $session = $this->authTokens->findActiveByTokenHash($tokenHash);

        if ($session === false) {
            $this->error('Invalid or expired token.', 401);
            return null;
        }

        return [
            'id' => (int) $session['id'],
            'name' => (string) $session['name'],
            'email' => (string) $session['email'],
        ];
    }

    private function extractBearerToken(Request $request): ?string
    {
        $authorizationHeader = $request->header('Authorization');

        if ($authorizationHeader === null) {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $authorizationHeader, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function validatePassword(string $password): ?string
    {
        if (mb_strlen($password) < 6) {
            return 'Password must be at least 6 characters long.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain at least one special character.';
        }

        return null;
    }
}