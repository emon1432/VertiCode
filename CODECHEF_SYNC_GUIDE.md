# CodeChef Sync Guide

This guide explains how to sync contests and problems from CodeChef platform.

## Overview

The CodeChef sync system consists of:
- **Data Collector** - Fetches data from CodeChef API
- **Adapter** - Transforms API data to standardized DTOs
- **Console Commands** - CLI interface for syncing
- **Actions** - Orchestrates the sync process

## Architecture

```
CodeChefDataCollector (API calls)
    ↓
CodeChefAdapter (Transform to DTOs)
    ↓
Console Commands (CLI interface)
    ↓
SyncPlatform Actions (Database operations)
```

## Components

### 1. CodeChefDataCollector
**Location**: `app/Services/Platforms/CodeChefDataCollector.php`

Fetches data from CodeChef REST API with smart limiting:
- Collects contests from `/api/list/contests/all` endpoint
- Collects problems from `/api/list/problems/school` endpoint
- Implements rate limiting (500ms delay between requests)
- Returns exactly the requested limit (no over-fetching)

**Key Methods**:
```php
public function collectContests(int $limit): Collection
public function collectProblems(int $limit): Collection
private function fetchAllContests(): Collection
private function fetchProblemsByDifficulty(string $difficulty): Collection
```

**API Endpoints Used**:
- Contests: `https://www.codechef.com/api/list/contests/all`
- Problems: `https://www.codechef.com/api/list/problems/school`

**API Response Structure**:
```json
{
  "status": "success",
  "future_contests": [...],
  "present_contests": [...],
  "past_contests": [...]
}
```

### 2. CodeChefAdapter
**Location**: `app/Platforms/CodeChef/CodeChefAdapter.php`

Transforms CodeChef data to standardized DTOs:
- Converts contests to `ContestDTO`
- Converts problems to `ProblemDTO`
- Maps contest phases (future → upcoming, present → running, past → finished)
- Maps difficulty levels based on accuracy (>70% = easy, >40% = medium, else = hard)

**Key Methods**:
```php
public function fetchContests(int $limit = 100): Collection
public function fetchProblems(int $limit = 200): Collection
```

### 3. Console Commands

#### Sync All (Contests + Problems)
**Command**: `php artisan sync:codechef`

**Location**: `app/Console/Commands/SyncCodeChef.php`

Syncs both contests and problems sequentially.

**Usage**:
```bash
# Sync all contests and problems
php artisan sync:codechef

# Force re-sync (ignores timestamps)
php artisan sync:codechef --force
```

#### Sync Contests Only
**Command**: `php artisan sync:codechef-contests`

**Location**: `app/Console/Commands/SyncCodeChefContests.php`

Syncs only contests from CodeChef.

**Usage**:
```bash
# Sync up to 100 contests (default)
php artisan sync:codechef-contests

# Sync specific number of contests
php artisan sync:codechef-contests --limit=50

# Force re-sync
php artisan sync:codechef-contests --force

# Disable progress bar
php artisan sync:codechef-contests --no-progress
```

**Options**:
- `--limit=N` - Number of contests to sync (default: 100)
- `--force` - Force sync even if recently synced
- `--no-progress` - Disable progress bar

#### Sync Problems Only
**Command**: `php artisan sync:codechef-problems`

**Location**: `app/Console/Commands/SyncCodeChefProblems.php`

Syncs only problems from CodeChef.

**Usage**:
```bash
# Sync up to 200 problems (default)
php artisan sync:codechef-problems

# Sync specific number of problems
php artisan sync:codechef-problems --limit=100

# Force re-sync
php artisan sync:codechef-problems --force
```

**Options**:
- `--limit=N` - Number of problems to sync (default: 200)
- `--force` - Force sync even if recently synced
- `--no-progress` - Disable progress bar

#### Test CodeChef API
**Command**: `php artisan test:codechef`

**Location**: `app/Console/Commands/TestCodeChef.php`

Tests the CodeChef API connection and displays sample data without saving.

**Usage**:
```bash
# Test with small sample
php artisan test:codechef

# Test with custom limits
php artisan test:codechef --contests=10 --problems=20

# Show full API response data
php artisan test:codechef --show-data
```

**Options**:
- `--contests=N` - Number of contests to test (default: 5)
- `--problems=N` - Number of problems to test (default: 10)
- `--show-data` - Display full API response data

## Data Flow

### Contest Sync Flow
1. **Fetch** - `CodeChefDataCollector` calls `/api/list/contests/all`
2. **Parse** - Extracts `future_contests`, `present_contests`, `past_contests`
3. **Transform** - `CodeChefAdapter` converts to `ContestDTO`:
   - Maps contest code to platform_contest_id
   - Converts ISO timestamps to Unix timestamps
   - Maps phases (future → upcoming, present → running, past → finished)
   - Calculates duration in seconds
4. **Store** - `SyncPlatformContestsAction` saves to database

### Problem Sync Flow
1. **Fetch** - `CodeChefDataCollector` calls `/api/list/problems/school`
2. **Parse** - Extracts problem list from API response
3. **Transform** - `CodeChefAdapter` converts to `ProblemDTO`:
   - Maps problem code to platform_problem_id
   - Generates problem URL
   - Maps difficulty based on accuracy percentage
   - Captures tags from category
4. **Store** - `SyncPlatformProblemsAction` saves to database

## Configuration

### Rate Limiting
```php
// In CodeChefDataCollector.php
private const DELAY_MS = 500; // 500ms between requests
private const TIMEOUT = 30; // 30 second timeout
```

