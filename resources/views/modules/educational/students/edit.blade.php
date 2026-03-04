@extends('core::layouts.master')
@section('title', __('educational::messages.edit_student'))

@section('content')

    @include('modules.educational.shared.alerts')

    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-soft-info border-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm me-3">
                            <div class="avatar-title bg-info text-white rounded-circle fs-4">
                                <i class="ri-user-settings-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0 fw-bold">{{ __('educational::messages.edit_student') }}:
                                {{ $student->arabic_name ?? $student->user->full_name }}</h5>
                            <p class="text-muted mb-0 small">تحديث بيانات المتدرب والمسارات التدريبية وجهات التواصل.</p>
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
                    <ul class="nav nav-tabs nav-tabs-custom nav-info nav-justified border-bottom-0 bg-light-subtle"
                        role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-3" data-bs-toggle="tab" href="#account-info" role="tab"
                                id="account-info-tab">
                                <i class="ri-shield-user-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">حساب النظام</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-bs-toggle="tab" href="#personal-info" role="tab"
                                id="personal-info-tab">
                                <i class="ri-user-settings-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">البيانات الشخصية</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-bs-toggle="tab" href="#academic-info" role="tab"
                                id="academic-info-tab">
                                <i class="ri-graduation-cap-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">المسار التدريبي</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-bs-toggle="tab" href="#emergency-contacts" role="tab"
                                id="emergency-contacts-tab">
                                <i class="ri-contacts-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">جهات الطوارئ</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-3" data-bs-toggle="tab" href="#student-documents" role="tab"
                                id="student-documents-tab">
                                <i class="ri-file-list-3-line me-1 align-bottom"></i> <span
                                    class="d-none d-md-inline-block">المستندات</span>
                            </a>
                        </li>
                    </ul>

                    <form action="{{ route('educational.students.update', $student->id) }}" method="POST"
                        enctype="multipart/form-data" id="studentEditForm">
                        @csrf
                        @method('PUT')

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
                                                    value="{{ old('first_name', $student->user->first_name) }}" required>
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
                                                    value="{{ old('last_name', $student->user->last_name) }}" required>
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
                                                    value="{{ old('username', $student->user->username) }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label fw-bold">كلمة المرور</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="ri-lock-2-line text-muted"></i></span>
                                                <input type="password" name="password"
                                                    class="form-control border-start-0"
                                                    placeholder="اتركها فارغة للاحتفاظ بالقديمة">
                                            </div>
                                            <div class="form-text fs-11 text-warning"><i
                                                    class="ri-error-warning-line me-1"></i> يتم التعديل فقط في حال إدخال
                                                قيمة جديدة.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-end">
                                    <button type="button" class="btn btn-info btn-label right btn-next-tab px-4"
                                        data-next="#personal-info-tab">
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
                                            value="{{ old('arabic_name', $student->arabic_name) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">الاسم الكامل بالإنجليزية</label>
                                        <input type="text" name="english_name" class="form-control py-2"
                                            value="{{ old('english_name', $student->english_name) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">البريد الإلكتروني <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control py-2"
                                            value="{{ old('email', $student->user->email) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">رقم الهاتف الأساسي</label>
                                        <input type="text" name="phone" class="form-control py-2"
                                            value="{{ old('phone', $student->user->phone) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">النوع (الجنس)</label>
                                        <select name="gender" class="form-select py-2">
                                            <option value="">اختر النوع</option>
                                            <option value="male"
                                                {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>ذكر
                                            </option>
                                            <option value="female"
                                                {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>أنثى
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">الرقم القومي</label>
                                        <input type="text" name="national_id" class="form-control py-2"
                                            value="{{ old('national_id', $student->revealSensitive('national_id')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">رقم الجواز (اختياري)</label>
                                        <input type="text" name="passport_number" class="form-control py-2"
                                            value="{{ old('passport_number', $student->revealSensitive('passport_number')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">الديانة</label>
                                        <select name="religion" class="form-select py-2">
                                            <option value="">اختر الديانة</option>
                                            <option value="muslim"
                                                {{ old('religion', $student->revealSensitive('religion')) == 'muslim' ? 'selected' : '' }}>
                                                مسلم</option>
                                            <option value="christian"
                                                {{ old('religion', $student->revealSensitive('religion')) == 'christian' ? 'selected' : '' }}>
                                                مسيحي</option>
                                            <option value="other"
                                                {{ old('religion', $student->revealSensitive('religion')) == 'other' ? 'selected' : '' }}>
                                                أخرى</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">الملة</label>
                                        <input type="text" name="sect" class="form-control py-2"
                                            value="{{ old('sect', $student->sect) }}" placeholder="الملة (اختياري)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">سيريال الجهاز</label>
                                        <input type="text" name="device_code" class="form-control py-2"
                                            value="{{ old('device_code', $student->device_code) }}"
                                            placeholder="سيريال الجهاز (اختياري)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">تاريخ الميلاد</label>
                                        <input type="date" name="date_of_birth" class="form-control py-2"
                                            value="{{ old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">المحلية / المحافظة</label>
                                        <select name="governorate_id" class="form-select py-2">
                                            <option value="">اختر المحافظة</option>
                                            @foreach ($governorates as $gov)
                                                <option value="{{ $gov->id }}"
                                                    {{ old('governorate_id', $student->governorate_id) == $gov->id ? 'selected' : '' }}>
                                                    {{ $gov->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">الجنسية</label>
                                        <input type="text" name="nationality" class="form-control py-2"
                                            value="{{ old('nationality', $student->nationality) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">العنوان التفصيلي</label>
                                        <input type="text" name="address" class="form-control py-2"
                                            value="{{ old('address', $student->address) }}">
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-soft-secondary btn-next-tab px-4"
                                        data-next="#account-info-tab">
                                        السابق
                                    </button>
                                    <button type="button" class="btn btn-info btn-label right btn-next-tab px-4"
                                        data-next="#academic-info-tab">
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
                                                <h6 class="fw-bold text-info mb-3"><i class="ri-focus-3-line me-1"></i>
                                                    التخصص الأكاديمي الحالي</h6>
                                                <label class="form-label small fw-bold">الملف الوظيفي / المسار</label>
                                                <select name="job_profile_id" class="form-select py-2">
                                                    <option value="">اختر التخصص</option>
                                                    @foreach ($tracks as $track)
                                                        <optgroup label="{{ $track->name }}">
                                                            @foreach ($track->jobProfiles as $prof)
                                                                <option value="{{ $prof->id }}"
                                                                    {{ old('job_profile_id', $student->job_profile_id) == $prof->id ? 'selected' : '' }}>
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
                                                <label class="form-label small fw-bold">حالة الالتحاق الحالية <span
                                                        class="text-danger">*</span></label>
                                                <select name="enrollment_status" class="form-select py-2 border-info"
                                                    required>
                                                    <option value="active"
                                                        {{ old('enrollment_status', $student->enrollment_status) == 'active' ? 'selected' : '' }}>
                                                        نشط / معتمد</option>
                                                    <option value="on_leave"
                                                        {{ old('enrollment_status', $student->enrollment_status) == 'on_leave' ? 'selected' : '' }}>
                                                        في اجازه</option>
                                                    <option value="graduated"
                                                        {{ old('enrollment_status', $student->enrollment_status) == 'graduated' ? 'selected' : '' }}>
                                                        خريج</option>
                                                    <option value="withdrawn"
                                                        {{ old('enrollment_status', $student->enrollment_status) == 'withdrawn' ? 'selected' : '' }}>
                                                        منسحب</option>
                                                    <option value="suspended"
                                                        {{ old('enrollment_status', $student->enrollment_status) == 'suspended' ? 'selected' : '' }}>
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
                                                    {{ old('program_id', $student->program_id) == $prog->id ? 'selected' : '' }}>
                                                    {{ $prog->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">المجموعة</label>
                                        <select name="group_id" id="group_select" class="form-select py-2"
                                            data-selected="{{ old('group_id', $student->group_id) }}">
                                            <option value="">اختر المجموعة</option>
                                            @foreach ($groups as $grp)
                                                <option value="{{ $grp->id }}"
                                                    data-program="{{ $grp->program_id }}">{{ $grp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-soft-secondary btn-next-tab px-4"
                                        data-next="#personal-info-tab">
                                        السابق
                                    </button>
                                    <button type="button" class="btn btn-info btn-label right btn-next-tab px-4"
                                        data-next="#emergency-contacts-tab">
                                        <i class="ri-contacts-line label-icon align-middle fs-16 ms-2"></i> التالي: جهات
                                        الطوارئ
                                    </button>
                                </div>
                            </div>

                            <!-- Emergency Contacts Tab -->
                            <div class="tab-pane" id="emergency-contacts" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
                                    <h6 class="fw-bold mb-0 text-muted">بيانات الأقارب للطوارئ
                                        ({{ $student->emergencyContacts->count() }})</h6>
                                    <button type="button" class="btn btn-soft-info btn-sm rounded-pill px-3"
                                        id="add_contact_btn">
                                        <i class="ri-add-circle-line align-middle me-1"></i> إضافة جهة اتصال جديدة
                                    </button>
                                </div>

                                <div id="contacts_container">
                                    @forelse($student->emergencyContacts as $i => $contact)
                                        <div
                                            class="contact-item border rounded-4 p-4 mb-4 bg-white shadow-sm position-relative overflow-hidden">
                                            <div class="position-absolute top-0 start-0 h-100 bg-info opacity-10"
                                                style="width: 4px;"></div>
                                            <input type="hidden" name="emergency_contacts[{{ $i }}][id]"
                                                value="{{ $contact->id }}">
                                            <button type="button"
                                                class="btn btn-icon btn-sm btn-soft-danger position-absolute top-0 end-0 m-3 remove-contact-btn shadow-sm">
                                                <i class="ri-delete-bin-fill"></i>
                                            </button>

                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold text-muted">صلة القرابة
                                                        *</label>
                                                    <select name="emergency_contacts[{{ $i }}][relation]"
                                                        class="form-select" required>
                                                        @foreach (['اب', 'ام', 'خال / خالة', 'عم / عمة', 'اخ', 'اخت', 'ابن عم / بنت عم', 'ابن خال / بنت خال', 'زوج اخت / زوجة اخ', 'جد / جدة', 'اخرى'] as $rel)
                                                            <option value="{{ $rel }}"
                                                                {{ $contact->relation == $rel ? 'selected' : '' }}>
                                                                {{ $rel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small fw-bold text-muted">الاسم الكامل
                                                        *</label>
                                                    <input type="text"
                                                        name="emergency_contacts[{{ $i }}][name]"
                                                        class="form-control" value="{{ $contact->name }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">رقم الهاتف *</label>
                                                    <input type="text"
                                                        name="emergency_contacts[{{ $i }}][phone]"
                                                        class="form-control" value="{{ $contact->phone }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">البريد
                                                        الإلكتروني</label>
                                                    <input type="email"
                                                        name="emergency_contacts[{{ $i }}][email]"
                                                        class="form-control" value="{{ $contact->email }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">تحديث الصورة</label>
                                                    <input type="file"
                                                        name="emergency_contacts[{{ $i }}][photo]"
                                                        class="form-control border-dashed">
                                                    @if ($contact->photo_path)
                                                        <div class="mt-2 avatar-xs">
                                                            <img src="{{ Storage::url($contact->photo_path) }}"
                                                                class="img-fluid rounded shadow-sm" alt="">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted">العنوان</label>
                                                    <input type="text"
                                                        name="emergency_contacts[{{ $i }}][address]"
                                                        class="form-control" value="{{ $contact->address }}">
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="empty-contacts text-center py-4">
                                            <div class="avatar-md mx-auto mb-3">
                                                <div class="avatar-title bg-light text-muted rounded-circle fs-2">
                                                    <i class="ri-contacts-book-line"></i>
                                                </div>
                                            </div>
                                            <p class="text-muted">لم يتم إضافة أي جهات اتصال للطوارئ بعد.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <button type="button" class="btn btn-soft-secondary btn-next-tab px-4"
                                        data-next="#academic-info-tab">
                                        السابق
                                    </button>
                                    <button type="button" class="btn btn-info btn-label right btn-next-tab px-4"
                                        data-next="#student-documents-tab">
                                        <i class="ri-file-list-3-line label-icon align-middle fs-16 ms-2"></i> التالي:
                                        المستندات
                                    </button>
                                </div>
                            </div>

                            <!-- Documents Tab -->
                            <div class="tab-pane" id="student-documents" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="card border-1 border-info border-dashed">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-3"><i class="ri-upload-cloud-2-line me-1"></i> رفع
                                                    مستندات جديدة (اختياري)</h6>
                                                <input type="file" name="documents[]" class="form-control" multiple>
                                                <p class="text-muted mt-2 small">يمكنك اختيار ملفات متعددة (صور، PDF، Word)
                                                    - الحد الأقصى 5 ميجا للملف.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h6 class="fw-bold mb-3">المستندات الحالية ({{ $student->documents->count() }})
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-nowrap align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>اسم الملف</th>
                                                        <th>النوع</th>
                                                        <th>الحجم</th>
                                                        <th class="text-center">إجراء</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($student->documents as $doc)
                                                        <tr>
                                                            <td>
                                                                <a href="{{ Storage::url($doc->file_path) }}"
                                                                    target="_blank" class="fw-medium text-primary">
                                                                    <i class="ri-file-line me-1"></i> {{ $doc->name }}
                                                                </a>
                                                            </td>
                                                            <td>{{ $doc->file_type }}</td>
                                                            <td>{{ number_format($doc->file_size / 1024, 2) }} KB</td>
                                                            <td class="text-center">
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="delete_documents[]"
                                                                        value="{{ $doc->id }}"
                                                                        id="del_doc_{{ $doc->id }}">
                                                                    <label class="form-check-label text-danger"
                                                                        for="del_doc_{{ $doc->id }}">حذف</label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted">لا يوجد
                                                                مستندات مرفوعة حالياً.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 border-top pt-4 text-center">
                                    <button type="button" class="btn btn-soft-secondary btn-next-tab me-2 px-5 py-2"
                                        data-next="#emergency-contacts-tab">
                                        السابق
                                    </button>
                                    <button type="submit" class="btn btn-info btn-label px-5 py-2 shadow-lg">
                                        <i class="ri-check-double-fill label-icon align-middle fs-16 me-2"></i> تحديث وحفظ
                                        التعديلات بالكامل
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Template for New Emergency Contacts -->
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
                    <label class="form-label small fw-bold text-muted">الاسم الكامل *</label>
                    <input type="text" name="emergency_contacts[__INDEX__][name]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">رقم الهاتف *</label>
                    <input type="text" name="emergency_contacts[__INDEX__][phone]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">البريد الإلكتروني</label>
                    <input type="email" name="emergency_contacts[__INDEX__][email]" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">صورة جهة الاتصال</label>
                    <input type="file" name="emergency_contacts[__INDEX__][photo]" class="form-control border-dashed">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">العنوان</label>
                    <input type="text" name="emergency_contacts[__INDEX__][address]" class="form-control">
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
            color: var(--vz-info);
            background: #fff !important;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--vz-info);
            box-shadow: 0 -2px 10px rgba(41, 156, 219, 0.4);
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
            const selectedGroup = groupSelect.dataset.selected;

            function updateGroups() {
                const progId = programSelect.value;
                groupSelect.innerHTML = '<option value="">اختر المجموعة</option>';
                allGroupOptions.forEach(opt => {
                    if (opt.dataset.program == progId || opt.value == "") {
                        const newOpt = opt.cloneNode(true);
                        if (newOpt.value == selectedGroup) newOpt.selected = true;
                        groupSelect.appendChild(newOpt);
                    }
                });
            }
            programSelect.addEventListener('change', updateGroups);
            updateGroups();

            // Contacts Manager
            let contactIdx = {{ $student->emergencyContacts->count() }};
            const container = document.getElementById('contacts_container');
            const addBtn = document.getElementById('add_contact_btn');
            const template = document.getElementById('contact_template').innerHTML;

            addBtn.addEventListener('click', function() {
                const emptyState = document.querySelector('.empty-contacts');
                if (emptyState) emptyState.classList.add('d-none');
                const html = template.replace(/__INDEX__/g, contactIdx++);
                container.insertAdjacentHTML('beforeend', html);
            });

            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-contact-btn')) {
                    e.target.closest('.contact-item').remove();
                }
            });

            // Validation Error Redirect & Highlight
            @if ($errors->any())
                const firstErrorField = document.querySelector('.is-invalid, [name="{{ $errors->keys()[0] }}"]');
                if (firstErrorField) {
                    // Find parent tab
                    const tabPane = firstErrorField.closest('.tab-pane');
                    if (tabPane) {
                        const tabId = tabPane.id;
                        const tabTrigger = document.querySelector(`[href="#${tabId}"]`);
                        if (tabTrigger) {
                            tabTrigger.click();
                            setTimeout(() => {
                                firstErrorField.classList.add('is-invalid');
                                firstErrorField.focus();
                                firstErrorField.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }, 300);
                        }
                    }
                }
            @endif
        });
    </script>
@endpush
