@extends('core::layouts.master')

@section('title', __('core::audit.audit_logs'))

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0 mt-3">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">{{ __('core::audit.system_audit_logs') }}</h5>
                    <div class="flex-shrink-0">
                        <a href="{{ route('audit.export', request()->all()) }}" class="btn btn-info">
                            <i class="ri-file-download-line align-bottom me-1"></i> {{ __('core::audit.export_csv') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body border border-dashed border-end-0 border-start-0">
                <form action="{{ route('audit.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-xxl-3 col-sm-6">
                            <div class="search-box">
                                <select class="form-control" name="event">
                                    <option value="">{{ __('core::audit.select_event') }}</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>{{ ucfirst($event) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xxl-3 col-sm-6">
                            <select class="form-control" name="category">
                                <option value="">{{ __('core::audit.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xxl-2 col-sm-6">
                            <select class="form-control" name="log_level">
                                <option value="">{{ __('core::audit.log_level') }}</option>
                                <option value="info" {{ request('log_level') == 'info' ? 'selected' : '' }}>{{ __('core::audit.info') }}</option>
                                <option value="warning" {{ request('log_level') == 'warning' ? 'selected' : '' }}>{{ __('core::audit.warning') }}</option>
                                <option value="critical" {{ request('log_level') == 'critical' ? 'selected' : '' }}>{{ __('core::audit.critical') }}</option>
                            </select>
                        </div>
                        <div class="col-xxl-1 col-sm-6">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-equalizer-fill me-1 align-bottom"></i> {{ __('core::audit.filter') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive table-card mb-4">
                    <table class="table align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('core::audit.user') }}</th>
                                <th>{{ __('core::audit.event') }}</th>
                                <th>{{ __('core::audit.category') }}</th>
                                <th>{{ __('core::audit.level') }}</th>
                                <th>{{ __('core::audit.ip_address') }}</th>
                                <th>{{ __('core::audit.date') }}</th>
                                <th>{{ __('core::audit.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 ms-2 name">{{ $log->user ? $log->user->full_name : __('core::audit.system') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $log->event == 'created' ? 'success' : ($log->event == 'updated' ? 'info' : ($log->event == 'deleted' ? 'danger' : 'primary')) }}-subtle text-{{ $log->event == 'created' ? 'success' : ($log->event == 'updated' ? 'info' : ($log->event == 'deleted' ? 'danger' : 'primary')) }} text-uppercase">
                                            {{ $log->event }}
                                        </span>
                                    </td>
                                    <td>{{ $log->category }}</td>
                                    <td>
                                        <span class="badge {{ $log->log_level == 'critical' ? 'bg-danger' : ($log->log_level == 'warning' ? 'bg-warning' : 'bg-info') }}">
                                            {{ strtoupper($log->log_level) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>{{ $log->created_at->format('d M, Y H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light show-log-details" data-id="{{ $log->id }}">
                                            <i class="ri-eye-line align-bottom"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('core::audit.no_logs') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">{{ __('core::audit.audit_log_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('audit.loading') }}...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.show-log-details').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
            const content = document.getElementById('logDetailsContent');
            
            content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            modal.show();

            fetch(`/audit-logs/${id}`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = '<div class="alert alert-danger">Error loading details.</div>';
                });
        });
    });
</script>
@endpush
