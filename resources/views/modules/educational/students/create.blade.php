@extends('core::layouts.master')
@section('title', __('educational::messages.add_student'))

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-soft-primary border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm me-3">
                            <div class="avatar-title bg-primary text-white rounded-circle fs-4">
                                <i class="ri-user-add-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.add_student') }}</h5>
                            <p class="text-muted mb-0 small">{{ __('educational::messages.manage_students_desc') }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('educational.students.index') }}"
                                class="btn btn-soft-secondary btn-sm rounded-pill px-3">
                                <i class="ri-history-line align-middle me-1"></i>
                                {{ __('educational::messages.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified border-bottom-0 bg-light-subtle"
                        role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-3" data-bs-toggle="tab" href="#account-info" role="tab">
                                <i class="ri-shield-user-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">حساب النظام</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-bs-toggle="tab" href="#personal-info" role="tab">
                                <i class="ri-user-settings-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">البيانات الشخصية</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-bs-toggle="tab" href="#academic-info" role="tab">
                                <i class="ri-graduation-cap-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">المسار التدريبي</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-bs-toggle="tab" href="#emergency-contacts" role="tab">
                                <i class="ri-contacts-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">جهات الطوارئ</span>
                            </a>
                        </li>
                    </ul>

                    <form action="{{ route('educational.students.store') }}" method="POST" enctype="multipart/form-data"
                        id="studentForm">
                        @csrf

                        <div class="tab-content p-4">
                            <!-- Account Info Tab -->
                            <div class="tab-pane active" id="account-info" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label fw-bold">الاسم الأول <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="ri-user-line text-muted"></i></span>
                                                <input type="text" name="first_name" class="form-control border-start-0"
                                                    value="{{ old('first_name') }}" required placeholder="أدخل الاسم الأول">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label fw-bold">اسم العائلة <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="ri-user-line text-muted"></i></span>
                                                <input type="text" name="last_name" class="form-control border-start-0"
                                                    value="{{ old('last_name') }}" required placeholder="أدخل اسم العائلة">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label fw-bold">اسم المستخدم <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="ri-at-line text-muted"></i></span>
                                                <input type="text" name="username" class="form-control border-start-0"
                                                    value="{{ old('username') }}" required placeholder="مثلاً: trainee2024">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label fw-bold">كلمة المرور <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="ri-lock-2-line text-muted"></i></span>
                                                <input type="password" name="password" class="form-control border-start-0"
                                                    required placeholder="8 رموز على الأقل">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary btn-label right btn-next-tab px-4"
                                        data-next="#personal-info-tab-link">
                                        <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i> التالي:
                                        البيانات الشخصية
                                    </button>
                                </div>
                            </div>

                            <!-- Personal Info Tab -->
                            <div class="tab-pane" id="personal-info" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">الاسم الكامل بالعربية</label>
                                        <input type="text" name="arabic_name" class="form-control py-2"
                                            value="{{ old('arabic_name') }}" placeholder="أدخل الاسم الرباعي">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">الاسم الكامل بالإنجليزية</label>
                                        <input type="text" name="english_name" class="form-control py-2"
                                            value="{{ old('english_name') }}" placeholder="Full Name in English">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">البريد الإلكتروني <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control py-2"
                                            value="{{ old('email') }}" required placeholder="example@email.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">رقم الهاتف الأساسي</label>
                                        <input type="text" name="phone" class="form-control py-2"
                                            value="{{ old('phone') }}" placeholder="01xxxxxxxxx">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">النوع (الجنس)</label>
                                        <select name="gender" class="form-select py-2">
                                            <option value="">اختر النوع</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر
                                            </option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">الرقم القومي</label>
                                        <input type="text" name="national_id" class="form-control py-2"
                                            value="{{ old('national_id') }}" placeholder="رقم البطاقة الشخصية">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">رقم الجواز (اختياري)</label>
                                        <input type="text" name="passport_number" class="form-control py-2"
                                            value="{{ old('passport_number') }}" placeholder="Passport ID">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">الديانة</label>
                                        <select name="religion" class="form-select py-2">
                                            <option value="">اختر الديانة</option>
                                            <option value="muslim" {{ old('religion') == 'muslim' ? 'selected' : '' }}>
                                                مسلم
                                            </option>
                                            <option value="christian"
                                                {{ old('religion') == 'christian' ? 'selected' : '' }}>
                                                مسيحي</option>
                                            <option value="other" {{ old('religion') == 'other' ? 'selected' : '' }}>أخرى
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">الملة</label>
                                        <input type="text" name="sect" class="form-control py-2"
                                            value="{{ old('sect') }}" placeholder="الملة (اختياري)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">سيريال الجهاز</label>
                                        <input type="text" name="device_code" class="form-control py-2"
                                            value="{{ old('device_code') }}" placeholder="سيريال الجهاز (اختياري)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">تاريخ الميلاد</label>
                                        <input type="date" name="date_of_birth" class="form-control py-2"
                                            value="{{ old('date_of_birth') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">المحلية / المحافظة</label>
                                        <select name="governorate_id" class="form-select py-2">
                                            <option value="">اختر المحافظة</option>
                                            @foreach ($governorates as $gov)
                                                <option value="{{ $gov->id }}"
                                                    {{ old('governorate_id') == $gov->id ? 'selected' : '' }}>
                                                    {{ $gov->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">العنوان التفصيلي</label>
                                        <input type="text" name="address" class="form-control py-2"
                                            value="{{ old('address') }}" placeholder="المدينة، الشارع، رقم المبنى">
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-soft-secondary btn-next-tab px-4"
                                        data-next="#account-info-tab-link">
                                        السابق
                                    </button>
                                    <button type="button" class="btn btn-primary btn-label right btn-next-tab px-4"
                                        data-next="#academic-info-tab-link">
                                        <i class="ri-graduation-cap-line label-icon align-middle fs-16 ms-2"></i> التالي:
                                        المسار التدريبي
                                    </button>
                                </div>
                            </div>

                            <!-- Academic Info Tab -->
                            <div class="tab-pane" id="academic-info" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0 shadow-none mb-0">
                                            <div class="card-body">
                                                <h6 class="fw-bold text-primary mb-3"><i class="ri-focus-3-line me-1"></i>
                                                    التخصص الأكاديمي</h6>
                                                <label class="form-label small fw-bold">الملف الوظيفي / المسار</label>
                                                <select name="job_profile_id" class="form-select py-2 select2-basic">
                                                    <option value="">اختر التخصص</option>
                                                    @foreach ($tracks as $track)
                                                        <optgroup label="{{ $track->name }}">
                                                            @foreach ($track->jobProfiles as $prof)
                                                                <option value="{{ $prof->id }}"
                                                                    {{ old('job_profile_id') == $prof->id ? 'selected' : '' }}>
                                                                    {{ $prof->name }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0 shadow-none mb-0 h-100">
                                            <div class="card-body">
                                                <h6 class="fw-bold text-success mb-3"><i
                                                        class="ri-settings-4-line me-1"></i> الحالة التشغيلية</h6>
                                                <label class="form-label small fw-bold">حالة الالتحاق بالسيستم <span
                                                        class="text-danger">*</span></label>
                                                <select name="enrollment_status" class="form-select py-2 border-success"
                                                    required>
                                                    <option value="active"
                                                        {{ old('enrollment_status', 'active') == 'active' ? 'selected' : '' }}>
                                                        نشط / معتمد</option>
                                                    <option value="on_leave"
                                                        {{ old('enrollment_status') == 'on_leave' ? 'selected' : '' }}>في
                                                        اجازه</option>
                                                    <option value="graduated"
                                                        {{ old('enrollment_status') == 'graduated' ? 'selected' : '' }}>
                                                        خريج</option>
                                                    <option value="withdrawn"
                                                        {{ old('enrollment_status') == 'withdrawn' ? 'selected' : '' }}>
                                                        منسحب</option>
                                                    <option value="suspended"
                                                        {{ old('enrollment_status') == 'suspended' ? 'selected' : '' }}>
                                                        موقف</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">البرنامج التعليمي</label>
                                        <select name="program_id" id="program_select" class="form-select py-2">
                                            <option value="">اختر البرنامج</option>
                                            @foreach ($programs as $prog)
                                                <option value="{{ $prog->id }}"
                                                    {{ old('program_id') == $prog->id ? 'selected' : '' }}>
                                                    {{ $prog->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">المجموعة</label>
                                        <select name="group_id" id="group_select" class="form-select py-2">
                                            <option value="">اختر المجموعة</option>
                                            @foreach ($groups as $grp)
                                                <option value="{{ $grp->id }}"
                                                    data-program="{{ $grp->program_id }}"
                                                    {{ old('group_id') == $grp->id ? 'selected' : '' }}>
                                                    {{ $grp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-soft-secondary btn-next-tab px-4"
                                        data-next="#personal-info-tab-link">
                                        السابق
                                    </button>
                                    <button type="button" class="btn btn-primary btn-label right btn-next-tab px-4"
                                        data-next="#emergency-contacts-tab-link">
                                        <i class="ri-contacts-line label-icon align-middle fs-16 ms-2"></i> التالي: جهات
                                        الطوارئ
                                    </button>
                                </div>
                            </div>

                            <!-- Emergency Contacts Tab -->
                            <div class="tab-pane" id="emergency-contacts" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
                                    <h6 class="fw-bold mb-0 text-muted">بيانات الأقارب للطوارئ</h6>
                                    <button type="button" class="btn btn-soft-info btn-sm rounded-pill px-3"
                                        id="add_contact_btn">
                                        <i class="ri-add-circle-line align-middle me-1"></i> إضافة جهة اتصال جديدة
                                    </button>
                                </div>

                                <div id="contacts_container">
                                    <!-- Dynamic Items -->
                                    <div
                                        class="empty-contacts text-center py-4 @if (old('emergency_contacts')) d-none @endif">
                                        <div class="avatar-md mx-auto mb-3">
                                            <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                                                <i class="ri-contacts-book-line"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted">لم يتم إضافة أي جهات اتصال للطوارئ بعد.</p>
                                    </div>
                                </div>

                                <div class="mt-5 border-top pt-4 text-center">
                                    <button type="button" class="btn btn-soft-secondary btn-next-tab me-2 px-5 py-2"
                                        data-next="#academic-info-tab-link">
                                        السابق
                                    </button>
                                    <button type="submit" class="btn btn-success btn-label px-5 py-2 shadow-lg">
                                        <i class="ri-save-fill label-icon align-middle fs-16 me-2"></i> حفظ ملف المتدرب
                                        بالكامل
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Tab Links for JS Navigation -->
    <div class="d-none">
        <a href="#account-info" id="account-info-tab-link" data-bs-toggle="tab"></a>
        <a href="#personal-info" id="personal-info-tab-link" data-bs-toggle="tab"></a>
        <a href="#academic-info" id="academic-info-tab-link" data-bs-toggle="tab"></a>
        <a href="#emergency-contacts" id="emergency-contacts-tab-link" data-bs-toggle="tab"></a>
    </div>

    <!-- Template for Emergency Contacts -->
    <template id="contact_template">
        <div
            class="contact-item border rounded-4 p-4 mb-4 bg-white shadow-sm position-relative overflow-hidden contact-reveal">
            <div class="position-absolute top-0 start-0 h-100 bg-info opacity-10" style="width: 4px;"></div>
            <button type="button"
                class="btn btn-icon btn-sm btn-soft-danger position-absolute top-0 end-0 m-3 remove-contact-btn shadow-sm">
                <i class="ri-delete-bin-fill"></i>
            </button>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">صلة القرابة *</label>
                    <select name="emergency_contacts[__INDEX__][relation]" class="form-select" required>
                        <option value="">اختر</option>
                        @foreach (['اب', 'ام', 'خال / خالة', 'عم / عمة', 'اخ', 'اخت', 'ابن عم / بنت عم', 'ابن خال / بنت خال', 'زوج اخت / زوجة اخ', 'جد / جدة', 'اخرى'] as $rel)
                            <option value="{{ $rel }}">{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">الاسم الكامل لجهة الاتصال *</label>
                    <input type="text" name="emergency_contacts[__INDEX__][name]" class="form-control" required
                        placeholder="أدخل الاسم">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">رقم الهاتف *</label>
                    <input type="text" name="emergency_contacts[__INDEX__][phone]" class="form-control" required
                        placeholder="رقم الموبايل">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">البريد الإلكتروني</label>
                    <input type="email" name="emergency_contacts[__INDEX__][email]" class="form-control"
                        placeholder="example@email.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">صورة جهة الاتصال</label>
                    <input type="file" name="emergency_contacts[__INDEX__][photo]" class="form-control border-dashed">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">العنوان</label>
                    <input type="text" name="emergency_contacts[__INDEX__][address]" class="form-control"
                        placeholder="العنوان السكني">
                </div>
            </div>
        </div>
    </template>

@endsection

@push('styles')
    <style>
        .nav-tabs-custom .nav-link {
            border: none;
            font-weight: 600;
            color: #777;
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-tabs-custom .nav-link.active {
            color: var(--vz-success);
            background: #fff !important;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--vz-success);
            box-shadow: 0 -2px 10px rgba(10, 179, 156, 0.4);
        }

        .contact-item {
            transition: all 0.3s ease;
            animation: slideUp 0.4s ease-out;
        }

        .contact-item:hover {
            transform: scale(1.005);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--vz-primary);
            box-shadow: 0 0 0 3px rgba(64, 81, 137, 0.1);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Navigation Buttons
            document.querySelectorAll('.btn-next-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetSelector = this.dataset.next;
                    const targetLink = document.querySelector(targetSelector);
                    if (targetLink) {
                        targetLink.click();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Dynamic Group Select
            const programSelect = document.getElementById('program_select');
            const groupSelect = document.getElementById('group_select');
            const allGroupOptions = Array.from(groupSelect.options);

            function updateGroups() {
                const progId = programSelect.value;
                groupSelect.innerHTML = '<option value="">اختر المجموعة</option>';
                allGroupOptions.forEach(opt => {
                    if (opt.dataset.program == progId || opt.value == "") {
                        groupSelect.appendChild(opt.cloneNode(true));
                    }
                });
            }
            programSelect.addEventListener('change', updateGroups);
            updateGroups();

            // Contacts Manager
            let contactIdx = 0;
            const container = document.getElementById('contacts_container');
            const addBtn = document.getElementById('add_contact_btn');
            const template = document.getElementById('contact_template').innerHTML;
            const emptyState = document.querySelector('.empty-contacts');

            addBtn.addEventListener('click', function() {
                emptyState.classList.add('d-none');
                const html = template.replace(/__INDEX__/g, contactIdx++);
                container.insertAdjacentHTML('beforeend', html);
            });

            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-contact-btn')) {
                    e.target.closest('.contact-item').remove();
                    if (container.querySelectorAll('.contact-item').length === 0) {
                        emptyState.classList.remove('d-none');
                    }
                }
            });
        });
    </script>
@endpush
