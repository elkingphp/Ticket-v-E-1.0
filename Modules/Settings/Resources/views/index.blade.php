@extends('core::layouts.master')

@section('title', __('settings::settings.system_settings'))

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0 mt-3">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ __('settings::settings.system_settings') }}</h5>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-3">
                        <div class="nav flex-column nav-pills text-{{ app()->getLocale() == 'ar' ? 'end' : 'start' }}" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            @foreach($settings as $group => $groupSettings)
                                <button class="nav-link {{ $loop->first ? 'active' : '' }} mb-2" id="v-pills-{{ $group }}-tab" data-bs-toggle="pill" data-bs-target="#v-pills-{{ $group }}" type="button" role="tab" aria-controls="v-pills-{{ $group }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    <i class="ri-{{ $group == 'general' ? 'settings-4-line' : ($group == 'branding' ? 'palette-line' : ($group == 'security' ? 'shield-keyhole-line' : ($group == 'google' ? 'google-line' : 'mail-line'))) }} align-middle me-1"></i> {{ __('settings::settings.' . $group) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-9">
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="tab-content text-muted" id="v-pills-tabContent">
                                @foreach($settings as $group => $groupSettings)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="v-pills-{{ $group }}" role="tabpanel" aria-labelledby="v-pills-{{ $group }}-tab">
                                        @if($group == 'google')
                                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0">
                                                        <i class="ri-information-line fs-20 align-middle"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="alert-heading fw-bold mb-1">{{ __('settings::settings.google_integration_info') }}</h6>
                                                        <p class="mb-2">{{ __('settings::settings.google_integration_help') }}</p>
                                                        <a href="https://console.cloud.google.com/" target="_blank" class="btn btn-sm btn-primary shadow-none">
                                                            <i class="ri-external-link-line align-middle me-1"></i> {{ __('settings::settings.google_console_link') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="row">
                                            @foreach($groupSettings as $setting)
                                                <div class="col-lg-12 mb-3">
                                                    <label for="{{ $setting->key }}" class="form-label">{{ __('settings::settings.' . $setting->key) }}</label>
                                                    @if($setting->type == 'string')
                                                        <input type="text" class="form-control" name="{{ $setting->key }}" id="{{ $setting->key }}" value="{{ $setting->value }}">
                                                    @elseif($setting->type == 'text')
                                                        <textarea class="form-control" name="{{ $setting->key }}" id="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                                                    @elseif($setting->type == 'boolean')
                                                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                                                            <input class="form-check-input" type="checkbox" name="{{ $setting->key }}" id="{{ $setting->key }}" value="1" {{ $setting->value == '1' ? 'checked' : '' }}>
                                                        </div>
                                                    @elseif($setting->type == 'image')
                                                        <input type="file" class="form-control" name="{{ $setting->key }}" id="{{ $setting->key }}">
                                                        @if($setting->value)
                                                            <div class="mt-2">
                                                                 <img src="{{ asset($setting->value) }}" alt="" class="img-thumbnail" style="max-height: 100px;">
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }}">
                                <button type="submit" class="btn btn-primary">{{ __('settings::settings.save_settings') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
