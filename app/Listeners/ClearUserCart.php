<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderPaymentSuccessful;
use Illuminate\Events\Attributes\AsListener;

#[AsListener( OrderPaymentSuccessful::class )]

class ClearUserCart implements ShouldQueue {

	use InteractsWithQueue;

	public function handle( OrderPaymentSuccessful $event ): void {
		$event->order->user->cart?->items()->delete();
	}
}
