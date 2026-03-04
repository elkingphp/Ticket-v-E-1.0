@extends('core::layouts.master')

@section('title', 'إعدادات نظام التقييم')

@section('title-actions')
    <a href="{{ route('educational.evaluations.forms.index') }}"
        class="btn btn-soft-secondary btn-sm d-flex align-items-center">
        <i class="ri-arrow-go-back-line me-1"></i> العودة للنماذج
    </a>
@endsection

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row mt-n2">
        <div class="col-xl-9 col-lg-10 mx-auto">
            {{-- ─── Tabs Navigation ────────────────────────────────────────── --}}
            <div class="card overflow-hidden border-0 shadow-sm mb-4 bg-glass">
                <div class="card-header bg-soft-primary border-0 p-0">
                    <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#notifications" role="tab">
                                <i class="ri-notification-3-line me-1 align-bottom"></i> الإشعارات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#analytics" role="tab">
                                <i class="ri-bar-chart-box-line me-1 align-bottom"></i> التحليلات و الـ Cache
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#scheduler" role="tab">
                                <i class="ri-calendar-todo-line me-1 align-bottom"></i> الجدولة والتقارير
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#evaluation-types" role="tab">
                                <i class="ri-file-settings-line me-1 align-bottom"></i> أنواع النماذج
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <form id="mainSettingsForm" action="{{ route('educational.evaluations.settings.update') }}" method="POST">
                @csrf
                {{-- ─── Tabs Content ───────────────────────────────────────────── --}}
                <div class="tab-content" id="main-tabs-content">

                    {{-- Tab 1: Notifications --}}
                    <div class="tab-pane active" id="notifications" role="tabpanel">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="flex-shrink-0 avatar-sm me-3">
                                        <div class="avatar-title bg-soft-success text-success rounded-circle fs-20">
                                            <i class="ri-notification-badge-line"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">إعدادات الإشعارات</h5>
                                        <p class="text-muted mb-0">تحكم بآلية وصول التنبيهات للمستخدمين والإدارة.</p>
                                    </div>
                                </div>

                                @php
                                    $evalSettings = app(
                                        \Modules\Educational\Application\Services\EvaluationSettings::class,
                                    );
                                    $sch = $evalSettings->weeklyReportSchedule();
                                @endphp
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">قناة الإرسال المفضلة</label>
                                        <select name="notification_channel"
                                            class="form-select border-light-subtle shadow-sm">
                                            <option value="mail"
                                                {{ $evalSettings->baseSettings->get('evaluation_notifications_channel') == 'mail' ? 'selected' : '' }}>
                                                بريد إلكتروني فقط</option>
                                            <option value="database"
                                                {{ $evalSettings->baseSettings->get('evaluation_notifications_channel') == 'database' ? 'selected' : '' }}>
                                                تنبيهات داخلية فقط</option>
                                            <option value="mail_and_database"
                                                {{ $evalSettings->baseSettings->get('evaluation_notifications_channel') == 'mail_and_database' ? 'selected' : '' }}>
                                                كلاهما (موصى به)</option>
                                        </select>
                                        <small class="text-muted">تحدد القنوات التي سيتم من خلالها إرسال كافة تنبيهات
                                            النظام.</small>
                                    </div>

                                    <div class="col-md-12">
                                        <hr class="my-3 border-light opacity-50">
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch form-switch-lg mb-3">
                                            <input type="hidden" name="assignment_notify_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="assignment_notify_enabled"
                                                value="1" id="notifyAssignment"
                                                {{ $evalSettings->isAssignmentNotificationEnabled() ? 'checked' : '' }}>
                                            <label class="form-check-label ms-2" for="notifyAssignment">
                                                <strong>إشعار تعيين النموذج</strong>
                                                <span class="d-block text-muted small">إرسال تنبيه للمتدربين والمراقبين فور
                                                    تفعيل نموذج لمحاضرة.</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch form-switch-lg mb-3">
                                            <input type="hidden" name="red_flag_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="red_flag_enabled"
                                                value="1" id="notifyRedFlag"
                                                {{ $evalSettings->isRedFlagEnabled() ? 'checked' : '' }}>
                                            <label class="form-check-label ms-2" for="notifyRedFlag">
                                                <strong>تفعيل تنبيهات الخط الأحمر</strong>
                                                <span class="d-block text-muted small">إرسال إنذار فوري للإدارة عند رصد تدني
                                                    حاد في التقييم.</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Analytics & Cache --}}
                    <div class="tab-pane" id="analytics" role="tabpanel">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="flex-shrink-0 avatar-sm me-3">
                                        <div class="avatar-title bg-soft-info text-info rounded-circle fs-20">
                                            <i class="ri-radar-line"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">التحليلات والأداء</h5>
                                        <p class="text-muted mb-0">ضبط معايير الدقة وسرعة استجابة لوحة البيانات.</p>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">حافة الإنذار (Red Flag Threshold)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-danger"><i
                                                    class="ri-alarm-warning-line"></i></span>
                                            <input type="number" name="red_flag_threshold" step="0.1"
                                                min="1" max="5" class="form-control"
                                                value="{{ $evalSettings->redFlagThreshold() }}">
                                        </div>
                                        <small class="text-muted">إذا قل متوسط تقييم المراقبين عن هذا الرقم، يعتبر "مؤشر
                                            خطر".</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">مدة التخزين المؤقت (Cache Seconds)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="ri-timer-line"></i></span>
                                            <input type="number" name="results_cache_seconds" min="0"
                                                class="form-control" value="{{ $evalSettings->resultsCacheDuration() }}">
                                        </div>
                                        <small class="text-muted">المدة بالثواني التي سيتم فيها الاحتفاظ بنتائج الـ Results
                                            Dashboard قبل إعادة حسابها.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Scheduler --}}
                    <div class="tab-pane" id="scheduler" role="tabpanel">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="flex-shrink-0 avatar-sm me-3">
                                        <div class="avatar-title bg-soft-warning text-warning rounded-circle fs-20">
                                            <i class="ri-time-line"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">جدولة التقارير الأسبوعية</h5>
                                        <p class="text-muted mb-0">تحديد متى يتم إرسال ملخص الأداء للإدارة العليا.</p>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">يوم الإرسال</label>
                                        <select name="weekly_report_day" class="form-select">
                                            <option value="saturdays" {{ $sch['day'] == 'saturdays' ? 'selected' : '' }}>
                                                السبت</option>
                                            <option value="sundays" {{ $sch['day'] == 'sundays' ? 'selected' : '' }}>الأحد
                                            </option>
                                            <option value="mondays" {{ $sch['day'] == 'mondays' ? 'selected' : '' }}>
                                                الاثنين</option>
                                            <option value="tuesdays" {{ $sch['day'] == 'tuesdays' ? 'selected' : '' }}>
                                                الثلاثاء</option>
                                            <option value="wednesdays"
                                                {{ $sch['day'] == 'wednesdays' ? 'selected' : '' }}>الأربعاء</option>
                                            <option value="thursdays" {{ $sch['day'] == 'thursdays' ? 'selected' : '' }}>
                                                الخميس</option>
                                            <option value="fridays" {{ $sch['day'] == 'fridays' ? 'selected' : '' }}>
                                                الجمعة</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">وقت الإرسال</label>
                                        <div class="input-group">
                                            <input type="time" name="weekly_report_time" class="form-control"
                                                value="{{ $sch['time'] }}">
                                        </div>
                                        <small class="text-muted">سيتم إرسال التقرير في هذا الوقت من اليوم المختار
                                            أعلاه.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card bg-soft-light border-0 py-3 mb-5 shadow-sm rounded-3">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-success btn-label rounded-pill px-5">
                            <i class="ri-save-line label-icon align-middle fs-16 me-2"></i> حفظ الإعدادات المركزية
                        </button>
                    </div>
                </div>
            </form>

            {{-- Tab 4: Evaluation Types Form (Outside main form to use its own actions) --}}
            <div class="tab-content mt-4" id="types-wrapper" style="display:none">
                <div class="tab-pane active" id="evaluation-types">
                    <div class="card shadow-sm border-0 mb-4">
                        <div
                            class="card-header border-bottom border-light pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar-sm me-3">
                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-20">
                                        <i class="ri-file-settings-line"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">الأنواع المخصصة للنماذج</h5>
                                    <p class="text-muted mb-0 small">إدارة أنواع نماذج التقييم والصلاحيات المرتبطة بكل نوع.
                                    </p>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                                <i class="ri-add-line me-1"></i> إضافة نوع جديد
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-nowrap mb-0 text-center">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th>#</th>
                                            <th>اسم النوع</th>
                                            <th>المستهدف</th>
                                            <th>أدوار المقيّمين</th>
                                            <th>الحالة</th>
                                            <th>مرتبط بـ</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($types as $type)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="fw-medium text-primary">{{ $type->name }}</td>
                                                <td>{{ $targetTypes[$type->target_type] ?? $type->target_type }}</td>
                                                <td>
                                                    @foreach ($type->allowed_roles ?? [] as $role)
                                                        <span
                                                            class="badge bg-soft-secondary text-secondary">{{ $roles[$role] ?? $role }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @if ($type->is_active)
                                                        <span class="badge bg-success-subtle text-success">مفعل</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">معطل</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-soft-info text-info">{{ $type->forms_count }}
                                                        نموذج</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-sm btn-ghost-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editTypeModal{{ $type->id }}">
                                                            <i class="ri-edit-line fs-16"></i>
                                                        </button>

                                                        @if ($type->forms_count == 0)
                                                            <form
                                                                action="{{ route('educational.evaluations.types.destroy', $type->id) }}"
                                                                method="POST" class="d-inline-block"
                                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا النوع؟');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-ghost-danger">
                                                                    <i class="ri-delete-bin-line fs-16"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-muted py-4">لم يتم إضافة أية أنواع بعد.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Modals -->
            @foreach ($types as $type)
                <div class="modal fade" id="editTypeModal{{ $type->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0">
                            <form action="{{ route('educational.evaluations.types.update', $type->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-soft-primary p-3">
                                    <h5 class="modal-title">تعديل النوع: {{ $type->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <div class="mb-3">
                                        <label class="form-label">الاسم</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ $type->name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">النوع المستهدف</label>
                                        <select name="target_type" class="form-select" required>
                                            @foreach ($targetTypes as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ $type->target_type == $key ? 'selected' : '' }}>{{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">الأدوار المسموح لها بالتقييم</label>
                                        <select name="allowed_roles[]" class="form-select" multiple required
                                            style="height: 120px">
                                            @foreach ($roles as $roleValue => $roleName)
                                                <option value="{{ $roleValue }}"
                                                    {{ in_array($roleValue, $type->allowed_roles ?? []) ? 'selected' : '' }}>
                                                    {{ $roleName }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">اضغط Ctrl لتحديد أكثر من دور</small>
                                    </div>

                                    <div class="form-check form-switch mt-3 d-flex align-items-center">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input me-2" type="checkbox" name="is_active"
                                            value="1" id="activeStatus{{ $type->id }}"
                                            {{ $type->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label mb-0" for="activeStatus{{ $type->id }}">مفعل
                                            القائمة</label>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light p-3 justify-content-between">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Type Modal -->
            <div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0">
                        <form action="{{ route('educational.evaluations.types.store') }}" method="POST">
                            @csrf
                            <div class="modal-header bg-soft-primary p-3">
                                <h5 class="modal-title">إضافة نوع جديد لنموذج التقييم</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3 text-start">
                                    <label class="form-label">الاسم (مثال: تقييم مدرب عام)</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3 text-start">
                                    <label class="form-label">النوع المستهدف</label>
                                    <select name="target_type" class="form-select" required>
                                        <option value="" disabled selected>-- اختر المستهدف بهذا التقييم --</option>
                                        @foreach ($targetTypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 text-start">
                                    <label class="form-label">الأدوار المسموح لها بالتقييم</label>
                                    <select name="allowed_roles[]" class="form-select" multiple required
                                        style="height: 120px">
                                        @foreach ($roles as $roleValue => $roleName)
                                            <option value="{{ $roleValue }}">{{ $roleName }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">اضغط Ctrl لتحديد أكثر من دور</small>
                                </div>
                            </div>
                            <div class="modal-footer bg-light p-3 justify-content-between">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-primary">إضافة وحفظ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .bg-glass {
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(10px);
        }

        .nav-tabs-custom.nav-success .nav-link.active {
            color: #0ab39c;
            background-color: transparent;
            font-weight: 700;
            border-bottom: 2px solid #0ab39c;
        }

        .form-switch-lg .form-check-input {
            width: 3.5rem;
            height: 1.75rem;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle standalone tab content switching
            const tabEls = document.querySelectorAll('a[data-bs-toggle="tab"]');
            tabEls.forEach(function(el) {
                el.addEventListener('shown.bs.tab', function(e) {
                    if (e.target.hash === '#evaluation-types') {
                        document.getElementById('mainSettingsForm').style.display = 'none';
                        document.getElementById('types-wrapper').style.display = 'block';
                    } else {
                        document.getElementById('mainSettingsForm').style.display = 'block';
                        document.getElementById('types-wrapper').style.display = 'none';
                    }
                });
            });

            // If coming from a redirect and there is a hash
            if (window.location.hash === '#evaluation-types') {
                const tabEl = document.querySelector('a[href="#evaluation-types"]');
                if (tabEl) {
                    const typesTab = new bootstrap.Tab(tabEl);
                    typesTab.show();
                }
            }
        });
    </script>
@endpush
