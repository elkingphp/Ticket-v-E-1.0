@extends('core::layouts.master')
@section('title', __('educational::messages.groups'))
@push('styles')
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0" id="groupsList">
                <div class="card-header border-0 pb-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <div class="avatar-xs me-3">
                                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16 shadow-sm">
                                        <i class="ri-group-line"></i>
                                    </div>
                                </div>
                                {{ __('educational::messages.groups') }}
                            </h5>
                        </div>
                        <div class="col-sm-auto">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-soft-info" data-bs-toggle="collapse"
                                    data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                                    <i class="ri-filter-2-line align-bottom me-1"></i> فلترة
                                </button>
                                <button type="button" class="btn btn-soft-success" data-bs-toggle="modal"
                                    data-bs-target="#importModal">
                                    <i class="ri-upload-2-line align-bottom me-1"></i> استيراد
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-soft-primary" type="button" id="dropdownMenuButton"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-download-2-line align-bottom me-1"></i> تصدير
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item"
                                                href="{{ route('educational.groups.export', ['format' => 'xlsx', 'program_id' => request('program_id'), 'status' => request('status')]) }}">Excel
                                                (XLSX)</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('educational.groups.export', ['format' => 'csv', 'program_id' => request('program_id'), 'status' => request('status')]) }}">CSV</a>
                                        </li>
                                    </ul>
                                </div>
                                <a href="{{ route('educational.groups.create') }}"
                                    class="btn btn-primary add-btn shadow-sm">
                                    <i class="ri-add-line align-bottom me-1"></i>
                                    {{ __('educational::messages.add_group') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="collapse" id="filterCollapse">
                    <div class="card-body border-bottom bg-light p-3">
                        <form action="{{ route('educational.groups.index') }}" method="GET">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">البرنامج التدريبي</label>
                                    <select name="program_id" class="form-select" data-choices>
                                        <option value="">الكل</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}"
                                                {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                                {{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">الحالة</label>
                                    <select name="status" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط
                                            (Active)</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                            مكتمل (Completed)</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                            ملغي (Cancelled)</option>
                                        <option value="transferred"
                                            {{ request('status') == 'transferred' ? 'selected' : '' }}>منقول (Transferred)
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <button type="submit" class="btn btn-primary w-100"><i
                                                class="ri-search-line me-1 align-bottom"></i> بحث</button>
                                        <a href="{{ route('educational.groups.index') }}"
                                            class="btn btn-soft-secondary w-100"><i
                                                class="ri-refresh-line me-1 align-bottom"></i> إعادة ضبط</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body mt-3">
                    <div class="table-responsive table-card">
                        <table class="table table-hover table-centered align-middle table-nowrap mb-0" id="groupsTable">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">{{ __('educational::messages.group_name') }}</th>
                                    <th scope="col">{{ __('educational::messages.program') }}</th>
                                    <th scope="col">{{ __('educational::messages.term') }} /
                                        {{ __('educational::messages.capacity') }}</th>
                                    <th scope="col">{{ __('educational::messages.status') }}</th>
                                    <th scope="col" colspan="2" style="width: 150px;">
                                        {{ __('educational::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @forelse($groups as $group)
                                    <tr>
                                        <td>
                                            <a href="#" class="fw-medium link-primary">#{{ $group->id }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h5 class="fs-14 mb-1 fw-bold text-dark">{{ $group->name }}</h5>
                                                    <p class="text-muted mb-0 fs-12">
                                                        {{ $group->jobProfile->name ?? __('educational::messages.unknown') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-medium">{{ $group->program->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1"><i
                                                    class="ri-calendar-event-line align-bottom me-1"></i>
                                                {{ $group->term ?? '-' }}</span>
                                            <span class="badge bg-info-subtle text-info px-2 py-1 ms-1"><i
                                                    class="ri-team-line align-bottom me-1"></i> {{ $group->capacity }}
                                                مقعد</span>
                                        </td>
                                        <td>
                                            @if ($group->pendingApprovalRequest())
                                                <span class="badge bg-warning-subtle text-warning fs-12 px-2 py-1">
                                                    <i class="ri-time-line align-bottom me-1"></i> بانتظار الموافقة على
                                                    الحذف
                                                </span>
                                            @else
                                                @if ($group->status == 'active')
                                                    <span class="badge bg-success-subtle text-success fs-12 px-2 py-1"><i
                                                            class="ri-checkbox-circle-line align-bottom me-1"></i>
                                                        {{ __('educational::messages.status_active') }}</span>
                                                @elseif($group->status == 'cancelled')
                                                    <span class="badge bg-danger-subtle text-danger fs-12 px-2 py-1"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $group->cancellation_reason }}"><i
                                                            class="ri-close-circle-line align-bottom me-1"></i>
                                                        {{ __('educational::messages.status_cancelled') }}</span>
                                                @elseif($group->status == 'completed')
                                                    <span class="badge bg-primary-subtle text-primary fs-12 px-2 py-1"><i
                                                            class="ri-check-double-line align-bottom me-1"></i>
                                                        {{ __('educational::messages.status_completed') }}</span>
                                                @elseif($group->status == 'transferred')
                                                    <span class="badge bg-warning-subtle text-warning fs-12 px-2 py-1"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('educational::messages.transferred_to') }}: {{ $group->transferredToGroup->name ?? '' }}"><i
                                                            class="ri-share-forward-line align-bottom me-1"></i>
                                                        {{ __('educational::messages.status_transferred') }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td colspan="2">
                                            <ul class="list-inline hstack gap-2 mb-0">
                                                <li class="list-inline-item" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="top"
                                                    title="{{ __('educational::messages.edit') }}">
                                                    <a href="{{ route('educational.groups.edit', $group->id) }}"
                                                        class="text-primary d-inline-block edit-item-btn p-2 rounded bg-primary-subtle fs-14">
                                                        <i class="ri-pencil-fill"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="top"
                                                    title="{{ __('educational::messages.delete') }}">
                                                    @if (!$group->pendingApprovalRequest())
                                                        <a class="text-danger d-inline-block remove-item-btn p-2 rounded bg-danger-subtle fs-14"
                                                            href="javascript:void(0);"
                                                            onclick="confirmDelete({{ $group->id }})">
                                                            <i class="ri-delete-bin-5-fill"></i>
                                                        </a>
                                                        <form id="delete-form-{{ $group->id }}"
                                                            action="{{ route('educational.groups.destroy', $group->id) }}"
                                                            method="POST" class="d-none">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    @else
                                                        <span
                                                            class="text-muted d-inline-block p-2 rounded bg-light fs-14 opacity-50 cursor-not-allowed">
                                                            <i class="ri-delete-bin-5-fill"></i>
                                                        </span>
                                                    @endif
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="text-center py-5">
                                                <div class="avatar-md mx-auto mb-4">
                                                    <div
                                                        class="avatar-title bg-light text-primary rounded-circle fs-24 shadow-sm">
                                                        <i class="ri-group-line"></i>
                                                    </div>
                                                </div>
                                                <h5 class="mt-2 text-dark">
                                                    {{ __('educational::messages.no_groups_found') ?? 'لا توجد مجموعات' }}
                                                </h5>
                                                <p class="text-muted mb-0">لم يتم إضافة أي مجموعات تدريبية إلى النظام بعد.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-top-0 py-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-sm">
                                <div class="text-muted">
                                    عرض <span class="fw-semibold">{{ $groups->firstItem() }}</span> إلى <span
                                        class="fw-semibold">{{ $groups->lastItem() }}</span> من أصل <span
                                        class="fw-semibold">{{ $groups->total() }}</span> مجموعة
                                </div>
                            </div>
                            <div class="col-sm-auto">
                                <div class="pagination-wrap hstack gap-2 justify-content-center">
                                    {{ $groups->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">استيراد المجموعات</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('educational.groups.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <label for="importFile" class="form-label mb-0">رفع ملف (Excel أو CSV)</label>
                                <a href="{{ route('educational.groups.template') }}" class="btn btn-sm btn-soft-primary">
                                    <i class="ri-download-line align-bottom me-1"></i> تحميل قالب فارغ
                                </a>
                            </div>
                            <div class="mb-3">
                                <input class="form-control" type="file" id="importFile" name="file"
                                    accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                    required>
                            </div>
                            <div class="alert alert-info mb-0 border-0">
                                <p class="mb-0 fs-13">تأكد من أن الملف يحتوي على الأعمدة التالية كترويسة (Header):</p>
                                <ul class="mb-0 mt-2 fs-12 ps-3">
                                    <li><code>name</code> أو <code>group_name</code>: اسم المجموعة <span
                                            class="text-danger">*</span></li>
                                    <li><code>program_id</code> أو <code>program</code>: رقم أو اسم البرنامج التدريبي <span
                                            class="text-danger">*</span></li>
                                    <li><code>capacity</code>: السعة المطلوبة (مثال: 20)</li>
                                    <li><code>status</code>: الحالة (active/completed/cancelled)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-success"><i
                                    class="ri-upload-2-line align-bottom me-1"></i> استيراد</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script>
            function confirmDelete(id) {
                Swal.fire({
                    title: "{{ __('educational::messages.delete_group_title') ?? 'حذف المجموعة؟' }}",
                    text: "{{ __('educational::messages.delete_group_text') ?? 'هل أنت متأكد من حذف هذه المجموعة؟ لا يمكن التراجع عن هذا الإجراء وسيتم طلب موافقة الإدارة.' }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    cancelButtonColor: '#878a99',
                    confirmButtonText: "{{ __('educational::messages.yes_delete') ?? 'نعم، أرسل طلب الحذف!' }}",
                    cancelButtonText: "{{ __('educational::messages.cancel') ?? 'إلغاء' }}",
                    customClass: {
                        confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                        cancelButton: 'btn btn-ghost-dark w-xs mt-2'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }
        </script>
    @endpush
