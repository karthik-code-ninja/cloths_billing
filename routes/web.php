<?php

use App\Http\Controllers\AuthController;
use App\Livewire\BarcodeLabelPrinter;
use App\Livewire\BrandMaster;
use App\Livewire\CashShiftManager;
use App\Livewire\CatalogueManager;
use App\Livewire\CategoryMaster;
use App\Livewire\GeneralSettingsManager;
use App\Livewire\InventoryManager;
use App\Livewire\PosBilling;
use App\Livewire\PrinterSettingsManager;
use App\Livewire\ProductLookup;
use App\Livewire\ProfileManager;
use App\Livewire\PurchasesManager;
use App\Livewire\ReportsManager;
use App\Livewire\ReturnsManager;
use App\Livewire\SeasonMaster;
use App\Livewire\SizeGroupMaster;
use App\Livewire\SupplierMaster;
use App\Livewire\UserManager;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout & Public API
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/api/manager-override', [AuthController::class, 'verifyManagerOverride'])->name('api.manager-override');

// Authenticated POS & Retail Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('pos');
    });

    Route::get('/pos', PosBilling::class)->name('pos');
    Route::get('/product-lookup/{id?}', ProductLookup::class)->name('product.lookup');
    Route::get('/barcode-labels', BarcodeLabelPrinter::class)->name('barcode.labels');
    Route::get('/catalogue', CatalogueManager::class)->name('catalogue');
    Route::get('/masters/categories', CategoryMaster::class)->name('masters.categories');
    Route::get('/masters/brands', BrandMaster::class)->name('masters.brands');
    Route::get('/masters/suppliers', SupplierMaster::class)->name('masters.suppliers');
    Route::get('/masters/size-groups', SizeGroupMaster::class)->name('masters.sizes');
    Route::get('/masters/seasons', SeasonMaster::class)->name('masters.seasons');
    Route::get('/inventory', InventoryManager::class)->name('inventory');
    Route::get('/purchases', PurchasesManager::class)->name('purchases');
    Route::get('/returns', ReturnsManager::class)->name('returns');
    Route::get('/cash-shifts', CashShiftManager::class)->name('cash-shifts');
    Route::get('/reports', ReportsManager::class)->name('reports');
    Route::get('/settings/general', GeneralSettingsManager::class)->name('settings.general');
    Route::get('/settings/printers', PrinterSettingsManager::class)->name('settings.printers');
    Route::get('/settings/users', UserManager::class)->name('settings.users');
    Route::get('/profile', ProfileManager::class)->name('profile');
});
