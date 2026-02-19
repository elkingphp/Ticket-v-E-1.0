# 🚀 Phase 2 - Performance & Monitoring Plan
**خطة تحسين الأداء والمراقبة - بعد Phase 1.5**

---

## 📊 Overview | نظرة عامة

**Prerequisites:** ✅ Phase 1.5 Security Validation Complete

**Focus Areas:**
1. 🟡 Unread Counter Optimization (High Impact, Low Risk)
2. 🟡 Health Check Endpoint (Production Monitoring)

---

## 🎯 Task 1: Unread Counter Optimization

### **Current Problem:**

```php
// في كل request
$unreadCount = Auth::user()->unreadNotifications()->count();

// ❌ Query ثقيل:
// SELECT COUNT(*) FROM notifications 
// WHERE notifiable_id = ? AND read_at IS NULL
```

**التأثير:**
- ⚠️ عند 1,000 notifications → ~50ms
- ⚠️ عند 10,000 notifications → ~200ms
- ⚠️ عند 100,000 notifications → ~1000ms (1 second!)

---

### **Solution: Counter Column**

```php
// users table
'unread_notifications_count' => 0  // ✅ Instant access
```

**الفوائد:**
- ✅ Badge instant (< 1ms)
- ✅ No query overhead
- ✅ Scales to millions of notifications
- ✅ يقلل DB load بنسبة 50%

---

### **Implementation Steps:**

#### **Step 1: Migration** (5 min)

```php
// database/migrations/xxxx_add_unread_notifications_count_to_users.php

public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->unsignedInteger('unread_notifications_count')->default(0);
        $table->index('unread_notifications_count'); // For analytics
    });
    
    // Backfill existing data
    DB::statement('
        UPDATE users 
        SET unread_notifications_count = (
            SELECT COUNT(*) 
            FROM notifications 
            WHERE notifications.notifiable_id = users.id 
            AND notifications.notifiable_type = ?
            AND notifications.read_at IS NULL
        )
    ', [User::class]);
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('unread_notifications_count');
    });
}
```

---

#### **Step 2: Update Observer** (5 min)

```php
// app/Observers/NotificationObserver.php

public function created(DatabaseNotification $notification)
{
    // Broadcast notification
    broadcast(new NewNotification($notification))->toOthers();
    
    // Increment counter
    $notification->notifiable()->increment('unread_notifications_count');
}

public function updated(DatabaseNotification $notification)
{
    // If marked as read
    if ($notification->wasChanged('read_at') && $notification->read_at !== null) {
        $notification->notifiable()->decrement('unread_notifications_count');
    }
}

public function deleted(DatabaseNotification $notification)
{
    // If was unread, decrement
    if ($notification->read_at === null) {
        $notification->notifiable()->decrement('unread_notifications_count');
    }
}
```

---

#### **Step 3: Update Controller** (3 min)

```php
// Modules/Core/Http/Controllers/NotificationController.php

public function latest(Request $request): JsonResponse
{
    // ... existing code ...
    
    // ✅ Instant - no query
    $unreadCount = Auth::user()->unread_notifications_count;
    
    return response()->json([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount,
    ]);
}

public function markAsRead(string $id): JsonResponse
{
    $notification = Auth::user()->notifications()->findOrFail($id);
    
    if ($notification->read_at === null) {
        $notification->markAsRead();
        // Counter decremented automatically via Observer
    }
    
    return response()->json([
        'success' => true,
        'unread_count' => Auth::user()->unread_notifications_count,
    ]);
}

public function markAllAsRead(): JsonResponse
{
    $count = Auth::user()->unreadNotifications->count();
    Auth::user()->unreadNotifications->markAsRead();
    
    // Update counter
    Auth::user()->update(['unread_notifications_count' => 0]);
    
    return response()->json([
        'success' => true,
        'marked_count' => $count,
    ]);
}
```

---

#### **Step 4: Testing** (7 min)

```bash
# 1. Run migration
php artisan migrate

# 2. Test counter
php artisan tinker

>>> $user = \Modules\Users\Domain\Models\User::first();
>>> echo "Current count: {$user->unread_notifications_count}\n";

>>> // Create notification
>>> $user->notifications()->create([
...     'id' => (string) \Illuminate\Support\Str::uuid(),
...     'type' => 'Test',
...     'data' => ['title' => 'Test', 'message' => 'Counter test']
... ]);

>>> $user->refresh();
>>> echo "After create: {$user->unread_notifications_count}\n";  // Should increment

>>> // Mark as read
>>> $notification = $user->unreadNotifications->first();
>>> $notification->markAsRead();

>>> $user->refresh();
>>> echo "After read: {$user->unread_notifications_count}\n";  // Should decrement
```

---

## 🏥 Task 2: Health Check Endpoint with Security

### **Design Decision: Secret Token** ✅

**Why Secret Token over IP Whitelist:**
- ✅ Easier to manage
- ✅ Works with cloud monitoring services
- ✅ No firewall coordination needed
- ✅ Easy to rotate
- ✅ Works in development/staging/production

---

### **Implementation Steps:**

#### **Step 1: Environment Variable** (2 min)

```bash
# .env
HEALTH_CHECK_TOKEN=

# Generate secure token
php artisan tinker
>>> echo \Illuminate\Support\Str::random(64);
```

```bash
# .env.example
HEALTH_CHECK_TOKEN=your-secret-token-here-change-in-production
```

---

#### **Step 2: Middleware** (8 min)

