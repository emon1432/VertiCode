# Codeforces Sync Implementation Guide

## Overview

This implementation provides full synchronization functionality for Codeforces contests and problems using the official Codeforces API. The sync system is modular, efficient, and designed to handle large-scale data synchronization.

## Architecture

### Components

1. **Data Collector** (`app/Services/Platforms/CodeforcesDataCollector.php`)
   - Direct API communication with Codeforces API
   - Implements smart limiting (collects exactly the requested amount)
   - Rate-limited requests (300ms between API calls)
   - Error handling and logging

2. **Adapter** (`app/Platforms/Codeforces/CodeforcesAdapter.php`)
   - Implements `ContestSyncAdapter` and `ProblemSyncAdapter` interfaces
   - Transforms API data to standardized DTOs
   - Supports contest and problem fetching with limits
   - Handles difficulty mapping and data enrichment

3. **Actions** (`app/Actions/`)
   - `SyncPlatformContestsAction.php` - Orchestrates contest synchronization
   - `SyncPlatformProblemsAction.php` - Orchestrates problem synchronization
   - Manages database operations and error handling

4. **Commands** (`app/Console/Commands/`)
   - `SyncCodeforces.php` - Combined sync command
   - `SyncCodeforcesContests.php` - Contest sync command
   - `SyncCodeforcesProblems.php` - Problem sync command
   - `TestCodeforces.php` - Test and verification command

5. **Controller** (`app/Http/Controllers/Admin/PlatformController.php`)
   - Web-based sync endpoints
   - Support for both synchronous and asynchronous execution

## API Details

### Codeforces API Endpoints Used

1. **Contest List**: `https://codeforces.com/api/contest.list`
   - Returns all available contests
   - Filters: gym=false (excludes practice contests)
   - Data: contest ID, name, type, phase, duration, start time

2. **Problemset**: `https://codeforces.com/api/problemset.problems`
   - Returns all available problems
   - Data: problem ID, name, rating, tags, solved count

## Usage

### CLI Commands

#### 1. Sync Codeforces Contests

```bash
# Basic sync (100 contests)
php artisan sync:codeforces-contests

# Force sync (ignore cooldown)
php artisan sync:codeforces-contests --force

# Custom limit
php artisan sync:codeforces-contests --limit=50

# Combined with problems
php artisan sync:codeforces
```

#### 2. Sync Codeforces Problems

```bash
# Basic sync (200 problems)
php artisan sync:codeforces-problems

# Force sync (ignore cooldown)
php artisan sync:codeforces-problems --force

# Custom limit
php artisan sync:codeforces-problems --limit=300

# Sync specific contest problems
php artisan sync:codeforces-problems --contest=1

# Combined with contests
php artisan sync:codeforces
```

#### 3. Combined Sync

```bash
# Sync both contests and problems
php artisan sync:codeforces

# Sync only contests
php artisan sync:codeforces --contests-only

# Sync only problems
php artisan sync:codeforces --problems-only

# Force sync both
php artisan sync:codeforces --force
```

#### 4. Test Functionality

```bash
# Test adapter
php artisan test:codeforces

# Test and show sample data
php artisan test:codeforces --show-data
```

### Web API Endpoints

#### Sync Contests

```http
POST /admin/platforms/{platformId}/sync-contests
Content-Type: application/json

{
  "async": true  // or false for synchronous
}
```

Response:
```json
{
  "status": 200,
  "message": "Contest sync successful",
  "data": {
    "synced": 100,
    "total": 2548
  }
}
```

#### Sync Problems

```http
POST /admin/platforms/{platformId}/sync-problems
Content-Type: application/json

{
  "async": true,
  "limit": 200
}
```

#### Sync All

```http
POST /admin/platforms/sync-all
Content-Type: application/json

{
  "async": true
}
```

## Data Limits

### Default Limits

- **Contests**: 100 contests per sync
- **Problems**: 200 problems per sync
- **API Rate Limiting**: 300ms delay between requests

### Why These Limits?

