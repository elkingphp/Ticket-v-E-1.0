# دليل إنشاء وحدة جديدة (Module Construction Blueprint)

هذا الدليل يعتبر **وثيقة تنفيذية (Executable Documentation)**. باتباع هذه الخطوات بالترتيب الحرفي، ستحصل على **وحدة (Module) جديدة متوافقة 100% مع معايير النظام البنائية**.

---

## الخطوة 1: التحضير والتوليد (Module Initialization)
**الهدف:** تجهيز بيئة العمل وإنشاء الهيكل الأساسي للوحدة النمطية.
**الأمر:**
```bash
php artisan module:make Inventory
```
**النتيجة المتوقعة:**
إنشاء مجلد الوحدة تحت `app/Modules/Inventory/` وملف `module.json` وتسجيلها في النظام.

---

## الخطوة 2: تهيئة معمارية المجلدات (Folder Structure)
**الهدف:** التأكد من مطابقة المجلدات لمعيار الطبقات (Layered Architecture) المؤسسي لمنع التشابك.
**الأمر:**
```bash
mkdir -p app/Modules/Inventory/{Models,Services,Repositories,Http/Requests,Policies,Events,Listeners,Contracts,Graph}
```
*ملاحظة: تأكد دائماً من فصل الـ Services (منطق الأعمال) والـ Repositories (قواعد البيانات) عن الـ Controllers.*

---

## الخطوة 3: تجهيز قاعدة البيانات (Database Migration)
**الهدف:** تعريف الجداول وضمان وجود أعمدة التدقيق والمعرفات الصلبة.
**الأمر:**
```bash
php artisan module:make-migration create_inventory_items_table Inventory
```
**الكود (داخل ملف الهجرة):**
```php
Schema::create('inventory_items', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('name');
    
    // الأعمدة الإلزامية للتدقيق (Audit)
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
    
    $table->timestamps();
    $table->softDeletes(); // إلزامية الحذف المنطقي
});
```
**التحقق:**
تشغيل `php artisan migrate` للتأكد من عدم وجود أخطاء SQL.

---

## الخطوة 4: تهيئة نموذج البيانات (Eloquent Model)
**الهدف:** ربط الجدول بالنظام مع تفعيل السمات الإلزامية كالتدقيق والـ UUID.
**الملف:** `app/Modules/Inventory/Models/InventoryItem.php`
```php
namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\LogsActivity;

class InventoryItem extends Model
{
    use HasUuid, SoftDeletes, LogsActivity;

    protected $guarded = ['id', 'uuid', 'created_by'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
```

---

## الخطوة 5: طبقة الوصول للبيانات (Repository Layer)
**الهدف:** تغليف عمليات الاستعلامات المعقدة وفصلها.
**الملف:** `app/Modules/Inventory/Repositories/InventoryRepository.php`
```php
namespace Modules\Inventory\Repositories;

use Modules\Inventory\Models\InventoryItem;

class InventoryRepository
{
    public function create(array $data): InventoryItem {
        return InventoryItem::create($data);
    }
}
```

---

## الخطوة 6: طبقة منطق الأعمال (Service Layer)
**الهدف:** تنفيذ الإجراءات المركزية وتطبيق الـ Transactions. لا يجب كتابة Logic داخل المتحكم.
**الملف:** `app/Modules/Inventory/Services/InventoryService.php`
```php
namespace Modules\Inventory\Services;

use Modules\Inventory\Repositories\InventoryRepository;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(private InventoryRepository $repo) {}

    public function createItem(array $data) {
        return DB::transaction(function () use ($data) {
            $item = $this->repo->create($data);
            // إطلاق الأحداث والإشعارات يتم هنا
            return $item;
        });
    }
}
```

---

## الخطوة 7: واجهات HTTP والمسارات (Controllers & Routes)
**الهدف:** تسجيل نقاط الاتصال (Endpoints) بشكل آمن.
**الملف:** `app/Modules/Inventory/Http/Controllers/InventoryController.php`
```php
namespace Modules\Inventory\Http\Controllers;

use Modules\Inventory\Services\InventoryService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function store(Request $request, InventoryService $service) {
        $item = $service->createItem($request->all()); // استبدالها بـ FormRequest مستقبلاً
        return response()->json($item, 201);
    }
}
```
**ملف المسارات:** `app/Modules/Inventory/routes.php`
```php
Route::middleware(['auth'])->prefix('api/inventory')->group(function () {
    Route::post('/', [InventoryController::class, 'store']);
});
```

---

## الخطوة 8: حواجز الصلاحيات (Policy & Registration)
**الهدف:** تقييد الإجراءات بصلاحيات المستخدمين.
**الأمر:**
```bash
php artisan make:policy InventoryPolicy -m InventoryItem
```
*يتم تسجيل هذه السياسة للتأكد من ربط الحماية بطلبات الدخول.*

---

## الخطوة 9: التسجيل الشامل (DomainGraph Registration - أساسي جداً)
**الهدف:** إشعار الـ Domain Graph المركزي بوجود الموديول الجديد لتفعيل عمليات البحث وتخطيط الترابط.
**الملف:** إنشاء `app/Modules/Inventory/Graph/InventoryDomainGraphBuilder.php`
```php
namespace Modules\Inventory\Graph;

use App\Contracts\DomainGraphNode;
use App\Services\DomainGraph;

class InventoryDomainGraphBuilder implements DomainGraphNode {
    public function registerNodes(DomainGraph $graph): void {
        $graph->addNode('inventory', [
            'model' => \Modules\Inventory\Models\InventoryItem::class,
            'dependencies' => ['users']
        ]);
    }
}
```
*أضفه إلى دالة التسجيل في مزود الخدمة الرئيسي.*

---

## الخطوة 10: المراقبة والتدقيق (Audit & Events)
**الهدف:** تطبيق مبدأ Fire-and-Forget لتسجيل كل شاردة وواردة في السجل وتخفيف الضغط.
```php
// استدعاء هذا أثناء الإدخال/التعديل عبر الـ Service Layer
\App\Services\AuditLoggerService::log(
    action: 'created',
    model: $item,
    actor: auth()->user(),
    context: ['ip' => request()->ip()]
);
```

---

## الخطوة 11: التقييم والاختبار (Testing Integration)
**الهدف:** إضافة تأصيل آلي للميزات للحماية المستمرة.
**الملف:** `app/Modules/Inventory/Tests/Feature/InventoryTest.php`
```php
test('authorized user can access module', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson('/api/inventory', ['name' => 'Test'])->assertCreated();
});
```

---

✅ **التحقق النهائي (Flight Pre-Check):**
- [ ] أمر الهجرة `php artisan migrate` يعمل بنجاح.
- [ ] النموذج يطابق قاعدة البيانات ויعمل عبر Tinker.
- [ ] المسارات مُأمنة ولا تعمل كزائر بدون تسجيل دخول.
- [ ] التدقيق Audit يكتب في الجداول عند الإضافة.
- [ ] الاختبارات الآلية ناجحة والاكواد سليمة.

إذا تم تأكيد ما سبق، مبروك، هيكل الوحدة جاهز للإنتاج.
