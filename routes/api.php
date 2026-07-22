<?php

use App\Http\Controllers\Api\OrderPaymentController;
use Illuminate\Support\Facades\Route;
Route::post( '/orders/{order:reference}/payment-success', OrderPaymentController::class )
	->name( 'orders.payment-success' );
