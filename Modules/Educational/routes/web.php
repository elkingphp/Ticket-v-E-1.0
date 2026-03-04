<?php

use Illuminate\Support\Facades\Route;
use Modules\Educational\Http\Controllers\Web\AttendanceController;
use Modules\Educational\Http\Controllers\Web\LectureController;
use Modules\Educational\Http\Controllers\Web\EvaluationController;
use Modules\Educational\Http\Controllers\Web\TrainingCompanyController;
use Modules\Educational\Http\Controllers\Web\InstructorController;
use Modules\Educational\Http\Controllers\Web\StudentController;
use Modules\Educational\Http\Controllers\Web\ProgramController;
use Modules\Educational\Http\Controllers\Web\GroupController;
use Modules\Educational\Http\Controllers\Web\CampusController;
use Modules\Educational\Http\Controllers\Web\BuildingController;
use Modules\Educational\Http\Controllers\Web\FloorController;
use Modules\Educational\Http\Controllers\Web\RoomController;
use Modules\Educational\Http\Controllers\Web\ScheduleTemplateController;
use Modules\Educational\Http\Controllers\Web\EducationalDashboardController;
use Modules\Educational\Http\Controllers\Web\TrackController;
use Modules\Educational\Http\Controllers\Web\JobProfileController;
use Modules\Educational\Http\Controllers\Web\GovernorateController;
use Modules\Educational\Http\Controllers\Web\SessionTypeController;
use Modules\Educational\Http\Controllers\Web\RoomTypeController;
use Modules\Educational\Http\Controllers\Web\LectureAssignmentController;

use Modules\Educational\Http\Controllers\Web\ExecutiveOverviewController;

