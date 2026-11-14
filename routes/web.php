<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PoolController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ServiceReportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\VisitController;
use App\Services\VisitGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', function (Request $request) {
        $user = $request->user();

        // Technicians land on today's route; admins on the dashboard.
        if ($user->isTechnician() && ! $user->isAdmin()) {
            return redirect()->route('route.index');
        }

        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:complete visits')->prefix('route')->name('route.')->group(function () {
        Route::get('/', [RouteController::class, 'index'])->name('index');
        Route::get('/visits/{visit}', [RouteController::class, 'show'])->name('show');
        Route::post('/visits/{visit}/start', [RouteController::class, 'start'])->name('start');
        Route::post('/visits/{visit}', [RouteController::class, 'update'])->name('update');
        Route::post('/visits/{visit}/complete', [RouteController::class, 'complete'])->name('complete');
        Route::post('/visits/{visit}/photos', [RouteController::class, 'storePhoto'])->name('photos.store');
        Route::delete('/visits/{visit}/photos/{photo}', [RouteController::class, 'destroyPhoto'])->name('photos.destroy');
    });

    Route::middleware('permission:manage customers')->group(function () {
        Route::patch('/customers/{customer}/toggle', [CustomerController::class, 'toggle'])->name('customers.toggle');
        Route::resource('customers', CustomerController::class);
    });

    Route::middleware('permission:manage pools')->group(function () {
        Route::get('/customers/{customer}/pools/create', [PoolController::class, 'create'])->name('customers.pools.create');
        Route::post('/customers/{customer}/pools', [PoolController::class, 'store'])->name('customers.pools.store');
        Route::get('/pools/{pool}', [PoolController::class, 'show'])->name('pools.show');
        Route::get('/pools/{pool}/edit', [PoolController::class, 'edit'])->name('pools.edit');
        Route::put('/pools/{pool}', [PoolController::class, 'update'])->name('pools.update');
        Route::patch('/pools/{pool}/toggle', [PoolController::class, 'toggle'])->name('pools.toggle');
        Route::delete('/pools/{pool}', [PoolController::class, 'destroy'])->name('pools.destroy');
    });

    Route::middleware('permission:manage schedules')->group(function () {
        Route::get('/pools/{pool}/schedule', [ScheduleController::class, 'edit'])->name('pools.schedule.edit');
        Route::put('/pools/{pool}/schedule', [ScheduleController::class, 'update'])->name('pools.schedule.update');

        Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
        Route::post('/visits/generate', function (Request $request, VisitGenerator $generator) {
            $result = $generator->generateForCompany($request->user()->company);

            return back()->with('success', "Generated {$result['created']} new visit(s); {$result['skipped']} already existed.");
        })->name('visits.generate');
        Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
        Route::post('/visits/{visit}/reassign', [VisitController::class, 'reassign'])->name('visits.reassign');
        Route::post('/visits/{visit}/reschedule', [VisitController::class, 'reschedule'])->name('visits.reschedule');
        Route::post('/visits/{visit}/skip', [VisitController::class, 'skip'])->name('visits.skip');
    });

    Route::middleware('permission:view reports')->group(function () {
        Route::get('/visits/{visit}/report', [ServiceReportController::class, 'show'])->name('visits.report');
        Route::post('/visits/{visit}/report/resend', [ServiceReportController::class, 'resend'])->name('visits.report.resend');
        Route::get('/email-logs', [EmailLogController::class, 'index'])->name('email-logs.index');
    });

    Route::middleware('permission:manage invoices')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    });

    // Admins and technicians of the signed-in user's company
    Route::middleware('permission:manage team')->group(function () {
        Route::patch('/team/{member}/toggle', [TeamController::class, 'toggle'])->name('team.toggle');
        Route::resource('team', TeamController::class)->parameters(['team' => 'member'])->except('show');
    });

    Route::middleware('permission:manage company')->group(function () {
        Route::get('/company', [CompanySettingsController::class, 'edit'])->name('company.edit');
        Route::put('/company', [CompanySettingsController::class, 'update'])->name('company.update');
    });

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->middleware('permission:view activity log')->name('activity.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
