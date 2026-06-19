<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\Admin\StaffQrController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\SettingsStageController;
use App\Http\Controllers\Admin\InstallmentTypeController;
use App\Http\Controllers\Admin\StudentPromotionController;

Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Users Management
    Route::get('/staff-qr', [StaffQrController::class, 'index'])->name('users.qr');
    Route::resource('users', UserController::class);

    // Student Promotion System (يجب أن يكون قبل students resource)
    Route::prefix('students/promotion')->name('students.promotion.')->group(function () {
        // صفحة الترحيل الرئيسية
        Route::get('/', [StudentPromotionController::class, 'index'])->name('index');
        
        // API Routes للبيانات
        Route::get('/academic-years', [StudentPromotionController::class, 'getAcademicYears'])->name('academic-years');
        Route::get('/stages', [StudentPromotionController::class, 'getStages'])->name('stages');
        Route::get('/grades', [StudentPromotionController::class, 'getGrades'])->name('grades');
        Route::get('/classrooms', [StudentPromotionController::class, 'getClassrooms'])->name('classrooms');
        Route::get('/students', [StudentPromotionController::class, 'getStudents'])->name('students');
        
        // عمليات الترحيل
        Route::post('/preview', [StudentPromotionController::class, 'previewPromotion'])->name('preview');
        Route::post('/execute', [StudentPromotionController::class, 'promoteStudents'])->name('execute');
        
        // التقارير والإحصائيات
        Route::get('/stats', [StudentPromotionController::class, 'getPromotionStats'])->name('stats');
        Route::get('/history', [StudentPromotionController::class, 'getPromotionHistory'])->name('history');
    });

    // Student Export and Print Routes (يجب أن تكون قبل students resource)
    Route::get('students/export/excel', [StudentController::class, 'exportExcel'])
        ->name('students.export.excel');
    
    Route::get('students/export/contacts', [StudentController::class, 'exportContacts'])
        ->name('students.export.contacts');
    
    Route::get('students/print', [StudentController::class, 'printView'])
        ->name('students.print');

    // AJAX Routes للطلاب (يجب أن تكون قبل students resource)
    Route::get('/students/grades-by-stage/{stage}', [StudentController::class, 'getGradesByStage'])->name('students.grades-by-stage');
    Route::get('/students/classrooms-by-grade/{grade}', [StudentController::class, 'getClassroomsByGrade'])->name('students.classrooms-by-grade');

    // Students Management (يأتي بعد جميع routes المحددة)
    Route::resource('students', StudentController::class);
    
    // Stages Management Routes
    Route::get('/stages', [SettingsStageController::class, 'index'])->name('stages.index');
    Route::post('/stages/update', [SettingsStageController::class, 'update'])->name('stages.update');
    
    // AJAX Routes for Stages
    Route::get('/stages/data/{id}', [SettingsStageController::class, 'getStageData'])->name('stages.data');
    Route::delete('/stages/{id}', [SettingsStageController::class, 'deleteStage'])->name('stages.delete');
    Route::delete('/grades/{id}', [SettingsStageController::class, 'deleteGrade'])->name('grades.delete');
    Route::delete('/classrooms/{id}', [SettingsStageController::class, 'deleteClassroom'])->name('classrooms.delete');
    Route::get('/stages/statistics', [SettingsStageController::class, 'getStatistics'])->name('stages.statistics');
    
    // Validation Routes for Stages
    Route::post('/stages/validate-name', [SettingsStageController::class, 'validateStageName'])->name('stages.validate-name');
    Route::post('/grades/validate-name', [SettingsStageController::class, 'validateGradeName'])->name('grades.validate-name');
    Route::post('/classrooms/validate-name', [SettingsStageController::class, 'validateClassroomName'])->name('classrooms.validate-name');

    // Academic Years Management
    Route::resource('academic-years', AcademicYearController::class)->parameters([
        'academic-years' => 'academic_year'
    ])->names('academic_years');

    Route::post('academic-years/{academic_year}/set-current', [AcademicYearController::class,'setCurrent'])
        ->name('academic_years.set_current');

    // Fee Structure Management
    Route::get('fees-catalog', [FeeStructureController::class, 'index'])->name('fees.catalog.index');
    Route::get('fees-catalog/data', [FeeStructureController::class, 'data'])->name('fees.catalog.data');
    Route::post('fees-catalog/upsert', [FeeStructureController::class, 'upsert'])->name('fees.catalog.upsert');


    Route::resource('installments-types', InstallmentTypeController::class)
    ->parameters(['installments-types' => 'installmentType']);

Route::post('installments-types/{installmentType}/toggle', [InstallmentTypeController::class, 'toggleStatus'])
    ->name('installments-types.toggle');
});