@extends('core::layouts.master')
@section('title', 'طلبات المراجعة والموافقة')

@section('title-actions')
    <a href="{{ route('educational.lectures.index') }}" class="btn btn-soft-secondary btn-sm d-flex align-items-center">
        <i class="ri-arrow-go-back-line me-1"></i> العودة للمحاضرات
    </a>
@endsection

@section('content')
@include('modules.educational.shared.alerts')
<div class="row">
    <div class="col-md-4">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">إجمالي الطلبات المعلقة</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ $requests->total() }}</h4>
                        <span class="badge bg-warning-subtle text-warning"><i class="ri-time-line align-middle me-1"></i> بانتظار المراجعة</span>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-soft-info rounded fs-3">
                            <i class="ri-git-pull-request-line text-info"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">قائمة الطلبات الجديدة</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-nowrap table-striped-columns mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">رقم الطلب</th>
                                <th scope="col">المحاضرة</th>
                                <th scope="col">نوع الإجراء</th>
                                <th scope="col">مقدم الطلب</th>
                                <th scope="col">السبب / المبرر</th>
                                <th scope="col">تاريخ الطلب</th>
                                <th scope="col">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                                <tr>
                                    <td>#{{ $req->id }}</td>
                                    <td>
                                        @if($req->approvable)
                                            <span class="fw-medium text-primary">{{ $req->approvable->subject ?? $req->approvable->sessionType->name }}</span><br>
                                            <small class="text-muted">{{ $req->approvable->starts_at->format('Y-m-d H:i') }}</small>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs flex-shrink-0 me-2">
                                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                        <i class="ri-book-open-line"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="fs-14 mb-1">{{ $req->approvable->subject ?? $req->approvable->sessionType->name }}</h6>
                                                    <p class="text-muted fs-12 mb-0"><i class="ri-calendar-event-line align-middle"></i> {{ $req->approvable->starts_at->translatedFormat('Y-m-d H:i') }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger"><i class="ri-error-warning-line me-1"></i> محاضرة محذوفة</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($req->action === 'delete_lecture')
                                            <span class="badge bg-danger-subtle text-danger py-2 px-2 fs-11">
                                                <i class="ri-delete-bin-5-line me-1"></i> حذف نهائي
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning py-2 px-2 fs-11">
                                                <i class="ri-close-circle-line me-1"></i> إلغاء فقط
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ri-user-star-line me-2 text-muted fs-17"></i>
                                            {{ $req->requester->full_name ?? $req->requester->name }}
                                        </div>
                                    </td>
                                    <td style="white-space: normal; min-width: 250px;">
                                        <div class="p-2 bg-light rounded fs-12 border-start border-3 border-warning">
                                            {{ $req->metadata['reason'] ?? 'بدون سبب' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $req->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <button class="btn btn-sm btn-soft-success flex-grow-1 fw-bold" onclick="handleAction('approve', {{ $req->id }})">
                                                <i class="ri-check-double-line align-bottom me-1"></i> موافقة
                                            </button>
                                            <button class="btn btn-sm btn-soft-danger flex-grow-1 fw-bold" onclick="handleAction('reject', {{ $req->id }})">
                                                <i class="ri-close-line align-bottom me-1"></i> رفض
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="avatar-lg mx-auto mb-3">
                                            <div class="avatar-title bg-soft-light text-muted rounded-circle fs-2">
                                                <i class="ri-inbox-archive-line"></i>
                                            </div>
                                        </div>
                                        <h5 class="text-muted">لا توجد طلبات معلقة حالياً</h5>
                                        <p class="text-muted mb-0">سيتم عرض طلبات الحذف والإلغاء هنا بمجرد تقديمها من قبل المشرفين.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden Forms --}}
<form id="action-form" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="comments" id="action-comments">
</form>

@endsection

@push('scripts')
<script>
    const routes = {
        approve: (id) => `{{ url('educational/lectures/requests') }}/${id}/approve`,
        reject:  (id) => `{{ url('educational/lectures/requests') }}/${id}/reject`,
    };

    function handleAction(action, id) {
        const isApprove = action === 'approve';
        const title     = isApprove ? 'تأكيد الموافقة على الطلب' : 'تأكيد رفض الطلب';
        const icon      = isApprove ? 'success' : 'warning';
        const color     = isApprove ? '#0ab39c' : '#f06548';
        const btnTxt    = isApprove ? '<i class="ri-check-double-line me-1"></i> موافقة' : '<i class="ri-close-line me-1"></i> رفض';

        Swal.fire({
            title: title,
            text: "يمكنك إضافة تعليق اختياري قبل تنفيذ الإجراء.",
            input: 'textarea',
            inputPlaceholder: 'أضف تعليقك هنا (اختياري)...',
            inputAttributes: { rows: 3 },
            icon: icon,
            showCancelButton: true,
            confirmButtonText: btnTxt,
            cancelButtonText: 'إلغاء',
            confirmButtonColor: color,
            showLoaderOnConfirm: true,
            preConfirm: (comments) => {
                const form = document.getElementById('action-form');
                form.action = routes[action](id);
                document.getElementById('action-comments').value = comments ?? '';
                form.submit();
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    }
</script>
@endpush
