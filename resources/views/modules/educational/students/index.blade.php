@extends('core::layouts.master')
@section('title', __('educational::messages.students'))

@section('content')
    @include('modules.educational.shared.alerts')

    <div class="container-fluid px-0">
        {{-- ─── HEADER SECTION ─── --}}
        <div class="row mb-4 align-items-center">
            <div class="col-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-soft-success text-success rounded-circle shadow-sm fs-2">
                            <i class="ri-user-star-line"></i>
                        </span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">{{ __('educational::messages.students_management') }}</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 fs-12 text-muted">
                                <li class="breadcrumb-item"><a href="#" class="text-muted">الرئيسية</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ __('educational::messages.students') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-sm-auto mt-3 mt-sm-0">
                <div class="hstack gap-2 flex-wrap">
                    <div class="dropdown">
                        <button class="btn btn-white btn-icon shadow-none border rounded-pill" type="button"
                            data-bs-toggle="dropdown">
                            <i class="ri-more-2-fill fs-16"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <li><a class="dropdown-item py-2"
                                    href="{{ route('educational.students.export', request()->all()) }}"><i
                                        class="ri-download-2-line me-2 text-success align-bottom"></i> تصدير البيانات
                                    (CSV)</a></li>
                            <li><a class="dropdown-item py-2" href="javascript:void(0);" data-bs-toggle="modal"
                                    data-bs-target="#importStudentsModal"><i
                                        class="ri-upload-2-line me-2 text-primary align-bottom"></i> استيراد من ملف</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item py-2"
                                    href="{{ route('educational.students.emergency_contacts.export', request()->all()) }}"><i
                                        class="ri-download-2-line me-2 text-success align-bottom"></i> تصدير جهات اتصال
                                    الطوارئ
                                    (CSV)</a></li>
                            <li><a class="dropdown-item py-2" href="javascript:void(0);" data-bs-toggle="modal"
                                    data-bs-target="#importEmergencyContactsModal"><i
                                        class="ri-upload-2-line me-2 text-primary align-bottom"></i> استيراد جهات اتصال
                                    الطوارئ</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('educational.students.create') }}"
                        class="btn btn-success shadow-success px-4 rounded-pill fw-bold">
                        <i class="ri-add-line align-bottom me-1"></i> {{ __('educational::messages.add_student') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- ─── STATS SECTION ─── --}}
        <div class="row mb-4 g-3">
            @php
                $statItems = [
                    [
                        'label' => __('educational::messages.total_students'),
                        'val' => $stats['total'],
                        'icon' => 'ri-group-line',
                        'color' => 'primary',
                        'bg' => 'soft-primary',
                    ],
                    [
                        'label' => __('educational::messages.active'),
                        'val' => $stats['active'],
                        'icon' => 'ri-checkbox-circle-line',
                        'color' => 'success',
                        'bg' => 'soft-success',
                    ],
                    [
                        'label' => __('educational::messages.graduated_students'),
                        'val' => $stats['graduated'],
                        'icon' => 'ri-graduation-cap-line',
                        'color' => 'info',
                        'bg' => 'soft-info',
                    ],
                    [
                        'label' => __('educational::messages.other_statuses'),
                        'val' => $stats['other'],
                        'icon' => 'ri-error-warning-line',
                        'color' => 'warning',
                        'bg' => 'soft-warning',
                    ],
                ];
            @endphp
            @foreach ($statItems as $stat)
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm overflow-hidden h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span
                                        class="avatar-title bg-{{ $stat['bg'] }} text-{{ $stat['color'] }} rounded-circle fs-3 shadow-none border border-{{ $stat['color'] }} border-opacity-10">
                                        <i class="{{ $stat['icon'] }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-semibold text-muted mb-1 fs-11 ls-1">{{ $stat['label'] }}
                                    </p>
                                    <h4 class="mb-0 fw-black text-dark"><span class="counter-value"
                                            data-target="{{ $stat['val'] }}">{{ number_format($stat['val']) }}</span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="progress progress-sm rounded-0 opacity-25">
                            <div class="progress-bar bg-{{ $stat['color'] }}" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ─── ACTIONS & SEARCH ROW ─── --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 shadow-hover transition-base">
            <div class="card-body p-2">
                <form action="{{ route('educational.students.index') }}" method="GET">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-auto">
                            <div class="dropdown">
                                <button
                                    class="btn btn-light-subtle rounded-pill px-3 shadow-none position-relative fw-bold border"
                                    type="button" data-bs-toggle="dropdown">
                                    <i class="ri-filter-3-line me-2 align-bottom"></i> تصفية متقدمة
                                    @if (request()->anyFilled(['status', 'program_id', 'group_id', 'campus_id', 'building_id', 'floor_id', 'room_id']))
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                                    @endif
                                </button>
                                <div class="dropdown-menu dropdown-menu-start shadow-xl border-0 p-4"
                                    style="min-width: 450px; border-radius: 12px;">
                                    <h6
                                        class="dropdown-header px-0 mb-3 text-uppercase fw-black border-bottom pb-2 fs-13 text-dark">
                                        <i class="ri-sound-module-line me-2 text-success"></i> تفاصيل التصفية
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">الحالة الأكاديمية</label>
                                            <select name="status"
                                                class="form-select form-select-sm border-light bg-light-subtle rounded-3">
                                                <option value="all">الكل</option>
                                                <option value="active"
                                                    {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                                <option value="graduated"
                                                    {{ request('status') == 'graduated' ? 'selected' : '' }}>خريج</option>
                                                <option value="withdrawn"
                                                    {{ request('status') == 'withdrawn' ? 'selected' : '' }}>منسحب</option>
                                                <option value="suspended"
                                                    {{ request('status') == 'suspended' ? 'selected' : '' }}>موقف</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">البرنامج</label>
                                            <select name="program_id"
                                                class="form-select form-select-sm border-light bg-light-subtle rounded-3">
                                                <option value="">كل البرامج</option>
                                                @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}"
                                                        {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                                        {{ $program->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">المجموعة</label>
                                            <select name="group_id"
                                                class="form-select form-select-sm border-light bg-light-subtle rounded-3">
                                                <option value="">كل المجموعات</option>
                                                @foreach ($groups as $group)
                                                    <option value="{{ $group->id }}"
                                                        {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                                        {{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">المقر/الحرم</label>
                                            <select name="campus_id"
                                                class="form-select form-select-sm border-light bg-light-subtle rounded-3">
                                                <option value="">كل المقرات</option>
                                                @foreach ($campuses as $campus)
                                                    <option value="{{ $campus->id }}"
                                                        {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                                                        {{ $campus->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">البناية</label>
                                            <select name="building_id"
                                                class="form-select form-select-sm border-light bg-light-subtle rounded-3">
                                                <option value="">كل البنايات</option>
                                                @foreach ($buildings as $building)
                                                    <option value="{{ $building->id }}"
                                                        {{ request('building_id') == $building->id ? 'selected' : '' }}>
                                                        {{ $building->name }} ({{ $building->campus->name ?? '' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">الدور</label>
                                            <select name="floor_id"
                                                class="form-select form-select-sm border-light bg-light-subtle rounded-3">
                                                <option value="">كل الأدوار</option>
                                                @foreach ($floors as $floor)
                                                    <option value="{{ $floor->id }}"
                                                        {{ request('floor_id') == $floor->id ? 'selected' : '' }}>
                                                        {{ $floor->name }} ({{ $floor->building->name ?? '' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold text-muted">المعمل/القاعة</label>
                                            <select name="room_id"
                                                class="form-select form-select-sm border-light bg-light-subtle rounded-3">
                                                <option value="">كل القاعات</option>
                                                @foreach ($rooms as $room)
                                                    <option value="{{ $room->id }}"
                                                        {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                                        {{ $room->name }} ({{ $room->floor->name ?? '' }} -
                                                        {{ $room->floor->building->name ?? '' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="p-3 bg-light rounded-3 mt-1 border border-dashed text-center">
                                                <p class="mb-0 small text-muted">يمكنك دمج أكثر من فلتر للحصول على معلومات
                                                    دقيقة جداً.</p>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-3 d-flex gap-2">
                                            <button type="submit"
                                                class="btn btn-success btn-sm flex-grow-1 rounded-pill fw-bold py-2 shadow-sm">تطبيق
                                                الفلاتر</button>
                                            @if (request()->anyFilled(['status', 'program_id', 'group_id', 'campus_id', 'building_id', 'floor_id', 'room_id']))
                                                <a href="{{ route('educational.students.index') }}"
                                                    class="btn btn-soft-danger btn-sm rounded-pill px-4 py-2 border">إلغاء</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="position-relative">
                                <i class="ri-search-2-line search-icon-v2 text-muted"></i>
                                <input type="text" name="search"
                                    class="form-control form-control-lg border-0 bg-transparent fs-14 ps-5 shadow-none"
                                    placeholder="ابحث عن متدرب بالاسم، البريد، أو الرقم التعريفي..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-auto pe-3">
                            <span class="text-muted fs-13"><i class="ri-timer-line me-1"></i> آخر تحديث:
                                {{ now()->translatedFormat('H:i') }}</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─── STUDENT GRID ─── --}}
        <div class="student-main-content mb-5">
            <div class="row g-3" id="student-grid-view">
                @forelse($students as $student)
                    @php
                        $user = $student->user;
                        $fullName = $student->arabic_name ?? $user->full_name;
                        $initials = mb_strtoupper(
                            mb_substr($user->first_name ?? ($student->arabic_name ?? 'م'), 0, 1) .
                                mb_substr($user->last_name ?? ($student->arabic_name ?? 'ت'), 0, 1),
                        );
                        $statusConfig = [
                            'active' => ['class' => 'success', 'icon' => 'ri-checkbox-circle-fill', 'pulse' => true],
                            'on_leave' => ['class' => 'warning', 'icon' => 'ri-time-fill', 'pulse' => false],
                            'graduated' => ['class' => 'info', 'icon' => 'ri-graduation-cap-fill', 'pulse' => false],
                            'withdrawn' => ['class' => 'danger', 'icon' => 'ri-close-circle-fill', 'pulse' => false],
                            'suspended' => [
                                'class' => 'secondary',
                                'icon' => 'ri-indeterminate-circle-fill',
                                'pulse' => false,
                            ],
                        ];
                        $conf = $statusConfig[$student->enrollment_status] ?? [
                            'class' => 'dark',
                            'icon' => 'ri-question-fill',
                            'pulse' => false,
                        ];
                    @endphp
                    <div class="col-xxl-3 col-xl-4 col-md-6 student-card-item">
                        <div class="card card-student border-0 h-100 shadow-sm transition-all rounded-4 overflow-hidden">
                            <div class="card-header border-0 bg-transparent p-3 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-subtle-{{ $conf['class'] }} rounded-pill px-3 py-1 fs-11">
                                        <i class="{{ $conf['icon'] }} me-1 {{ $conf['pulse'] ? 'pulse-icon' : '' }}"></i>
                                        {{ __('educational::messages.' . $student->enrollment_status) }}
                                    </span>
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-soft-secondary btn-icon btn-sm rounded-circle border-0 shadow-none"
                                            type="button" data-bs-toggle="dropdown">
                                            <i class="ri-more-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                                            <li><a class="dropdown-item py-2"
                                                    href="{{ route('educational.students.show', $student->id) }}"><i
                                                        class="ri-eye-line me-2 text-muted"></i> عرض الملف الشخصي</a></li>
                                            <li><a class="dropdown-item py-2"
                                                    href="{{ route('educational.students.edit', $student->id) }}"><i
                                                        class="ri-pencil-line me-2 text-info"></i>تعديل البيانات</a></li>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li><a class="dropdown-item py-2 text-danger" href="javascript:void(0);"
                                                    onclick="confirmDeleteStudent({{ $student->id }})"><i
                                                        class="ri-delete-bin-fill me-2"></i>حذف المتدرب</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4 text-center">
                                <div class="avatar-wrapper mx-auto mb-3 position-relative">
                                    <div class="avatar-lg mx-auto">
                                        <span
                                            class="avatar-title bg-soft-success text-success rounded-4 fw-black fs-24 border-3 border-white shadow-sm shimmer">
                                            {{ $initials }}
                                        </span>
                                    </div>
                                    @if ($conf['pulse'])
                                        <span
                                            class="position-absolute bottom-0 end-0 p-1 bg-success border border-white border-2 rounded-circle shadow-sm"
                                            style="width: 14px; height: 14px;"></span>
                                    @endif
                                </div>
                                <h5 class="fw-black mb-1 text-dark truncate-1">
                                    <a href="{{ route('educational.students.show', $student->id) }}"
                                        class="text-dark">{{ $student->arabic_name ?? $user->full_name }}</a>
                                </h5>
                                <p class="text-muted mb-3 fs-13"><i
                                        class="ri-fingerprint-line me-1 text-success align-bottom"></i>{{ $user->username }}
                                </p>

                                <div class="info-badges d-flex flex-wrap justify-content-center gap-1 mb-3">
                                    <span class="badge bg-light text-muted border px-2 py-1 fs-11 rounded-3">
                                        <i class="ri-book-open-line me-1 text-primary"></i>
                                        {{ $student->program->name ?? 'بدون برنامج' }}
                                    </span>
                                    <span class="badge bg-light text-muted border px-2 py-1 fs-11 rounded-3">
                                        <i class="ri-group-line me-1 text-warning"></i>
                                        {{ $student->group->name ?? '---' }}
                                    </span>
                                </div>

                                <div class="student-stats-mini row g-0 border rounded-3 bg-light-subtle overflow-hidden">
                                    <div class="col-6 border-end py-2">
                                        <span class="d-block fs-10 text-uppercase text-muted fw-bold ls-1 mb-1">الرتبة
                                            التجارية</span>
                                        <span
                                            class="fw-semibold fs-12 text-dark">{{ $student->jobProfile->name ?? '---' }}</span>
                                    </div>
                                    <div class="col-6 py-2">
                                        <span class="d-block fs-10 text-uppercase text-muted fw-bold ls-1 mb-1">تاريخ
                                            الانضمام</span>
                                        <span
                                            class="fw-semibold fs-12 text-dark">{{ optional($student->created_at)->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="card-footer border-0 p-3 bg-light-subtle d-flex align-items-center justify-content-center gap-4">
                                <a href="mailto:{{ $user->email }}" class="btn-action-circle bg-soft-danger text-danger"
                                    title="{{ $user->email }}"><i class="ri-mail-line"></i></a>
                                <a href="tel:{{ $user->phone }}" class="btn-action-circle bg-soft-success text-success"
                                    title="{{ $user->phone }}"><i class="ri-phone-line"></i></a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}" target="_blank"
                                    class="btn-action-circle bg-soft-info text-info"><i class="ri-whatsapp-line"></i></a>
                            </div>
                        </div>
                        <form id="delete-student-form-{{ $student->id }}"
                            action="{{ route('educational.students.destroy', $student->id) }}" method="POST"
                            class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center">
                        <div class="empty-state">
                            <div class="avatar-xl mx-auto mb-4">
                                <div class="avatar-title bg-light-subtle text-muted rounded-circle fs-1 display-3">
                                    <i class="ri-user-search-fill opacity-25"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold">لم يتم العثور على أي متدربين!</h4>
                            <p class="text-muted mx-auto" style="max-width: 400px;">تأكد من معايير البحث والفلترة أو قم
                                بإضافة متدرب جديد إلى النظام.</p>
                            <a href="{{ route('educational.students.create') }}"
                                class="btn btn-success px-5 rounded-pill shadow-lg mt-3 fw-bold">إضافة متدرب جديد</a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- ─── PAGINATION SECTION ─── --}}
            @if ($students->hasPages())
                <div class="pagination-premium mt-5 mb-0 px-2 py-4 bg-white rounded-4 shadow-sm border">
                    <div class="row align-items-center">
                        <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
                            <span class="text-muted fs-13 fw-medium">
                                إظهار الصفوف من <span class="text-dark fw-bold">{{ $students->firstItem() }}</span> إلى
                                <span class="text-dark fw-bold">{{ $students->lastItem() }}</span>
                                من إجمالي <span
                                    class="bg-soft-success text-success px-2 py-1 rounded fs-12 fw-bold ms-1">{{ $students->total() }}</span>
                                سجل متاح
                            </span>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-center justify-content-sm-end">
                            <nav class="custom-pagination-v2">
                                {{ $students->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── MODALS ─── --}}
    <div class="modal fade" id="importStudentsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 bg-soft-primary p-4 pb-0">
                    <div class="avatar-sm flex-shrink-0 me-3">
                        <span class="avatar-title bg-primary text-white rounded-circle fs-4">
                            <i class="ri-upload-cloud-2-line"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black">استيراد جماعي</h5>
                        <p class="text-muted small mb-0 fs-12 ls-0">تأكد من توافق ملف CSV مع القوالب المعتمدة.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('educational.students.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-soft-info border-0 shadow-none mb-4 rounded-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-info fs-13"><i class="ri-lightbulb-line me-1"></i>نصيحة
                                        سريعة:</h6>
                                    <p class="mb-3 fs-11 text-muted">قم بتحميل القالب الفارغ لملئه بالبيانات لضمان عدم حدوث
                                        أخطاء أثناء الاستيراد.</p>
                                    <a href="{{ route('educational.students.template') }}"
                                        class="btn btn-info btn-sm rounded-pill px-3 shadow-sm border-0 fs-11 fw-bold">
                                        <i class="ri-download-2-fill me-1"></i> تحميل قالب البيانات الأساسي
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div
                            class="upload-area p-4 border border-2 border-dashed rounded-4 text-center transition-base mb-2">
                            <input type="file" name="file" class="form-control d-none" id="import_file" required
                                accept=".csv">
                            <label for="import_file" class="cursor-pointer mb-0">
                                <div class="mb-3">
                                    <i class="ri-file-excel-2-line text-muted display-4 opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-dark">اختر ملف .CSV من جهازك</h6>
                                <p class="text-muted small">أو قم بسحب وإسقاط الملف هنا مباشرة</p>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-ghost-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-primary fw-bold">بدء
                            الاستيراد للبيانات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Emergency Contacts Import Modal -->
    <div class="modal fade" id="importEmergencyContactsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 bg-soft-success p-4 pb-0">
                    <div class="avatar-sm flex-shrink-0 me-3">
                        <span class="avatar-title bg-success text-white rounded-circle fs-4">
                            <i class="ri-upload-cloud-2-line"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black">استيراد جهات اتصال الطوارئ</h5>
                        <p class="text-muted small mb-0 fs-12 ls-0">يجب إضافة البريد الإلكتروني للطالب في كل صف لربط القريب
                            به.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('educational.students.emergency_contacts.import') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-soft-success border-0 shadow-none mb-4 rounded-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-success fs-13"><i
                                            class="ri-lightbulb-line me-1"></i>نصيحة
                                        سريعة:</h6>
                                    <p class="mb-3 fs-11 text-muted">قم بتحميل قالب الطوارئ لملئه بالبيانات. يمكنك تكرار
                                        البريد الإلكتروني للطالب لإضافة أكثر من قريب لنفس الطالب.</p>
                                    <a href="{{ route('educational.students.emergency_contacts.template') }}"
                                        class="btn btn-success btn-sm rounded-pill px-3 shadow-sm border-0 fs-11 fw-bold">
                                        <i class="ri-download-2-fill me-1"></i> تحميل قالب الطوارئ
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div
                            class="upload-area p-4 border border-2 border-success-dashed rounded-4 text-center transition-base mb-2">
                            <input type="file" name="file" class="form-control d-none" id="import_contacts_file"
                                required accept=".csv">
                            <label for="import_contacts_file" class="cursor-pointer mb-0">
                                <div class="mb-3">
                                    <i class="ri-file-excel-2-line text-muted display-4 opacity-50"></i>
                                </div>
                                <h6 class="fw-bold text-dark">اختر ملف .CSV من جهازك</h6>
                                <p class="text-muted small">أو قم بسحب وإسقاط الملف هنا مباشرة</p>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-ghost-secondary px-4 fw-bold"
                            data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success px-5 rounded-pill shadow-success fw-bold">بدء
                            الاستيراد للبيانات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        :root {
            --vz-card-border-radius: 16px;
        }

        .fw-black {
            font-weight: 800;
        }

        .ls-1 {
            letter-spacing: 0.5px;
        }

        .transition-base {
            transition: all 0.25s ease;
        }

        .shimmer {
            animation: 2s shimmer infinite linear;
            background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
            background-size: 200% 100%;
        }

        @keyframes shimmer {
            to {
                background-position: -200% 0;
            }
        }

        .card-animate {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .card-animate:hover {
            transform: translateY(-5px);
        }

        .shadow-hover:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        }

        .search-icon-v2 {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            z-index: 1;
        }

        .card-student {
            border: 1px solid rgba(0, 0, 0, 0.02) !important;
            transition: all 0.35s ease;
        }

        .card-student:hover {
            border-color: rgba(10, 179, 156, 0.2) !important;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06) !important;
            transform: translateY(-8px);
        }

        .card-student:hover .avatar-title {
            transform: scale(1.1) rotate(2deg);
            background-color: #0ab39c !important;
            color: white !important;
        }

        .avatar-wrapper {
            width: fit-content;
        }

        .avatar-wrapper .avatar-lg {
            width: 4.5rem;
            height: 4.5rem;
        }

        .avatar-title {
            transition: all 0.3s ease;
        }

        .btn-action-circle {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-action-circle:hover {
            transform: scale(1.15) translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            filter: brightness(0.95);
        }

        .pulse-icon {
            animation: 2s pulse infinite;
            display: inline-block;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                opacity: 1;
            }

            50% {
                transform: scale(1.15);
                opacity: 0.7;
            }

            100% {
                transform: scale(0.95);
                opacity: 1;
            }
        }

        .truncate-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .upload-area:hover {
            border-color: var(--vz-primary) !important;
            background: var(--vz-light);
        }

        .badge-subtle-success {
            background: rgba(10, 179, 156, 0.1);
            color: #0ab39c;
        }

        .badge-subtle-info {
            background: rgba(41, 156, 219, 0.1);
            color: #299cdb;
        }

        .badge-subtle-warning {
            background: rgba(247, 184, 75, 0.1);
            color: #f7b84b;
        }

        .badge-subtle-danger {
            background: rgba(240, 101, 72, 0.1);
            color: #f06548;
        }

        .badge-subtle-secondary {
            background: rgba(53, 119, 241, 0.1);
            color: #3577f1;
        }

        /* Premium Pagination Customize */
        .custom-pagination-v2 .pagination {
            gap: 6px;
            margin-bottom: 0;
        }

        .custom-pagination-v2 .page-link {
            border: none !important;
            border-radius: 10px !important;
            background: #f3f3f9;
            color: #495057;
            font-weight: 700;
            padding: 10px 16px;
            transition: all 0.2s ease;
        }

        .custom-pagination-v2 .page-item.active .page-link {
            background-color: var(--vz-success) !important;
            color: white !important;
            box-shadow: 0 5px 15px rgba(10, 179, 156, 0.3);
        }

        .custom-pagination-v2 .page-item.disabled .page-link {
            background: transparent;
            opacity: 0.4;
        }

        .custom-pagination-v2 .page-link:hover:not(.active) {
            background: #e2e2eb;
            color: #000;
            transform: translateY(-2px);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Counter animation
            const counters = document.querySelectorAll('.counter-value');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const count = 0;
                const updateCount = () => {
                    const current = +counter.innerText.replace(/,/g, '');
                    const increment = target / 50;
                    if (current < target) {
                        counter.innerText = Math.ceil(current + increment).toLocaleString();
                        setTimeout(updateCount, 20);
                    } else {
                        counter.innerText = target.toLocaleString();
                    }
                };
                updateCount();
            });
        });

        function confirmDeleteStudent(id) {
            Swal.fire({
                title: "هل أنت متأكد؟",
                text: "لا يمكنك استعادة بيانات هذا المتدرب بعد الحذف!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "نعم، احذف الملف",
                cancelButtonText: "تراجع عن الحذف",
                customClass: {
                    confirmButton: 'btn btn-danger w-xs me-2 shadow-sm rounded-pill fw-bold',
                    cancelButton: 'btn btn-light w-xs shadow-none rounded-pill fw-bold'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-student-form-' + id).submit();
                }
            });
        }
    </script>
@endpush
