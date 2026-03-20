<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Core\Database;
use Src\Core\Request;
use Src\Models\Poll;
use Src\Models\PollOption;
use Src\Models\PollVote;
use Throwable;

class PollController extends AppController
{
    private Poll $polls;
    private PollOption $options;
    private PollVote $votes;

    public function __construct(Database $database)
    {
        parent::__construct($database);

        $this->polls = new Poll($database);
        $this->options = new PollOption($database);
        $this->votes = new PollVote($database);
    }

    public function index(Request $request): void
    {
        $user = $request->user();

        if ($user === null) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $polls = $this->polls->all();

        $this->success($polls, 'Polls fetched successfully.');
    }

    public function active(): void
    {
        $poll = $this->polls->findActive();

        if ($poll === null) {
            $this->success(null, 'No active poll found.');
            return;
        }

        $poll['options'] = $this->options->findByPollId((int) $poll['id']);

        $this->success($poll, 'Active poll fetched successfully.');
    }

    public function closed(): void
    {
        $polls = $this->polls->findClosed();

        $this->success($polls, 'Closed polls fetched successfully.');
    }

    public function show(Request $request): void
    {
        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid poll id.', 422);
            return;
        }

        $poll = $this->polls->findById($id);

        if ($poll === null) {
            $this->error('Poll not found.', 404);
            return;
        }

        $poll['options'] = $this->options->findByPollId($id);