- **Contests**: 100 covers most active/recent contests (beyond that are archived)
- **Problems**: 200 provides a solid sample of diverse difficulty levels
- **Rate Limiting**: Respects API usage policies and prevents rate limiting

### Changing Limits

```bash
# Fetch more contests
php artisan sync:codeforces-contests --limit=500

# Fetch more problems
php artisan sync:codeforces-problems --limit=1000
```

## Database Integration

### Contest Fields

- `id` - Auto-increment
- `platform_id` - Foreign key to platforms
- `platform_contest_id` - Codeforces contest ID
- `name` - Contest name
- `slug` - URL-friendly identifier
- `type` - CONTEST, PRACTICE, etc.
- `phase` - BEFORE, RUNNING, FINISHED
- `start_time` - Contest start timestamp
- `end_time` - Contest end timestamp
- `duration_seconds` - Contest duration
- `url` - Full Codeforces contest URL
- `raw` - JSON: raw API data
- `timestamps` - created_at, updated_at

### Problem Fields

- `id` - Auto-increment
- `platform_id` - Foreign key to platforms
- `platform_problem_id` - Codeforces problem ID (e.g., "1000A")
- `name` - Problem name
- `slug` - URL-friendly identifier
- `code` - Problem index (A, B, C, etc.)
- `contest_id` - Associated contest ID
- `difficulty` - EASY, MEDIUM, HARD, UNKNOWN
- `rating` - Codeforces rating (if available)
- `url` - Full Codeforces problem URL
- `tags` - JSON array: problem tags
- `solved_count` - Number of solvers
- `raw` - JSON: raw API data
- `timestamps` - created_at, updated_at

## Error Handling

### Common Issues and Solutions

1. **API Rate Limiting**
   - Solution: Reduce limit or increase delay
   - `CodeforcesDataCollector` includes 300ms delay

2. **Network Timeouts**
   - Solution: Increase timeout (configured to 30s)
   - Solution: Run during off-peak hours

3. **Partial Data Sync**
   - Solution: Force re-sync with `--force` flag
   - Uses transaction for atomic operations

## Monitoring

### Logging

All sync operations are logged in `storage/logs/laravel.log`:

```
[2026-02-12] INFO: Codeforces: Starting contest collection with limit: 100
[2026-02-12] INFO: Codeforces: Collected 98 contests
[2026-02-12] INFO: Codeforces: Successfully inserted 98 contests
```

### Database Tracking

Track sync operations via Platform model:

```php
$platform = Platform::where('name', 'codeforces')->first();
echo $platform->last_contest_sync_at;  // Last contest sync
echo $platform->last_problem_sync_at;  // Last problem sync
$platform->contests()->count();  // Total contests synced
$platform->problems()->count();  // Total problems synced
```

## Extending to Other Platforms

This implementation serves as a template for other platforms:

1. Create `app/Services/Platforms/{Platform}DataCollector.php`
2. Create `app/Platforms/{Platform}/{Platform}Adapter.php`
3. Create commands in `app/Console/Commands/`
4. Routes are auto-handled by controller

Example platforms to implement:
- LeetCode
- CodeChef
- HackerRank
- HackerEarth

## Performance Notes

- **API Response Time**: ~200-500ms per request
- **Database Insert**: ~50-100ms per 100 records
- **Total Sync Time**: ~5-10 seconds for 100 contests + 200 problems

### Optimization Tips

1. Use `--async` flag in web interface for large syncs
2. Schedule syncs during off-peak hours
3. Limit to necessary data (don't sync more than needed)
4. Use queue workers for background processing

## Configuration

### Environment Variables

```env
CODEFORCES_API_RATE_LIMIT_MS=300  # Delay between requests
CODEFORCES_CONTEST_LIMIT=100      # Default contest limit
CODEFORCES_PROBLEM_LIMIT=200      # Default problem limit
SYNC_COOLDOWN_HOURS=1             # Minimum hours between syncs
```

## Support

For issues or improvements, refer to:
- Codeforces API: https://codeforces.com/apiStatus
- Database schemas: `database/migrations/`
- Test data: `php artisan test:codeforces --show-data`
