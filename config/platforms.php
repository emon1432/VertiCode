<?php

return [
    'hackerearth' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => true,
        'sync_cost' => 'high',
        'note' => 'Rating works. Submissions endpoint has timeout issues (TODO: fix)',
    ],

    'codeforces' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => true,
        'supports_rating_graph' => true,
        'supports_contest_history' => true,
        'supports_problem_tags' => true,
        'sync_cost' => 'medium',
        'features' => [
            'max_rating' => true,
            'rank_tracking' => true,
            'contest_participation' => true,
            'problem_tags' => true,
            'submission_details' => true, // time, memory, language
        ],
        'note' => 'Full functionality: rating, max rating, contest history, problem tags, submission details',
    ],
];
