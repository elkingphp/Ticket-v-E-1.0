@extends('core::layouts.master')
@section('title', __('educational::messages.schedule_templates'))

@push('styles')
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        /* Mac OS Folder Tree Style */
        .folder-tree {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .folder-tree ul {
            list-style: none;
            padding-right: 20px;
            /* RTL padding */
            padding-left: 0;
            display: none;
            border-right: 1px dashed #e9ebec;
            margin-top: 5px;
        }

        .folder-tree li {
            margin: 5px 0;
            position: relative;
        }

        .folder-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            color: #495057;
            font-size: 14px;
            font-weight: 500;
        }

        .folder-item:hover {
            background-color: #f3f6f9;
        }

        .folder-item.active {
            background-color: #e8ebf3;
            color: #405189;
            font-weight: 600;
        }

        .folder-icon {
            font-size: 18px;
            color: #878a99;
            margin-left: 10px;
            transition: transform 0.2s;
        }

        .folder-item.open .folder-icon {
            color: #f7b84b;
        }

        .folder-item.open .folder-arrow {
            transform: rotate(90deg);
        }

        .folder-arrow {
            font-size: 14px;
            color: #adb5bd;
            margin-left: 5px;
            transition: transform 0.2s;
        }

        .group-item {
            display: flex;
            align-items: center;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            color: #6c757d;
            font-size: 13px;
        }

        .group-item:hover {
            background-color: #f8f9fa;
            color: #405189;
        }

        .group-item.active {
            background-color: rgba(64, 81, 137, 0.1);
            color: #405189;
            font-weight: 600;
        }

        .group-icon {
            font-size: 16px;
            margin-left: 8px;
            color: #0ab39c;
        }

        /* Schedule Timeline Grid */
        .timeline-container {
            overflow-x: auto;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e9ebec;
        }

        .timeline-row {
            display: flex;
            border-bottom: 1px solid #e9ebec;
            min-width: 2800px;
            /* Enhanced horizontal width for clearer reading */
        }

        .timeline-row.day-row {
            min-height: 130px;
            /* Give enough vertical space for the lecture card contents */
        }

        .timeline-row:last-child {
            border-bottom: none;
        }

        .timeline-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .timeline-day-label {
            width: 100px;
            flex-shrink: 0;
            padding: 15px 10px;
            background-color: #f3f6f9;
            border-left: 1px solid #e9ebec;
            text-align: center;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            position: sticky;
            right: 0;
            z-index: 2;
        }

        .timeline-hours {
            display: flex;
            flex-grow: 1;
            position: relative;
        }

        .timeline-hour-slot {
            flex: 1;
            border-left: 1px dashed #e9ebec;
            padding: 10px 5px;
            text-align: center;
            font-size: 13px;
            color: #878a99;
            min-width: 100px;
            position: relative;
            transition: background-color 0.2s;
        }

        .timeline-row.day-row .timeline-hour-slot:hover {
            background-color: rgba(64, 81, 137, 0.03);
        }

        .add-slot-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            background-color: #405189;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            padding: 0;
            font-size: 16px;
            cursor: pointer;
            z-index: 5;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .add-slot-btn:hover {
            background-color: #293866;
            transform: translate(-50%, -50%) scale(1.1);
        }

        .timeline-row.day-row .timeline-hour-slot:hover .add-slot-btn {
            display: block;
        }

        .timeline-hour-slot:last-child {
            border-left: none;
            /* Last one doesn't need border in RTL if at edge, actually we use left border for grid */
        }

        .lecture-card {
            position: absolute;
            top: 5px;
            bottom: 5px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-right: 4px solid #405189;
            /* Left in LTR, Right in RTL. we are RTL */
            border-radius: 6px;
            padding: 8px 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 10;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .lecture-card.inactive-card {
            background-color: #f8fafc;
            border-right-color: #94a3b8;
        }

        .lecture-card:hover {
            z-index: 11;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .lecture-title {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 6px;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
            /* Line height fixes cut-off bottoms */
            padding-bottom: 2px;
        }

        .lecture-meta {
            font-size: 12px;
            color: #475569;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lecture-meta i {
            margin-left: 6px;
            color: #64748b;
            font-size: 14px;
            flex-shrink: 0;
        }

        .lecture-actions {
            position: absolute;
            bottom: 2px;
            left: 2px;
            /* RTL */
            display: none;
        }

        .lecture-card:hover .lecture-actions {
            display: block;
        }

        .action-btn {
            padding: 2px;
            cursor: pointer;
            margin-right: 2px;
            color: #878a99;
            border: none;
            background: none;
        }

        .action-btn:hover {
            color: #405189;
        }

        .action-btn.delete:hover {
            color: #f06548;
        }

        /* Tabs for Week 1 / Week 2 */
        .nav-tabs-custom .nav-link {
            font-weight: 600;
            color: #495057;
            border-radius: 0;
            border: none;
            border-bottom: 2px solid transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #405189;
            border-bottom-color: #405189;
            background: transparent;
        }
    </style>
@endpush

@section('content')
    @include('modules.educational.shared.alerts')

    <div class="row">
        <!-- Sidebar: Programs and Groups (4/12) -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 pb-2 bg-light">
                    <h5 class="card-title mb-0 d-flex align-items-center fs-15">
                        <i class="ri-folder-open-fill text-warning me-2 fs-20"></i>
                        البرامج والمجموعات التدريبية
                    </h5>
                </div>
                <div class="card-body p-3" style="max-height: 80vh; overflow-y: auto;">
                    <ul class="folder-tree">
                        @forelse($programsList as $program)
                            <li>
                                <div class="folder-item dropdown-toggle-folder" onclick="toggleFolder(this)">
                                    <i class="ri-arrow-right-s-line folder-arrow"></i>
                                    <i class="ri-folder-fill folder-icon"></i>
                                    <span class="ms-2 flex-grow-1">{{ $program->name }}</span>
                                    <span
                                        class="badge bg-primary-subtle text-primary rounded-pill">{{ $program->groups->count() }}</span>
                                </div>
                                <ul class="groups-list">
                                    @forelse($program->groups as $group)
                                        <li>
                                            <div class="group-item"
                                                onclick="loadGroupSchedule({{ $group->id }}, '{{ addslashes($group->name) }}', this, {{ $program->id }})">
                                                <i class="ri-group-fill group-icon"></i>
                                                <span class="ms-2">{{ $group->name }}</span>
                                            </div>
                                        </li>
                                    @empty
                                        <li>
                                            <div class="text-muted fs-12 ms-4 mb-2">لا توجد مجموعات</div>
                                        </li>
                                    @endforelse
                                </ul>
                            </li>
                        @empty
                            <div class="text-muted text-center pt-4">لا توجد برامج تدريبية</div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content: Schedule Grid (8/12) -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar-xs me-3">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16 shadow-sm">
                                <i class="ri-calendar-event-line"></i>
                            </div>
                        </div>
                        <h5 class="card-title mb-0" id="selectedGroupName">الرجاء اختيار مجموعة لاستعراض الجدول</h5>
                    </div>
                    <div>
                        <a href="{{ route('educational.schedules.create') }}"
                            class="btn btn-primary add-btn shadow-sm btn-sm" id="addScheduleBtn" style="display: none;">
                            <i class="ri-add-line align-bottom me-1"></i>
                            إضافة محاضرة
                        </a>
                    </div>
                </div>

                <div class="card-body" id="scheduleViewArea" style="display: none;">
                    <!-- Week Toggle -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#week-all" role="tab"
                                onclick="filterRecurrence('all')">
                                <i class="ri-calendar-todo-fill me-1 align-bottom"></i> الكل
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#week-odd" role="tab"
                                onclick="filterRecurrence('odd')">
                                <i class="ri-calendar-2-line me-1 align-bottom"></i> أسبوع 1 (فردي)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#week-even" role="tab"
                                onclick="filterRecurrence('even')">
                                <i class="ri-calendar-check-line me-1 align-bottom"></i> أسبوع 2 (زوجي)
                            </a>
                        </li>
                    </ul>

                    <div class="position-relative">
                        <!-- Loading Overlay -->
                        <div id="loadingOverlay"
                            style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:99; justify-content:center; align-items:center;">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        @php
                            $daysMapping = [
                                6 => 'السبت',
                                0 => 'الأحد',
                                1 => 'الإثنين',
                                2 => 'الثلاثاء',
                                3 => 'الأربعاء',
                                4 => 'الخميس',
                                5 => 'الجمعة',
                            ];
                        @endphp

                        @foreach (['odd' => 'الأسبوع الأول (فردي)', 'even' => 'الأسبوع الثاني (زوجي)'] as $weekType => $weekLabel)
                            <div id="timeline-{{ $weekType }}"
                                class="mb-4 bg-white border border-light-subtle rounded shadow-sm">
                                <div
                                    class="bg-primary-subtle text-primary fw-bold p-2 text-center border-bottom border-primary-subtle rounded-top">
                                    {{ $weekLabel }}
                                </div>
                                <div class="timeline-container relative drag-scrollable"
                                    style="border: none; border-radius: 0; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                                    <!-- Header Row (Hours) -->
                                    <div class="timeline-row timeline-header">
                                        <div class="timeline-day-label bg-light border-0">اليوم / الساعة</div>
                                        <div class="timeline-hours">
                                            @for ($i = 0; $i < 24; $i++)
                                                <div class="timeline-hour-slot fw-bold">
                                                    {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00</div>
                                            @endfor
                                        </div>
                                    </div>

                                    <!-- Days Rows -->
                                    @foreach ($daysMapping as $dayNum => $dayName)
                                        <div class="timeline-row day-row" data-day="{{ $dayNum }}">
                                            <div class="timeline-day-label">{{ $dayName }}</div>
                                            <div class="timeline-hours day-track"
                                                id="track-day-{{ $dayNum }}-{{ $weekType }}">
                                                <!-- Background grid -->
                                                @for ($i = 0; $i < 24; $i++)
                                                    <div class="timeline-hour-slot"
                                                        data-hour="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:00"
                                                        data-day="{{ $dayNum }}" data-week="{{ $weekType }}">
                                                        <button class="add-slot-btn" title="إضافة محاضرة هنا"><i
                                                                class="ri-add-line"></i></button>
                                                    </div>
                                                @endfor
                                                <!-- Lecture cards will be injected here via JS -->
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Empty State -->
                <div class="card-body text-center pt-5 pb-5" id="emptyStateArea">
                    <img src="{{ asset('assets/images/illustrator-1.png') }}" alt="" class="mb-3" height="120"
                        style="opacity: 0.5;">
                    <h5 class="text-muted">الرجاء اختيار مجموعة من القائمة الجانبية</h5>
                    <p class="text-muted fs-13">سيتم عرض الجدول الخاص بالمجموعة هنا مع إمكانية التعديل والإضافة.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for deleting templates -->
    <form id="delete-template-form" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

@endsection

@push('scripts')
    <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        let currentTemplates = [];
        let currentFilter = 'all';

        // Toggle Folder Tree
        function toggleFolder(element) {
            const ul = element.nextElementSibling;
            const parent = element.parentElement;
            const icon = element.querySelector('.folder-icon');

            // Toggle open class
            element.classList.toggle('open');

            if (ul.style.display === 'block') {
                ul.style.display = 'none';
                icon.className = 'ri-folder-fill folder-icon';
            } else {
                ul.style.display = 'block';
                icon.className = 'ri-folder-open-fill folder-icon';
            }
        }

        // Load Group Schedule via AJAX
        function loadGroupSchedule(groupId, groupName, element, programId) {
            currentGroupId = groupId;
            currentProgramId = programId;
            // Update active state in sidebar
            document.querySelectorAll('.group-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');

            // Update UI
            document.getElementById('emptyStateArea').style.display = 'none';
            document.getElementById('scheduleViewArea').style.display = 'block';
            document.getElementById('addScheduleBtn').style.display = 'inline-block';
            document.getElementById('selectedGroupName').innerText = 'جدول مجموعة: ' + groupName;

            // Set create link group_id prefill (if applicable, can be handled in create method)
            const createBtn = document.getElementById('addScheduleBtn');
            createBtn.href = "{{ route('educational.schedules.create') }}?group_id=" + groupId;

            // Show loading
            document.getElementById('loadingOverlay').style.display = 'flex';

            // Fetch templates
            fetch(`{{ route('educational.schedules.index') }}?group_id=${groupId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    currentTemplates = data.templates;
                    renderSchedule();
                    document.getElementById('loadingOverlay').style.display = 'none';
                })
                .catch(error => {
                    console.error('Error fetching schedules:', error);
                    document.getElementById('loadingOverlay').style.display = 'none';
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'حدث خطأ أثناء تحميل الجدول'
                    });
                });
        }

        // Filter Recurrence (All, Odd, Even)
        function filterRecurrence(filterType) {
            currentFilter = filterType;
            if (filterType === 'all') {
                document.getElementById('timeline-odd').style.display = 'block';
                document.getElementById('timeline-even').style.display = 'block';
            } else if (filterType === 'odd') {
                document.getElementById('timeline-odd').style.display = 'block';
                document.getElementById('timeline-even').style.display = 'none';
            } else if (filterType === 'even') {
                document.getElementById('timeline-odd').style.display = 'none';
                document.getElementById('timeline-even').style.display = 'block';
            }
        }

        // Render Schedule inside the grid
        function renderSchedule() {
            // Clear existing tracks
            document.querySelectorAll('.day-track').forEach(track => {
                // Remove existing cards
                const cards = track.querySelectorAll('.lecture-card');
                cards.forEach(card => card.remove());
            });

            currentTemplates.forEach(template => {
                let targetWeeks = [];
                if (template.recurrence_type === 'weekly') {
                    targetWeeks = ['odd', 'even'];
                } else if (template.recurrence_type === 'biweekly_odd') {
                    targetWeeks = ['odd'];
                } else if (template.recurrence_type === 'biweekly_even') {
                    targetWeeks = ['even'];
                }

                targetWeeks.forEach(weekType => {
                    const dayTrack = document.getElementById(
                        `track-day-${template.day_of_week}-${weekType}`);
                    if (!dayTrack) return;

                    // Calculate position and width based on 24-hour scale (100% width = 24 hours)
                    // Assuming start_time is "09:00:00"
                    const startTimeParts = template.start_time.split(':');
                    const endTimeParts = template.end_time.split(':');

                    const startHour = parseInt(startTimeParts[0]);
                    const startMin = parseInt(startTimeParts[1]);
                    const endHour = parseInt(endTimeParts[0]);
                    const endMin = parseInt(endTimeParts[1]);

                    const startDecimal = startHour + (startMin / 60);
                    const endDecimal = endHour + (endMin / 60);
                    const duration = endDecimal - startDecimal;

                    // Calculate percentage based on 24 hours
                    // right side is 0% in RTL, but we position left/right correctly
                    const leftPercent = (startDecimal / 24) * 100;
                    const widthPercent = (duration / 24) * 100;

                    // Build Card
                    const card = document.createElement('div');
                    card.className = 'lecture-card';
                    // In RTL, standard positioning usually places 0% at the right.
                    // However, our timeline is likely acting from right-to-left naturally if the container is RTL.
                    // Let's use right positioning to match the RTL flow.
                    card.style.right = `${leftPercent}%`;
                    card.style.width = `calc(${widthPercent}% - 2px)`; // subtract a bit for gap

                    // Color based on session type or status
                    if (!template.is_active) {
                        card.classList.add('inactive-card');
                    }

                    const InstructorName = template.instructor_profile && template.instructor_profile.user ?
                        template.instructor_profile.user.full_name : '-';
                    const RoomName = template.room ? template.room.name : '-';
                    const SessionTypeName = template.session_type ? template.session_type.name : '-';

                    card.innerHTML = `
                        <div class="lecture-title" title="${template.subject || template.name || 'محاضرة'}">${template.subject || template.name || 'موعد'}</div>
                        <div class="lecture-meta"><i class="ri-time-line"></i> ${template.start_time.substring(0,5)} - ${template.end_time.substring(0,5)}</div>
                        <div class="lecture-meta"><i class="ri-book-open-line"></i> ${SessionTypeName}</div>
                        <div class="lecture-meta"><i class="ri-user-line"></i> ${InstructorName}</div>
                        <div class="lecture-meta"><i class="ri-map-pin-line"></i> ${RoomName}</div>
                        
                        <div class="lecture-actions">
                            <a href="/educational/schedules/${template.id}/edit" class="action-btn" title="تعديل"><i class="ri-pencil-fill"></i></a>
                            <button onclick="confirmDelete(${template.id})" class="action-btn delete" title="حذف"><i class="ri-delete-bin-5-fill"></i></button>
                        </div>
                    `;

                    dayTrack.appendChild(card);
                });
            });
        }

        // Delete Confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: 'تأكيد الحذف',
                html: '<span class="text-danger fw-bold">تحذير هام:</span><br> سيتم حذف هذا القالب و<strong class="text-danger">جميع المحاضرات</strong> التي تمت إضافتها بناءً عليه.<br>وسيتم إرسال طلب للحصول على موافقة الإدارة قبل التنفيذ النهائي.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f06548',
                cancelButtonColor: '#878a99',
                confirmButtonText: 'نعم، أرسل طلب الحذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-template-form');
                    form.action = `/educational/schedules/${id}`;
                    form.submit();
                }
            });
        }
        let currentGroupId = null;

        // Drag to scroll functionality
        document.addEventListener('DOMContentLoaded', () => {
            const sliders = document.querySelectorAll('.drag-scrollable');
            let isDown = false;
            let startX;
            let scrollLeft;
            let dragThreshold = false;

            // Handle add schedule button click from within the timeline
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.add-slot-btn');
                if (btn) {
                    if (!currentGroupId) {
                        Swal.fire('تنبيه', 'الرجاء اختيار مجموعة أولاً لتتمكن من إضافة محاضرة مباشرةً.',
                            'info');
                        return;
                    }
                    const slot = btn.closest('.timeline-hour-slot');
                    const hour = slot.getAttribute('data-hour');
                    const day = slot.getAttribute('data-day');
                    const week = slot.getAttribute('data-week');

                    let recurrenceType = '';
                    if (week === 'odd') recurrenceType = 'biweekly_odd';
                    else if (week === 'even') recurrenceType = 'biweekly_even';

                    // Navigate to creation form with pre-filled parameters
                    const url =
                        `{{ route('educational.schedules.create') }}?program_id=${currentProgramId}&group_id=${currentGroupId}&day_of_week=${day}&start_time=${hour}&recurrence_type=${recurrenceType}`;
                    window.location.href = url;
                }
            });

            sliders.forEach(slider => {
                // Prevent text selection inside scrollable areas
                slider.style.userSelect = 'none';
                slider.style.webkitUserSelect = 'none';

                slider.addEventListener('mousedown', (e) => {
                    isDown = true;
                    slider.classList.add('active');
                    startX = e.pageX - slider.offsetLeft;
                    scrollLeft = slider.scrollLeft;
                    slider.style.cursor = 'grabbing';
                });

                slider.addEventListener('mouseleave', () => {
                    isDown = false;
                    slider.classList.remove('active');
                    slider.style.cursor = 'grab';
                });

                slider.addEventListener('mouseup', () => {
                    isDown = false;
                    slider.classList.remove('active');
                    slider.style.cursor = 'grab';
                });

                slider.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - slider.offsetLeft;
                    const walk = (x - startX) * 1.5; // Scroll speed multiplier
                    slider.scrollLeft = scrollLeft - walk;
                });

                // Initial cursor
                slider.style.cursor = 'grab';
            });
        });
    </script>
@endpush
