@extends('core::layouts.master')
@section('title', __('educational::messages.generate_engine'))

@section('content')

@include('modules.educational.shared.alerts')

<div class="row justify-content-center mt-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-soft-primary border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm me-3">
                        <div class="avatar-title bg-primary text-white rounded-circle fs-4">
                            <i class="ri-flashlight-fill"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.generate_engine') }}</h5>
                        <p class="text-muted mb-0 small">توليد المحاضرات تلقائياً بناءً على قوالب الجدولة المحفوظة مع خاصية مراجعة التعارضات.</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('educational.lectures.index') }}" class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                            <i class="ri-arrow-left-line align-middle me-1"></i> {{ __('educational::messages.back_to_lectures') }}
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <form id="generate-form" action="{{ route('educational.lectures.generate') }}" method="POST">
                    @csrf
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <h5 class="fw-bold mb-3"><i class="ri-settings-5-line text-primary me-2"></i> إعدادات التوليد</h5>
                            <p class="text-muted mb-4 small"><i class="ri-information-line me-1"></i> حدد قالب الجدولة ليتم استدعاء تواريخ البداية والنهاية الخاصة به تلقائياً في حقول التوليد.</p>
                            
                            <div class="card border border-2 shadow-none border-light bg-light w-100 transition-all setting-card mb-4">
                                <div class="card-body p-4">
                                    <div class="row align-items-center mb-4 pb-4 border-bottom border-light">
                                        <div class="col-md-5 mb-3 mb-md-0">
                                            <h6 class="fw-bold text-dark fs-15 mb-1"><i class="ri-layout-grid-line text-primary me-1"></i> قالب الجدولة المعتمد</h6>
                                            <p class="text-muted small mb-0">اختر القالب المطلوب لتوليد جدوله الزمني.</p>
                                        </div>
                                        <div class="col-md-7">
                                            <select name="template_id" id="template_id" class="form-select form-select-lg border-primary shadow-sm" required onchange="updateDates()">
                                                <option value="">{{ __('educational::messages.choose_template') }}</option>
                                                @foreach($templates as $template)
                                                    <option value="{{ $template->id }}" 
                                                        data-from="{{ $template->effective_from ? \Carbon\Carbon::parse($template->effective_from)->format('Y-m-d') : '' }}"
                                                        data-to="{{ $template->effective_until ? \Carbon\Carbon::parse($template->effective_until)->format('Y-m-d') : '' }}">
                                                        {{ $template->name ? $template->name . ' | ' : '' }}{{ __('educational::messages.program') }}: {{ $template->program->name ?? '' }} | {{ __('educational::messages.group') }}: {{ $template->group->name ?? '' }} | {{ __('educational::messages.day_of_week') }}: {{ __('educational::messages.'.strtolower(date('l', strtotime("Sunday +{$template->day_of_week} days")))) }} | {{ substr($template->start_time, 0, 5) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row align-items-center">
                                        <div class="col-md-5 mb-3 mb-md-0">
                                            <h6 class="fw-bold text-dark fs-15 mb-1"><i class="ri-calendar-event-line text-primary me-1"></i> النطاق الزمني للتوليد</h6>
                                            <p class="text-muted small mb-0">تواريخ البداية والنهاية (يمكنك تعديلها إذا رغبت في توليد فترة جزئية مستقطعة من القالب).</p>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0 fw-bold border-primary text-muted">{{ __('educational::messages.from') }}</span>
                                                        <input type="date" name="generate_from" id="generate_from" class="form-control border-start-0 border-primary" required>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-end-0 fw-bold border-primary text-muted">{{ __('educational::messages.to') }}</span>
                                                        <input type="date" name="generate_to" id="generate_to" class="form-control border-start-0 border-primary" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-0 mt-3 rounded-3">
                                <div class="flex-shrink-0">
                                    <i class="ri-alert-line fs-24 text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold text-warning-emphasis mb-1">ملاحظة هامة جداً:</h6>
                                    <p class="mb-0 text-dark small">{{ __('educational::messages.generator_note') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-4 border-top pt-4">
                            <div class="d-flex justify-content-end">
                                <div class="hstack gap-2">
                                    <a href="{{ route('educational.lectures.index') }}" class="btn btn-soft-secondary px-4">{{ __('educational::messages.cancel') }}</a>
                                    <button type="button" class="btn btn-success px-5 shadow-sm fw-bold" onclick="confirmGenerate()">
                                        <i class="ri-flashlight-fill align-bottom me-1"></i> {{ __('educational::messages.trigger_generation') }}
                                    </button>
                                </div>
                            </div>
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
    .card-header.bg-soft-primary {
        background-color: rgba(64, 81, 137, 0.05);
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    .setting-card {
        border-color: #e9ebec !important;
    }
    .setting-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
        border-color: #ced4da !important;
        background-color: #fff !important;
    }
</style>
<link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    function updateDates() {
        var select = document.getElementById('template_id');
        var option = select.options[select.selectedIndex];
        
        var fromDate = option.getAttribute('data-from');
        var toDate = option.getAttribute('data-to');
        
        if (fromDate) {
            document.getElementById('generate_from').value = fromDate;
        } else {
            document.getElementById('generate_from').value = '';
        }
        
        if (toDate) {
            document.getElementById('generate_to').value = toDate;
        } else {
            document.getElementById('generate_to').value = '';
        }
    }

    // Call updateDates on load in case there's an old value selected
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('template_id').value !== '') {
            updateDates();
        }
    });

    function confirmGenerate() {
        var templateId = document.getElementById('template_id').value;
        var fromDate = document.getElementById('generate_from').value;
        var toDate = document.getElementById('generate_to').value;

        if (!templateId || !fromDate || !toDate) {
            Swal.fire({
                icon: 'error',
                title: '{{ __("educational::messages.missing_details") }}',
                text: '{{ __("educational::messages.fill_fields") }}',
                confirmButtonColor: '#0ab39c',
            });
            return;
        }

        Swal.fire({
            title: '{{ __("educational::messages.run_engine_title") }}',
            html: `{!! __("educational::messages.run_engine_text") !!}`.replace(':from', `<b class="text-primary">${fromDate}</b>`).replace(':to', `<b class="text-danger">${toDate}</b>`),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ab39c',
            cancelButtonColor: '#f06548',
            confirmButtonText: '{{ __("educational::messages.execute_engine") }}',
            cancelButtonText: '{{ __("educational::messages.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                document.getElementById('generate-form').submit();
            }
        });
    }
</script>
@endpush
