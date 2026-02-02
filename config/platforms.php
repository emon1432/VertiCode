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

    'leetcode' => [
        'supports_rating' => true, // contest rating
        'supports_solved' => true,
        'supports_submissions' => false, // only recent 20 available
        'supports_contest_history' => true,
        'supports_badges' => true,
        'supports_calendar' => true,
        'sync_cost' => 'low',
        'features' => [
            'difficulty_breakdown' => true, // easy/medium/hard
            'contest_rating' => true,
            'contest_ranking' => true,
            'badges' => true,
            'recent_submissions' => true, // last 20 AC submissions
            'submission_calendar' => true,
            'streak_tracking' => true,
        ],
        'note' => 'GraphQL API: profile, difficulty breakdown, contest rating/history, badges, calendar, recent 20 submissions',
    ],
];
