<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;
use PDO;
use Throwable;

class Poll
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->pdo();
    }

    public function createWithOptions(int $userId, string $question, array $options): int
    {
        try {
            $this->pdo->beginTransaction();

            $pollStatement = $this->pdo->prepare(
                'INSERT INTO polls (user_id, question, status) VALUES (:user_id, :question, :status)'
            );

            $pollStatement->execute([
                'user_id' => $userId,
                'question' => $question,
                'status' => 'inactive',
            ]);

            $pollId = (int) $this->pdo->lastInsertId();

            $optionStatement = $this->pdo->prepare(
                'INSERT INTO poll_options (poll_id, answer_text, sort_order)
                 VALUES (:poll_id, :answer_text, :sort_order)'
            );

            foreach ($options as $index => $option) {
                $optionStatement->execute([
                    'poll_id' => $pollId,
                    'answer_text' => $option,
                    'sort_order' => $index + 1,
                ]);
            }

            $this->pdo->commit();

            return $pollId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT * FROM polls ORDER BY id DESC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM polls WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $poll = $statement->fetch();

        return $poll ?: null;
    }

    public function findActive(): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM polls WHERE status = :status ORDER BY id DESC LIMIT 1'
        );

        $statement->execute([
            'status' => 'active',
        ]);

        $poll = $statement->fetch();

        return $poll ?: null;
    }

    public function findClosed(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM polls WHERE status = :status ORDER BY closed_at DESC, id DESC'
        );

        $statement->execute([
            'status' => 'closed',
        ]);

        return $statement->fetchAll();
    }

    public function findInactiveByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM polls WHERE user_id = :user_id AND status = :status ORDER BY id DESC'
        );

        $statement->execute([
            'user_id' => $userId,
            'status' => 'inactive',
        ]);

        return $statement->fetchAll();
    }

    public function activate(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $poll = $this->findByIdForUpdate($id);

            if ($poll === null) {
                $this->pdo->rollBack();
                return false;
            }

            if ($poll['status'] === 'closed') {
                $this->pdo->rollBack();
                return false;
            }

            if ($poll['status'] === 'active') {
                $this->pdo->rollBack();
                return false;
            }

            $currentActive = $this->findActiveForUpdate();

            if ($currentActive !== null && (int) $currentActive['id'] !== $id) {
                $closeStatement = $this->pdo->prepare(
                    'UPDATE polls
                     SET status = :status,
                         closed_at = NOW(),
                         updated_at = NOW()
                     WHERE id = :id'
                );

                $closeStatement->execute([
                    'status' => 'closed',
                    'id' => (int) $currentActive['id'],
                ]);
            }

            $activateStatement = $this->pdo->prepare(
                'UPDATE polls
                 SET status = :status,
                     activated_at = NOW(),
                     closed_at = NULL,
                     updated_at = NOW()
                 WHERE id = :id'
            );

            $activateStatement->execute([
                'status' => 'active',
                'id' => $id,
            ]);

            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return false;
        }
    }

    public function close(int $id): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE polls
             SET status = :status,
                 closed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id AND status = :current_status'
        );

        $statement->execute([
            'status' => 'closed',
            'id' => $id,
            'current_status' => 'active',
        ]);

        return $statement->rowCount() > 0;
    }

    public function exists(int $id): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM polls WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function countVotes(int $pollId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM poll_votes WHERE poll_id = :poll_id'
        );

        $statement->execute([
            'poll_id' => $pollId,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function findResults(int $pollId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                po.id,
                po.answer_text,
                po.sort_order,
                COUNT(pv.id) AS votes
             FROM poll_options po
             LEFT JOIN poll_votes pv
                ON pv.poll_option_id = po.id
             WHERE po.poll_id = :poll_id
             GROUP BY po.id, po.answer_text, po.sort_order
             ORDER BY po.sort_order ASC, po.id ASC'
        );

        $statement->execute([
            'poll_id' => $pollId,
        ]);

        return $statement->fetchAll();
    }

    private function findByIdForUpdate(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM polls WHERE id = :id LIMIT 1 FOR UPDATE'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $poll = $statement->fetch();

        return $poll ?: null;
    }

    private function findActiveForUpdate(): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM polls WHERE status = :status ORDER BY id DESC LIMIT 1 FOR UPDATE'
        );

        $statement->execute([
            'status' => 'active',
        ]);

        $poll = $statement->fetch();

        return $poll ?: null;
    }

    public function findWithOptions(int $pollId): ?array
    {
        $pollStatement = $this->pdo->prepare(
            'SELECT id, user_id, question, status, activated_at, closed_at, created_at, updated_at
         FROM polls
         WHERE id = :id
         LIMIT 1'
        );

        $pollStatement->execute([
            'id' => $pollId,
        ]);

        $poll = $pollStatement->fetch();

        if (!$poll) {
            return null;
        }

        $answersStatement = $this->pdo->prepare(
            'SELECT id, poll_id, answer_text, sort_order, created_at
         FROM poll_options
         WHERE poll_id = :poll_id
         ORDER BY sort_order ASC, id ASC'
        );

        $answersStatement->execute([
            'poll_id' => $pollId,
        ]);

        $poll['options'] = $answersStatement->fetchAll();

        return $poll;
    }

    public function updateWithOptions(int $pollId, string $question, array $options): ?array
    {
        $this->pdo->beginTransaction();

        try {
            $updatePollStatement = $this->pdo->prepare(
                'UPDATE polls
             SET question = :question, updated_at = NOW()
             WHERE id = :id'
            );

            $updatePollStatement->execute([
                'id' => $pollId,
                'question' => $question,
            ]);

            $existingOptionsStatement = $this->pdo->prepare(
                'SELECT id, answer_text, sort_order
             FROM poll_options
             WHERE poll_id = :poll_id'
            );

            $existingOptionsStatement->execute([
                'poll_id' => $pollId,
            ]);

            $existingOptions = $existingOptionsStatement->fetchAll();

            $existingById = [];

            foreach ($existingOptions as $existingOption) {
                $existingById[(int) $existingOption['id']] = $existingOption;
            }

            $keptIds = [];

            $updateOptionStatement = $this->pdo->prepare(
                'UPDATE poll_options
             SET answer_text = :answer_text,
                 sort_order = :sort_order
             WHERE id = :id
               AND poll_id = :poll_id'
            );

            $insertOptionStatement = $this->pdo->prepare(
                'INSERT INTO poll_options (poll_id, answer_text, sort_order, created_at)
             VALUES (:poll_id, :answer_text, :sort_order, NOW())'
            );

            foreach ($options as $index => $option) {
                $optionId = isset($option['id']) && $option['id'] !== null
                    ? (int) $option['id']
                    : null;

                $answerText = (string) $option['answer_text'];
                $sortOrder = $index + 1;

                if ($optionId !== null && isset($existingById[$optionId])) {
                    $updateOptionStatement->execute([
                        'id' => $optionId,
                        'poll_id' => $pollId,
                        'answer_text' => $answerText,
                        'sort_order' => $sortOrder,
                    ]);

                    $keptIds[] = $optionId;
                    continue;
                }

                $insertOptionStatement->execute([
                    'poll_id' => $pollId,
                    'answer_text' => $answerText,
                    'sort_order' => $sortOrder,
                ]);

                $keptIds[] = (int) $this->pdo->lastInsertId();
            }

            $idsToDelete = array_diff(array_keys($existingById), $keptIds);

            if (!empty($idsToDelete)) {
                $placeholders = implode(', ', array_fill(0, count($idsToDelete), '?'));

                $deleteStatement = $this->pdo->prepare(
                    "DELETE FROM poll_options
                 WHERE poll_id = ?
                   AND id IN ({$placeholders})"
                );

                $deleteStatement->execute([
                    $pollId,
                    ...array_values($idsToDelete),
                ]);
            }

            $this->pdo->commit();

            return $this->findWithOptions($pollId);
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}