<?php

use App\Http\Controllers\Admin\AdminAccessRequestController;
use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOwnerController;
use App\Http\Controllers\Admin\AdminProtectedDataController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Owner\OwnerAnnouncementController;
use App\Http\Controllers\Owner\OwnerBedController;
use App\Http\Controllers\Owner\OwnerBillingController;
use App\Http\Controllers\Owner\OwnerContractController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerExpenseController;
use App\Http\Controllers\Owner\OwnerMaintenanceController;
use App\Http\Controllers\Owner\OwnerPaymentController;
use App\Http\Controllers\Owner\OwnerPhotoController;
use App\Http\Controllers\Owner\OwnerPrivacyController;
use App\Http\Controllers\Owner\OwnerPropertyController;
use App\Http\Controllers\Owner\OwnerRentalRequestController;
use App\Http\Controllers\Owner\OwnerReportController;
use App\Http\Controllers\Owner\OwnerRoomController;
use App\Http\Controllers\Owner\OwnerTenantController;
use App\Http\Controllers\Public\HouseController;
use App\Http\Controllers\Tenant\TenantAnnouncementController;
use App\Http\Controllers\Tenant\TenantBillingController;
use App\Http\Controllers\Tenant\TenantContractController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Http\Controllers\Tenant\TenantMaintenanceController;
use App\Http\Controllers\Tenant\TenantProfileController;
use App\Http\Controllers\Tenant\TenantRentalRequestController;
use Illuminate\Support\Facades\Route;

// -----------------------------------------------------------------------------
// Public Marketplace
// -----------------------------------------------------------------------------

Route::get('/', fn () => redirect()->route('houses.index'))->name('welcome');

Route::get('/houses', [HouseController::class, 'index'])->name('houses.index');
Route::get('/houses/{property}', [HouseController::class, 'show'])->name('houses.show');
Route::get('/houses/{property}/rooms/{room}', [HouseController::class, 'room'])->name('houses.room');

