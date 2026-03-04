@php
    $byRoom = collect($todayLecturesDetail)->groupBy('room_name');
@endphp

@forelse($byRoom as $roomName => $lectures)
    <div class="border-bottom room-group" data-room="{{ $roomName }}">
        {{-- Room header --}}
        <div class="px-4 py-2 d-flex align-items-center gap-2 table-room-header">
            <i class="ri-map-pin-2-fill text-primary"></i>
            <span class="fw-bold fs-13">{{ $roomName ?? 'قاعة غير محددة' }}</span>
            <span class="badge bg-primary-subtle text-primary rounded-pill ms-1 fs-11 room-count">
                {{ $lectures->count() }} محاضرات
            </span>
        </div>
        {{-- Lectures table --}}
        <div class="table-responsive">
            <table class="table table-borderless table-centered align-middle mb-0"
                style="font-size:12px">
                <thead class="text-muted" style="font-size:11px;background:#fafbfc">
                    <tr>
                        <th class="ps-4 fw-semibold">البرنامج / المجموعة</th>
                        <th class="fw-semibold">الوقت</th>
                        <th class="fw-semibold">النوع</th>
                        <th class="fw-semibold text-center">إجمالي</th>
                        <th class="fw-semibold text-success text-center">حضور</th>
                        <th class="fw-semibold text-danger text-center">غياب</th>
                        <th class="fw-semibold text-warning text-center">تأخير</th>
                        <th class="fw-semibold text-info text-center">أعذار</th>
                        <th class="fw-semibold text-center" style="min-width:110px">نسبة الحضور
                        </th>
                        <th class="fw-semibold text-center">تقييمات</th>
                        <th class="fw-semibold text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lectures as $row)
                        @php
                            $ar =
                                $row->total_trainees > 0
                                    ? round(($row->present / $row->total_trainees) * 100)
                                    : 0;
                            $bc = $ar >= 80 ? 'success' : ($ar >= 50 ? 'warning' : 'danger');
                            [$sLbl, $sClr] = match ($row->status) {
                                'running' => ['جارٍ', 'primary'],
                                'completed' => ['مكتمل', 'success'],
                                'cancelled' => ['ملغى', 'danger'],
                                'rescheduled' => ['مؤجل', 'warning'],
                                default => ['مجدول', 'info'],
                            };
                            $ep =
                                $row->evaluations_assigned > 0
                                    ? round(
                                        ($row->evaluations_submitted /
                                            $row->evaluations_assigned) *
                                            100,
                                    )
                                    : 0;
                        @endphp
                        <tr class="lecture-row" data-room="{{ $row->room_name }}"
                            data-program="{{ $row->program_name }}"
                            data-session-type="{{ $row->session_type }}"
                            data-status="{{ $row->status }}">
                            <td class="ps-4">
                                <div class="fw-semibold" style="font-size:12px">
                                    {{ $row->program_name ?? '—' }}</div>
                                <div class="text-muted" style="font-size:11px">
                                    {{ $row->group_name ?? '—' }}</div>
                            </td>
                            <td style="white-space:nowrap">
                                <span class="badge bg-light text-dark border fw-semibold">
                                    {{ \Carbon\Carbon::parse($row->starts_at)->format('H:i') }}
                                </span>
                                <span class="text-muted mx-1">—</span>
                                <span class="text-muted"
                                    style="font-size:11px">{{ \Carbon\Carbon::parse($row->ends_at)->format('H:i') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill"
                                    style="font-size:10px">
                                    {{ $row->session_type ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center fw-semibold">{{ $row->total_trainees }}</td>
                            <td class="text-center fw-bold text-success">{{ $row->present }}</td>
                            <td class="text-center fw-bold text-danger">{{ $row->absent }}</td>
                            <td class="text-center fw-bold text-warning">{{ $row->late }}</td>
                            <td class="text-center fw-bold text-info">{{ $row->excused }}</td>
                            <td style="min-width:110px">
                                <div class="d-flex align-items-center gap-1">
                                    <div class="progress flex-grow-1 progress-sm">
                                        <div class="progress-bar bg-{{ $bc }}"
                                            style="width:{{ $ar }}%"></div>
                                    </div>
                                    <span class="fw-bold text-{{ $bc }}"
                                        style="font-size:11px;min-width:30px">{{ $ar }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($row->evaluations_assigned > 0)
                                    <span
                                        class="badge {{ $ep >= 80 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill fs-10">
                                        {{ $row->evaluations_submitted }}/{{ $row->evaluations_assigned }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:11px">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge bg-{{ $sClr }}-subtle text-{{ $sClr }} rounded-pill fs-10">{{ $sLbl }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="text-center py-5">
        <i class="ri-calendar-event-line fs-1 text-muted d-block mb-2"></i>
        <p class="text-muted fs-13">لا توجد محاضرات في هذا اليوم</p>
    </div>
@endforelse
