<?php

use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/midtrans', [MidtransWebhookController::class, 'handle'])->name('midtrans.webhook');
