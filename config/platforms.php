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
            'submission_details' => true,
        ],
        'note' => 'Full functionality: rating, max rating, contest history, problem tags, submission details',
    ],

    'leetcode' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => false,
        'supports_contest_history' => true,
        'supports_badges' => true,
        'supports_calendar' => true,
        'sync_cost' => 'low',
        'features' => [
            'difficulty_breakdown' => true,
            'contest_rating' => true,
            'contest_ranking' => true,
            'badges' => true,
            'recent_submissions' => true,
            'submission_calendar' => true,
            'streak_tracking' => true,
        ],
        'note' => 'GraphQL API: profile, difficulty breakdown, contest rating/history, badges, calendar, recent 20 submissions',
    ],

    'atcoder' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => true,
        'supports_rating_graph' => true,
        'supports_contest_history' => true,
        'sync_cost' => 'medium',
        'features' => [
            'highest_rating' => true,
            'rank_tracking' => true,
            'contest_participation' => true,
            'editorial_links' => true,
            'performance_rating' => true,
            'submission_details' => true,
        ],
        'note' => 'Profile and contest history work. Kenkoooo API for submissions may be rate limited (returns 403). Gracefully falls back to profile-based total_solved.',
        'known_issues' => [
            'kenkoooo_api_403' => 'Third-party Kenkoooo API may return 403 due to rate limits. Submissions will be skipped but profile sync continues.',
        ],
    ],

    'codechef' => [
        'supports_rating' => true,
        'supports_solved' => true,
        'supports_submissions' => false,
        'supports_rating_graph' => true,
        'sync_cost' => 'low',
        'features' => [
            'max_rating' => true,
            'stars' => true,
            'rank_tracking' => true,
            'contest_categories' => true,
            'fully_partially_solved' => true,
            'badges' => true,
            'problem_api' => true,
        ],
        'note' => 'Profile scraping with rating graph by contest category. Submissions require OAuth API (not implemented).',
        'contest_types' => [
            'long' => 'Long Challenge',
            'cookoff' => 'Cook-off',
            'lunchtime' => 'Lunchtime',
            'starters' => 'Starters',
        ],
    ],

    'spoj' => [
        'supports_rating' => false,
        'supports_solved' => true,
        'supports_submissions' => true,
        'sync_cost' => 'medium',
        'features' => [
            'rank_tracking' => true,
            'problem_details' => true, // tags and author from problem page
            'cloudflare_handling' => true, // Automatic retry with delays
        ],
        'note' => 'Profile scraping with rank. Submissions via paginated status pages. Handles Cloudflare challenge with retry logic.',
        'known_issues' => [
            'cloudflare_delay' => 'SPOJ uses Cloudflare challenge which takes 3-10 seconds. Implementation handles this with automatic retries.',
        ],
    ],

    'hackerrank' => [
        'supports_rating' => false,
        'supports_solved' => true,
        'supports_submissions' => true,
        'supports_rating_graph' => true,
        'sync_cost' => 'medium',
        'features' => [
            'rating_graph' => true,
            'recent_submissions' => true,
            'problem_details' => true, // tags/editorial/author
        ],
        'note' => 'Uses HackerRank REST endpoints for rating graph and recent challenges.',
    ],
];
