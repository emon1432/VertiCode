# AtCoder Sync Implementation Guide

## Overview

This implementation provides full synchronization functionality for AtCoder contests and problems. The sync system is designed to be modular, extensible, and easy to use both via command line and web interface.

## Architecture

### Components

1. **Adapters** (`app/Platforms/AtCoder/AtCoderAdapter.php`)
   - Implements `ContestSyncAdapter` and `ProblemSyncAdapter` interfaces
   - Handles data fetching and transformation from AtCoder
   - Maps raw data to standardized DTOs

2. **Actions** (`app/Actions/`)
   - `SyncPlatformContestsAction.php` - Orchestrates contest synchronization
   - `SyncPlatformProblemsAction.php` - Orchestrates problem synchronization
   - Handles database operations and error logging

3. **Jobs** (`app/Jobs/`)
   - `SyncPlatformContestsJob.php` - Queue-based contest sync
   - `SyncPlatformProblemsJob.php` - Queue-based problem sync
   - Allows asynchronous processing for large datasets

4. **Commands** (`app/Console/Commands/`)
   - `SyncAtCoderContests.php` - CLI command for contest sync
   - `SyncAtCoderProblems.php` - CLI command for problem sync
   - `SyncAtCoder.php` - Combined sync command
   - `TestAtCoderContestsProblems.php` - Test and verification command

5. **Controller** (`app/Http/Controllers/Admin/PlatformController.php`)
   - Web-based sync endpoints
   - Support for both synchronous and asynchronous execution

## Usage

### CLI Commands

#### 1. Sync AtCoder Contests

```bash
# Basic sync
php artisan sync:atcoder-contests

# Force sync (ignore cooldown)
php artisan sync:atcoder-contests --force

# Limit number of contests
php artisan sync:atcoder-contests --limit=50

# Verbose output
php artisan sync:atcoder-contests -v
```

#### 2. Sync AtCoder Problems

```bash
# Basic sync
php artisan sync:atcoder-problems

# Force sync (ignore cooldown)
php artisan sync:atcoder-problems --force

# Sync problems for specific contest
php artisan sync:atcoder-problems --contest=abc123

# Limit number of problems
php artisan sync:atcoder-problems --limit=200
```

#### 3. Combined Sync

```bash
# Sync both contests and problems
php artisan sync:atcoder

# Sync only contests
php artisan sync:atcoder --contests-only

# Sync only problems
php artisan sync:atcoder --problems-only

# Force sync both
php artisan sync:atcoder --force
```

#### 4. Test Sync Functionality

```bash
# Test both syncs
php artisan test:atcoder-sync

# Test only contests
php artisan test:atcoder-sync --contests-only

# Test only problems
php artisan test:atcoder-sync --problems-only

# Show sample data
php artisan test:atcoder-sync --show-data
```

### Web API Endpoints

All endpoints require admin authentication.

#### 1. Sync Contests for Platform

```http
POST /admin/platforms/{platform}/sync-contests
```

**Parameters:**
- `async` (optional, boolean) - Run in background queue

**Example:**
```bash
curl -X POST http://yourapp.com/admin/platforms/1/sync-contests \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "async=false"
```

#### 2. Sync Problems for Platform

```http
POST /admin/platforms/{platform}/sync-problems
```

**Parameters:**
- `async` (optional, boolean) - Run in background queue
- `contest_id` (optional, string) - Filter by contest

**Example:**
```bash
curl -X POST http://yourapp.com/admin/platforms/1/sync-problems \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "async=true&contest_id=abc123"
```

#### 3. Sync All Platforms

```http
POST /admin/platforms/sync-all
```

**Parameters:**
- `type` (optional, string) - `all`, `contests`, or `problems`
- `force` (optional, boolean) - Ignore cooldown period

**Example:**
```bash
curl -X POST http://yourapp.com/admin/platforms/sync-all \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "type=all&force=true"
```

## Data Structure

### Contests

The sync process stores the following contest data:

- **platform_id**: Reference to platform
- **platform_contest_id**: Unique contest ID from AtCoder
- **name**: Contest name
- **slug**: URL-friendly identifier
- **type**: Contest type (contest, practice, challenge, etc.)
- **phase**: Contest phase (finished, before, coding, etc.)
- **duration_seconds**: Duration in seconds
- **start_time**: Start timestamp
- **end_time**: End timestamp
- **url**: Contest URL on AtCoder
- **participant_count**: Number of participants
- **is_rated**: Whether contest is rated
- **tags**: JSON array of tags
- **raw**: Raw API response

### Problems

The sync process stores the following problem data:

