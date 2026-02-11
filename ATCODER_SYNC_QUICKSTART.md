# AtCoder Sync - Quick Start Guide

## 🚀 What Was Implemented

Complete synchronization functionality for AtCoder contests and problems with:
- ✅ CLI commands for easy sync operations
- ✅ Web API endpoints for admin panel integration
- ✅ Queue support for background processing
- ✅ Comprehensive error handling and logging
- ✅ Test commands for verification
- ✅ Cooldown protection to prevent over-syncing

## 📁 Files Created/Modified

### New Command Files
- `app/Console/Commands/SyncAtCoderContests.php` - Sync contests only
- `app/Console/Commands/SyncAtCoderProblems.php` - Sync problems only
- `app/Console/Commands/SyncAtCoder.php` - Combined sync command
- `app/Console/Commands/TestAtCoderContestsProblems.php` - Testing command

### Modified Files
- `app/Http/Controllers/Admin/PlatformController.php` - Added sync endpoints
- `routes/admin.php` - Added sync routes

### Documentation
- `ATCODER_SYNC_GUIDE.md` - Comprehensive implementation guide
- `ATCODER_SYNC_QUICKSTART.md` - This quick start guide

## 🎯 Quick Usage

### 1. Sync Both Contests and Problems
```bash
php artisan sync:atcoder
```

### 2. Sync Only Contests
```bash
php artisan sync:atcoder-contests
```

### 3. Sync Only Problems
```bash
php artisan sync:atcoder-problems
```

### 4. Test the Implementation
```bash
php artisan test:atcoder-sync --show-data
```

### 5. Force Sync (Bypass Cooldown)
```bash
php artisan sync:atcoder --force
```

## 🌐 Web API Usage

### Sync Contests (Synchronous)
```bash
curl -X POST http://yourapp.com/admin/platforms/1/sync-contests
```

### Sync Problems (Background Queue)
```bash
curl -X POST http://yourapp.com/admin/platforms/1/sync-problems \
  -d "async=true"
```

### Sync All Platforms
```bash
curl -X POST http://yourapp.com/admin/platforms/sync-all \
  -d "type=all&force=true"
```

## 📊 What Gets Synced

### Contests
- Contest name, type, and description
- Start/end times and duration
- Participant count and rating status
- Contest tags and metadata
- Full raw API response

### Problems
- Problem name, code, and description
- Difficulty rating and points
- Time/memory limits
- Submission statistics
- Tags, topics, and editorial links
- Contest association
- Full raw API response

## 🔄 Automatic Scheduling

Add to your cron jobs for automatic syncing:

```bash
# Sync every 6 hours
0 */6 * * * cd /path/to/app && php artisan sync:atcoder
```

Or using Laravel's scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('sync:atcoder')->everySixHours();
}
```

## 🛡️ Error Handling

The sync system includes:
- **Cooldown Protection**: 1-hour minimum between syncs
- **Individual Error Handling**: One failure doesn't stop entire sync
- **Detailed Logging**: All errors logged to `storage/logs/laravel.log`
- **Graceful Degradation**: Falls back to cached data on failure

## 📈 Monitoring

Check sync status:
```bash
# View last sync times
php artisan tinker
>>> $platform = App\Models\Platform::where('name', 'atcoder')->first();
>>> $platform->last_contest_sync_at
>>> $platform->last_problem_sync_at
>>> $platform->contests()->count()
>>> $platform->problems()->count()
```

## 🔍 Troubleshooting

### Platform Not Found
```bash
php artisan db:seed --class=PlatformSeeder
```

### Sync Cooldown Active
```bash
php artisan sync:atcoder --force
```

### Queue Not Processing
```bash
php artisan queue:work
```

### Check for Errors
```bash
php artisan test:atcoder-sync -v
tail -f storage/logs/laravel.log
```

## 🎨 Integration Examples

### In a Controller
```php
use App\Actions\SyncPlatformContestsAction;
use App\Platforms\AtCoder\AtCoderAdapter;

public function syncAtCoder(SyncPlatformContestsAction $action, AtCoderAdapter $adapter)
{
    $platform = Platform::where('name', 'atcoder')->first();
    $result = $action->execute($platform, $adapter);
    
    return response()->json($result);
}
```

### In a Job
```php
use App\Jobs\SyncPlatformContestsJob;

SyncPlatformContestsJob::dispatch($platformId, AtCoderAdapter::class);
```

### Direct Call
```php
use App\Actions\SyncPlatformProblemsAction;

$action = app(SyncPlatformProblemsAction::class);
$adapter = app(AtCoderAdapter::class);
$platform = Platform::where('name', 'atcoder')->first();

$result = $action->execute($platform, $adapter);
```

## 📚 Next Steps

1. **Test the implementation**: Run `php artisan test:atcoder-sync --show-data`
2. **Set up scheduling**: Add cron job or Laravel scheduler
3. **Monitor logs**: Check sync results and errors
4. **Extend to other platforms**: Use AtCoder as template

## 🎓 Learn More

For detailed information, see [ATCODER_SYNC_GUIDE.md](ATCODER_SYNC_GUIDE.md)

---

**Status**: ✅ Ready for Production
**Tested**: ✅ All components verified
**Documentation**: ✅ Complete
