<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockHistoryController;
use App\Http\Controllers\TransactionController;
use App\Livewire\MenuManager;
use App\Livewire\RoleManager;
use App\Livewire\RolePermissionManager;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WasteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::resource('user', UserController::class)->only(['index'])->middleware('permission:show-users');
    Route::resource('user', UserController::class)->only(['store'])->middleware('permission:store-users');
    Route::resource('user', UserController::class)->only(['update'])->middleware('permission:update-users');
    Route::resource('user', UserController::class)->only(['destroy'])->middleware('permission:delete-users');

    Route::resource('customers', CustomerController::class)->only(['index'])->middleware('permission:show-customers');
    Route::resource('customers', CustomerController::class)->only(['store'])->middleware('permission:store-customers');
    Route::resource('customers', CustomerController::class)->only(['update'])->middleware('permission:update-customers');
    Route::resource('customers', CustomerController::class)->only(['destroy'])->middleware('permission:delete-customers');

    Route::resource('drivers', DriverController::class)->only(['index'])->middleware('permission:show-pengemudi');
    Route::resource('drivers', DriverController::class)->only(['store'])->middleware('permission:store-pengemudi');
    Route::resource('drivers', DriverController::class)->only(['update'])->middleware('permission:update-pengemudi');
    Route::resource('drivers', DriverController::class)->only(['destroy'])->middleware('permission:delete-pengemudi');

    Route::resource('categories', CategoryController::class)->only(['index'])->middleware('permission:show-kategori');
    Route::resource('categories', CategoryController::class)->only(['store'])->middleware('permission:store-kategori');
    Route::resource('categories', CategoryController::class)->only(['update'])->middleware('permission:update-kategori');
    Route::resource('categories', CategoryController::class)->only(['destroy'])->middleware('permission:delete-kategori');

    Route::resource('wastes', WasteController::class)->only(['index'])->middleware('permission:show-limbah');
    Route::resource('wastes', WasteController::class)->only(['store'])->middleware('permission:store-limbah');
    Route::resource('wastes', WasteController::class)->only(['update'])->middleware('permission:update-limbah');
    Route::resource('wastes', WasteController::class)->only(['destroy'])->middleware('permission:delete-limbah');

    Route::resource('stocks', StockController::class)->only(['index'])->middleware('permission:show-stok');
    Route::resource('stocks', StockController::class)->only(['store'])->middleware('permission:store-stok');
    Route::resource('stocks', StockController::class)->only(['destroy'])->middleware('permission:delete-stok');

    Route::middleware('permission:show-riwayat-stok')->group(function () {
        Route::resource('stock-histories', StockHistoryController::class)->only(['index']);
        Route::get('/stock-histories-export', [StockHistoryController::class, 'export'])->name('stock-histories.export');
    });

    Route::middleware('permission:show-pengiriman')->group(function () {
        Route::resource('shipments', ShipmentController::class)->only(['index', 'show']);
        Route::get('/shipments/{shipment}/surat-jalan', [ShipmentController::class, 'suratJalan'])->name('shipments.surat-jalan');
    });
    Route::resource('shipments', ShipmentController::class)->only(['store'])->middleware('permission:store-pengiriman');
    Route::resource('shipments', ShipmentController::class)->only(['update'])->middleware('permission:update-pengiriman');
    Route::resource('shipments', ShipmentController::class)->only(['destroy'])->middleware('permission:delete-pengiriman');
    Route::patch('/shipments/{shipment}/ship', [ShipmentController::class, 'ship'])->middleware('role:pengemudi')->name('shipments.ship');
    Route::patch('/shipments/{shipment}/arrive', [ShipmentController::class, 'arrive'])->middleware('role:pengemudi')->name('shipments.arrive');
    Route::patch('/shipments/{shipment}/receive', [ShipmentController::class, 'receive'])->middleware('role:pelanggan')->name('shipments.receive');

    Route::middleware('permission:show-transaksi')->group(function () {
        Route::resource('transactions', TransactionController::class)->only(['index']);
        Route::get('/transactions-export', [TransactionController::class, 'export'])->name('transactions.export');
    });
    Route::resource('transactions', TransactionController::class)->only(['store'])->middleware('permission:store-transaksi');
    Route::resource('transactions', TransactionController::class)->only(['update'])->middleware('permission:update-transaksi');
    Route::patch('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->middleware('role:admin')->name('transactions.approve');
    Route::patch('/transactions/{transaction}/reject', [TransactionController::class, 'reject'])->middleware('role:admin')->name('transactions.reject');
    Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->middleware('role:pelanggan')->name('transactions.cancel');

    Route::resource('returs', ReturController::class)->only(['index'])->middleware('permission:show-retur');
    Route::get('/returs-export', [ReturController::class, 'export'])->middleware('permission:show-retur')->name('returs.export');
    Route::resource('returs', ReturController::class)->only(['store'])->middleware('permission:store-retur');
    Route::patch('/returs/{retur}/approve', [ReturController::class, 'approve'])->middleware('role:admin')->name('returs.approve');
    Route::patch('/returs/{retur}/reject', [ReturController::class, 'reject'])->middleware('role:admin')->name('returs.reject');
    Route::patch('/returs/{retur}/settle', [ReturController::class, 'settle'])->middleware('role:admin')->name('returs.settle');
    Route::patch('/returs/{retur}/cancel', [ReturController::class, 'cancel'])->middleware('role:pelanggan')->name('returs.cancel');

    Route::middleware('permission:show-invoices')->group(function () {
        Route::resource('invoices', InvoiceController::class)->only(['index']);
        Route::get('/invoices-export', [InvoiceController::class, 'export'])->name('invoices.export');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    });
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->middleware('role:pelanggan')->name('invoices.pay');

    Route::get('/menus', MenuManager::class)->middleware('permission:show-menu')->name('menus.index');
    Route::get('/roles', RoleManager::class)->middleware('permission:show-role')->name('roles.index');
    Route::get('/roles/{role}/permissions', RolePermissionManager::class)->middleware('permission:update-role')->name('roles.permissions');
});

require __DIR__ . '/auth.php';
