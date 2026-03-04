@extends('core::layouts.master')
@section('title', __('educational::messages.programs'))

@section('content')

@include('modules.educational.shared.alerts')

<div class="row mb-4 align-items-center g-3">
    <div class="col-sm">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 avatar-sm me-3">
                <div class="avatar-title bg-soft-primary text-primary rounded-circle fs-2">
                    <i class="ri-rocket-line"></i>
                </div>
            </div>
            <div>
                <h4 class="mb-1 fw-bold">{{ __('educational::messages.programs') }}</h4>
                <p class="text-muted mb-0 small">إدارة وتتبع البرامج التدريبية المتاحة وتوزيعها على الفروع.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('educational.programs.create') }}" class="btn btn-primary shadow-sm px-4">
                <i class="ri-add-line align-middle me-1 fw-bold"></i> {{ __('educational::messages.add_program') }}
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm bg-light-subtle">
            <div class="card-body p-3">
                <form action="{{ route('educational.programs.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="search-box">
                            <input type="text" name="search" class="form-control bg-white border-0 py-2" placeholder="بحث باسم البرنامج أو الكود..." value="{{ request('search') }}">
                            <i class="ri-search-line search-icon text-muted ms-2"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select bg-white border-0 py-2">
                            <option value="">كل الحالات</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('educational::messages.status_draft') }}</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>{{ __('educational::messages.status_published') }}</option>
                            <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>{{ __('educational::messages.status_running') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('educational::messages.status_completed') }}</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>{{ __('educational::messages.status_archived') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info w-100 shadow-sm py-2">تطبيق البحث</button>
                    </div>
                    @if(request()->anyFilled(['search', 'status']))
                        <div class="col-md-2">
                            <a href="{{ route('educational.programs.index') }}" class="btn btn-soft-danger w-100 py-2">إعادة تعيين</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @forelse($programs as $program)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm card-animate program-card overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            @if($program->pendingApprovalRequest())
                                <span class="badge badge-soft-warning fs-11 px-2 py-1">
                                    <i class="ri-time-line me-1"></i> بانتظار الموافقة على الحذف
                                </span>
                            @else
                                @php
                                    $statusClass = match($program->status) {
                                        'draft' => 'badge-soft-secondary',
                                        'published' => 'badge-soft-info',
                                        'running' => 'badge-soft-success',
                                        'completed' => 'badge-soft-primary',
                                        'archived' => 'badge-soft-dark',
                                        default => 'badge-soft-light'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} fs-11 px-2 py-1">
                                    {{ __('educational::messages.status_'.$program->status) }}
                                </span>
                            @endif
                        </div>
                        <div class="dropdown flex-shrink-0">
                            <a href="javascript:void(0);" class="text-muted" data-bs-toggle="dropdown">
                                <i class="ri-more-2-fill fs-18"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                <a class="dropdown-item py-2" href="{{ route('educational.programs.edit', $program->id) }}"><i class="ri-pencil-line me-2 text-muted"></i> {{ __('educational::messages.edit') }}</a>
                                <div class="dropdown-divider"></div>
                                @if(!$program->pendingApprovalRequest())
                                    <a href="javascript:void(0);" class="dropdown-item py-2 text-danger" onclick="confirmDeleteProgram({{ $program->id }})">
                                        <i class="ri-delete-bin-line me-2"></i> {{ __('educational::messages.delete') }}
                                    </a>
                                @else
                                    <button class="dropdown-item py-2 text-muted opacity-50 cursor-not-allowed" disabled>
                                        <i class="ri-delete-bin-line me-2"></i> قيد المراجعة
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-sm flex-shrink-0 me-3">
                            <div class="avatar-title bg-soft-primary text-primary rounded-3 fs-20">
                                <i class="ri-graduation-cap-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h5 class="fs-15 mb-1 text-truncate fw-bold">
                                <a href="{{ route('educational.programs.edit', $program->id) }}" class="text-dark">{{ $program->name }}</a>
                            </h5>
                            <p class="text-muted text-truncate mb-0 small">كود البرنامج: <span class="fw-medium text-info">{{ $program->code }}</span></p>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top border-top-dashed">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="text-muted fs-11 mb-1 d-block text-uppercase fw-bold">تاريخ البدء</label>
                                <span class="fw-semibold text-dark fs-12"><i class="ri-calendar-event-line me-1 text-muted"></i> {{ $program->starts_at ? \Carbon\Carbon::parse($program->starts_at)->format('Y-m-d') : '-' }}</span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted fs-11 mb-1 d-block text-uppercase fw-bold">تاريخ الانتهاء</label>
                                <span class="fw-semibold text-dark fs-12"><i class="ri-calendar-check-line me-1 text-muted"></i> {{ $program->ends_at ? \Carbon\Carbon::parse($program->ends_at)->format('Y-m-d') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="text-muted fs-11 mb-2 d-block text-uppercase fw-bold">الفروع والمقار المتاحة ({{ $program->campuses_count }})</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($program->campuses->take(3) as $campus)
                                <span class="badge bg-light text-dark border border-light fw-medium">{{ $campus->name }}</span>
                            @endforeach
                            @if($program->campuses_count > 3)
                                <span class="badge bg-soft-info text-info">+{{ $program->campuses_count - 3 }}</span>
                            @endif
                            @if($program->campuses_count == 0)
                                <span class="text-muted fs-11 italic">لم يتم تحديد فروع بعد</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light-subtle p-0 border-top-0 overflow-hidden">
                    <div class="btn-group w-100">
                        <a href="{{ route('educational.programs.edit', $program->id) }}" class="btn btn-ghost-primary rounded-0 py-2 fs-12 fw-semibold border-end">
                            <i class="ri-edit-line me-1"></i> تعديل
                        </a>
                        @if(!$program->pendingApprovalRequest())
                            <a href="javascript:void(0);" onclick="confirmDeleteProgram({{ $program->id }})" class="btn btn-ghost-danger rounded-0 py-2 fs-12 fw-semibold">
                                <i class="ri-delete-bin-line me-1"></i> حذف
                            </a>
                        @else
                            <button class="btn btn-ghost-muted rounded-0 py-2 fs-12 fw-semibold opacity-50" disabled>
                                <i class="ri-time-line me-1"></i> قيد المراجعة
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="avatar-xl mx-auto mb-4 bg-soft-primary bg-opacity-10 rounded-circle p-3">
                <i class="ri-rocket-line fs-1 text-primary opacity-50"></i>
            </div>
            <h5 class="fw-bold text-dark">لا توجد برامج تدريبية مسجلة</h5>
            <p class="text-muted">ابدأ بإضافة أول برنامج لتدريبي للمنصة الآن.</p>
            <a href="{{ route('educational.programs.create') }}" class="btn btn-primary mt-3 px-4 rounded-pill shadow">
                <i class="ri-add-line align-middle me-1"></i> إضافة برنامج جديد
            </a>
        </div>
    @endforelse
</div>

@push('styles')
<style>
    .program-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .program-card:hover { transform: translateY(-7px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
    .search-box { position: relative; }
    .search-box .search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); }
    [dir="rtl"] .search-box .search-icon { left: auto; right: 13px; }
    [dir="rtl"] .search-box input { padding-left: 12px; padding-right: 48px; }
</style>
@endpush

@push('scripts')
<script>
    function confirmDeleteProgram(id) {
        Swal.fire({
            title: '{{ __("educational::messages.delete_program_title") }}',
            text: '{{ __("educational::messages.delete_program_text") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ __("educational::messages.yes_delete") }}',
            cancelButtonText: '{{ __("educational::messages.cancel") }}',
            customClass: {
                confirmButton: 'btn btn-danger w-xs me-2',
                cancelButton: 'btn btn-light w-xs'
            },
            buttonsStyling: false,
            showCloseButton: true,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-program-form-' + id).submit();
            }
        });
    }
</script>
@endpush

@foreach($programs as $program)
    <form id="delete-program-form-{{ $program->id }}" action="{{ route('educational.programs.destroy', $program->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endforeach

@endsection
