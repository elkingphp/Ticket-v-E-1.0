@extends('core::layouts.master')

@section('title', __('tickets::messages.templates'))

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0 mt-2">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">{{ __('tickets::messages.records') }}</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive table-card mb-1">
                        <table class="table align-middle table-nowrap" id="templateTable">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th style="width: 50px;">{{ __('tickets::messages.id') }}</th>
                                    <th>{{ __('tickets::messages.subject') }}</th>
                                    <th>{{ __('tickets::messages.last_updated') }}</th>
                                    <th style="width: 150px;">{{ __('tickets::messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($templates as $template)
                                    <tr>
                                        <td>
                                            <span
                                                class="badge bg-primary-subtle text-primary text-uppercase">{{ $template->event_key }}</span>
                                        </td>
                                        <td class="fw-medium">{{ $template->subject }}</td>
                                        <td>
                                            <div class="text-muted">
                                                <i
                                                    class="ri-calendar-event-line align-middle me-1"></i>{{ $template->updated_at->format('Y-m-d H:i') }}
                                            </div>
                                        </td>
                                        <td>
                                            <ul class="list-inline hstack gap-2 mb-0">
                                                <li class="list-inline-item" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="top"
                                                    title="{{ __('tickets::messages.preview_template') }}">
                                                    <button class="btn btn-soft-success btn-sm preview-btn"
                                                        data-id="{{ $template->id }}"
                                                        data-key="{{ $template->event_key }}">
                                                        <i class="ri-eye-line align-bottom"></i>
                                                    </button>
                                                </li>
                                                <li class="list-inline-item" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="top"
                                                    title="{{ __('tickets::messages.test_email') }}">
                                                    <button class="btn btn-soft-warning btn-sm test-btn"
                                                        data-id="{{ $template->id }}"
                                                        data-key="{{ $template->event_key }}">
                                                        <i class="ri-send-plane-fill align-bottom"></i>
                                                    </button>
                                                </li>
                                                <li class="list-inline-item" data-bs-toggle="tooltip"
                                                    data-bs-trigger="hover" data-bs-placement="top"
                                                    title="{{ __('tickets::messages.lookups.assigned_to') }}">
                                                    <button class="btn btn-soft-info btn-sm edit-template-btn"
                                                        data-bs-toggle="modal" data-bs-target="#editTemplateModal"
                                                        data-id="{{ $template->id }}"
                                                        data-key="{{ $template->event_key }}"
                                                        data-subject="{{ $template->subject }}"
                                                        data-body="{{ $template->body }}">
                                                        <i class="ri-pencil-fill align-bottom"></i>
                                                    </button>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade zoomIn" id="editTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-soft-info">
                    <h5 class="modal-title">{{ __('tickets::messages.edit_template') }}: <span id="template_key_title"
                            class="text-primary text-uppercase"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTemplateForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tickets::messages.subject') }}</label>
                            <input type="text" name="subject" id="edit_subject" class="form-control" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tickets::messages.details') }} <span
                                    class="badge bg-info-subtle text-info ms-1">HTML</span></label>
                            <textarea name="body" id="edit_body" class="form-control" rows="12" required style="font-family: monospace;"></textarea>
                            <div class="alert alert-info mt-3" role="alert"
                                style="background-color: #f0f7ff; border-left: 5px solid #007bff;">
                                <h6 class="alert-heading text-xs font-weight-bold text-uppercase mb-2"
                                    style="color: #0056b3;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('tickets::messages.available_variables') }}
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ ticket_number }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_id') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ subject }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_subject') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ customer_name }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_customer') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ status }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_status') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ priority }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_priority') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ assignee }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_assignee') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ message }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_reply') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ logo }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_logo') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ app_name }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_app') }}</small></span>
                                    <span class="badge bg-white text-dark border p-2 mb-2"><code
                                            class="text-primary">@{{ link }}</code> <small
                                            class="text-muted">{{ __('tickets::messages.variable_link') }}</small></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('tickets::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('tickets::messages.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade flip" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header p-3 bg-primary text-white">
                    <h5 class="modal-title text-white">
                        <i class="ri-eye-line me-2 align-middle"></i>
                        {{ __('tickets::messages.preview_template') }}: <span id="preview_title"
                            class="text-warning text-uppercase small ms-2"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="bg-light p-3 border-bottom d-flex align-items-center g-2">
                        <div class="flex-grow-1">
                            <i class="ri-mail-line me-1 text-muted"></i>
                            <span class="text-muted small">{{ __('tickets::messages.real_time_rendering') }}</span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active" id="btn-desktop"><i
                                    class="ri-computer-line"></i> {{ __('tickets::messages.desktop') }}</button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-mobile"><i
                                    class="ri-smartphone-line"></i> {{ __('tickets::messages.mobile') }}</button>
                        </div>
                    </div>
                    <div class="p-4 bg-white d-flex justify-content-center transition-all" id="preview_content_wrapper"
                        style="min-height: 400px; background-color: #f1f1f1 !important;">
                        <div id="preview_frame_container"
                            style="width: 100%; transition: width 0.3s ease; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                            <iframe id="preview_iframe" style="width: 100%; border: none; min-height: 500px;"></iframe>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('tickets::messages.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Mail Modal -->
    <div class="modal fade zoomIn" id="testMailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-soft-warning">
                    <h5 class="modal-title">{{ __('tickets::messages.send_test') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="testMailForm">
                    @csrf
                    <input type="hidden" name="template_id" id="test_template_id">
                    <div class="modal-body">
                        <p class="text-muted">{{ __('tickets::messages.send_test_help') }}</p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('tickets::messages.reporter') }} (Email)</label>
                            <input type="email" name="email" id="test_email_input" class="form-control"
                                placeholder="{{ __('tickets::messages.test_email_placeholder') }}" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">{{ __('tickets::messages.cancel') }}</button>
                        <button type="submit" class="btn btn-warning" id="send_test_btn">
                            <i class="ri-send-plane-2-line align-middle me-1"></i> {{ __('tickets::messages.send_test') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Edit Button Handler
                const editButtons = document.querySelectorAll('.edit-template-btn');
                editButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const key = this.dataset.key;
                        const subject = this.dataset.subject;
                        const body = this.dataset.body;

                        document.getElementById('editTemplateForm').action =
                            `/admin/tickets/templates/${id}`;
                        document.getElementById('template_key_title').innerText = key;
                        document.getElementById('edit_subject').value = subject;
                        document.getElementById('edit_body').value = body;
                    });
                });

                // View Mode Toggles
                const btnDesktop = document.getElementById('btn-desktop');
                const btnMobile = document.getElementById('btn-mobile');
                const frameContainer = document.getElementById('preview_frame_container');

                btnDesktop.addEventListener('click', function() {
                    this.classList.add('active');
                    btnMobile.classList.remove('active');
                    frameContainer.style.width = '100%';
                });

                btnMobile.addEventListener('click', function() {
                    this.classList.add('active');
                    btnDesktop.classList.remove('active');
                    frameContainer.style.width = '375px'; // Typical mobile width
                });

                // Preview Button Handler
                const previewButtons = document.querySelectorAll('.preview-btn');
                previewButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const key = this.dataset.key;
                        document.getElementById('preview_title').innerText = key;
                        document.getElementById('preview_iframe').src =
                            `/admin/tickets/templates/${id}/preview`;

                        // Reset to desktop view when opening
                        btnDesktop.classList.add('active');
                        btnMobile.classList.remove('active');
                        frameContainer.style.width = '100%';

                        new bootstrap.Modal(document.getElementById('previewModal')).show();
                    });
                });

                // Test Mail Handler
                const testMailModal = new bootstrap.Modal(document.getElementById('testMailModal'));
                const testButtons = document.querySelectorAll('.test-btn');
                testButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        document.getElementById('test_template_id').value = id;
                        document.getElementById('test_email_input').value = '';
                        testMailModal.show();
                    });
                });

                const testForm = document.getElementById('testMailForm');
                testForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const id = document.getElementById('test_template_id').value;
                    const email = document.getElementById('test_email_input').value;
                    const btn = document.getElementById('send_test_btn');
                    const originalText = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML =
                        '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> {{ __('tickets::messages.sending') }}';

                    fetch(`/admin/tickets/templates/${id}/test`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                email: email
                            })
                        })
                        .then(async response => {
                            const isJson = response.headers.get('content-type')?.includes(
                                'application/json');
                            const data = isJson ? await response.json() : null;

                            if (!response.ok) {
                                throw new Error(data?.message ||
                                    `Server responded with ${response.status}: ${response.statusText}`
                                );
                            }
                            return data;
                        })
                        .then(data => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;

                            Swal.fire({
                                icon: 'success',
                                title: '{{ __('tickets::messages.success') }}',
                                text: data.message ||
                                    '{{ __('tickets::messages.test_sent_success') }}',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                            testMailModal.hide();
                        })
                        .catch(error => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            console.error('Error:', error);

                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('tickets::messages.error') }}',
                                text: error.message ||
                                    '{{ __('tickets::messages.generic_error') }}',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        });
                });
            });
        </script>
    @endpush

@endsection
