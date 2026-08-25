<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('user', UserController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('wastes', WasteController::class);
    Route::resource('stocks', StockController::class);
    Route::resource('stock-histories', StockHistoryController::class);
    Route::get('/stock-histories-export', [StockHistoryController::class, 'export'])->name('stock-histories.export');
    Route::resource('transactions', TransactionController::class)->only(['index', 'store', 'update']);
    Route::patch('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::get('/menus', MenuManager::class)->name('menus.index');
    Route::get('/roles', RoleManager::class)->name('roles.index');
    Route::get('/roles/{role}/permissions', RolePermissionManager::class)->name('roles.permissions');
});

require __DIR__ . '/auth.php';
