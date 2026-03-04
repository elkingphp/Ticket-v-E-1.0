@extends('core::layouts.master')
@section('title', 'مراجعة المكررين قبل الاستيراد')

@section('content')

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1 fw-bold">{{ __('educational::messages.import_preview_title') }}</h4>
            <p class="text-muted mb-0 small">{{ __('educational::messages.import_preview_desc') }}</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-soft-warning border-0">
            <h5 class="card-title mb-0 text-warning"><i class="ri-alert-line me-1"></i>
                {{ __('educational::messages.found_already_registered') }}
                ({{ count($duplicates) }})</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('educational.students.import.confirm') }}" method="POST">
                @csrf
                <input type="hidden" name="file_path" value="{{ $path }}">

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                    </div>
                                </th>
                                <th scope="col">{{ __('educational::messages.row_number') }}</th>
                                <th scope="col">{{ __('educational::messages.email') ?? 'البريد الإلكتروني' }}</th>
                                <th scope="col">{{ __('educational::messages.username') ?? 'اسم المستخدم' }}</th>
                                <th scope="col">{{ __('educational::messages.current_name_in_system') }}</th>
                                <th scope="col">{{ __('educational::messages.new_name_in_file') }}</th>
                                <th scope="col">{{ __('educational::messages.status_review') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($duplicates as $dup)
                                <tr>
                                    <th scope="row">
                                        <div class="form-check">
                                            <input class="form-check-input update-checkbox" type="checkbox"
                                                name="update_emails[]" value="{{ $dup['email'] }}">
                                        </div>
                                    </th>
                                    <td><span class="badge bg-light text-dark">#{{ $dup['row'] }}</span></td>
                                    <td>{{ $dup['email'] }}</td>
                                    <td><span class="text-primary">{{ $dup['username'] }}</span></td>
                                    <td>{{ $dup['current_name'] }}</td>
                                    <td><span class="text-success fw-bold">{{ $dup['new_name'] }}</span></td>
                                    <td>
                                        <span
                                            class="badge badge-soft-warning">{{ __('educational::messages.duplicate') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        تنويه: {{ __('educational::messages.import_note_no_update') }}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('educational.students.index') }}" class="btn btn-light"><i
                                class="ri-close-line me-1"></i> {{ __('educational::messages.cancel_import') }}</a>
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>
                            {{ __('educational::messages.continue_import') }}</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.getElementById('checkAll').addEventListener('change', function(e) {
            var checkboxes = document.querySelectorAll('.update-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = e.target.checked;
            });
        });
    </script>
@endpush
