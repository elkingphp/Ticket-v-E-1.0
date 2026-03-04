@extends('core::layouts.master')
@section('title', 'نتائج: ' . $form->title)

@push('styles')
    <style>
        .kpi-card {
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }

        .kpi-card .kpi-value {
            font-size: 2.4rem;
            font-weight: 700;
            line-height: 1;
        }

        .kpi-card .kpi-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 6px;
        }

        .completion-ring {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .red-flag-pulse {
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(240, 101, 72, .2);
            }

            50% {
                box-shadow: 0 0 0 3px rgba(240, 101, 72, 0);
            }
        }

        .chart-container {
            position: relative;
            height: 260px;
        }

        .dist-bar-fill {
            border-radius: 4px;
            transition: width .4s;
        }

        .pending-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">

            {{-- ─── Header ─────────────────────────────────────────────────────── --}}
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm">
                        <div class="avatar-title bg-info-subtle text-info rounded-circle fs-20">
                            <i class="ri-bar-chart-line"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-semibold">{{ $form->title }}</h4>
                        <p class="text-muted mb-0 small">
                            {{ $form->formType ? $form->formType->name : \Modules\Educational\Domain\Models\EvaluationForm::TYPES[$form->type] ?? $form->type }}
                            @if ($form->published_at)
                                · نُشر {{ $form->published_at->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @can('viewResults', $form)
                        <a href="{{ route('educational.evaluations.forms.export', $form) }}"
                            class="btn btn-success btn-sm shadow-sm">
                            <i class="ri-file-excel-2-line me-1"></i> تصدير Excel
                        </a>
                    @endcan
                    <a href="{{ route('educational.evaluations.forms.index') }}" class="btn btn-ghost-secondary btn-sm">
                        <i class="ri-arrow-right-line me-1"></i> رجوع
                    </a>
                </div>
            </div>

            {{-- ─── Snapshot Integrity Alert ───────────────────────────────────── --}}
            @if ($hasInconsistentSnapshots)
                <div
                    class="alert alert-warning border-warning border-1 rounded-3 mb-3 d-flex gap-2 align-items-center shadow-sm">
                    <i class="ri-history-line fs-18"></i>
                    <span class="small">تم تحديث النموذج بعد بدء التقييم. النتائج المعروضة هي "Snapshots" تعكس حالة النموذج
                        وقت الإرسال.</span>
                </div>
            @endif

            {{-- ─── Invalid Filter Warning ─────────────────────────────────────── --}}
            @if ($isInvalidLecture)
                <div
                    class="alert alert-danger border-danger border-1 rounded-3 mb-3 d-flex gap-2 align-items-center shadow-sm">
                    <i class="ri-error-warning-line fs-18"></i>
                    <span class="small">تنبيه: تم إدخال معرّف محاضرة غير صالح في الرابط. تم عرض النتائج العامة للنموذج
                        بدلاً من ذلك.</span>
                </div>
            @endif

            {{-- ─── Red Flag Alert ─────────────────────────────────────────────── --}}
            @if (count($redFlagQuestions) > 0)
                <div
                    class="alert alert-danger border-danger border-2 rounded-3 mb-4 red-flag-pulse d-flex gap-3 align-items-start shadow-sm">
                    <i class="ri-alarm-warning-fill fs-24 text-danger flex-shrink-0 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-danger">🚨 تحذير: أسئلة تستدعي مراجعة الإدارة</h6>
                        <p class="mb-2 small text-muted">المراقبون أعطوا تقييماً أقل من 3 على الأسئلة التالية:</p>
                        <ul class="mb-0 small">
                            @foreach ($redFlagQuestions as $rf)
                                <li>
                                    <strong>{{ $rf['question'] }}</strong>
                                    — متوسط المراقبين: <span class="badge bg-danger">{{ $rf['observer_avg'] }} / 5</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ─── Filters ─────────────────────────────────────────────────────── --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('educational.evaluations.forms.results', $form) }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">المحاضرة</label>
                                <select name="lecture_id" class="form-select form-select-sm">
                                    <option value="">كل المحاضرات</option>
                                    @foreach ($lectureOptions as $lid => $ldate)
                                        <option value="{{ $lid }}"
                                            {{ request('lecture_id') == $lid ? 'selected' : '' }}>
                                            {{ $ldate }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">من تاريخ</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">إلى تاريخ</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <i class="ri-filter-line me-1"></i>تطبيق على الإحصائيات
                                </button>
                                <a href="{{ route('educational.evaluations.forms.results', $form) }}"
                                    class="btn btn-light btn-sm" title="إعادة تعيين">
                                    <i class="ri-refresh-line"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ─── KPI Summary Cards ───────────────────────────────────────────── --}}
            <div class="row g-3 mb-4">

                {{-- Overall Avg --}}
                <div class="col-6 col-md-3">
                    <div class="kpi-card card border-0 shadow-sm h-100">
                        <div class="kpi-value text-warning">
                            {{ $overallAvg ?? '—' }}
                            @if ($overallAvg)
                                <span class="fs-18">⭐</span>
                            @endif
                        </div>
                        <div class="kpi-label">المتوسط العام</div>
                        @if ($overallAvg)
                            <div class="mt-2">
                                @for ($s = 1; $s <= 5; $s++)
                                    <i
                                        class="ri-star-{{ $s <= round($overallAvg) ? 'fill' : 'line' }} text-warning fs-14"></i>
                                @endfor
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Total Count --}}
                <div class="col-6 col-md-3">
                    <div class="kpi-card card border-0 shadow-sm h-100">
                        <div class="kpi-value text-primary">{{ $totalCount }}</div>
                        <div class="kpi-label">إجمالي التقييمات</div>
                        <div class="mt-2 d-flex justify-content-center gap-2">
                            <span class="badge bg-success-subtle text-success border fs-11">{{ $traineeCount }}
                                متدرب</span>
                            <span class="badge bg-info-subtle text-info border fs-11">{{ $observerCount }} مراقب</span>
                        </div>
                    </div>
                </div>

                {{-- Trainee Completion --}}
                <div class="col-6 col-md-3">
                    <div class="kpi-card card border-0 shadow-sm h-100">
                        <div class="kpi-value text-success">
                            {{ $traineeCompletionRate !== null ? $traineeCompletionRate . '%' : '—' }}
                        </div>
                        <div class="kpi-label">نسبة إتمام المتدربين</div>
                        @if ($traineeCompletionRate !== null)
                            <div class="progress mt-2" style="height:6px">
                                <div class="progress-bar bg-success" style="width:{{ $traineeCompletionRate }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Observer Completion --}}
                <div class="col-6 col-md-3">
                    <div class="kpi-card card border-0 shadow-sm h-100">
                        <div class="kpi-value text-info">
                            {{ $observerCompletionRate !== null ? $observerCompletionRate . '%' : '—' }}
                        </div>
                        <div class="kpi-label">نسبة إتمام المراقبين</div>
                        @if ($observerCompletionRate !== null)
                            <div class="progress mt-2" style="height:6px">
                                <div class="progress-bar bg-info" style="width:{{ $observerCompletionRate }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ─── Role Avg Comparison Mini Cards ────────────────────────────── --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                        <div class="avatar-md flex-shrink-0">
                            <div class="avatar-title bg-success-subtle text-success rounded-circle fs-22">
                                <i class="ri-graduation-cap-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small fw-medium">متوسط المتدربين</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <h2 class="fw-bold text-success mb-0">{{ $traineeAvg ?? '—' }}</h2>
                                @if ($traineeAvg)
                                    <small class="text-muted">/ 5</small>
                                @endif
                            </div>
                        </div>
                        @if ($traineeAvg)
                            <div class="text-end">
                                @for ($s = 1; $s <= 5; $s++)
                                    <i class="ri-star-{{ $s <= round($traineeAvg) ? 'fill' : 'line' }} text-success"></i>
                                @endfor
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center gap-3">
                        <div class="avatar-md flex-shrink-0">
                            <div class="avatar-title bg-info-subtle text-info rounded-circle fs-22">
                                <i class="ri-user-star-line"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small fw-medium">
                                متوسط المراقبين
                                @if ($observerAvg !== null && $observerAvg < 3)
                                    <span class="badge bg-danger ms-1"><i class="ri-alarm-warning-line"></i></span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <h2
                                    class="fw-bold {{ $observerAvg !== null && $observerAvg < 3 ? 'text-danger' : 'text-info' }} mb-0">
                                    {{ $observerAvg ?? '—' }}
                                </h2>
                                @if ($observerAvg)
                                    <small class="text-muted">/ 5</small>
                                @endif
                            </div>
                        </div>
                        @if ($observerAvg)
                            <div class="text-end">
                                @for ($s = 1; $s <= 5; $s++)
                                    <i class="ri-star-{{ $s <= round($observerAvg) ? 'fill' : 'line' }} text-info"></i>
                                @endfor
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($totalCount === 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center py-5">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-light text-muted rounded-circle fs-28"><i
                                    class="ri-search-line"></i></div>
                        </div>
                        @if (request()->filled('lecture_id') || request()->filled('date_from') || request()->filled('date_to'))
                            <h5>لا توجد نتائج تطابق الفلاتر المختارة</h5>
                            <p class="text-muted">البيانات التي تم إدخالها في الرابط أو الفلاتر لا تحتوي على أي تقييمات
                                مسجلة.</p>
                            <a href="{{ route('educational.evaluations.forms.results', $form->id) }}"
                                class="btn btn-primary btn-sm mt-3">تفريغ الفلاتر والعودة للكل</a>
                        @else
                            <h5>لا توجد نتائج بعد</h5>
                            <p class="text-muted">لم يتم إجراء أي تقييمات بهذا النموذج حتى الآن.</p>
                        @endif
                    </div>
                </div>
            @else
                {{-- ─── Charts Row ──────────────────────────────────────────────────── --}}
                <div class="row g-4 mb-4">

                    {{-- Trend Chart --}}
                    @if (count($trendLabels) > 0)
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                    <h6 class="fw-semibold mb-0">
                                        <i class="ri-line-chart-line text-primary me-2"></i>
                                        📈 الاتجاه الزمني للتقييم
                                    </h6>
                                    <small class="text-muted">متوسط التقييم عبر الزمن (المحور Y من 1 إلى 5)</small>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="trendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Role Comparison Bar --}}
                    @if (count($roleComparisonLabels) > 0)
                        <div class="col-lg-{{ count($trendLabels) > 0 ? '5' : '12' }}">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                    <h6 class="fw-semibold mb-0">
                                        <i class="ri-bar-chart-grouped-line text-secondary me-2"></i>
                                        📊 مقارنة: متدرب vs مراقب
                                    </h6>
                                    <small class="text-muted">لكل سؤال تقييمي</small>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="roleComparisonChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ─── Per-Question Breakdown ──────────────────────────────────────── --}}
                <div class="d-flex flex-column gap-4 mb-4">
                    @foreach ($questionStats as $idx => $stat)
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pt-3 pb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-light text-dark border me-2">{{ $idx + 1 }}</span>
                                        <span class="fw-semibold">{{ $stat['question']->question_text }}</span>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <small class="text-muted">{{ $stat['response_count'] }} إجابة</small>
                                        <span class="badge bg-light text-dark border rounded-pill fs-11">
                                            {{ \Modules\Educational\Domain\Models\EvaluationQuestion::TYPES[$stat['question']->type] }}
                                        </span>
                                        @if (
                                            $stat['question']->type === 'rating_1_to_5' &&
                                                isset($stat['observer_average']) &&
                                                $stat['observer_average'] !== null &&
                                                $stat['observer_average'] < 3)
                                            <span class="badge bg-danger">🚨 يستدعي مراجعة</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card-body pt-2">

                                @if ($stat['question']->type === 'rating_1_to_5' && $stat['response_count'] > 0)
                                    <div class="row g-4 align-items-center">
                                        {{-- Average + Stars --}}
                                        <div class="col-md-2 text-center">
                                            <h1 class="fw-bold text-warning mb-0">{{ $stat['rating_average'] }}</h1>
                                            <small class="text-muted">/ 5</small>
                                            <div class="mt-1">
                                                @for ($s = 1; $s <= 5; $s++)
                                                    <i
                                                        class="ri-star-{{ $s <= round($stat['rating_average']) ? 'fill' : 'line' }} text-warning fs-14"></i>
                                                @endfor
                                            </div>
                                        </div>

                                        {{-- Distribution Bars --}}
                                        <div class="col-md-4">
                                            @for ($r = 5; $r >= 1; $r--)
                                                @php
                                                    $cnt = $stat['rating_distribution'][$r] ?? 0;
                                                    $pct =
                                                        $stat['response_count'] > 0
                                                            ? round(($cnt / $stat['response_count']) * 100)
                                                            : 0;
                                                @endphp
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <small class="text-muted fw-medium"
                                                        style="width:14px">{{ $r }}</small>
                                                    <i class="ri-star-fill text-warning fs-11"></i>
                                                    <div class="progress flex-grow-1"
                                                        style="height:8px; border-radius:4px">
                                                        <div class="progress-bar bg-warning dist-bar-fill"
                                                            style="width:{{ $pct }}%"></div>
                                                    </div>
                                                    <small class="text-muted"
                                                        style="width:28px; font-size:11px">{{ $cnt }}</small>
                                                </div>
                                            @endfor
                                        </div>

                                        {{-- Distribution Mini Chart --}}
                                        <div class="col-md-3">
                                            <canvas id="distChart-{{ $idx }}" height="120"></canvas>
                                        </div>

                                        {{-- Role Comparison --}}
                                        <div class="col-md-3">
                                            <div class="d-flex flex-column gap-2">
                                                <div
                                                    class="d-flex justify-content-between align-items-center p-2 bg-success-subtle rounded-3">
                                                    <div><i class="ri-graduation-cap-line text-success me-1"></i><small
                                                            class="fw-medium">متدربون</small></div>
                                                    <span
                                                        class="fw-bold text-success">{{ $stat['trainee_average'] ?? '—' }}
                                                        @if ($stat['trainee_average'])
                                                            <small class="fw-normal">/5</small>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between align-items-center p-2 {{ isset($stat['observer_average']) && $stat['observer_average'] !== null && $stat['observer_average'] < 3 ? 'bg-danger-subtle' : 'bg-info-subtle' }} rounded-3">
                                                    <div><i
                                                            class="ri-user-star-line {{ isset($stat['observer_average']) && $stat['observer_average'] !== null && $stat['observer_average'] < 3 ? 'text-danger' : 'text-info' }} me-1"></i><small
                                                            class="fw-medium">مراقبون</small></div>
                                                    <span
                                                        class="fw-bold {{ isset($stat['observer_average']) && $stat['observer_average'] !== null && $stat['observer_average'] < 3 ? 'text-danger' : 'text-info' }}">{{ $stat['observer_average'] ?? '—' }}
                                                        @if ($stat['observer_average'])
                                                            <small class="fw-normal">/5</small>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($stat['question']->type === 'boolean' && $stat['response_count'] > 0)
                                    @php
                                        $total = $stat['yes_count'] + $stat['no_count'];
                                        $yesPct = $total > 0 ? round(($stat['yes_count'] / $total) * 100) : 0;
                                    @endphp
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-3 text-center p-3 bg-success-subtle rounded-3">
                                            <h3 class="fw-bold text-success mb-0">{{ $stat['yes_count'] }}</h3>
                                            <small class="text-muted">نعم ({{ $yesPct }}%)</small>
                                        </div>
                                        <div class="col-md-3 text-center p-3 bg-danger-subtle rounded-3">
                                            <h3 class="fw-bold text-danger mb-0">{{ $stat['no_count'] }}</h3>
                                            <small class="text-muted">لا ({{ 100 - $yesPct }}%)</small>
                                        </div>
                                        <div class="col-md-6 d-flex align-items-center">
                                            <div class="w-100">
                                                <div class="progress" style="height:18px; border-radius:9px">
                                                    <div class="progress-bar bg-success"
                                                        style="width:{{ $yesPct }}%">{{ $yesPct }}%</div>
                                                    <div class="progress-bar bg-danger"
                                                        style="width:{{ 100 - $yesPct }}%">{{ 100 - $yesPct }}%</div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <small class="text-success fw-medium">نعم ✅</small>
                                                    <small class="text-danger fw-medium">لا ❌</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($stat['question']->type === 'multiple_choice' && $stat['response_count'] > 0)
                                    @foreach ($stat['choice_distribution'] ?? [] as $choice => $count)
                                        @php $pct = $stat['response_count'] > 0 ? round($count/$stat['response_count']*100) : 0; @endphp
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <span style="min-width:160px; font-size:13px">{{ $choice }}</span>
                                            <div class="progress flex-grow-1" style="height:10px; border-radius:5px">
                                                <div class="progress-bar bg-primary dist-bar-fill"
                                                    style="width:{{ $pct }}%"></div>
                                            </div>
                                            <small class="text-muted" style="min-width:70px">{{ $count }}
                                                ({{ $pct }}%)
                                            </small>
                                        </div>
                                    @endforeach
                                @elseif($stat['question']->type === 'text' && !empty($stat['text_answers']))
                                    <div class="d-flex flex-column gap-2">
                                        <small class="text-muted fw-medium mb-1">آخر {{ count($stat['text_answers']) }}
                                            ردود:</small>
                                        @foreach ($stat['text_answers'] as $answer)
                                            <div class="p-3 bg-light rounded-3 text-muted small d-flex gap-2">
                                                <i class="ri-chat-quote-line text-primary flex-shrink-0 mt-1"></i>
                                                <span>{{ $answer }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted small fst-italic mb-0">لا توجد إجابات لهذا السؤال بعد.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ─── Pending Evaluations or Attendance Widget ───────────────────── --}}
                @if ($pendingTrainees->count() > 0)
                    <div class="card border-0 shadow-sm border-start border-4 border-warning mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex align-items-center gap-2">
                            <i class="ri-user-unfollow-line text-warning fs-20"></i>
                            <h6 class="fw-semibold mb-0">
                                👤 المتدربون الذين لم يُكملوا التقييم
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning ms-2">{{ $pendingTrainees->count() }}</span>
                            </h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-2">
                                @foreach ($pendingTrainees as $trainee)
                                    <div class="col-md-4 col-lg-3">
                                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3">
                                            <div class="pending-avatar">
                                                {{ \Str::upper(\Str::substr($trainee->user?->full_name ?? '?', 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-medium small text-truncate">
                                                    {{ $trainee->user?->full_name ?? 'غير معروف' }}</div>
                                                <div class="text-muted" style="font-size:11px">
                                                    {{ $trainee->user?->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif($lectureAttendance && $lectureAttendance->count() > 0)
                    <div class="card border-0 shadow-sm border-start border-4 border-info mb-4">
                        <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex align-items-center gap-2">
                            <i class="ri-group-line text-info fs-20"></i>
                            <h6 class="fw-semibold mb-0">
                                👤 سجل حضور الطلاب لهذه المحاضرة
                                <span
                                    class="badge bg-info-subtle text-info border border-info ms-2">{{ $lectureAttendance->count() }}</span>
                            </h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-2">
                                @foreach ($lectureAttendance as $att)
                                    <div class="col-md-4 col-lg-3">
                                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3">
                                            <div
                                                class="pending-avatar {{ $att->status === 'present' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                <i
                                                    class="ri-{{ $att->status === 'present' ? 'check' : 'close' }}-line"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-medium small text-truncate">
                                                    {{ $att->traineeProfile->user?->full_name ?? 'غير معروف' }}</div>
                                                <div
                                                    class="badge bg-{{ $att->status === 'present' ? 'success' : 'danger' }}-subtle text-{{ $att->status === 'present' ? 'success' : 'danger' }} fs-10">
                                                    {{ $att->status === 'present' ? 'حاضر' : 'غائب' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ─── Individual Submissions List ────────────────────────────────── --}}
                <div class="card border-0 shadow-sm mb-4" id="submissions-list-card">
                    <div
                        class="card-header bg-transparent border-bottom border-light pt-4 pb-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="ri-list-check text-primary me-2"></i>قائمة التقييمات المُستلمة
                        </h6>
                        <span class="badge bg-soft-primary text-primary" id="total-evals-badge">{{ $totalCount }}
                            إجمالي</span>
                    </div>
                    <div class="card-body p-0" id="table-wrapper">
                        @include('modules.educational.evaluations.partials.submissions_table')
                    </div>
                </div>

            @endif {{-- end if totalCount > 0 --}}

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        Chart.defaults.font.family = "'Segoe UI', Tahoma, sans-serif";
        Chart.defaults.color = '#64748b';

        // ── 1. Trend Line Chart ────────────────────────────────────────────────────
        @if (count($trendLabels) > 0)
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: @json($trendLabels),
                    datasets: [{
                        label: 'متوسط التقييم',
                        data: @json($trendData),
                        borderColor: '#405189',
                        backgroundColor: 'rgba(64,81,137,.1)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#405189',
                        pointRadius: 5,
                        fill: true,
                        tension: 0.35,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` متوسط: ${ctx.parsed.y} / 5`
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 1,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        @endif

        // ── 2. Role Comparison Bar Chart ──────────────────────────────────────────
        @if (count($roleComparisonLabels) > 0)
            new Chart(document.getElementById('roleComparisonChart'), {
                type: 'bar',
                data: {
                    labels: @json($roleComparisonLabels),
                    datasets: [{
                            label: 'المتدربون',
                            data: @json($roleComparisonTrainee),
                            backgroundColor: 'rgba(10,179,156,.75)',
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                        {
                            label: 'المراقبون',
                            data: @json($roleComparisonObserver),
                            backgroundColor: 'rgba(64,81,137,.75)',
                            borderRadius: 6,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} / 5`
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 15
                            }
                        }
                    }
                }
            });
        @endif

        // ── 3. Per-Question Distribution Doughnut Charts ──────────────────────────
        @foreach ($questionStats as $idx => $stat)
            @if ($stat['question']->type === 'rating_1_to_5' && $stat['response_count'] > 0)
                new Chart(document.getElementById('distChart-{{ $idx }}'), {
                    type: 'doughnut',
                    data: {
                        labels: ['1★', '2★', '3★', '4★', '5★'],
                        datasets: [{
                            data: @json($stat['dist_chart_data']),
                            backgroundColor: ['#f06548', '#f0a048', '#f0c847', '#0ab39c', '#405189'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        size: 10
                                    },
                                    padding: 8,
                                    boxWidth: 10
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.label}: ${ctx.raw} إجابة`
                                }
                            }
                        }
                    }
                });
            @endif
        @endforeach
        // ── 5. AJAX Table Functions (Robust Delegation) ──────────────────────
        const tableWrapper = document.getElementById('table-wrapper');

        if (tableWrapper) {
            function loadTable(url) {
                tableWrapper.style.opacity = '0.5';
                tableWrapper.style.pointerEvents = 'none';

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        tableWrapper.innerHTML = html;
                        tableWrapper.style.opacity = '1';
                        tableWrapper.style.pointerEvents = 'all';
                    })
                    .catch(err => {
                        console.error('AJAX Error:', err);
                        tableWrapper.style.opacity = '1';
                        tableWrapper.style.pointerEvents = 'all';
                    });
            }

            function getFilteredTableUrl(page = null) {
                const url = new URL(window.location.href);
                const search = document.getElementById('table-search')?.value;
                const role = document.getElementById('table-role-filter')?.value;

                if (search) url.searchParams.set('search', search);
                else url.searchParams.delete('search');
                if (role) url.searchParams.set('evaluator_role', role);
                else url.searchParams.delete('evaluator_role');
                if (page) url.searchParams.set('page', page);
                else url.searchParams.delete('page');

                return url.toString();
            }

            // Delegation
            tableWrapper.addEventListener('click', function(e) {
                const link = e.target.closest('.pagination-ajax a');
                if (link) {
                    e.preventDefault();
                    loadTable(link.href);
                }
                const refreshBtn = e.target.closest('#refresh-table');
                if (refreshBtn) loadTable(getFilteredTableUrl());
            });

            let searchDebounce;
            tableWrapper.addEventListener('input', function(e) {
                if (e.target.id === 'table-search') {
                    clearTimeout(searchDebounce);
                    searchDebounce = setTimeout(() => loadTable(getFilteredTableUrl()), 500);
                }
            });

            tableWrapper.addEventListener('change', function(e) {
                if (e.target.id === 'table-role-filter') loadTable(getFilteredTableUrl());
            });
        }
    </script>
@endpush