```php
// app/Http/Middleware/HealthCheckToken.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HealthCheckToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-Health-Token');
        $expectedToken = config('app.health_check_token');
        
        // Allow in development without token
        if (app()->environment('local') && !$expectedToken) {
            return $next($request);
        }
        
        // Validate token
        if (!$token || !hash_equals($expectedToken, $token)) {
            \Log::warning('Unauthorized health check attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'token_provided' => $token ? 'yes' : 'no',
            ]);
            
            abort(403, 'Access denied');
        }
        
        return $next($request);
    }
}
```

```php
// app/Http/Kernel.php

protected $middlewareAliases = [
    // ... existing middleware ...
    'health.token' => \App\Http\Middleware\HealthCheckToken::class,
];
```

```php
// config/app.php

return [
    // ... existing config ...
    'health_check_token' => env('HEALTH_CHECK_TOKEN'),
];
```

---

#### **Step 3: Health Check Controller** (10 min)

```php
// app/Http/Controllers/HealthCheckController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'reverb' => $this->checkReverb(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        $hasWarnings = collect($checks)->contains(fn($check) => $check['status'] === 'warning');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : ($hasWarnings ? 'degraded' : 'unhealthy'),
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'checks' => $checks,
        ], $allHealthy ? 200 : ($hasWarnings ? 200 : 503));
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => $responseTime < 100 ? 'ok' : 'warning',
                'message' => 'Database connection successful',
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => app()->environment('production') ? 'Connection error' : $e->getMessage(),
            ];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => $responseTime < 50 ? 'ok' : 'warning',
                'message' => 'Redis connection successful',
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis connection failed',
                'error' => app()->environment('production') ? 'Connection error' : $e->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            $failed = DB::table('failed_jobs')->count();
            
            return [
                'status' => $failed > 100 ? 'warning' : 'ok',
                'message' => 'Queue is operational',
                'failed_jobs' => $failed,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed',
                'error' => app()->environment('production') ? 'Check error' : $e->getMessage(),
            ];
        }
    }

    private function checkReverb(): array
    {
        try {
            $port = config('reverb.port', 8080);
            $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
            
            if ($connection) {
                fclose($connection);
                return [
                    'status' => 'ok',
                    'message' => 'Reverb is running',
                    'port' => $port,
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Reverb is not responding',
                'port' => $port,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Reverb check failed',
                'error' => app()->environment('production') ? 'Check error' : $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $writable = is_writable(storage_path());
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            
            return [
                'status' => $usedPercent > 90 ? 'warning' : 'ok',
                'message' => 'Storage is accessible',
                'writable' => $writable,
                'free_space_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'used_percent' => round($usedPercent, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => app()->environment('production') ? 'Check error' : $e->getMessage(),
            ];
        }
    }
}
```

---

#### **Step 4: Route** (2 min)

```php
// routes/web.php

Route::get('/health', [App\Http\Controllers\HealthCheckController::class, 'check'])
    ->name('health.check')
    ->middleware(['health.token', 'throttle:60,1']);
```

---

#### **Step 5: Testing** (3 min)

```bash
# 1. Generate token
php artisan tinker
>>> echo \Illuminate\Support\Str::random(64);
# Copy to .env as HEALTH_CHECK_TOKEN

# 2. Test without token (should fail)
curl http://localhost:8000/health
# Expected: 403 Forbidden

# 3. Test with token (should work)
curl -H "X-Health-Token: your-token-here" http://localhost:8000/health

# Expected:
{
    "status": "healthy",
    "timestamp": "2026-02-14T18:10:00+00:00",
    "environment": "local",
    "checks": {
        "database": {"status": "ok", ...},
        "redis": {"status": "ok", ...},
        "queue": {"status": "ok", ...},
        "reverb": {"status": "ok", ...},
        "storage": {"status": "ok", ...}
    }
}
```

---

## 📋 Phase 2 Checklist | قائمة المهام

### **Task 1: Unread Counter** (20 min)
- [ ] Create migration
- [ ] Run migration
- [ ] Update Observer
- [ ] Update Controller
- [ ] Test increment
- [ ] Test decrement
- [ ] Test mark all as read
- [ ] Verify performance improvement

### **Task 2: Health Check** (25 min)
- [ ] Generate secure token
- [ ] Add to .env
- [ ] Create middleware
- [ ] Register middleware
- [ ] Create controller
- [ ] Add route
- [ ] Test without token (403)
- [ ] Test with token (200)
- [ ] Test all checks
- [ ] Document for monitoring team

---

## ⏱️ Timeline | الجدول الزمني

| Phase | Task | Time | Total |
|:------|:-----|:----:|:-----:|
| 1.5 | Security Validation | 18 min | 18 min |
| 2.1 | Unread Counter | 20 min | 38 min |
| 2.2 | Health Check | 25 min | 63 min |

**Total Phase 1.5 + Phase 2: ~1 hour**

---

## ✅ Success Criteria | معايير النجاح

### **Unread Counter:**
- ✅ Migration successful
- ✅ Counter increments on create
- ✅ Counter decrements on read
- ✅ Counter resets on mark all
- ✅ Performance < 1ms (vs 50-1000ms)

### **Health Check:**
- ✅ 403 without token
- ✅ 200 with valid token
- ✅ All checks return status
- ✅ Response time < 1 second
- ✅ Logs unauthorized attempts

---

**Status:** 📋 READY FOR EXECUTION  
**Prerequisites:** Phase 1.5 Complete  
**ETA:** 45 minutes

---

**هل تريد المتابعة بـ Phase 1.5 أولاً، أم لديك ملاحظات على Phase 2?**