- **platform_id**: Reference to platform
- **contest_id**: Reference to contest (nullable)
- **platform_problem_id**: Unique problem ID from AtCoder
- **name**: Problem name
- **slug**: URL-friendly identifier
- **code**: Problem code (A, B, C, etc.)
- **difficulty**: Difficulty level (easy, medium, hard)
- **rating**: Problem rating/difficulty score
- **points**: Points awarded for solving
- **accuracy**: Success rate percentage
- **time_limit_ms**: Time limit in milliseconds
- **memory_limit_mb**: Memory limit in MB
- **total_submissions**: Total submission count
- **accepted_submissions**: Accepted submission count
- **solved_count**: Number of users who solved it
- **tags**: JSON array of problem tags
- **topics**: JSON array of topics
- **url**: Problem URL on AtCoder
- **editorial_url**: Editorial/solution URL
- **raw**: Raw API response

## Features

### 1. Cooldown Protection

The system prevents excessive syncing by implementing a cooldown period:

- Contests: 1 hour cooldown between syncs
- Problems: 1 hour cooldown between syncs
- Use `--force` flag to bypass cooldown

### 2. Error Handling

- Individual errors don't stop entire sync process
- Failed items are logged for review
- Detailed error messages in logs
- Graceful fallback to cached data

### 3. Rate Limiting

- Respectful scraping with delays
- Configurable rate limits
- Automatic retry on failure

### 4. Data Validation

- DTOs ensure data consistency
- Required fields validation
- Type casting and sanitization
- Duplicate prevention via unique constraints

## Extending to Other Platforms

To implement sync for other platforms:

1. **Create Adapter** (`app/Platforms/{Platform}/{Platform}Adapter.php`)
   ```php
   class NewPlatformAdapter implements ContestSyncAdapter, ProblemSyncAdapter
   {
       public function fetchContests(int $limit = 100): Collection { }
       public function fetchProblems(int $limit = 500, ?string $contestId = null): Collection { }
       public function supportsContests(): bool { return true; }
       public function supportsProblems(): bool { return true; }
   }
   ```

2. **Create Commands** (`app/Console/Commands/`)
   - Copy and modify AtCoder commands
   - Update adapter references

3. **Register in PlatformController**
   ```php
   private function getAdapterClass(string $platformName): ?string
   {
       $adapters = [
           'atcoder' => AtCoderAdapter::class,
           'newplatform' => NewPlatformAdapter::class,
       ];
       return $adapters[strtolower($platformName)] ?? null;
   }
   ```

4. **Register in PlatformSyncService**
   ```php
   protected array $platformAdapters = [
       'atcoder' => AtCoderAdapter::class,
       'newplatform' => NewPlatformAdapter::class,
   ];
   ```

## Monitoring and Logging

All sync operations are logged to:
- Laravel logs (`storage/logs/laravel.log`)
- Sync-specific logs with context

Monitor sync status via:
- Platform model timestamps (`last_contest_sync_at`, `last_problem_sync_at`)

- Database records (contests and problems tables)

## Troubleshooting

### Issue: "Platform not found"
**Solution:** Run platform seeder:
```bash
php artisan db:seed --class=PlatformSeeder
```

### Issue: "Sync cooldown active"
**Solution:** Use `--force` flag to bypass:
```bash
php artisan sync:atcoder --force
```

### Issue: "No contests/problems synced"
**Solution:** 
1. Check platform status is 'Active'
2. Verify network connectivity to AtCoder
3. Check logs for detailed errors
4. Try with verbose output: `-v`

### Issue: "Queue not processing"
**Solution:** Start queue worker:
```bash
php artisan queue:work
```

## Best Practices

1. **Regular Syncing**: Set up cron jobs for automated syncs
   ```bash
   0 */6 * * * cd /path/to/app && php artisan sync:atcoder >> /dev/null 2>&1
   ```

2. **Use Queues**: For production, use async syncing to avoid timeouts
   ```bash
   php artisan queue:work --queue=default
   ```

3. **Monitor Logs**: Regularly check logs for errors or issues

4. **Database Indexes**: Ensure proper indexes on frequently queried fields

5. **Backup**: Regular database backups before large syncs

## Performance

- Contest sync typically takes: 30-60 seconds
- Problem sync typically takes: 2-5 minutes (depending on number of contests)
- Async execution recommended for production environments
- Consider running syncs during off-peak hours

## Security

- All admin routes require authentication
- Rate limiting protects against abuse
- Input validation on all parameters
- SQL injection prevention via Eloquent ORM
- XSS protection on all outputs

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Run test command: `php artisan test:atcoder-sync --show-data`
3. Review this documentation
4. Check adapter implementation for platform-specific details
