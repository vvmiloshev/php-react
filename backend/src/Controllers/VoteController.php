<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Models\Vote;

class VoteController extends AppController
{
    private Vote $votes;

    public function __construct(Database $database)
    {
        parent::__construct($database);

        $this->votes = new Vote($database);
    }

    public function store(Request $request): void
    {
        $photoId = (int) ($request->input('photo_id') ?? 0);
        $userId = (int) ($request->input('user_id') ?? 0);
        $value = (int) ($request->input('value') ?? 0);

        if ($photoId <= 0) {
            $this->json([
                'message' => 'Valid photo_id is required.',
            ], 422);

            return;
        }

        if ($userId <= 0) {
            $this->json([
                'message' => 'Valid user_id is required.',
            ], 422);

            return;
        }

        if (!in_array($value, [1, -1], true)) {
            $this->json([
                'message' => 'Vote value must be 1 or -1.',
            ], 422);

            return;
        }

        $existingVote = $this->votes->findUserVoteForPhoto($photoId, $userId);

        if ($existingVote !== false) {
            $updated = $this->votes->updateValue(
                (int) $existingVote['id'],
                $value
            );

            if ($updated === false) {
                $this->json([
                    'message' => 'Vote could not be updated.',
                ], 500);

                return;
            }

            $this->json([
                'message' => 'Vote updated successfully.',
                'data' => $this->votes->findById((int) $existingVote['id']),
            ]);

            return;
        }

        $voteId = $this->votes->create($photoId, $userId, $value);

        $this->json([
            'message' => 'Vote created successfully.',
            'data' => $this->votes->findById($voteId),
        ], 201);
    }

    public function remove(Request $request): void
    {
        $photoId = (int) ($request->input('photo_id') ?? 0);
        $userId = (int) ($request->input('user_id') ?? 0);

        if ($photoId <= 0) {
            $this->json([
                'message' => 'Valid photo_id is required.',
            ], 422);

            return;
        }

        if ($userId <= 0) {
            $this->json([
                'message' => 'Valid user_id is required.',
            ], 422);

            return;
        }

        $existingVote = $this->votes->findUserVoteForPhoto($photoId, $userId);

        if ($existingVote === false) {
            $this->json([
                'message' => 'Vote not found.',
            ], 404);

            return;
        }

        $deleted = $this->votes->deleteById((int) $existingVote['id']);

        if ($deleted === false) {
            $this->json([
                'message' => 'Vote could not be removed.',
            ], 500);

            return;
        }

        $this->json([
            'message' => 'Vote removed successfully.',
        ]);
    }
}