### Default Limits
```php
// In commands
protected const DEFAULT_CONTEST_LIMIT = 100;
protected const DEFAULT_PROBLEM_LIMIT = 200;
```

## API Details

### Contest API
**Endpoint**: `https://www.codechef.com/api/list/contests/all`

**Response Fields**:
```json
{
  "future_contests": [
    {
      "contest_code": "START123",
      "contest_name": "Starters 123",
      "contest_start_date_iso": "2024-01-15T20:00:00+05:30",
      "contest_end_date_iso": "2024-01-15T23:00:00+05:30",
      "contest_duration": 180
    }
  ]
}
```

### Problem API
**Endpoint**: `https://www.codechef.com/api/list/problems/school`

**Response Fields**:
```json
{
  "status": "success",
  "data": {
    "content": [
      {
        "problemCode": "TEST",
        "problemName": "ATM",
        "successfulSubmissions": 12345,
        "accuracy": 85.5,
        "categoryName": "beginner"
      }
    ]
  }
}
```

## Difficulty Mapping

CodeChef problems are mapped to difficulty levels based on accuracy:

| Accuracy | Difficulty |
|----------|-----------|
| > 70%    | Easy      |
| > 40%    | Medium    |
| ≤ 40%    | Hard      |

## Contest Phase Mapping

| CodeChef Type | Phase    |
|---------------|----------|
| future        | upcoming |
| present       | running  |
| past          | finished |

## Database Schema

### Contests Table
```sql
- platform_id (FK to platforms)
- platform_contest_id (contest code)
- name
- phase (upcoming/running/finished)
- start_time
- end_time
- duration_seconds
- url
- is_rated
```

### Problems Table
```sql
- platform_id (FK to platforms)
- platform_problem_id (problem code)
- name
- difficulty (easy/medium/hard)
- url
- tags
- accuracy (success rate)
- solved_count
```

## Testing

### 1. Test API Connection
```bash
php artisan test:codechef --contests=5 --problems=10
```

### 2. Sync Small Sample
```bash
php artisan sync:codechef-contests --limit=10
php artisan sync:codechef-problems --limit=20
```

### 3. Verify Database
```bash
php artisan tinker
> \App\Models\Contest::whereHas('platform', fn($q) => $q->where('name', 'CodeChef'))->count()
> \App\Models\Problem::whereHas('platform', fn($q) => $q->where('name', 'CodeChef'))->count()
```

### 4. Check Logs
```bash
tail -f storage/logs/laravel.log | grep CodeChef
```

## Example Output

### Successful Contest Sync
```
═════════════════════════════════════════════
   CodeChef Contests Sync
═════════════════════════════════════════════

Configuration:
  • Limit: 100 contests
  • Force: No

🔄 Starting sync...

✓ Synced 23 contests from codechef

Statistics:
  • Total contests: 23
  • Last synced: 2026-02-12 14:47:41
```

### Successful Problem Sync
```
═════════════════════════════════════════════
   CodeChef Problems Sync
═════════════════════════════════════════════

Configuration:
  • Limit: 200 problems
  • Force: No

🔄 Starting sync...

✓ Synced 200 problems from codechef

Statistics:
  • Total problems: 200
  • Last synced: 2026-02-12 14:47:43
```

## Troubleshooting

### No Contests Synced
**Symptom**: "Synced 0 contests"

**Possible Causes**:
1. Network/API issues
2. API structure changed
3. Rate limiting by CodeChef

**Solutions**:
```bash
# Check logs
tail -50 storage/logs/laravel.log | grep CodeChef

# Test API directly
curl "https://www.codechef.com/api/list/contests/all"

# Force re-sync
php artisan sync:codechef-contests --force
```

### API Timeout
**Symptom**: Timeout errors in logs

**Solutions**:
- Increase timeout in `CodeChefDataCollector.php`
- Check internet connection
- Try during off-peak hours

### Duplicate Contests/Problems
**Symptom**: Same contest appears multiple times

**Solutions**:
- Check `platform_contest_id` uniqueness constraint
- Review adapter transformation logic
- Clear and re-sync with `--force`

## Maintenance

### Update Contest Phases
Contests automatically update their phase based on current time:
- `upcoming` - start_time > now
- `running` - start_time ≤ now ≤ end_time
- `finished` - end_time < now

### Re-sync All Data
```bash
# Clear existing data
php artisan tinker
> \App\Models\Contest::whereHas('platform', fn($q) => $q->where('name', 'CodeChef'))->delete()
> \App\Models\Problem::whereHas('platform', fn($q) => $q->where('name', 'CodeChef'))->delete()

# Re-sync
php artisan sync:codechef
```

### Update Platform Timestamp
Platform model tracks last sync time automatically:
```php
$platform->markContestsSynced();
$platform->markProblemsSynced();
```

## Performance

### API Rate Limits
- **Delay**: 500ms between requests
- **Timeout**: 30 seconds per request
- **Impact**: ~2 requests per second

### Sync Times (Approximate)
- 100 contests: ~2-3 seconds
- 200 problems: ~3-4 seconds
- Full sync: ~5-7 seconds

## See Also
- [AtCoder Sync Guide](ATCODER_SYNC_GUIDE.md)
- [Codeforces Sync Guide](CODEFORCES_SYNC_GUIDE.md)
- [Platform Adapters Documentation](app/Platforms/README.md)
- [Actions Documentation](app/Actions/README.md)
