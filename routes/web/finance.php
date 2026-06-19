<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\PayrollController;
use App\Http\Controllers\Finance\ReportsController;
use App\Http\Controllers\Finance\TreasuryController;
use App\Http\Controllers\Finance\DashboardController;
use App\Http\Controllers\Finance\TransactionController;
use App\Http\Controllers\Finance\StudentBillingController;
use App\Http\Controllers\Finance\EmployeeBalanceController;
use App\Http\Controllers\Finance\TreasuryTransferController;
use App\Http\Controllers\Finance\TeacherSettlementController;

Route::middleware(['role:finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // الخزائن المالية
    Route::resource('treasuries', TreasuryController::class)
        ->names('treasuries')
        ->middleware('permission:manage_financial_vaults');

    // المعاملات المالية
    Route::resource('transactions', TransactionController::class)
        ->names('transactions')
        ->middleware('permission:financial_transactions');

    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'printReceipt'])
        ->name('transactions.receipt')
        ->middleware('permission:financial_transactions');

    Route::get('/transactions-statement', [TransactionController::class, 'printStatement'])
        ->name('transactions.statement')
        ->middleware('permission:financial_transactions');

    // تحويلات الخزائن
    Route::resource('treasury-transfers', TreasuryTransferController::class)
        ->names('treasury-transfers')
        ->middleware('permission:vault_transfers');

    // الرواتب
    Route::prefix('payrolls')->name('payrolls.')->middleware('permission:salaries')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/form-data', [PayrollController::class, 'formData'])->name('form');
        Route::get('/attendance/{user}', [PayrollController::class, 'attendance'])->name('attendance');
        
        // إضافة الـ routes الجديدة
        Route::get('/employees', [PayrollController::class, 'getEmployees'])->name('employees');
        Route::post('/process', [PayrollController::class, 'processPayroll'])->name('process');
        
        Route::post('/', [PayrollController::class, 'store'])->name('store');
        Route::get('/list', [PayrollController::class, 'list'])->name('list');
        Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
    });

    // تسوية المعلمين
    Route::prefix('teacher-settlements')->name('teacher-settlements.')
        ->middleware('permission:teacher_settlements')->group(function () {
            Route::get('/', [TeacherSettlementController::class, 'index'])->name('index');
            Route::post('/', [TeacherSettlementController::class, 'store'])->name('store');
            Route::get('/list', [TeacherSettlementController::class, 'list'])->name('list');
            Route::get('/{teacherSettlement}', [TeacherSettlementController::class, 'show'])->name('show');
        });

    // أقساط الطلاب
    Route::get('students-billing', [StudentBillingController::class, 'index'])
        ->name('students.index')->middleware('permission:student_installments');

    Route::get('/students/print', [StudentBillingController::class, 'print'])
        ->name('students.print');

    Route::get('students-billing/{student}', [StudentBillingController::class, 'show'])
        ->name('students.show')
        ->middleware('permission:student_installments');

    Route::post('students/{student}/refund',[StudentBillingController::class,'refund'])->name('students.refund');
    
    Route::post('students/{student}/installments/{installment}/update', [StudentBillingController::class, 'updateInstallment'])
        ->name('students.installments.update');

    Route::delete('students-billing/{student}/installments/{installment}', [StudentBillingController::class, 'destroyInstallment'])
        ->name('students.installments.destroy')
        ->middleware('permission:student_installments');

    Route::post('students-billing/{student}/pay', [StudentBillingController::class, 'pay'])
        ->name('students.pay')
        ->middleware('permission:student_installments');
    
    Route::post('students-billing/{student}/installments', [StudentBillingController::class, 'addInstallment'])
        ->name('students.installments.store')
        ->middleware('permission:student_installments');

    // تقارير المالية
    Route::prefix('reports')->name('reports.')->group(function () {
        // الصفحة الرئيسية للتقارير
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        
        // كشف حساب خزينة
        Route::get('/treasury', [ReportsController::class, 'treasury'])->name('treasury');
        
        // كشف حساب موظف
        Route::get('/employee', [ReportsController::class, 'employee'])->name('employee');
        
        // تقرير تسوية المعلمين
        Route::get('/teachers', [ReportsController::class, 'teachers'])->name('teachers');
        
        // تقرير مدفوعات الطلاب
        Route::get('/students', [ReportsController::class, 'students'])->name('students');
    });

    // 🆕 أرصدة الموظفين والسلف
    Route::prefix('employee-balances')->name('employee-balances.')->group(function () {
        // صفحة أرصدة الموظفين الرئيسية
        Route::get('/', [EmployeeBalanceController::class, 'index'])->name('index');
        
        Route::get('/print', [EmployeeBalanceController::class, 'print'])->name('print');

        
        // تحديث رصيد موظف (إضافة/خصم)
        Route::post('/{user}/update-balance', [EmployeeBalanceController::class, 'updateBalance'])->name('update-balance');
        
        // 🆕 إدارة السلف - صفحة سلف الموظف
        Route::get('/{user}/advances', [EmployeeBalanceController::class, 'advances'])->name('advances');
        
        // 🆕 إضافة سلفة جديدة
        Route::post('/{user}/advances', [EmployeeBalanceController::class, 'storeAdvance'])->name('advances.store');
        
        // 🆕 تعديل سلفة (زيادة/تقليل/تعديل الاستقطاع)
        Route::put('/advances/{advance}', [EmployeeBalanceController::class, 'updateAdvance'])->name('advances.update');
        
        // 🆕 حذف سلفة
        Route::delete('/advances/{advance}', [EmployeeBalanceController::class, 'destroyAdvance'])->name('advances.destroy');
        
        // كشف حساب موظف
        Route::get('/{user}/statement', [EmployeeBalanceController::class, 'statement'])->name('statement');
    });
    
    // API Routes (اختياري، نفس الصلاحيات)
    Route::get('/api/transaction-types', [TransactionController::class, 'getTransactionTypes'])
        ->name('api.transaction-types')
        ->middleware('permission:financial_transactions');
    
    Route::post('/api/transaction-types', [TransactionController::class, 'storeTransactionType'])
        ->name('api.store-transaction-type')
        ->middleware('permission:financial_transactions');
    
    Route::get('/api/treasury-balance', [TreasuryTransferController::class, 'getTreasuryBalance'])
        ->name('api.treasury-balance')
        ->middleware('permission:vault_transfers');
});