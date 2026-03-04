@extends('core::layouts.master')
@section('title', 'إعدادات المحاضرين')

@section('content')
@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-info border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-info text-white rounded-circle fs-4">
                            <i class="ri-settings-4-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">إعدادات المحاضرين</h5>
                        <p class="text-muted mb-0 small">تخصيص طريقة عرض وإدارة قائمة المحاضرين.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.instructors.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('educational.instructors.saveSettings') }}" method="POST">
                    @csrf

                    {{-- ── Pagination Setting ── --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-1">
                            <i class="ri-layout-grid-line text-info me-2"></i>عدد العناصر في الصفحة
                        </h6>
                        <p class="text-muted small mb-4">
                            <i class="ri-information-line me-1"></i>
                            حدد عدد المحاضرين المعروضين في كل صفحة من قائمة المحاضرين.
                        </p>

                        <div class="row g-3 justify-content-center">
                            @foreach([12, 24, 36] as $option)
                                @php $isSelected = $currentPerPage == $option; @endphp
                                <div class="col-4">
                                    <label class="per-page-card w-100 text-center cursor-pointer"
                                           for="per_page_{{ $option }}">
                                        <input type="radio" name="per_page"
                                               id="per_page_{{ $option }}"
                                               value="{{ $option }}"
                                               class="d-none per-page-radio"
                                               {{ $isSelected ? 'checked' : '' }}>
                                        <div class="p-4 rounded-3 border border-2 transition-card position-relative"
                                             style="{{ $isSelected ? 'border-color: #299cdb !important; background-color: rgba(41,156,219,0.1) !important; box-shadow: 0 4px 15px rgba(41,156,219,0.2);' : 'border-color: #e9ebec; background-color: #f8f9fa;' }}"
                                             data-selected="{{ $isSelected ? '1' : '0' }}">
                                            @if($isSelected)
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge bg-info rounded-circle p-1">
                                                        <i class="ri-check-line" style="font-size:11px;"></i>
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="fw-black mb-1" style="font-size:36px; line-height:1; color: {{ $isSelected ? '#299cdb' : '#878a99' }};">
                                                {{ $option }}
                                            </div>
                                            <div class="fs-12 text-muted">عنصر / صفحة</div>
                                            @if($option == 12)
                                                <div class="mt-1 badge rounded-pill" style="background:rgba(135,138,153,0.1); color:#878a99; font-size:11px;">افتراضي</div>
                                            @elseif($option == 24)
                                                <div class="mt-1 badge rounded-pill" style="background:rgba(10,179,156,0.1); color:#0ab39c; font-size:11px;">موصى به</div>
                                            @else
                                                <div class="mt-1 badge rounded-pill" style="background:rgba(249,166,44,0.1); color:#f9a62c; font-size:11px;">متقدم</div>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        @error('per_page')
                            <div class="text-danger small mt-2"><i class="ri-error-warning-line me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="border-top pt-4">
                        <div class="hstack gap-2 justify-content-end">
                            <a href="{{ route('educational.instructors.index') }}" class="btn btn-soft-secondary px-4">
                                {{ __('educational::messages.cancel') ?? 'إلغاء' }}
                            </a>
                            <button type="submit" class="btn btn-info px-5 shadow-sm fw-bold">
                                <i class="ri-save-line align-bottom me-1"></i> {{ __('educational::messages.save') ?? 'حفظ' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .cursor-pointer { cursor: pointer; }
    .fw-black { font-weight: 800; }
    .fs-36 { font-size: 36px; }
    .fs-11 { font-size: 11px; }
    .fs-12 { font-size: 12px; }
    .lh-1 { line-height: 1; }

    .per-page-card { position: relative; }

    .transition-card {
        transition: all 0.2s ease-in-out;
    }

    .per-page-card:hover .transition-card {
        border-color: var(--vz-info) !important;
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(41, 156, 219, 0.2) !important;
    }

    .per-page-card.selected .transition-card {
        border-color: var(--vz-info) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function resetAllCards() {
        document.querySelectorAll('.transition-card').forEach(function (inner) {
            inner.style.borderColor = '#e9ebec';
            inner.style.backgroundColor = '#f8f9fa';
            inner.style.boxShadow = 'none';
            inner.setAttribute('data-selected', '0');
            const num = inner.querySelector('.fw-black');
            if (num) num.style.color = '#878a99';
            const badge = inner.querySelector('.position-absolute');
            if (badge) badge.remove();
        });
    }

    function applyCardStyle(radio) {
        const inner = radio.closest('label').querySelector('.transition-card');
        inner.style.borderColor = '#299cdb';
        inner.style.backgroundColor = 'rgba(41,156,219,0.1)';
        inner.style.boxShadow = '0 4px 15px rgba(41,156,219,0.2)';
        inner.setAttribute('data-selected', '1');
        const num = inner.querySelector('.fw-black');
        if (num) num.style.color = '#299cdb';

        // Add check badge
        const old = inner.querySelector('.position-absolute');
        if (old) old.remove();
        const div = document.createElement('div');
        div.className = 'position-absolute top-0 end-0 m-2';
        div.innerHTML = '<span class="badge bg-info rounded-circle p-1"><i class="ri-check-line" style="font-size:11px;"></i></span>';
        inner.appendChild(div);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.per-page-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                resetAllCards();
                applyCardStyle(this);
            });
        });
    });
</script>
@endpush