Route::middleware(['auth', 'permission:educational.dashboard.view'])->prefix('educational')->name('educational.')->group(function () {

    Route::get('/', [EducationalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/overview', [ExecutiveOverviewController::class, 'index'])->name('overview');
    Route::get('/overview/export', [ExecutiveOverviewController::class, 'export'])->name('overview.export');



    // ─── Students ───
    Route::prefix('students')->name('students.')->group(function () {
        Route::middleware('permission:students.view')->group(function () {
            Route::get('', [StudentController::class, 'index'])->name('index');
            Route::get('export', [StudentController::class, 'export'])->name('export')->middleware('permission:students.export');
            Route::get('emergency-contacts/export', [StudentController::class, 'exportEmergencyContacts'])->name('emergency_contacts.export')->middleware('permission:students.export');
        });

        Route::middleware('permission:students.create')->group(function () {
            Route::get('create', [StudentController::class, 'create'])->name('create');
            Route::post('', [StudentController::class, 'store'])->name('store');
            Route::get('template', [StudentController::class, 'downloadTemplate'])->name('template')->middleware('permission:students.import');
            Route::post('import', [StudentController::class, 'import'])->name('import')->middleware('permission:students.import');
            Route::post('import/confirm', [StudentController::class, 'confirmImport'])->name('import.confirm')->middleware('permission:students.import');

            Route::get('emergency-contacts/template', [StudentController::class, 'downloadEmergencyContactsTemplate'])->name('emergency_contacts.template')->middleware('permission:students.import');
            Route::post('emergency-contacts/import', [StudentController::class, 'importEmergencyContacts'])->name('emergency_contacts.import')->middleware('permission:students.import');
        });

        Route::middleware('permission:students.view')->get('{student}', [StudentController::class, 'show'])->name('show');

        Route::middleware('permission:students.edit')->group(function () {
            Route::get('{student}/edit', [StudentController::class, 'edit'])->name('edit');
            Route::put('{student}', [StudentController::class, 'update'])->name('update');
        });

        Route::middleware('permission:students.delete')->delete('{student}', [StudentController::class, 'destroy'])->name('destroy');
        Route::post('{student}/ticket', [StudentController::class, 'storeTicket'])->name('ticket.store');
    });

    // ─── Instructors ───
    Route::prefix('instructors')->name('instructors.')->group(function () {
        Route::middleware('permission:instructors.view')->group(function () {
            Route::get('', [InstructorController::class, 'index'])->name('index');
            Route::get('export', [InstructorController::class, 'export'])->name('export')->middleware('permission:instructors.export');
        });

        Route::middleware('permission:instructors.create')->group(function () {
            Route::get('create', [InstructorController::class, 'create'])->name('create');
            Route::post('', [InstructorController::class, 'store'])->name('store');
            Route::get('template', [InstructorController::class, 'downloadTemplate'])->name('template')->middleware('permission:instructors.import');
            Route::post('import', [InstructorController::class, 'import'])->name('import')->middleware('permission:instructors.import');
        });

        Route::middleware('permission:instructors.view')->get('{instructor}', [InstructorController::class, 'show'])->name('show');

        Route::middleware('permission:instructors.edit')->group(function () {
            Route::get('{instructor}/edit', [InstructorController::class, 'edit'])->name('edit');
            Route::put('{instructor}', [InstructorController::class, 'update'])->name('update');
        });

        Route::middleware('permission:instructors.delete')->delete('{instructor}', [InstructorController::class, 'destroy'])->name('destroy');
    });

    // ─── Groups ───
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::middleware('permission:groups.view')->group(function () {
            Route::get('', [GroupController::class, 'index'])->name('index');
            Route::get('export', [GroupController::class, 'export'])->name('export')->middleware('permission:groups.export');
        });

        Route::middleware('permission:groups.create')->group(function () {
            Route::get('create', [GroupController::class, 'create'])->name('create');
            Route::post('', [GroupController::class, 'store'])->name('store');
            Route::get('template', [GroupController::class, 'downloadTemplate'])->name('template')->middleware('permission:groups.import');
            Route::post('import', [GroupController::class, 'import'])->name('import')->middleware('permission:groups.import');
        });

        Route::middleware('permission:groups.view')->get('{group}', [GroupController::class, 'show'])->name('show');

        Route::middleware('permission:groups.edit')->group(function () {
            Route::get('{group}/edit', [GroupController::class, 'edit'])->name('edit');
            Route::put('{group}', [GroupController::class, 'update'])->name('update');
        });

        Route::middleware('permission:groups.delete')->delete('{group}', [GroupController::class, 'destroy'])->name('destroy');
    });

    // ─── Lectures & Schedules ───
    Route::middleware('permission:lectures.view')->group(function () {
        Route::get('/lectures', [LectureController::class, 'index'])->name('lectures.index');
        Route::resource('schedules', ScheduleTemplateController::class);
    });

    Route::middleware('permission:lectures.create')->group(function () {
        Route::get('/lectures/create', [LectureController::class, 'create'])->name('lectures.create');
        Route::post('/lectures/generate', [LectureController::class, 'generate'])->name('lectures.generate');
        Route::post('/lectures/store-manual', [LectureController::class, 'storeManual'])->name('lectures.store_manual');
        Route::post('/lectures/{lecture}/assign-supervisor', [LectureController::class, 'assignSupervisor'])->name('lectures.assign_supervisor');
        Route::post('/lectures/batch-assign-supervisor', [LectureController::class, 'batchAssignSupervisor'])->name('lectures.batch_assign_supervisor');
    });

    Route::middleware('permission:lectures.edit')->patch('/lectures/{lecture}', [LectureController::class, 'update'])->name('lectures.update');
    Route::middleware('permission:lectures.edit')->patch('/lectures/{lecture}/details', [LectureController::class, 'updateDetails'])->name('lectures.update_details');
    Route::middleware('permission:lectures.delete')->delete('/lectures/{lecture}', [LectureController::class, 'destroy'])->name('lectures.destroy');

    Route::middleware('permission:lectures.manage')->group(function () {
        Route::get('/lectures/requests', [\Modules\Educational\Http\Controllers\Web\LectureApprovalController::class, 'index'])->name('requests.index');
        Route::post('/lectures/requests/{id}/approve', [\Modules\Educational\Http\Controllers\Web\LectureApprovalController::class, 'approve'])->name('requests.approve');
        Route::post('/lectures/requests/{id}/reject', [\Modules\Educational\Http\Controllers\Web\LectureApprovalController::class, 'reject'])->name('requests.reject');
    });

    // ─── Attendance ───
    Route::middleware('permission:attendance.manage')->group(function () {
        Route::get('/attendance/dashboard', [AttendanceController::class, 'dashboard'])->name('attendance.dashboard');
        Route::get('/attendance/{lecture_id}/mark', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::post('/attendance/{lecture_id}/mark', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/override', [AttendanceController::class, 'overrideList'])->name('attendance.override.list');
        Route::put('/attendance/override/{id}', [AttendanceController::class, 'requestOverride'])->name('attendance.override');
        Route::get('/attendance/{lecture_id}/absence-report', [AttendanceController::class, 'absenceReport'])->name('attendance.absence_report');
    });

    // ─── Academic Programs & Tracks ───
    Route::middleware('permission:programs.manage')->resource('programs', ProgramController::class);

    Route::middleware('permission:tracks.manage')->resource('tracks', TrackController::class);
    Route::middleware('permission:job_profiles.manage')->resource('job_profiles', JobProfileController::class);

    // ─── Infrastructure ───
    Route::middleware('permission:campus_structure.manage')->group(function () {
        Route::resource('campuses', CampusController::class);
        Route::resource('buildings', BuildingController::class);
        Route::resource('floors', FloorController::class);
        Route::resource('rooms', RoomController::class);
        Route::resource('room_types', RoomTypeController::class);
        Route::resource('governorates', GovernorateController::class);
    });

    // ─── Evaluations ───
    Route::middleware('permission:evaluations.manage')->prefix('evaluations')->name('evaluations.')->group(function () {
        Route::resource('forms', EvaluationController::class);
        Route::post('/forms/{form}/publish', [EvaluationController::class, 'publish'])->name('forms.publish');
        Route::post('/forms/{form}/archive', [EvaluationController::class, 'archive'])->name('forms.archive');
        Route::get('/forms/{form}/results', [EvaluationController::class, 'results'])->name('forms.results')->middleware('permission:evaluation_results.view');
        Route::get('/forms/{form}/export', [EvaluationController::class, 'export'])->name('forms.export')->middleware('permission:evaluation_results.view');

        Route::post('/forms/{form}/questions', [EvaluationController::class, 'storeQuestion'])->name('questions.store');
        Route::put('/questions/{question}', [EvaluationController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{question}', [EvaluationController::class, 'destroyQuestion'])->name('questions.destroy');
        Route::post('/forms/{form}/questions/reorder', [EvaluationController::class, 'reorderQuestions'])->name('questions.reorder');

        Route::post('/lectures/{lecture}/assign', [LectureAssignmentController::class, 'assign'])->name('lectures.assign');
        Route::delete('/assignments/{assignment}', [LectureAssignmentController::class, 'revoke'])->name('assignments.revoke');
        Route::get('/assignments/{assignment}/fill', [LectureAssignmentController::class, 'fill'])->name('assignments.fill');
        Route::post('/assignments/{assignment}/fill', [LectureAssignmentController::class, 'submit'])->name('assignments.submit');
        Route::get('/assignments/{assignment}/results', [LectureAssignmentController::class, 'viewAssignmentResults'])->name('assignments.results');
        Route::get('/submissions/{evaluation}', [LectureAssignmentController::class, 'showEvaluation'])->name('submissions.show');

        Route::get('/settings', [\Modules\Educational\Http\Controllers\Web\EvaluationSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\Modules\Educational\Http\Controllers\Web\EvaluationSettingsController::class, 'update'])->name('settings.update');
        Route::resource('types', \Modules\Educational\Http\Controllers\Web\EvaluationTypeController::class)->except(['show', 'create']);
    });

    Route::middleware('permission:settings.manage')->group(function () {
        Route::resource('companies', TrainingCompanyController::class);
        Route::resource('session_types', SessionTypeController::class);
    });

    // API
    Route::get('/api/programs/{program}/groups', [ScheduleTemplateController::class, 'getGroups'])->name('api.programs.groups');
    Route::get('/api/rooms/{room}/booked-slots', [LectureController::class, 'bookedSlots'])->name('api.rooms.booked_slots');
});