// -----------------------------------------------------------------------------
// Authentication
// -----------------------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegisterTenant'])->name('register');
    Route::post('/register', [AuthController::class, 'registerTenant'])
        ->middleware('throttle:5,1')
        ->name('register.store');

    Route::get('/register/owner', [AuthController::class, 'showRegisterOwner'])->name('register.owner');
    Route::post('/register/owner', [AuthController::class, 'registerOwner'])
        ->middleware('throttle:5,1')
        ->name('register.owner.store');

    Route::get('/register/tenant', [AuthController::class, 'showRegisterTenant'])->name('register.tenant');
    Route::post('/register/tenant', [AuthController::class, 'registerTenant'])
        ->middleware('throttle:5,1')
        ->name('register.tenant.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -----------------------------------------------------------------------------
// Super Admin — platform-level management (NEVER owner private business data)
// -----------------------------------------------------------------------------

Route::prefix('admin')->name('admin.')->middleware('role:super_admin')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users (platform accounts)
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // House owners (platform account level only)
    Route::get('owners', [AdminOwnerController::class, 'index'])->name('owners.index');
    Route::get('owners/{owner}', [AdminOwnerController::class, 'show'])->name('owners.show');
    Route::get('owners/{owner}/edit', [AdminOwnerController::class, 'edit'])->name('owners.edit');
    Route::put('owners/{owner}', [AdminOwnerController::class, 'update'])->name('owners.update');

    // Privacy access requests (admin side)
    Route::get('access-requests', [AdminAccessRequestController::class, 'index'])->name('access-requests.index');
    Route::get('access-requests/create', [AdminAccessRequestController::class, 'create'])->name('access-requests.create');
    Route::post('access-requests', [AdminAccessRequestController::class, 'store'])->name('access-requests.store');
    Route::get('access-requests/{accessRequest}', [AdminAccessRequestController::class, 'show'])->name('access-requests.show');

    // Protected owner business data — READ ONLY, DENIED BY DEFAULT.
    // Each route requires an active approved access session holding the exact permission.
    Route::get('owners/{owner}/protected/properties', [AdminProtectedDataController::class, 'properties'])
        ->middleware('privacy:VIEW_PROPERTY')->name('protected.properties');
    Route::get('owners/{owner}/protected/rooms', [AdminProtectedDataController::class, 'rooms'])
        ->middleware('privacy:VIEW_ROOMS')->name('protected.rooms');
    Route::get('owners/{owner}/protected/tenants', [AdminProtectedDataController::class, 'tenants'])
        ->middleware('privacy:VIEW_TENANTS')->name('protected.tenants');
    Route::get('owners/{owner}/protected/contracts', [AdminProtectedDataController::class, 'contracts'])
        ->middleware('privacy:VIEW_CONTRACTS')->name('protected.contracts');
    Route::get('owners/{owner}/protected/billings', [AdminProtectedDataController::class, 'billings'])
        ->middleware('privacy:VIEW_BILLING')->name('protected.billings');
    Route::get('owners/{owner}/protected/payments', [AdminProtectedDataController::class, 'payments'])
        ->middleware('privacy:VIEW_PAYMENTS')->name('protected.payments');
    Route::get('owners/{owner}/protected/expenses', [AdminProtectedDataController::class, 'expenses'])
        ->middleware('privacy:VIEW_EXPENSES')->name('protected.expenses');

    // Security / activity
    Route::get('activity-logs', [AdminActivityController::class, 'index'])->name('activity-logs.index');

    // Backups
    Route::get('backups', [AdminBackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [AdminBackupController::class, 'store'])->name('backups.store');
    Route::get('backups/{filename}/download', [AdminBackupController::class, 'download'])
        ->where('filename', '[A-Za-z0-9\-\._]+')->name('backups.download');
    Route::post('backups/{filename}/restore', [AdminBackupController::class, 'restore'])
        ->where('filename', '[A-Za-z0-9\-\._]+')->name('backups.restore');
    Route::delete('backups/{filename}', [AdminBackupController::class, 'destroy'])
        ->where('filename', '[A-Za-z0-9\-\._]+')->name('backups.destroy');

    // Platform settings
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});
// -----------------------------------------------------------------------------
// House Owner — isolated business workspace
// -----------------------------------------------------------------------------

