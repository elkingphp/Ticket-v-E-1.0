@extends('core::layouts.master')

@section('title', __('tickets::messages.create_ticket'))

@section('content')
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header p-4 bg-primary bg-gradient">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0 me-3 ms-3">
                            <div class="avatar-title bg-white bg-opacity-25 text-white rounded-circle fs-20">
                                <i class="ri-add-circle-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="card-title mb-1 text-white fw-bold">{{ __('tickets::messages.create_ticket') }}</h4>
                            <p class="text-white text-opacity-75 mb-0 small">
                                {{ __('tickets::messages.create_ticket_help') ?? 'يرجى ملء البيانات التالية بدقة لضمان سرعة الحل.' }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('tickets.index') }}" class="btn btn-light btn-sm fw-bold px-3 shadow-none">
                                <i class="ri-arrow-left-line align-bottom me-1"></i> {{ __('tickets::messages.cancel') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data"
                        id="ticketCreateForm">
                        @csrf

                        <div class="row g-4">
                            <!-- Step 1: Classification -->
                            <div class="col-12">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-primary-subtle text-primary rounded-circle p-2 me-2 ms-2"><i
                                            class="ri-list-settings-line fs-14"></i></span>
                                    <h5 class="mb-0 fw-bold text-dark">{{ __('tickets::messages.lookups.lookups') }}</h5>
                                </div>
                                <hr class="mt-0 opacity-10">
                            </div>

                            <!-- Stage Selection -->
                            <div class="col-md-6 mb-2">
                                <label for="stage_id" class="form-label fw-bold text-dark"><i
                                        class="ri-node-tree me-1 text-primary"></i> {{ __('tickets::messages.stage') }}
                                    <span class="text-danger">*</span></label>
                                <select class="form-select bg-light border-0 py-2 shadow-none" name="stage_id"
                                    id="stage_id" required onchange="populateCategories()">
                                    <option value="">{{ __('tickets::messages.lookups.stages') }}...</option>
                                    @foreach ($stages as $stage)
                                        <option value="{{ $stage->id }}">{{ $stage->external_name ?: $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">
                                    {{ __('tickets::messages.stage_selection_help') ?? 'اختر المرحلة الأساسية للطلب.' }}
                                </div>
                            </div>

                            <!-- Category Selection -->
                            <div class="col-md-6 mb-2">
                                <label for="category_id" class="form-label fw-bold text-dark"><i
                                        class="ri-stack-line me-1 text-primary"></i> {{ __('tickets::messages.category') }}
                                    <span class="text-danger">*</span></label>
                                <select class="form-select bg-light border-0 py-2 shadow-none" name="category_id"
                                    id="category_id" required disabled onchange="populateComplaints()">
                                    <option value="">---</option>
                                </select>
                                <div class="form-text text-muted small">
                                    {{ __('tickets::messages.category_selection_help') ?? 'اختر التصنيف الذي يصف مشكلتك.' }}
                                </div>
                            </div>

                            <!-- Complaint Selection (Optional) -->
                            <div class="col-md-6 mb-2 animate__animated animate__fadeIn" id="complaint_group"
                                style="display: none;">
                                <label for="complaint_id" class="form-label fw-bold text-dark"><i
                                        class="ri-error-warning-line me-1 text-primary"></i>
                                    {{ __('tickets::messages.complaint') }}</label>
                                <select class="form-select bg-light border-0 py-2 shadow-none" name="complaint_id"
                                    id="complaint_id" onchange="populateSubComplaints()">
                                    <option value="">---</option>
                                </select>
                                <div class="form-text text-muted small">
                                    {{ __('tickets::messages.complaint_selection_help') ?? 'تحديد نوع الشكوى يساعدنا في توجيهها للفريق المختص.' }}
                                </div>
                            </div>

                            <!-- Priority Selection -->
                            <div class="col-md-6 mb-2">
                                <label for="priority_id" class="form-label fw-bold text-dark"><i
                                        class="ri-scales-line me-1 text-primary"></i>
                                    {{ __('tickets::messages.priority') }} <span class="text-danger">*</span></label>
                                <select class="form-select bg-light border-0 py-2 shadow-none" name="priority_id"
                                    id="priority_id" required>
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->id }}" {{ $priority->is_default ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">
                                    {{ __('tickets::messages.priority_selection_help') ?? 'حدد مدى استعجال الطلب.' }}</div>
                            </div>

                            <!-- Sub-Complaints Selection (Optional) -->
                            <div class="col-12 mb-2 animate__animated animate__fadeIn" id="sub_complaint_group"
                                style="display: none;">
                                <label class="form-label fw-bold text-dark"><i
                                        class="ri-settings-3-line me-1 text-primary"></i>
                                    {{ __('tickets::messages.sub_complaints') }}</label>
                                <div class="p-4 border-0 rounded-4 bg-light bg-opacity-50">
                                    <div id="sub_complaints_container" class="row g-3">
                                        <!-- Checkboxes will be injected here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Details -->
                            <div class="col-12 mt-5">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-primary-subtle text-primary rounded-circle p-2 me-2 ms-2"><i
                                            class="ri-edit-2-line fs-14"></i></span>
                                    <h5 class="mb-0 fw-bold text-dark">{{ __('tickets::messages.details') }}</h5>
                                </div>
                                <hr class="mt-0 opacity-10">
                            </div>

                            <div class="col-12 mb-2">
                                <label for="subject" class="form-label fw-bold text-dark"><i
                                        class="ri-bookmark-3-line me-1 text-primary"></i>
                                    {{ __('tickets::messages.subject') }}</label>
                                <input type="text" class="form-control bg-light border-0 py-2 shadow-none" name="subject"
                                    placeholder="{{ __('tickets::messages.subject') }} ({{ __('tickets::messages.optional') ?? 'Optional' }})">
                                <div class="form-text text-muted small">
                                    {{ __('tickets::messages.subject_help') ?? 'عنوان مختصر يوضح جوهر المشكلة.' }}</div>
                            </div>

                            <div class="col-12 mb-2">
                                <label for="description" class="form-label fw-bold text-dark"><i
                                        class="ri-file-list-3-line me-1 text-primary"></i>
                                    {{ __('tickets::messages.details') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control bg-light border-0 py-3 shadow-none" name="description" rows="6" required
                                    placeholder="{{ __('tickets::messages.details') }}..."></textarea>
                                <div class="form-text text-muted small">
                                    {{ __('tickets::messages.details_help') ?? 'يرجى كتابة كافة التفاصيل التي قد تساعدنا في حل مشكلتك.' }}
                                </div>
                            </div>

                            <div class="col-12 mb-4">
                                <label for="attachments" class="form-label fw-bold text-dark"><i
                                        class="ri-attachment-line me-1 text-primary"></i>
                                    {{ __('tickets::messages.attachments') }}</label>
                                <div class="input-group">
                                    <input type="file" class="form-control bg-light border-0 py-2 shadow-none"
                                        name="attachments[]" multiple id="attachments">
                                    <label class="input-group-text bg-primary text-white border-0 px-3 cursor-pointer"
                                        for="attachments">
                                        <i class="ri-upload-cloud-2-line me-1"></i>
                                        {{ __('tickets::messages.upload') ?? 'رفع الملفات' }}
                                    </label>
                                </div>
                                <div class="form-text text-muted small">
                                    {{ __('tickets::messages.attachments_help') ?? 'يمكنك إرفاق صور أو مستندات (الحد الأقصى: 10 ميجابايت لكل ملف).' }}
                                </div>
                            </div>

                            <div class="col-12 mt-4 pt-3 border-top">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('tickets.index') }}"
                                        class="btn btn-ghost-secondary px-4 fw-medium">{{ __('tickets::messages.cancel') }}</a>
                                    <button type="submit"
                                        class="btn btn-primary btn-load btn-lg px-5 fw-bold shadow-sm d-flex align-items-center">
                                        <i class="ri-send-plane-fill me-2 fs-18"></i> {{ __('tickets::messages.save') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const stagesData = @json($stages);

        function populateCategories() {
            const stageId = document.getElementById('stage_id').value;
            const categorySelect = document.getElementById('category_id');
            const complaintSelect = document.getElementById('complaint_id');
            const complaintGroup = document.getElementById('complaint_group');
            const subComplaintGroup = document.getElementById('sub_complaint_group');

            categorySelect.innerHTML = '<option value="">{{ __('tickets::messages.lookups.categories') }}...</option>';
            complaintSelect.innerHTML = '<option value="">{{ __('tickets::messages.lookups.complaints') }}...</option>';
            categorySelect.disabled = true;

            // Hide with animation
            complaintGroup.style.display = 'none';
            subComplaintGroup.style.display = 'none';

            if (!stageId) return;

            const selectedStage = stagesData.find(s => s.id == stageId);

            if (selectedStage && selectedStage.categories && selectedStage.categories.length > 0) {
                categorySelect.disabled = false;
                selectedStage.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
            }
        }

        function populateComplaints() {
            const stageId = document.getElementById('stage_id').value;
            const categoryId = document.getElementById('category_id').value;
            const complaintSelect = document.getElementById('complaint_id');
            const complaintGroup = document.getElementById('complaint_group');
            const subComplaintGroup = document.getElementById('sub_complaint_group');

            complaintSelect.innerHTML = '<option value="">{{ __('tickets::messages.lookups.complaints') }}...</option>';
            complaintGroup.style.display = 'none';
            subComplaintGroup.style.display = 'none';

            if (!stageId || !categoryId) return;

            const selectedStage = stagesData.find(s => s.id == stageId);
            const selectedCategory = selectedStage.categories.find(c => c.id == categoryId);

            if (selectedCategory && selectedCategory.complaints && selectedCategory.complaints.length > 0) {
                complaintGroup.style.display = 'block';
                selectedCategory.complaints.forEach(complaint => {
                    const option = document.createElement('option');
                    option.value = complaint.id;
                    option.textContent = complaint.name;
                    complaintSelect.appendChild(option);
                });
            }
        }

        function populateSubComplaints() {
            const stageId = document.getElementById('stage_id').value;
            const categoryId = document.getElementById('category_id').value;
            const complaintId = document.getElementById('complaint_id').value;
            const subComplaintGroup = document.getElementById('sub_complaint_group');
            const subComplaintContainer = document.getElementById('sub_complaints_container');

            subComplaintContainer.innerHTML = '';
            subComplaintGroup.style.display = 'none';

            if (!stageId || !categoryId || !complaintId) return;

            const selectedStage = stagesData.find(s => s.id == stageId);
            const selectedCategory = selectedStage.categories.find(c => c.id == categoryId);
            const selectComplaint = selectedCategory.complaints.find(c => c.id == complaintId);

            if (selectComplaint && selectComplaint.sub_complaints && selectComplaint.sub_complaints.length > 0) {
                subComplaintGroup.style.display = 'block';
                selectComplaint.sub_complaints.forEach(sub => {
                    const div = document.createElement('div');
                    div.className = 'col-md-6 col-lg-4';
                    div.innerHTML = `
                    <div class="form-check card-radio p-0 border-0">
                        <input class="form-check-input d-none" type="checkbox" name="sub_complaints[]" value="${sub.id}" id="sub_${sub.id}">
                        <label class="form-check-label border rounded p-3 d-flex align-items-center gap-2 cursor-pointer transition-all hover-shadow" for="sub_${sub.id}">
                            <i class="ri-checkbox-blank-circle-line fs-18 text-muted icon-unchecked"></i>
                            <i class="ri-checkbox-circle-fill fs-18 text-primary icon-checked d-none"></i>
                            <span class="fs-13 fw-medium text-dark">${sub.name}</span>
                        </label>
                    </div>
                `;
                    subComplaintContainer.appendChild(div);

                    // Add click behavior for custom cards
                    const input = div.querySelector('input');
                    const label = div.querySelector('label');
                    const uncheckedIcon = label.querySelector('.icon-unchecked');
                    const checkedIcon = label.querySelector('.icon-checked');

                    label.addEventListener('click', function() {
                        input.checked = !input.checked;
                        if (input.checked) {
                            label.classList.add('border-primary', 'bg-primary-subtle');
                            uncheckedIcon.classList.add('d-none');
                            checkedIcon.classList.remove('d-none');
                        } else {
                            label.classList.remove('border-primary', 'bg-primary-subtle');
                            uncheckedIcon.classList.remove('d-none');
                            checkedIcon.classList.add('d-none');
                        }
                    });
                });
            }
        }
    </script>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .card-radio input:checked+label {
            border-color: var(--vz-primary) !important;
            background-color: var(--vz-primary-bg-subtle) !important;
        }

        .form-select:disabled {
            background-color: #f3f6f9 !important;
            opacity: 0.6;
        }
    </style>
@endsection
