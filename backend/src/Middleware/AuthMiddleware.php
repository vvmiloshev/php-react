<?php

declare(strict_types=1);

namespace Src\Middleware;

use Src\Core\Database;
use Src\Core\Request;
use Src\Core\Response;
use Src\Models\AuthToken;
use Src\Models\User;

class AuthMiddleware
{
    private AuthToken $authTokens;
    private User $users;

    public function __construct(
        private Database $database,
        private Response $response
    ) {
        $this->authTokens = new AuthToken($this->database);
        $this->users = new User($this->database);
    }

    public function handle(Request $request): void
    {
        $token = $request->bearerToken();

        if (!$token) {
            $this->response->json([
                'message' => 'Unauthorized',
            ], 401);
            exit;
        }

        $tokenHash = hash('sha256', $token);
        $authToken = $this->authTokens->findActiveByTokenHash($tokenHash);

        if (!$authToken) {
            $this->response->json([
                'message' => 'Unauthorized',
            ], 401);
            exit;
        }

        $user = $this->users->findById((int) $authToken['user_id']);

        if (!$user) {
            $this->response->json([
                'message' => 'Unauthorized',
            ], 401);
            exit;
        }

        $request->setUser($user);
    }
}