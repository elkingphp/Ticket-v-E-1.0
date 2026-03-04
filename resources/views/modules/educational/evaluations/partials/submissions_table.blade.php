<div id="submissions-table-container">
    <div class="row g-3 mb-3 p-3 align-items-end">
        <div class="col-md-5">
            <div class="search-box">
                <input type="text" id="table-search" class="form-control form-control-sm"
                    placeholder="بحث سريع باسم المقيّم..." value="{{ request('search') }}">
                <i class="ri-search-line search-icon"></i>
            </div>
        </div>
        <div class="col-md-4">
            <select id="table-role-filter" class="form-select form-select-sm">
                <option value="">كل الأدوار</option>
                <option value="trainee" {{ request('evaluator_role') == 'trainee' ? 'selected' : '' }}>متدرب</option>
                <option value="observer" {{ request('evaluator_role') == 'observer' ? 'selected' : '' }}>مراقب</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="button" id="refresh-table" class="btn btn-light btn-sm flex-fill">
                <i class="ri-refresh-line me-1"></i> تحديث
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted">
                <tr>
                    <th>المُقيّم</th>
                    <th>الدور</th>
                    <th>التاريخ</th>
                    <th class="text-center">المتوسط</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluations as $eval)
                    @php
                        $ratingAns = $eval->answers->whereNotNull('answer_rating');
                        $avg = $ratingAns->count() ? round($ratingAns->avg('answer_rating'), 1) : null;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar-xs me-2">
                                    <div class="avatar-title bg-soft-info text-info rounded-circle fs-12 fw-bold">
                                        {{ mb_substr($eval->evaluator?->user?->full_name ?? ($eval->evaluator?->full_name ?? ($eval->evaluator?->name ?? 'G')), 0, 1) }}
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fs-13 mb-0">
                                        {{ $eval->evaluator?->user?->full_name ?? ($eval->evaluator?->full_name ?? ($eval->evaluator?->name ?? 'غير متوفر')) }}
                                    </h6>
                                    <small class="text-muted">{{ $eval->lecture?->starts_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span
                                class="badge bg-{{ $eval->evaluator_role === 'trainee' ? 'success' : 'info' }}-subtle text-{{ $eval->evaluator_role === 'trainee' ? 'success' : 'info' }} fs-11">
                                {{ $eval->evaluator_role === 'trainee' ? 'متدرب' : ($eval->evaluator_role === 'admin' ? 'مسؤول' : 'مراقب') }}
                            </span>
                        </td>
                        <td><small>{{ $eval->submitted_at->format('d/m/Y H:i') }}</small></td>
                        <td class="text-center">
                            @if ($avg !== null)
                                <span
                                    class="fw-bold {{ $avg < 3 ? 'text-danger' : 'text-success' }}">{{ $avg }}</span>
                                <small class="text-muted">/5</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('educational.evaluations.submissions.show', $eval) }}"
                                class="btn btn-sm btn-ghost-primary">
                                <i class="ri-eye-line me-1"></i> عرض
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">لا توجد تقييمات مطابقة للبحث.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($evaluations->hasPages())
        <div class="card-footer border-top-0 py-3 pagination-ajax">
            {{ $evaluations->links() }}
        </div>
    @endif
</div>
