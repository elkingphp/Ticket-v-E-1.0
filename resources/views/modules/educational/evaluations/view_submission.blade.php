@extends('core::layouts.master')
@section('title', 'تفاصيل التقييم')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm">
                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                        <i class="ri-survey-line"></i>
                    </div>
                </div>
                <div>
                    <h4 class="mb-0 fw-semibold">تفاصيل التقييم المُقدم</h4>
                    <p class="text-muted mb-0 small">
                        {{ $evaluation->form->title }} · 
                        <span class="badge bg-light text-dark border">{{ $evaluation->submitted_at->format('d/m/Y H:i') }}</span>
                    </p>
                </div>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-ghost-secondary btn-sm">
                <i class="ri-arrow-right-line me-1"></i> رجوع
            </a>
        </div>

        {{-- Logistics & Instructor Info --}}
        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3 fs-12">بيانات المحاضرة واللوجستيات</h6>
                        <div class="row g-3">
                            {{-- المجموعة --}}
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs flex-shrink-0">
                                        <div class="avatar-title bg-soft-primary text-primary rounded">
                                            <i class="ri-group-line"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">المجموعة</small>
                                        <span class="fw-medium">{{ $evaluation->lecture?->group?->name ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                            {{-- المقر / الدور / القاعة --}}
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs flex-shrink-0">
                                        <div class="avatar-title bg-soft-success text-success rounded">
                                            <i class="ri-map-pin-2-line"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">الموقع (المقر / الدور / القاعة)</small>
                                        <span class="fw-medium">
                                            @if($evaluation->lecture?->room)
                                                {{ $evaluation->lecture->room->floor?->building?->campus?->name ?? '—' }} / 
                                                {{ $evaluation->lecture->room->floor?->name ?? '—' }} / 
                                                {{ $evaluation->lecture->room->name }}
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- المحاضر --}}
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs flex-shrink-0">
                                        <div class="avatar-title bg-soft-info text-info rounded">
                                            <i class="ri-user-voice-line"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">المحاضر</small>
                                        <span class="fw-medium">{{ $evaluation->lecture?->instructorProfile?->user?->full_name ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- شركة التدريب --}}
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs flex-shrink-0">
                                        <div class="avatar-title bg-soft-warning text-warning rounded">
                                            <i class="ri-building-line"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">شركة التدريب (حسب التخصص)</small>
                                        @php
                                            $instructor = $evaluation->lecture?->instructorProfile;
                                            $companyName = '—';
                                            if ($instructor && $instructor->track_id) {
                                                // Load job profiles for companies if not loaded
                                                $match = $instructor->companies->first(function($c) use ($instructor) {
                                                    $jp = \Modules\Educational\Domain\Models\JobProfile::find($c->pivot->job_profile_id);
                                                    return $jp && $jp->track_id == $instructor->track_id;
                                                });
                                                if ($match) $companyName = $match->name;
                                            }
                                        @endphp
                                        <span class="fw-medium">{{ $companyName }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Answers --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom border-light pt-4 pb-3">
                <h6 class="fw-bold mb-0"><i class="ri-question-answer-line text-primary me-2"></i>الإجابات المقدمة</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-nowrap align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th style="width: 60%">السؤال</th>
                                <th class="text-center">الإجابة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluation->answers as $answer)
                            <tr>
                                <td class="text-wrap">
                                    <p class="mb-0 fw-medium">{{ $answer->question?->question_text ?? 'سؤال محذوف' }}</p>
                                </td>
                                <td class="text-center">
                                    @if($answer->answer_rating !== null)
                                        <div class="text-warning">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="ri-star-{{ $i <= $answer->answer_rating ? 'fill' : 'line' }} fs-16"></i>
                                            @endfor
                                            <span class="ms-1 text-dark fw-bold">({{ $answer->answer_rating }})</span>
                                        </div>
                                    @elseif($answer->question?->type === 'boolean')
                                        @if($answer->answer_value === '1')
                                            <span class="badge bg-success-subtle text-success fs-12 px-3 py-1 rounded-pill">نعم ✅</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger fs-12 px-3 py-1 rounded-pill">لا ❌</span>
                                        @endif
                                    @else
                                        <span class="text-muted">{{ $answer->answer_value ?? '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Overall comments --}}
        @if($evaluation->overall_comments)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom border-light pt-4 pb-3">
                <h6 class="fw-bold mb-0"><i class="ri-chat-1-line text-primary me-2"></i>ملاحظات عامة</h6>
            </div>
            <div class="card-body">
                <p class="text-muted bg-light p-3 rounded-3 mb-0" style="white-space: pre-wrap;">{{ $evaluation->overall_comments }}</p>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