        $this->success($poll, 'Poll fetched successfully.');
    }

    public function store(Request $request): void
    {
        $user = $request->user();

        if ($user === null) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $question = trim((string) ($request->input('question') ?? ''));
        $rawOptions = $request->input('options');

        if ($question === '') {
            $this->error('Question is required.', 422);
            return;
        }

        if (mb_strlen($question) > 500) {
            $this->error('Question must not exceed 500 characters.', 422);
            return;
        }

        if (!is_array($rawOptions)) {
            $this->error('Options must be an array.', 422);
            return;
        }

        $options = $this->sanitizeOptions($rawOptions);

        if (count($options) < 2) {
            $this->error('A poll must have at least 2 options.', 422);
            return;
        }

        try {
            $pollId = $this->polls->createWithOptions((int) $user['id'], $question, $options);

            $poll = $this->polls->findById($pollId);
            $poll['options'] = $this->options->findByPollId($pollId);

            $this->success($poll, 'Poll created successfully.', 201);
        } catch (Throwable $e) {
            $this->error('Failed to create poll.', 500);
        }
    }

    public function activate(Request $request): void
    {
        $user = $request->user();

        if ($user === null) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid poll id.', 422);
            return;
        }

        $poll = $this->polls->findById($id);

        if ($poll === null) {
            $this->error('Poll not found.', 404);
            return;
        }

        if ($poll['status'] === 'closed') {
            $this->error('Closed polls cannot be reopened.', 422);
            return;
        }

        if ($poll['status'] === 'active') {
            $this->error('Poll is already active.', 422);
            return;
        }

        $optionCount = $this->options->countByPollId($id);

        if ($optionCount < 2) {
            $this->error('A poll must have at least 2 options to be activated.', 422);
            return;
        }

        $activated = $this->polls->activate($id);

        if (!$activated) {
            $this->error('Failed to activate poll.', 422);
            return;
        }

        $updatedPoll = $this->polls->findById($id);
        $updatedPoll['options'] = $this->options->findByPollId($id);

        $this->success($updatedPoll, 'Poll activated successfully.');
    }

    public function close(Request $request): void
    {
        $user = $request->user();

        if ($user === null) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid poll id.', 422);
            return;
        }

        $poll = $this->polls->findById($id);

        if ($poll === null) {
            $this->error('Poll not found.', 404);
            return;
        }

        if ($poll['status'] === 'closed') {
            $this->error('Poll is already closed.', 422);
            return;
        }

        if ($poll['status'] !== 'active') {
            $this->error('Only active polls can be closed.', 422);
            return;
        }

        $closed = $this->polls->close($id);

        if (!$closed) {
            $this->error('Failed to close poll.', 422);
            return;
        }

        $updatedPoll = $this->polls->findById($id);
        $updatedPoll['options'] = $this->options->findByPollId($id);

        $this->success($updatedPoll, 'Poll closed successfully.');
    }

    public function vote(Request $request): void
    {
        $user = $request->user();

        if ($user === null) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $id = (int) ($request->param('id') ?? 0);
        $optionId = (int) ($request->input('option_id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid poll id.', 422);
            return;
        }

        if ($optionId <= 0) {
            $this->error('Invalid option id.', 422);
            return;
        }

        $poll = $this->polls->findById($id);

        if ($poll === null) {
            $this->error('Poll not found.', 404);
            return;
        }

        if ($poll['status'] !== 'active') {
            $this->error('Voting is allowed only for active polls.', 422);
            return;
        }

        $option = $this->options->findByIdAndPollId($optionId, $id);

        if ($option === null) {
            $this->error('Selected option does not belong to this poll.', 422);
            return;
        }

        if ($this->votes->hasUserVoted($id, (int) $user['id'])) {
            $this->error('You have already voted for this poll.', 422);
            return;
        }

        $created = $this->votes->create($id, $optionId, (int) $user['id']);

        if (!$created) {
            $this->error('Failed to save vote.', 422);
            return;
        }

        $this->success([
            'poll_id' => $id,
            'option_id' => $optionId,
            'user_id' => (int) $user['id'],
        ], 'Vote submitted successfully.', 201);
    }

    public function results(Request $request): void
    {
        $user = $request->user();

        if ($user === null) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid poll id.', 422);
            return;
        }

        $poll = $this->polls->findById($id);

        if ($poll === null) {
            $this->error('Poll not found.', 404);
            return;
        }

        /*if ($poll['status'] !== 'closed') {
            $this->error('Results are available only for closed polls.', 422);
            return;
        }*/

        $results = $this->polls->findResults($id);
        $totalVotes = $this->polls->countVotes($id);

        $formattedResults = array_map(
            function (array $row) use ($totalVotes): array {
                $votes = (int) $row['votes'];
                $percentage = $totalVotes > 0
                    ? round(($votes / $totalVotes) * 100, 2)
                    : 0;

                return [
                    'option_id' => (int) $row['id'],
                    'answer_text' => $row['answer_text'],
                    'sort_order' => (int) $row['sort_order'],
                    'votes' => $votes,
                    'percentage' => $percentage,
                ];
            },
            $results
        );

        $this->success([
            'poll' => [
                'id' => (int) $poll['id'],
                'question' => $poll['question'],
                'status' => $poll['status'],
                'closed_at' => $poll['closed_at'],
                'total_votes' => $totalVotes,
            ],
            'results' => $formattedResults,
        ], 'Poll results fetched successfully.');
    }

    private function sanitizeOptions(array $rawOptions): array
    {
        $result = [];

        foreach ($rawOptions as $option) {
            $value = trim((string) $option);

            if ($value === '') {
                continue;
            }

            if (mb_strlen($value) > 255) {
                continue;
            }

            $result[] = $value;
        }

        $result = array_values(array_unique($result));

        return $result;
    }

    public function update(Request $request): void
    {
        $user = $request->user();

        if ($user === null) {
            $this->error('Unauthorized.', 401);
            return;
        }

        $id = (int) ($request->param('id') ?? 0);

        if ($id <= 0) {
            $this->error('Invalid poll id.', 422);
            return;
        }

        $poll = $this->polls->findWithOptions($id);

        if ($poll === null) {
            $this->error('Poll not found.', 404);
            return;
        }

        if ((int) $poll['user_id'] !== (int) $user['id']) {
            $this->error('Forbidden.', 403);
            return;
        }

        if (($poll['status'] ?? '') === 'closed') {
            $this->error('Closed polls cannot be edited.', 422);
            return;
        }

        $question = trim((string) ($request->input('question') ?? ''));
        $options = $request->input('options') ?? [];

        if ($question === '') {
            $this->error('Question is required.', 422);
            return;
        }

        if (mb_strlen($question) > 500) {
            $this->error('Question must not exceed 500 characters.', 422);
            return;
        }

        if (!is_array($options)) {
            $this->error('Options must be an array.', 422);
            return;
        }

        $sanitizedOptions = [];
        $seen = [];

        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $optionId = isset($option['id']) && $option['id'] !== null
                ? (int) $option['id']
                : null;

            $answerText = trim((string) ($option['answer_text'] ?? ''));

            if ($answerText === '') {
                continue;
            }

            if (mb_strlen($answerText) > 255) {
                $this->error('Each option must not exceed 255 characters.', 422);
                return;
            }

            $key = mb_strtolower($answerText);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $sanitizedOptions[] = [
                'id' => $optionId,
                'answer_text' => $answerText,
            ];
        }

        if (count($sanitizedOptions) < 2) {
            $this->error('At least 2 non-empty unique options are required.', 422);
            return;
        }

        $updatedPoll = $this->polls->updateWithOptions($id, $question, $sanitizedOptions);

        $this->success($updatedPoll, 'Poll updated successfully.');
    }
}