Route::prefix('owner')->name('owner.')->middleware('role:owner')->group(function () {
    Route::get('dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

    // Properties
    Route::get('properties', [OwnerPropertyController::class, 'index'])->name('properties.index');
    Route::get('properties/create', [OwnerPropertyController::class, 'create'])->name('properties.create');
    Route::post('properties', [OwnerPropertyController::class, 'store'])->name('properties.store');
    Route::get('properties/{property}', [OwnerPropertyController::class, 'show'])->name('properties.show');
    Route::get('properties/{property}/edit', [OwnerPropertyController::class, 'edit'])->name('properties.edit');
    Route::put('properties/{property}', [OwnerPropertyController::class, 'update'])->name('properties.update');
    Route::delete('properties/{property}', [OwnerPropertyController::class, 'destroy'])->name('properties.destroy');

    // Property Photos
    Route::post('properties/{property}/photos', [OwnerPhotoController::class, 'storePropertyPhoto'])->name('properties.photos.store');
    Route::patch('properties/{property}/photos/{photo}/cover', [OwnerPhotoController::class, 'setCover'])->name('properties.photos.cover');
    Route::delete('properties/{property}/photos/{photo}', [OwnerPhotoController::class, 'destroyPropertyPhoto'])->name('properties.photos.destroy');

    // Room Photo
    Route::post('rooms/{room}/photo', [OwnerPhotoController::class, 'storeRoomPhoto'])->name('rooms.photo.store');
    Route::delete('rooms/{room}/photo', [OwnerPhotoController::class, 'destroyRoomPhoto'])->name('rooms.photo.destroy');

    // Rooms
    Route::get('rooms', [OwnerRoomController::class, 'index'])->name('rooms.index');
    Route::get('rooms/create', [OwnerRoomController::class, 'create'])->name('rooms.create');
    Route::post('rooms', [OwnerRoomController::class, 'store'])->name('rooms.store');
    Route::get('rooms/{room}', [OwnerRoomController::class, 'show'])->name('rooms.show');
    Route::get('rooms/{room}/edit', [OwnerRoomController::class, 'edit'])->name('rooms.edit');
    Route::put('rooms/{room}', [OwnerRoomController::class, 'update'])->name('rooms.update');
    Route::delete('rooms/{room}', [OwnerRoomController::class, 'destroy'])->name('rooms.destroy');

    // Beds
    Route::post('rooms/{room}/beds', [OwnerBedController::class, 'store'])->name('rooms.beds.store');
    Route::post('beds/{bed}/photo', [OwnerBedController::class, 'storePhoto'])->name('beds.photo.store');
    Route::patch('beds/{bed}/status', [OwnerBedController::class, 'updateStatus'])->name('beds.update-status');
    Route::delete('beds/{bed}', [OwnerBedController::class, 'destroy'])->name('beds.destroy');

    // Tenants
    Route::get('tenants', [OwnerTenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/create', [OwnerTenantController::class, 'create'])->name('tenants.create');
    Route::post('tenants', [OwnerTenantController::class, 'store'])->name('tenants.store');
    Route::get('tenants/{tenant}', [OwnerTenantController::class, 'show'])->name('tenants.show');
    Route::get('tenants/{tenant}/edit', [OwnerTenantController::class, 'edit'])->name('tenants.edit');
    Route::put('tenants/{tenant}', [OwnerTenantController::class, 'update'])->name('tenants.update');
    Route::post('tenants/{tenant}/assign-bed', [OwnerTenantController::class, 'assignBed'])->name('tenants.assign-bed');
    Route::post('tenants/{tenant}/move-out', [OwnerTenantController::class, 'moveOut'])->name('tenants.move-out');
    Route::delete('tenants/{tenant}', [OwnerTenantController::class, 'destroy'])->name('tenants.destroy');

    // Contracts
    Route::get('contracts', [OwnerContractController::class, 'index'])->name('contracts.index');
    Route::get('contracts/create', [OwnerContractController::class, 'create'])->name('contracts.create');
    Route::post('contracts', [OwnerContractController::class, 'store'])->name('contracts.store');
    Route::get('contracts/{contract}', [OwnerContractController::class, 'show'])->name('contracts.show');
    Route::get('contracts/{contract}/edit', [OwnerContractController::class, 'edit'])->name('contracts.edit');
    Route::put('contracts/{contract}', [OwnerContractController::class, 'update'])->name('contracts.update');
    Route::delete('contracts/{contract}', [OwnerContractController::class, 'destroy'])->name('contracts.destroy');

    // Rent billing
    Route::get('billings', [OwnerBillingController::class, 'index'])->name('billings.index');
    Route::get('billings/create', [OwnerBillingController::class, 'create'])->name('billings.create');
    Route::post('billings/generate', [OwnerBillingController::class, 'generateBatch'])->name('billings.generate-batch');
    Route::post('billings', [OwnerBillingController::class, 'store'])->name('billings.store');
    Route::get('billings/{billing}', [OwnerBillingController::class, 'show'])->name('billings.show');
    Route::delete('billings/{billing}', [OwnerBillingController::class, 'destroy'])->name('billings.destroy');

    // Payments
    Route::get('payments', [OwnerPaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [OwnerPaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [OwnerPaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [OwnerPaymentController::class, 'show'])->name('payments.show');

    // Expenses
    Route::get('expenses', [OwnerExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [OwnerExpenseController::class, 'create'])->name('expenses.create');
    Route::post('expenses', [OwnerExpenseController::class, 'store'])->name('expenses.store');
    Route::get('expenses/{expense}/edit', [OwnerExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('expenses/{expense}', [OwnerExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/{expense}', [OwnerExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Maintenance
    Route::get('maintenance', [OwnerMaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('maintenance/{maintenance}', [OwnerMaintenanceController::class, 'show'])->name('maintenance.show');
    Route::patch('maintenance/{maintenance}/status', [OwnerMaintenanceController::class, 'updateStatus'])->name('maintenance.update-status');

    // Announcements
    Route::get('announcements', [OwnerAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/create', [OwnerAnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('announcements', [OwnerAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('announcements/{announcement}/edit', [OwnerAnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('announcements/{announcement}', [OwnerAnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('announcements/{announcement}', [OwnerAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Rental Requests (marketplace)
    Route::get('rental-requests', [OwnerRentalRequestController::class, 'index'])->name('rental-requests.index');
    Route::get('rental-requests/{rentalRequest}', [OwnerRentalRequestController::class, 'show'])->name('rental-requests.show');
    Route::post('rental-requests/{rentalRequest}/approve', [OwnerRentalRequestController::class, 'approve'])->name('rental-requests.approve');
    Route::post('rental-requests/{rentalRequest}/reject', [OwnerRentalRequestController::class, 'reject'])->name('rental-requests.reject');

    // Reports
    Route::get('reports', [OwnerReportController::class, 'index'])->name('reports.index');

    // Privacy & access control
    Route::get('privacy', [OwnerPrivacyController::class, 'index'])->name('privacy.index');
    Route::post('privacy/requests/{accessRequest}/respond', [OwnerPrivacyController::class, 'respond'])->name('privacy.respond');
    Route::post('privacy/sessions/{session}/revoke', [OwnerPrivacyController::class, 'revoke'])->name('privacy.revoke');
    Route::put('privacy/settings', [OwnerPrivacyController::class, 'updateSettings'])->name('privacy.settings');
});

// -----------------------------------------------------------------------------
// Tenant — own rental information only
// -----------------------------------------------------------------------------

Route::prefix('tenant')->name('tenant.')->middleware('role:tenant')->group(function () {
    Route::get('dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');

    // Rental Requests (marketplace flow)
    Route::get('rental-requests', [TenantRentalRequestController::class, 'index'])->name('rental-requests.index');
    Route::get('rental-requests/create', [TenantRentalRequestController::class, 'create'])->name('rental-requests.create');
    Route::post('rental-requests', [TenantRentalRequestController::class, 'store'])->name('rental-requests.store');
    Route::get('rental-requests/{rentalRequest}', [TenantRentalRequestController::class, 'show'])->name('rental-requests.show');
    Route::patch('rental-requests/{rentalRequest}/cancel', [TenantRentalRequestController::class, 'cancel'])->name('rental-requests.cancel');

    // Profile
    Route::get('profile', [TenantProfileController::class, 'show'])->name('profile');
    Route::put('profile/password', [TenantProfileController::class, 'updatePassword'])->name('profile.password');

    // Contract / property / room
    Route::get('contract', [TenantContractController::class, 'index'])->name('contract');
    Route::get('contracts/{contract}', [TenantContractController::class, 'show'])->name('contracts.show');

    // Rent & payments
    Route::get('billings', [TenantBillingController::class, 'index'])->name('billings');
    Route::get('billings/{billing}', [TenantBillingController::class, 'showBilling'])->name('billings.show-billing');
    Route::get('payments/{payment}', [TenantBillingController::class, 'showPayment'])->name('billings.show-payment');

    // Maintenance
    Route::get('maintenance', [TenantMaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('maintenance/create', [TenantMaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('maintenance', [TenantMaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('maintenance/{maintenance}', [TenantMaintenanceController::class, 'show'])->name('maintenance.show');

    // Announcements
    Route::get('announcements', [TenantAnnouncementController::class, 'index'])->name('announcements');
});
