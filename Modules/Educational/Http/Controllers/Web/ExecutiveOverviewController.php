<?php

namespace Modules\Educational\Http\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Educational\Domain\Models\Lecture;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExecutiveOverviewController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════
    // INDEX
    // ═══════════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));
        $today = $request->input('filter_date', Carbon::today()->format('Y-m-d'));

        // ── KPIs ──────────────────────────────────────────────────────
        $attendanceStats = DB::select("
            SELECT
                COUNT(DISTINCT l.id)                                             AS total_lectures,
                COUNT(DISTINCT tp.id)                                            AS total_trainees_eligible,
                COUNT(a.id)                                                      AS total_attendance_records,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present')                 AS present_count,
                CASE 
                    WHEN COUNT(DISTINCT a.id) > 0 THEN 
                        (COUNT(DISTINCT tp.id) - (
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')
                        ))
                    ELSE 0 
                END AS absent_count,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late')                    AS late_count,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')                 AS excused_count
            FROM education.lectures l
            LEFT JOIN education.trainee_profiles tp ON tp.group_id = l.group_id AND tp.enrollment_status = 'active'
            LEFT JOIN education.attendances a ON a.lecture_id = l.id AND a.trainee_profile_id = tp.id
            WHERE DATE(l.starts_at) BETWEEN :date_from AND :date_to
        ", ['date_from' => $dateFrom, 'date_to' => $dateTo]);

        $kpi = $attendanceStats[0] ?? null;
        $presentCount = (int) ($kpi?->present_count ?? 0);
        $absentCount = (int) ($kpi?->absent_count ?? 0);
        $lateCount = (int) ($kpi?->late_count ?? 0);
        $excusedCount = (int) ($kpi?->excused_count ?? 0);
        $totalEligible = (int) ($kpi?->total_trainees_eligible ?? 0);
        $totalLectures = (int) ($kpi?->total_lectures ?? 0);

        $presentRate = $totalEligible > 0 ? round($presentCount / $totalEligible * 100, 1) : 0;
        $absentRate = $totalEligible > 0 ? round($absentCount / $totalEligible * 100, 1) : 0;
        $lateRate = $totalEligible > 0 ? round($lateCount / $totalEligible * 100, 1) : 0;
        $excusedRate = $totalEligible > 0 ? round($excusedCount / $totalEligible * 100, 1) : 0;

        // ── Trend ──────────────────────────────────────────────────────
        $trendData = DB::select("
            SELECT
                DATE(l.starts_at) AS day,
                COUNT(a.id) FILTER (WHERE a.status = 'present') AS present,
                COUNT(a.id) FILTER (WHERE a.status = 'absent')  AS absent,
                COUNT(a.id) FILTER (WHERE a.status = 'late')    AS late,
                COUNT(a.id) FILTER (WHERE a.status = 'excused') AS excused
            FROM education.lectures l
            LEFT JOIN education.attendances a ON a.lecture_id = l.id
            WHERE DATE(l.starts_at) BETWEEN :date_from AND :date_to
            GROUP BY DATE(l.starts_at)
            ORDER BY day
        ", ['date_from' => $dateFrom, 'date_to' => $dateTo]);

        // ── Today status ───────────────────────────────────────────────
        $todayStatuses = DB::select("
            SELECT
                COUNT(*) FILTER (WHERE status = 'scheduled')    AS scheduled,
                COUNT(*) FILTER (WHERE status = 'running'
                    OR (starts_at <= NOW() AND ends_at >= NOW()
                        AND status NOT IN ('cancelled','rescheduled'))) AS running,
                COUNT(*) FILTER (WHERE status = 'completed'
                    OR (ends_at < NOW() AND status NOT IN ('cancelled','rescheduled'))) AS completed,
                COUNT(*) FILTER (WHERE status = 'cancelled')    AS cancelled,
                COUNT(*) FILTER (WHERE status = 'rescheduled')  AS rescheduled,
                COUNT(*)                                         AS total
            FROM education.lectures
            WHERE DATE(starts_at) = :today
        ", ['today' => $today]);
        $todayStatus = $todayStatuses[0] ?? null;

        // ── Running NOW ────────────────────────────────────────────────
        $runningNow = Lecture::with(['program', 'group', 'room'])
            ->whereDate('starts_at', $today)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->whereNotIn('status', ['cancelled', 'rescheduled'])
            ->withCount('attendances')
            ->addSelect([
                'trainees_count' => \Modules\Educational\Domain\Models\TraineeProfile::selectRaw('count(*)')
                    ->whereColumn('group_id', 'education.lectures.group_id'),
            ])
            ->orderBy('starts_at')
            ->get();

        // ── Today detail ───────────────────────────────────────────────
        $todayLecturesDetail = DB::select("
            SELECT
                l.id,
                p.name                                                      AS program_name,
                g.name                                                      AS group_name,
                r.name                                                      AS room_name,
                b.name                                                      AS building_name,
                fl.name                                                     AS floor_name,
                st.name                                                     AS session_type,
                l.starts_at,
                l.ends_at,
                l.status,
                COUNT(DISTINCT tp.id)                                       AS total_trainees,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present')            AS present,
                CASE 
                    WHEN COUNT(DISTINCT a.id) > 0 THEN 
                        (COUNT(DISTINCT tp.id) - (
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')
                        ))
                    ELSE 0 
                END AS absent,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late')               AS late,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')            AS excused,
                COUNT(DISTINCT lfa.id)                                              AS evaluations_assigned,
                COUNT(DISTINCT le.id)                                               AS evaluations_submitted
            FROM education.lectures l
            LEFT JOIN education.programs p           ON p.id = l.program_id
            LEFT JOIN education.groups g             ON g.id = l.group_id
            LEFT JOIN education.rooms r              ON r.id = l.room_id
            LEFT JOIN education.floors fl            ON fl.id = r.floor_id
            LEFT JOIN education.buildings b          ON b.id = fl.building_id
            LEFT JOIN education.session_types st     ON st.id = l.session_type_id
            LEFT JOIN education.trainee_profiles tp  ON tp.group_id = l.group_id AND tp.enrollment_status = 'active'
            LEFT JOIN education.attendances a        ON a.lecture_id = l.id AND a.trainee_profile_id = tp.id
            LEFT JOIN education.lecture_form_assignments lfa
                                                     ON lfa.lecture_id = l.id AND lfa.is_active = true
            LEFT JOIN education.lecture_evaluations le ON le.lecture_id = l.id
            WHERE DATE(l.starts_at) = :today
            GROUP BY l.id, p.name, g.name, r.name, b.name, fl.name, st.name,
                     l.starts_at, l.ends_at, l.status
            ORDER BY r.name, l.starts_at
        ", ['today' => $today]);

        // ── Company breakdown ──────────────────────────────────────────
        $lecturesByCompany = DB::select("
            SELECT
                COALESCE(tc.name, 'غير محدد')                            AS company_name,
                COUNT(DISTINCT l.id)                                      AS lecture_count,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present')          AS present,
                CASE 
                    WHEN COUNT(DISTINCT a.id) > 0 THEN 
                        (COUNT(DISTINCT tp.id) - (
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')
                        ))
                    ELSE 0 
                END AS absent,
                COUNT(DISTINCT tp.id)                                     AS total_trainees
            FROM education.lectures l
            LEFT JOIN education.instructor_profiles ip            ON ip.id = l.instructor_profile_id
            LEFT JOIN education.instructor_company_assignments ica ON ica.instructor_profile_id = ip.id
            LEFT JOIN education.training_companies tc              ON tc.id = ica.company_id
            LEFT JOIN education.trainee_profiles tp                ON tp.group_id = l.group_id AND tp.enrollment_status = 'active'
            LEFT JOIN education.attendances a                      ON a.lecture_id = l.id AND a.trainee_profile_id = tp.id
            WHERE DATE(l.starts_at) = :today
            GROUP BY tc.name
            ORDER BY lecture_count DESC
        ", ['today' => $today]);

        // ── Session type breakdown ─────────────────────────────────────
        $lecturesBySessionType = DB::select("
            SELECT
                COALESCE(st.name, 'غير محدد')                            AS session_type,
                COUNT(DISTINCT l.id)                                      AS lecture_count,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present')          AS present,
                CASE 
                    WHEN COUNT(DISTINCT a.id) > 0 THEN 
                        (COUNT(DISTINCT tp.id) - (
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')
                        ))
                    ELSE 0 
                END AS absent,
                COUNT(DISTINCT tp.id)                                     AS total_trainees
            FROM education.lectures l
            LEFT JOIN education.session_types st    ON st.id = l.session_type_id
            LEFT JOIN education.trainee_profiles tp ON tp.group_id = l.group_id AND tp.enrollment_status = 'active'
            LEFT JOIN education.attendances a       ON a.lecture_id = l.id AND a.trainee_profile_id = tp.id
            WHERE DATE(l.starts_at) = :today
            GROUP BY st.name
            ORDER BY lecture_count DESC
        ", ['today' => $today]);

        // ── Top absent groups ──────────────────────────────────────────
        $topAbsentGroups = DB::select("
            SELECT
                g.name                                                    AS group_name,
                p.name                                                    AS program_name,
                COUNT(DISTINCT l.id)                                      AS lecture_count,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'absent')           AS absent_count,
                ROUND(
                    100.0 * COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'absent')
                    / NULLIF(COUNT(DISTINCT tp.id) * COUNT(DISTINCT l.id), 0), 1
                )                                                         AS absence_rate
            FROM education.lectures l
            JOIN  education.groups g              ON g.id = l.group_id
            JOIN  education.programs p            ON p.id = l.program_id
            LEFT JOIN education.trainee_profiles tp ON tp.group_id = l.group_id AND tp.enrollment_status = 'active'
            LEFT JOIN education.attendances a       ON a.lecture_id = l.id AND a.trainee_profile_id = tp.id
            WHERE DATE(l.starts_at) BETWEEN :date_from AND :date_to
            GROUP BY g.name, p.name
            HAVING COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'absent') > 0
            ORDER BY absence_rate DESC NULLS LAST
            LIMIT 10
        ", ['date_from' => $dateFrom, 'date_to' => $dateTo]);

        // ── Room utilization ───────────────────────────────────────────
        $roomUtilization = DB::select("
            SELECT
                r.name                            AS room_name,
                fl.name                           AS floor_name,
                b.name                            AS building_name,
                COUNT(DISTINCT l.id)              AS lectures_today,
                COALESCE(SUM(EXTRACT(EPOCH FROM (l.ends_at - l.starts_at))/3600), 0)
                    ::NUMERIC(5,1)                AS total_hours
            FROM education.rooms r
            LEFT JOIN education.floors fl    ON fl.id = r.floor_id
            LEFT JOIN education.buildings b  ON b.id = fl.building_id
            LEFT JOIN education.lectures l   ON l.room_id = r.id AND DATE(l.starts_at) = :today
            GROUP BY r.name, fl.name, b.name
            HAVING COUNT(DISTINCT l.id) > 0
            ORDER BY lectures_today DESC
            LIMIT 10
        ", ['today' => $today]);

        // ── Eval stats ─────────────────────────────────────────────────
        $evalStats = DB::select("
            SELECT
                COUNT(DISTINCT lfa.id)   AS assignments,
                COUNT(DISTINCT le.id)    AS submissions,
                ROUND(
                    100.0 * COUNT(DISTINCT le.id) / NULLIF(COUNT(DISTINCT lfa.id), 0), 1
                ) AS completion_rate
            FROM education.lectures l
            LEFT JOIN education.lecture_form_assignments lfa
                ON lfa.lecture_id = l.id AND lfa.is_active = true
            LEFT JOIN education.lecture_evaluations le ON le.lecture_id = l.id
            WHERE DATE(l.starts_at) BETWEEN :date_from AND :date_to
        ", ['date_from' => $dateFrom, 'date_to' => $dateTo]);
        $evalStat = $evalStats[0] ?? null;

        // ── Filter options (for view dropdowns) ───────────────────────
        $filterRooms = collect($todayLecturesDetail)->pluck('room_name')->unique()->filter()->sort()->values();
        $filterPrograms = collect($todayLecturesDetail)->pluck('program_name')->unique()->filter()->sort()->values();
        $filterSessionTypes = collect($todayLecturesDetail)->pluck('session_type')->unique()->filter()->sort()->values();
        $filterStatuses = collect($todayLecturesDetail)->pluck('status')->unique()->filter()->sort()->values();

        // ── Chart arrays ───────────────────────────────────────────────
        $trendLabels = collect($trendData)->pluck('day')->toArray();
        $trendPresent = collect($trendData)->pluck('present')->map(fn($v) => (int) $v)->toArray();
        $trendAbsent = collect($trendData)->pluck('absent')->map(fn($v) => (int) $v)->toArray();
        $trendLate = collect($trendData)->pluck('late')->map(fn($v) => (int) $v)->toArray();
        $trendExcused = collect($trendData)->pluck('excused')->map(fn($v) => (int) $v)->toArray();

        if ($request->ajax() && $request->has('fetch_lectures')) {
            $html = view('modules.educational.overview._lectures_list', compact('todayLecturesDetail'))->render();
            return response()->json([
                'html' => $html,
                'count' => count($todayLecturesDetail),
                'filters' => [
                    'rooms' => $filterRooms,
                    'programs' => $filterPrograms,
                    'sessionTypes' => $filterSessionTypes,
                    'statuses' => $filterStatuses
                ]
            ]);
        }

        return view('modules.educational.overview.index', compact(
            'dateFrom',
            'dateTo',
            'today',
            'totalLectures',
            'totalEligible',
            'presentCount',
            'absentCount',
            'lateCount',
            'excusedCount',
            'presentRate',
            'absentRate',
            'lateRate',
            'excusedRate',
            'trendLabels',
            'trendPresent',
            'trendAbsent',
            'trendLate',
            'trendExcused',
            'todayStatus',
            'runningNow',
            'todayLecturesDetail',
            'filterRooms',
            'filterPrograms',
            'filterSessionTypes',
            'filterStatuses',
            'lecturesByCompany',
            'lecturesBySessionType',
            'topAbsentGroups',
            'roomUtilization',
            'evalStat'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════
    // EXPORT  – تفاصيل محاضرات اليوم → Excel
    // ═══════════════════════════════════════════════════════════════════
    public function export(Request $request)
    {
        $today = $request->input('filter_date', Carbon::today()->format('Y-m-d'));
        $room = $request->input('room');
        $program = $request->input('program');
        $sessionType = $request->input('session_type');
        $status = $request->input('status');

        $params = ['today' => $today];
        $where = "WHERE DATE(l.starts_at) = :today";
        if ($room) {
            $where .= " AND r.name = :room";
            $params['room'] = $room;
        }
        if ($program) {
            $where .= " AND p.name = :program";
            $params['program'] = $program;
        }
        if ($sessionType) {
            $where .= " AND st.name = :session_type";
            $params['session_type'] = $sessionType;
        }
        if ($status) {
            $where .= " AND l.status = :status";
            $params['status'] = $status;
        }

        $rows = DB::select("
            SELECT
                l.id,
                p.name                                                      AS program_name,
                g.name                                                      AS group_name,
                r.name                                                      AS room_name,
                b.name                                                      AS building_name,
                fl.name                                                     AS floor_name,
                st.name                                                     AS session_type,
                l.starts_at,
                l.ends_at,
                l.status,
                COUNT(DISTINCT tp.id)                                       AS total_trainees,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present')            AS present,
                CASE 
                    WHEN COUNT(DISTINCT a.id) > 0 THEN 
                        (COUNT(DISTINCT tp.id) - (
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'present') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late') +
                            COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')
                        ))
                    ELSE 0 
                END AS absent,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'late')               AS late,
                COUNT(DISTINCT a.id) FILTER (WHERE a.status = 'excused')            AS excused,
                COUNT(DISTINCT lfa.id)                                              AS evaluations_assigned,
                COUNT(DISTINCT le.id)                                               AS evaluations_submitted
            FROM education.lectures l
            LEFT JOIN education.programs p           ON p.id = l.program_id
            LEFT JOIN education.groups g             ON g.id = l.group_id
            LEFT JOIN education.rooms r              ON r.id = l.room_id
            LEFT JOIN education.floors fl            ON fl.id = r.floor_id
            LEFT JOIN education.buildings b          ON b.id = fl.building_id
            LEFT JOIN education.session_types st     ON st.id = l.session_type_id
            LEFT JOIN education.trainee_profiles tp  ON tp.group_id = l.group_id AND tp.enrollment_status = 'active'
            LEFT JOIN education.attendances a        ON a.lecture_id = l.id AND a.trainee_profile_id = tp.id
            LEFT JOIN education.lecture_form_assignments lfa
                                                     ON lfa.lecture_id = l.id AND lfa.is_active = true
            LEFT JOIN education.lecture_evaluations le ON le.lecture_id = l.id
            $where
            GROUP BY l.id, p.name, g.name, r.name, b.name, fl.name, st.name,
                     l.starts_at, l.ends_at, l.status
            ORDER BY r.name, l.starts_at
        ", $params);

        // ── Spreadsheet ────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->setTitle('محاضرات اليوم');

        // Title row
        $titleDate = Carbon::parse($today)->translatedFormat('d F Y');
        $sheet->setCellValue('A1', "تقرير تفاصيل محاضرات اليوم — {$titleDate}");
        $sheet->mergeCells('A1:R1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '405189']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Filter info row
        $filterParts = array_filter([
            $room ? "القاعة: {$room}" : '',
            $program ? "البرنامج: {$program}" : '',
            $sessionType ? "النوع: {$sessionType}" : '',
            $status ? "الحالة: {$status}" : '',
        ]);
        $filterInfo = $filterParts ? implode(' | ', $filterParts) : 'بدون فلتر';
        $sheet->setCellValue('A2', "الفلاتر المطبّقة: {$filterInfo}");
        $sheet->mergeCells('A2:R2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '444444']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EEF1FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Headers
        $headers = [
            'A' => '#',
            'B' => 'البرنامج',
            'C' => 'المجموعة',
            'D' => 'القاعة',
            'E' => 'المبنى',
            'F' => 'الطابق',
            'G' => 'نوع الجلسة',
            'H' => 'وقت البداية',
            'I' => 'وقت النهاية',
            'J' => 'الإجمالي',
            'K' => 'حضور',
            'L' => 'غياب',
            'M' => 'تأخير',
            'N' => 'أعذار',
            'O' => 'نسبة الحضور %',
            'P' => 'تقييمات مُعيَّنة',
            'Q' => 'تقييمات مُرسَلة',
            'R' => 'الحالة',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}3", $label);
        }
        $sheet->getStyle('A3:R3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '0AB39C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // Data rows
        $statusMap = [
            'scheduled' => 'مجدول',
            'running' => 'جارٍ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
            'rescheduled' => 'مؤجل',
        ];
        foreach ($rows as $i => $row) {
            $r = $i + 4;
            $attendRate = (int) $row->total_trainees > 0
                ? round((int) $row->present / (int) $row->total_trainees * 100, 1)
                : 0;
            $bg = ($i % 2 === 0) ? 'FFFFFF' : 'F8F9FF';

            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $row->program_name ?? '—');
            $sheet->setCellValue("C{$r}", $row->group_name ?? '—');
            $sheet->setCellValue("D{$r}", $row->room_name ?? '—');
            $sheet->setCellValue("E{$r}", $row->building_name ?? '—');
            $sheet->setCellValue("F{$r}", $row->floor_name ?? '—');
            $sheet->setCellValue("G{$r}", $row->session_type ?? '—');
            $sheet->setCellValue("H{$r}", Carbon::parse($row->starts_at)->format('H:i'));
            $sheet->setCellValue("I{$r}", Carbon::parse($row->ends_at)->format('H:i'));
            $sheet->setCellValue("J{$r}", (int) $row->total_trainees);
            $sheet->setCellValue("K{$r}", (int) $row->present);
            $sheet->setCellValue("L{$r}", (int) $row->absent);
            $sheet->setCellValue("M{$r}", (int) $row->late);
            $sheet->setCellValue("N{$r}", (int) $row->excused);
            $sheet->setCellValue("O{$r}", $attendRate . '%');
            $sheet->setCellValue("P{$r}", (int) $row->evaluations_assigned);
            $sheet->setCellValue("Q{$r}", (int) $row->evaluations_submitted);
            $sheet->setCellValue("R{$r}", $statusMap[$row->status] ?? $row->status);

            $sheet->getStyle("A{$r}:R{$r}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                'font' => ['size' => 10],
            ]);

            // colour-code attendance rate cell
            $rateColor = $attendRate >= 80 ? '0AB39C' : ($attendRate >= 50 ? 'D97706' : 'DC2626');
            $sheet->getStyle("O{$r}")->getFont()->getColor()->setRGB($rateColor);
        }

        // Summary row
        $lastRow = count($rows) + 4;
        $sumTrainees = collect($rows)->sum(fn($r) => (int) $r->total_trainees);
        $sumPresent = collect($rows)->sum(fn($r) => (int) $r->present);
        $sumAbsent = collect($rows)->sum(fn($r) => (int) $r->absent);
        $sumLate = collect($rows)->sum(fn($r) => (int) $r->late);
        $sumExcused = collect($rows)->sum(fn($r) => (int) $r->excused);
        $avgRate = $sumTrainees > 0 ? round($sumPresent / $sumTrainees * 100, 1) : 0;

        $sheet->setCellValue("A{$lastRow}", 'الإجمالي');
        $sheet->mergeCells("A{$lastRow}:I{$lastRow}");
        $sheet->setCellValue("J{$lastRow}", $sumTrainees);
        $sheet->setCellValue("K{$lastRow}", $sumPresent);
        $sheet->setCellValue("L{$lastRow}", $sumAbsent);
        $sheet->setCellValue("M{$lastRow}", $sumLate);
        $sheet->setCellValue("N{$lastRow}", $sumExcused);
        $sheet->setCellValue("O{$lastRow}", $avgRate . '%');
        $sheet->getStyle("A{$lastRow}:R{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '405189']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Column widths
        $widths = [
            'A' => 5,
            'B' => 28,
            'C' => 20,
            'D' => 18,
            'E' => 16,
            'F' => 12,
            'G' => 16,
            'H' => 12,
            'I' => 12,
            'J' => 10,
            'K' => 8,
            'L' => 8,
            'M' => 8,
            'N' => 8,
            'O' => 14,
            'P' => 16,
            'Q' => 16,
            'R' => 12,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Download
        $filename = 'محاضرات_اليوم_' . $today . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($filename) . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
