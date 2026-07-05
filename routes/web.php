<?php

use App\Http\Controllers\ConsumableCategoryController;
use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\ConsumableStockController;
use App\Http\Controllers\ConsumableUsageController;
use App\Http\Controllers\DistributionRequestController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\InstrumentCategoryController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\InstrumentItemController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SterilizationBatchController;
use App\Http\Controllers\SterilizeController;
use App\Http\Controllers\StorageRackController;
use App\Http\Controllers\TrayController;
use App\Http\Controllers\TrayReturnController;
use App\Http\Controllers\TrayTemplateController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/switch-hospital/{hospital}', function (\App\Models\Hospital $hospital) {
        $isMember = auth()->user()->hospitals()
                          ->where('hospitals.id', $hospital->id)
                          ->where('hospital_users.is_active', true)
                          ->exists();
        if ($isMember) {
            session(['active_hospital_id' => $hospital->id]);
        }
        return redirect()->back();
    })->name('switch-hospital');

    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->middleware('permission:dashboard.view')
         ->name('dashboard');

    Route::middleware(['permission:hospitals.view'])->group(function () {

        Route::get('hospitals', [HospitalController::class, 'index'])
            ->name('hospitals.index');

        Route::get('hospitals/create', [HospitalController::class, 'create'])
            ->name('hospitals.create');

        Route::post('hospitals', [HospitalController::class, 'store'])
            ->name('hospitals.store');

        // Sub-routes dengan segment tambahan HARUS di atas {hospital}
        Route::get('hospitals/{hospital}/edit', [HospitalController::class, 'edit'])
            ->name('hospitals.edit');

        Route::patch('hospitals/{hospital}/toggle-active', [HospitalController::class, 'toggleActive'])
            ->name('hospitals.toggle-active');

        Route::patch('hospitals/{id}/restore', [HospitalController::class, 'restore'])
            ->name('hospitals.restore');

        // PUT/DELETE {hospital} — tidak konflik karena beda method
        Route::put('hospitals/{hospital}', [HospitalController::class, 'update'])
            ->name('hospitals.update');

        Route::delete('hospitals/{hospital}', [HospitalController::class, 'destroy'])
            ->name('hospitals.destroy');

        // Show PALING BAWAH
        Route::get('hospitals/{hospital}', [HospitalController::class, 'show'])
            ->name('hospitals.show');

    });

    // Users
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('users', [UserController::class, 'index'])
             ->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])
             ->name('users.create');
        Route::post('users', [UserController::class, 'store'])
             ->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])
             ->name('users.edit');
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
             ->name('users.toggle-active');
        Route::patch('users/{id}/restore', [UserController::class, 'restore'])
             ->name('users.restore');
        Route::put('users/{user}', [UserController::class, 'update'])
             ->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])
             ->name('users.destroy');
        Route::get('users/{user}', [UserController::class, 'show'])
             ->name('users.show');
    });

    // Roles
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::get('roles', [RoleController::class, 'index'])
             ->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])
             ->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])
             ->name('roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
             ->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])
             ->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])
             ->name('roles.destroy');
        Route::get('roles/{role}', [RoleController::class, 'show'])
             ->name('roles.show');
    });

    // Units
    Route::middleware(['permission:units.view'])->group(function () {
        Route::get('units', [UnitController::class, 'index'])
            ->name('units.index');
        Route::get('units/create', [UnitController::class, 'create'])
            ->name('units.create');
        Route::post('units', [UnitController::class, 'store'])
            ->name('units.store');
        Route::get('units/{unit}/edit', [UnitController::class, 'edit'])
            ->name('units.edit');
        Route::patch('units/{unit}/toggle-active', [UnitController::class, 'toggleActive'])
            ->name('units.toggle-active');
        Route::patch('units/{id}/restore', [UnitController::class, 'restore'])
            ->name('units.restore');
        Route::put('units/{unit}', [UnitController::class, 'update'])
            ->name('units.update');
        Route::delete('units/{unit}', [UnitController::class, 'destroy'])
            ->name('units.destroy');
        Route::get('units/{unit}', [UnitController::class, 'show'])
            ->name('units.show');
    });

    // Instrument Categories
    Route::middleware(['permission:instrument-categories.view'])->group(function () {
        Route::get('instrument-categories', [InstrumentCategoryController::class, 'index'])
            ->name('instrument-categories.index');
        Route::get('instrument-categories/create', [InstrumentCategoryController::class, 'create'])
            ->name('instrument-categories.create');
        Route::post('instrument-categories', [InstrumentCategoryController::class, 'store'])
            ->name('instrument-categories.store');
        Route::get('instrument-categories/{instrumentCategory}/edit', [InstrumentCategoryController::class, 'edit'])
            ->name('instrument-categories.edit');
        Route::patch('instrument-categories/{instrumentCategory}/toggle-active', [InstrumentCategoryController::class, 'toggleActive'])
            ->name('instrument-categories.toggle-active');
        Route::patch('instrument-categories/{id}/restore', [InstrumentCategoryController::class, 'restore'])
            ->name('instrument-categories.restore');
        Route::put('instrument-categories/{instrumentCategory}', [InstrumentCategoryController::class, 'update'])
            ->name('instrument-categories.update');
        Route::delete('instrument-categories/{instrumentCategory}', [InstrumentCategoryController::class, 'destroy'])
            ->name('instrument-categories.destroy');
        Route::get('instrument-categories/{instrumentCategory}', [InstrumentCategoryController::class, 'show'])
            ->name('instrument-categories.show');
    });

    // Instruments
    Route::middleware(['permission:instruments.view'])->group(function () {
        Route::get('instruments', [InstrumentController::class, 'index'])
            ->name('instruments.index');
        Route::get('instruments/create', [InstrumentController::class, 'create'])
            ->name('instruments.create');
        Route::post('instruments', [InstrumentController::class, 'store'])
            ->name('instruments.store');
        Route::get('instruments/{instrument}/edit', [InstrumentController::class, 'edit'])
            ->name('instruments.edit');
        Route::patch('instruments/{instrument}/toggle-active', [InstrumentController::class, 'toggleActive'])
            ->name('instruments.toggle-active');
        Route::patch('instruments/{id}/restore', [InstrumentController::class, 'restore'])
            ->name('instruments.restore');
        Route::put('instruments/{instrument}', [InstrumentController::class, 'update'])
            ->name('instruments.update');
        Route::delete('instruments/{instrument}', [InstrumentController::class, 'destroy'])
            ->name('instruments.destroy');
        Route::get('instruments/{instrument}', [InstrumentController::class, 'show'])
            ->name('instruments.show');
    });

    // Instrument Items
    Route::middleware(['permission:instrument-items.view'])->group(function () {
        Route::get('instrument-items', [InstrumentItemController::class, 'index'])
            ->name('instrument-items.index');
        Route::get('instrument-items/create', [InstrumentItemController::class, 'create'])
            ->name('instrument-items.create');
        Route::post('instrument-items', [InstrumentItemController::class, 'store'])
            ->name('instrument-items.store');
        Route::get('instrument-items/{instrumentItem}/edit', [InstrumentItemController::class, 'edit'])
            ->name('instrument-items.edit');
        Route::patch('instrument-items/{instrumentItem}/toggle-active', [InstrumentItemController::class, 'toggleActive'])
            ->name('instrument-items.toggle-active');
        Route::patch('instrument-items/{id}/restore', [InstrumentItemController::class, 'restore'])
            ->name('instrument-items.restore');
        Route::put('instrument-items/{instrumentItem}', [InstrumentItemController::class, 'update'])
            ->name('instrument-items.update');
        Route::delete('instrument-items/{instrumentItem}', [InstrumentItemController::class, 'destroy'])
            ->name('instrument-items.destroy');
        Route::get('instrument-items/{instrumentItem}', [InstrumentItemController::class, 'show'])
            ->name('instrument-items.show');
    });

    // Tray Templates
    Route::middleware(['permission:tray-templates.view'])->group(function () {
        Route::get('tray-templates', [TrayTemplateController::class, 'index'])
            ->name('tray-templates.index');
        Route::get('tray-templates/create', [TrayTemplateController::class, 'create'])
            ->name('tray-templates.create');
        Route::post('tray-templates', [TrayTemplateController::class, 'store'])
            ->name('tray-templates.store');
        Route::get('tray-templates/{trayTemplate}/edit', [TrayTemplateController::class, 'edit'])
            ->name('tray-templates.edit');
        Route::patch('tray-templates/{trayTemplate}/toggle-active', [TrayTemplateController::class, 'toggleActive'])
            ->name('tray-templates.toggle-active');
        Route::patch('tray-templates/{id}/restore', [TrayTemplateController::class, 'restore'])
            ->name('tray-templates.restore');
        Route::put('tray-templates/{trayTemplate}', [TrayTemplateController::class, 'update'])
            ->name('tray-templates.update');
        Route::delete('tray-templates/{trayTemplate}', [TrayTemplateController::class, 'destroy'])
            ->name('tray-templates.destroy');
        Route::get('tray-templates/{trayTemplate}', [TrayTemplateController::class, 'show'])
            ->name('tray-templates.show');
    });

    // Sterilizers
    Route::middleware(['permission:sterilizers.view'])->group(function () {
        Route::get('sterilizers', [SterilizeController::class, 'index'])
            ->name('sterilizers.index');
        Route::get('sterilizers/create', [SterilizeController::class, 'create'])
            ->name('sterilizers.create');
        Route::post('sterilizers', [SterilizeController::class, 'store'])
            ->name('sterilizers.store');
        Route::get('sterilizers/{sterilizer}/edit', [SterilizeController::class, 'edit'])
            ->name('sterilizers.edit');
        Route::patch('sterilizers/{sterilizer}/toggle-active', [SterilizeController::class, 'toggleActive'])
            ->name('sterilizers.toggle-active');
        Route::patch('sterilizers/{id}/restore', [SterilizeController::class, 'restore'])
            ->name('sterilizers.restore');
        Route::put('sterilizers/{sterilizer}', [SterilizeController::class, 'update'])
            ->name('sterilizers.update');
        Route::delete('sterilizers/{sterilizer}', [SterilizeController::class, 'destroy'])
            ->name('sterilizers.destroy');
        Route::get('sterilizers/{sterilizer}', [SterilizeController::class, 'show'])
            ->name('sterilizers.show');
    });

    // Storage Racks
    Route::middleware(['permission:storage-racks.view'])->group(function () {
        Route::get('storage-racks', [StorageRackController::class, 'index'])
            ->name('storage-racks.index');
        Route::get('storage-racks/create', [StorageRackController::class, 'create'])
            ->name('storage-racks.create');
        Route::post('storage-racks', [StorageRackController::class, 'store'])
            ->name('storage-racks.store');
        Route::get('storage-racks/{storageRack}/edit', [StorageRackController::class, 'edit'])
            ->name('storage-racks.edit');
        Route::patch('storage-racks/{storageRack}/toggle-active', [StorageRackController::class, 'toggleActive'])
            ->name('storage-racks.toggle-active');
        Route::patch('storage-racks/{id}/restore', [StorageRackController::class, 'restore'])
            ->name('storage-racks.restore');
        Route::put('storage-racks/{storageRack}', [StorageRackController::class, 'update'])
            ->name('storage-racks.update');
        Route::delete('storage-racks/{storageRack}', [StorageRackController::class, 'destroy'])
            ->name('storage-racks.destroy');
        Route::get('storage-racks/{storageRack}', [StorageRackController::class, 'show'])
            ->name('storage-racks.show');
    });

    // Consumable Categories
    Route::middleware(['permission:consumable-categories.view'])->group(function () {
        Route::get('consumable-categories', [ConsumableCategoryController::class, 'index'])
            ->name('consumable-categories.index');
        Route::get('consumable-categories/create', [ConsumableCategoryController::class, 'create'])
            ->name('consumable-categories.create');
        Route::post('consumable-categories', [ConsumableCategoryController::class, 'store'])
            ->name('consumable-categories.store');
        Route::get('consumable-categories/{consumableCategory}/edit', [ConsumableCategoryController::class, 'edit'])
            ->name('consumable-categories.edit');
        Route::patch('consumable-categories/{consumableCategory}/toggle-active', [ConsumableCategoryController::class, 'toggleActive'])
            ->name('consumable-categories.toggle-active');
        Route::patch('consumable-categories/{id}/restore', [ConsumableCategoryController::class, 'restore'])
            ->name('consumable-categories.restore');
        Route::put('consumable-categories/{consumableCategory}', [ConsumableCategoryController::class, 'update'])
            ->name('consumable-categories.update');
        Route::delete('consumable-categories/{consumableCategory}', [ConsumableCategoryController::class, 'destroy'])
            ->name('consumable-categories.destroy');
        Route::get('consumable-categories/{consumableCategory}', [ConsumableCategoryController::class, 'show'])
            ->name('consumable-categories.show');
    });

    // Consumables
    Route::middleware(['permission:consumables.view'])->group(function () {
        Route::get('consumables', [ConsumableController::class, 'index'])
            ->name('consumables.index');
        Route::get('consumables/create', [ConsumableController::class, 'create'])
            ->name('consumables.create');
        Route::post('consumables', [ConsumableController::class, 'store'])
            ->name('consumables.store');
        Route::get('consumables/{consumable}/edit', [ConsumableController::class, 'edit'])
            ->name('consumables.edit');
        Route::patch('consumables/{consumable}/toggle-active', [ConsumableController::class, 'toggleActive'])
            ->name('consumables.toggle-active');
        Route::patch('consumables/{id}/restore', [ConsumableController::class, 'restore'])
            ->name('consumables.restore');
        Route::put('consumables/{consumable}', [ConsumableController::class, 'update'])
            ->name('consumables.update');
        Route::delete('consumables/{consumable}', [ConsumableController::class, 'destroy'])
            ->name('consumables.destroy');
        Route::get('consumables/{consumable}', [ConsumableController::class, 'show'])
            ->name('consumables.show');
    });

    // Consumable Stocks & Movements
    Route::middleware(['permission:consumables.view'])->group(function () {
        Route::get('consumable-stocks', [ConsumableStockController::class, 'index'])
            ->name('consumable-stocks.index');
        Route::get('consumable-stocks/movements', [ConsumableStockController::class, 'movements'])
            ->name('consumable-stocks.movements');
        Route::get('consumable-stocks/create', [ConsumableStockController::class, 'create'])
            ->name('consumable-stocks.create');
        Route::post('consumable-stocks', [ConsumableStockController::class, 'store'])
            ->name('consumable-stocks.store');
    });

    // Trays
    Route::middleware(['permission:trays.view'])->group(function () {
        Route::get('trays', [TrayController::class, 'index'])
            ->name('trays.index');
        Route::get('trays/create', [TrayController::class, 'create'])
            ->name('trays.create');
        Route::post('trays', [TrayController::class, 'store'])
            ->name('trays.store');
        Route::get('trays/{tray}/edit', [TrayController::class, 'edit'])
            ->name('trays.edit');
        Route::patch('trays/{id}/restore', [TrayController::class, 'restore'])
            ->name('trays.restore');
        Route::put('trays/{tray}', [TrayController::class, 'update'])
            ->name('trays.update');
        Route::delete('trays/{tray}', [TrayController::class, 'destroy'])
            ->name('trays.destroy');
        Route::get('trays/{tray}', [TrayController::class, 'show'])
            ->name('trays.show');
    });

    // Sterilization Batches
    Route::middleware(['permission:sterilization-batches.view'])->group(function () {
        Route::get('sterilization-batches', [SterilizationBatchController::class, 'index'])
            ->name('sterilization-batches.index');
        Route::get('sterilization-batches/create', [SterilizationBatchController::class, 'create'])
            ->name('sterilization-batches.create');
        Route::post('sterilization-batches', [SterilizationBatchController::class, 'store'])
            ->name('sterilization-batches.store');
        Route::get('sterilization-batches/{sterilizationBatch}/edit', [SterilizationBatchController::class, 'edit'])
            ->name('sterilization-batches.edit');
        Route::patch('sterilization-batches/{sterilizationBatch}/result', [SterilizationBatchController::class, 'updateResult'])
            ->name('sterilization-batches.result');
        Route::patch('sterilization-batches/{id}/restore', [SterilizationBatchController::class, 'restore'])
            ->name('sterilization-batches.restore');
        Route::put('sterilization-batches/{sterilizationBatch}', [SterilizationBatchController::class, 'update'])
            ->name('sterilization-batches.update');
        Route::delete('sterilization-batches/{sterilizationBatch}', [SterilizationBatchController::class, 'destroy'])
            ->name('sterilization-batches.destroy');
        Route::get('sterilization-batches/{sterilizationBatch}', [SterilizationBatchController::class, 'show'])
            ->name('sterilization-batches.show');
    });

    // Distribution Requests
    Route::middleware(['permission:distribution-requests.view'])->group(function () {
        Route::get('distribution-requests', [DistributionRequestController::class, 'index'])
            ->name('distribution-requests.index');
        Route::get('distribution-requests/create', [DistributionRequestController::class, 'create'])
            ->name('distribution-requests.create');
        Route::post('distribution-requests', [DistributionRequestController::class, 'store'])
            ->name('distribution-requests.store');
        Route::get('distribution-requests/{distributionRequest}/edit', [DistributionRequestController::class, 'edit'])
            ->name('distribution-requests.edit');
        Route::patch('distribution-requests/{distributionRequest}/approve', [DistributionRequestController::class, 'approve'])
            ->name('distribution-requests.approve')
            ->middleware('permission:distribution-requests.approve');
        Route::patch('distribution-requests/{id}/restore', [DistributionRequestController::class, 'restore'])
            ->name('distribution-requests.restore');
        Route::put('distribution-requests/{distributionRequest}', [DistributionRequestController::class, 'update'])
            ->name('distribution-requests.update');
        Route::delete('distribution-requests/{distributionRequest}', [DistributionRequestController::class, 'destroy'])
            ->name('distribution-requests.destroy');
        Route::get('distribution-requests/{distributionRequest}', [DistributionRequestController::class, 'show'])
            ->name('distribution-requests.show');
        Route::get('distribution-requests/{distributionRequest}/fulfill', [DistributionRequestController::class, 'fulfill'])
            ->name('distribution-requests.fulfill')
            ->middleware('permission:distribution-requests.fulfill');
        Route::post('distribution-requests/{distributionRequest}/fulfill', [DistributionRequestController::class, 'processFulfillment'])
            ->name('distribution-requests.process-fulfillment')
            ->middleware('permission:distribution-requests.fulfill');
        Route::get('tray-returns', [TrayReturnController::class, 'index'])
         ->name('tray-returns.index');
    });

    // Tray Returns
    Route::middleware(['permission:distribution-requests.return'])->group(function () {
        Route::get('distribution-requests/{distributionRequest}/returns/create', [TrayReturnController::class, 'create'])
            ->name('tray-returns.create');
        Route::post('distribution-requests/{distributionRequest}/returns', [TrayReturnController::class, 'store'])
            ->name('tray-returns.store');
    });

    Route::post('consumable-usages', [ConsumableUsageController::class, 'store'])
     ->name('consumable-usages.store');
});

Route::get('/', fn() => redirect()->route('dashboard'));