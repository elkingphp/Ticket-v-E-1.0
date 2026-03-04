@extends('core::layouts.master')

@section('title', __('settings::settings.system_settings'))

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header border-0 bg-white pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="card-title mb-1 fw-bold text-dark">{{ __('settings::settings.system_settings') }}</h4>
                            <p class="text-muted mb-0 small">{{ __('settings::settings.system_configuration') }}</p>
                        </div>
                        <div class="flex-shrink-0 d-flex align-items-center">
                            @php
                                $redisStatus = 'Unknown';
                                $redisClass = 'secondary';
                                try {
                                    \Illuminate\Support\Facades\Redis::ping();
                                    $redisStatus = 'Active';
                                    $redisClass = 'success';
                                } catch (\Exception $e) {
                                    $redisStatus = 'Offline';
                                    $redisClass = 'danger';
                                }
                            @endphp
                            <div
                                class="badge bg-{{ $redisClass }}-subtle text-{{ $redisClass }} p-2 px-3 rounded-pill h6 mb-0 me-3 {{ $redisStatus == 'Offline' ? 'pulse-danger' : '' }}">
                                <i
                                    class="ri-{{ $redisStatus == 'Active' ? 'checkbox-circle' : 'error-warning' }}-line align-middle me-1"></i>
                                Redis {{ $redisStatus }}
                            </div>
                            <span
                                class="badge bg-primary-subtle text-primary p-2 px-3 rounded-pill h6 mb-0 shadow-sm border border-primary-subtle">
                                <i class="ri-instance-line align-middle me-1"></i> Enterprise v1.2
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if (session('success'))
                        <div class="px-4 pt-3">
                            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3"
                                role="alert">
                                <i class="ri-check-double-line me-2 align-middle"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    @endif

                    <div class="row g-0 mt-3 border-top">
                        <!-- Sidebar Navigation -->
                        @php $activeTab = old('active_tab', session('active_tab', array_key_first($settings))); @endphp
                        <div class="col-md-3 border-end bg-light-subtle">
                            <div class="nav flex-column nav-pills nav-pills-enterprise p-3 text-{{ app()->getLocale() == 'ar' ? 'end' : 'start' }}"
                                id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                @foreach ($settings as $group => $groupSettings)
                                    @php $meta = $groupMetadata[$group] ?? []; @endphp
                                    <button class="nav-link {{ $group == $activeTab ? 'active' : '' }} mb-2 rounded-3"
                                        id="v-pills-{{ $group }}-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-{{ $group }}" type="button" role="tab"
                                        aria-controls="v-pills-{{ $group }}"
                                        aria-selected="{{ $group == $activeTab ? 'true' : 'false' }}">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="nav-icon-box me-3">
                                                <i class="ri-{{ $meta['icon'] ?? 'list-settings-line' }} fs-18"></i>
                                            </div>
                                            <span
                                                class="fw-bold">{{ __($meta['label'] ?? 'settings::settings.' . $group) }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Content Area -->
                        <div class="col-md-9 bg-white">
                            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="active_tab" id="active_tab_input" value="{{ $activeTab }}">
                                <div class="tab-content p-4" id="v-pills-tabContent">
                                    @foreach ($settings as $group => $groupSettings)
                                        @php $meta = $groupMetadata[$group] ?? []; @endphp
                                        <div class="tab-pane fade {{ $group == $activeTab ? 'show active' : '' }}"
                                            id="v-pills-{{ $group }}" role="tabpanel"
                                            aria-labelledby="v-pills-{{ $group }}-tab">

                                            <!-- Group Intro -->
                                            <div
                                                class="group-header-banner p-4 bg-light rounded-4 mb-5 border-start border-primary border-5 position-relative">
                                                <h5 class="fw-bold text-primary mb-2">
                                                    {{ __($meta['label'] ?? 'settings::settings.' . $group) }}</h5>
                                                <p class="text-muted mb-0">{{ __($meta['description'] ?? '') }}</p>
                                                <div class="header-icon-bg ri-{{ $meta['icon'] ?? 'list-settings-line' }}">
                                                </div>
                                            </div>

                                            @if ($group == 'google')
                                                <div class="alert alert-soft-info border-0 p-4 rounded-4 mb-5">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="avatar-sm bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center me-3 flex-shrink-0">
                                                            <i class="ri-google-fill fs-24"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="fw-bold mb-1">
                                                                {{ __('settings::settings.google_integration_info') }}</h6>
                                                            <p class="mb-2 small opacity-75">
                                                                {{ __('settings::settings.google_integration_help') }}</p>
                                                            <a href="https://console.cloud.google.com/" target="_blank"
                                                                class="text-info fw-bold small text-decoration-underline">
                                                                {{ __('settings::settings.google_console_link') }} <i
                                                                    class="ri-arrow-right-up-line"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                                @foreach ($groupSettings as $setting)
                                                    @php
                                                        $fullWidth = in_array($setting->type, [
                                                            'text',
                                                            'multiselect',
                                                            'image',
                                                        ]);
                                                    @endphp
                                                    <div class="{{ $fullWidth ? 'col-md-12' : 'col-md-4' }}">
                                                        <div class="enterprise-field-card h-100">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <label class="form-label-premium mb-0">
                                                                    {{ __($setting->label) }}
                                                                </label>
                                                                @if (isset($setting->description) && $setting->description)
                                                                    <i class="ri-question-line ms-2 text-muted-premium"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ __($setting->description) }}"></i>
                                                                @endif
                                                            </div>

                                                            <div class="field-control-wrapper">
                                                                @if ($setting->type == 'string')
                                                                    <input type="text" class="form-control premium-input"
                                                                        name="{{ $setting->key }}"
                                                                        id="{{ $setting->key }}"
                                                                        value="{{ $setting->value }}" placeholder="...">
                                                                @elseif($setting->type == 'integer')
                                                                    <input type="number" class="form-control premium-input"
                                                                        name="{{ $setting->key }}"
                                                                        id="{{ $setting->key }}"
                                                                        value="{{ $setting->value }}">
                                                                @elseif($setting->type == 'text')
                                                                    <textarea class="form-control premium-input" name="{{ $setting->key }}" id="{{ $setting->key }}" rows="4">{{ $setting->value }}</textarea>
                                                                @elseif($setting->type == 'boolean')
                                                                    <div
                                                                        class="premium-switch-box bg-light-subtle p-3 rounded-4 border d-flex justify-content-between align-items-center transition-all hover-shadow-sm">
                                                                        <div class="d-flex align-items-center">
                                                                            <div
                                                                                class="avatar-xs bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm border">
                                                                                <i class="ri-toggle-line text-primary"></i>
                                                                            </div>
                                                                            <span
                                                                                class="text-dark small fw-bold">{{ $setting->value == '1' ? 'ENABLED' : 'DISABLED' }}</span>
                                                                        </div>
                                                                        <div class="form-check form-switch form-switch-lg p-0 m-0"
                                                                            dir="ltr">
                                                                            <input class="form-check-input ms-0"
                                                                                type="checkbox"
                                                                                name="{{ $setting->key }}"
                                                                                id="{{ $setting->key }}" value="1"
                                                                                {{ isset($setting->value) && $setting->value == '1' ? 'checked' : '' }}>
                                                                        </div>
                                                                    </div>
                                                                @elseif($setting->type == 'select')
                                                                    <div class="premium-select-wrapper position-relative">
                                                                        <select
                                                                            class="form-select premium-select ps-5 rounded-4 shadow-sm border-light-subtle py-3"
                                                                            name="{{ $setting->key }}"
                                                                            id="{{ $setting->key }}">
                                                                            @foreach ($setting->options ?? [] as $val => $label)
                                                                                <option value="{{ $val }}"
                                                                                    {{ $setting->value == $val ? 'selected' : '' }}>
                                                                                    {{ $label }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <div
                                                                            class="select-icon-left position-absolute top-50 translate-middle-y ms-3 text-primary">
                                                                            <i class="ri-list-check-2 fs-20"></i>
                                                                        </div>
                                                                    </div>
                                                                @elseif($setting->type == 'multiselect')
                                                                    @php
                                                                        $selectedValues = [];
                                                                        if (isset($setting->value)) {
                                                                            $selectedVal = is_string($setting->value)
                                                                                ? json_decode($setting->value, true)
                                                                                : $setting->value;
                                                                            $selectedValues = is_array($selectedVal)
                                                                                ? $selectedVal
                                                                                : [$selectedVal];
                                                                        }
                                                                    @endphp
                                                                    <div class="premium-multiselect-container">
                                                                        <div class="row g-3">
                                                                            @foreach ($setting->options ?? [] as $val => $label)
                                                                                <div class="col-md-3 col-sm-6">
                                                                                    <label
                                                                                        class="expert-selection-card {{ in_array($val, $selectedValues) ? 'selected' : '' }} w-100"
                                                                                        for="{{ $setting->key }}_{{ $val }}">
                                                                                        <input class="d-none"
                                                                                            type="checkbox"
                                                                                            name="{{ $setting->key }}[]"
                                                                                            id="{{ $setting->key }}_{{ $val }}"
                                                                                            value="{{ $val }}"
                                                                                            {{ in_array($val, $selectedValues) ? 'checked' : '' }}>
                                                                                        <div
                                                                                            class="selection-card-body p-3 text-center transition-all h-100 rounded-4 border bg-white shadow-sm position-relative overflow-hidden">
                                                                                            <div class="selection-overlay">
                                                                                            </div>
                                                                                            <div
                                                                                                class="selection-icon mb-2">
                                                                                                <div
                                                                                                    class="icon-circle mx-auto mb-2">
                                                                                                    <i
                                                                                                        class="ri-shield-check-line fs-20"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                            <h6
                                                                                                class="mb-0 text-dark fw-bold small text-truncate position-relative z-1">
                                                                                                {{ $label }}</h6>
                                                                                            <div
                                                                                                class="selection-check-badge position-absolute top-0 end-0 m-2">
                                                                                                <i
                                                                                                    class="ri-checkbox-circle-fill text-success fs-18"></i>
                                                                                            </div>
                                                                                        </div>
                                                                                    </label>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @elseif($setting->type == 'image')
                                                                    <div
                                                                        class="premium-image-uploader p-4 border-1 border-info border-dashed rounded-4 bg-light-subtle">
                                                                        <div class="row align-items-center">
                                                                            <div class="col-auto">
                                                                                <div
                                                                                    class="image-preview-enterprise border rounded-3 bg-white p-1">
                                                                                    @if (isset($setting->value) && $setting->value)
                                                                                        <img src="{{ asset($setting->value) }}"
                                                                                            class="img-fluid rounded-2">
                                                                                    @else
                                                                                        <div
                                                                                            class="empty-preview h-100 w-100 d-flex align-items-center justify-content-center text-muted">
                                                                                            <i
                                                                                                class="ri-image-2-line fs-32"></i>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            <div class="col">
                                                                                <label
                                                                                    class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-none overflow-hidden position-relative">
                                                                                    <i
                                                                                        class="ri-upload-cloud-2-line me-1"></i>
                                                                                    Choose New Image
                                                                                    <input type="file"
                                                                                        name="{{ $setting->key }}"
                                                                                        class="position-absolute top-0 start-0 opacity-0 cursor-pointer w-100 h-100">
                                                                                </label>
                                                                                <p class="text-muted small mt-2 mb-0">
                                                                                    Recommended: Transparent PNG or SVG (max
                                                                                    2MB)</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            @if (isset($setting->description) && $setting->description)
                                                                <div class="mt-3 field-hint-box d-flex align-items-start">
                                                                    <i
                                                                        class="ri-information-line me-2 text-primary opacity-75"></i>
                                                                    <span
                                                                        class="text-muted small italic">{{ __($setting->description) }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Enterprise Footer Button -->
                                <div
                                    class="p-4 bg-light-subtle border-top d-flex justify-content-between align-items-center">
                                    <div class="d-none d-md-flex align-items-center text-muted small">
                                        <i class="ri-shield-check-fill text-success fs-20 me-2"></i>
                                        <span class="fw-bold text-uppercase ls-1">Enterprise Configuration Manager</span>
                                    </div>
                                    <button type="submit"
                                        class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg d-flex align-items-center save-button-premium">
                                        <i class="ri-save-3-fill me-2 fs-20"></i>
                                        <span
                                            class="fw-bold ls-1 text-uppercase">{{ __('settings::settings.save_settings') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-pills-enterprise .nav-link {
            border: 1px solid transparent;
            color: #6d7080;
            transition: all 0.3s ease;
        }

        .nav-pills-enterprise .nav-link.active {
            background-color: white !important;
            color: var(--vz-primary) !important;
            border-color: #eee !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transform: scale(1.02);
        }

        .nav-icon-box {
            width: 36px;
            height: 36px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .nav-link.active .nav-icon-box {
            background: var(--vz-primary);
            color: white;
            box-shadow: 0 4px 10px rgba(64, 81, 137, 0.3);
        }

        .group-header-banner {
            overflow: hidden;
            z-index: 1;
        }

        .header-icon-bg {
            position: absolute;
            right: -20px;
            top: -20px;
            font-size: 150px;
            color: var(--vz-primary);
            opacity: 0.03;
            z-index: -1;
        }

        .premium-input {
            background: #fdfdfd;
            border: 1px solid #e9ebec;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }

        .premium-input:focus {
            background: #fff;
            border-color: var(--vz-primary);
            box-shadow: 0 0 0 4px rgba(64, 81, 137, 0.08);
        }

        .form-label-premium {
            font-size: 0.95rem;
            font-weight: 800;
            color: #2a2e34;
            letter-spacing: -0.01em;
        }

        /* Expert Selection Cards */
        .expert-selection-card {
            cursor: pointer;
            perspective: 1000px;
        }

        .selection-card-body {
            border: 2px solid #f1f3f5 !important;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .icon-circle {
            width: 48px;
            height: 48px;
            background: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            transition: all 0.3s;
        }

        .selection-check-badge {
            transform: scale(0);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
        }

        .selection-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--vz-primary) 0%, var(--vz-info) 100%);
            opacity: 0;
            transition: all 0.3s;
            z-index: 0;
        }

        .expert-selection-card.selected .selection-card-body {
            border-color: var(--vz-primary) !important;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(64, 81, 137, 0.1) !important;
        }

        .expert-selection-card.selected .icon-circle {
            background: var(--vz-primary);
            color: white;
            box-shadow: 0 5px 15px rgba(64, 81, 137, 0.3);
        }

        .expert-selection-card.selected .selection-check-badge {
            transform: scale(1);
            opacity: 1;
        }

        .expert-selection-card:hover .selection-card-body {
            border-color: var(--vz-primary-subtle) !important;
            transform: translateY(-3px);
        }

        .expert-selection-card.selected:hover .selection-card-body {
            transform: translateY(-7px) scale(1.02);
        }

        .expert-selection-card .text-dark {
            transition: all 0.3s;
        }

        .expert-selection-card.selected .text-dark {
            color: var(--vz-primary) !important;
        }

        .image-preview-enterprise {
            width: 100px;
            height: 100px;
        }

        .image-preview-enterprise img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .save-button-premium {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .save-button-premium:hover {
            transform: scale(1.05) translateY(-2px);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .pulse-danger {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .italic {
            font-style: italic;
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Expert Multi-select Logic
            document.querySelectorAll('.expert-selection-card').forEach(card => {
                const input = card.querySelector('input');

                input.addEventListener('change', () => {
                    card.classList.toggle('selected', input.checked);
                });
            });


            // Init bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Tab preservation logic
            const tabBtns = document.querySelectorAll('button[data-bs-toggle="pill"]');
            const activeTabInput = document.getElementById('active_tab_input');

            tabBtns.forEach(btn => {
                btn.addEventListener('shown.bs.tab', function(event) {
                    const targetId = event.target.getAttribute('data-bs-target').replace(
                        '#v-pills-', '');
                    activeTabInput.value = targetId;
                    // Update URL fragment without jumping
                    history.replaceState(null, null, '#' + targetId);
                });
            });

            // On load, check URL hash
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const tabEl = document.querySelector('button[data-bs-target="#v-pills-' + hash + '"]');
                if (tabEl) {
                    const bootstrapTab = new bootstrap.Tab(tabEl);
                    bootstrapTab.show();
                }
            }
        });
    </script>
@endsection
