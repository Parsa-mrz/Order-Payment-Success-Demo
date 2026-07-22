<?php

namespace App\Listeners;

use App\Events\OrderPaymentSuccessful;
use App\Mail\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue {

	use InteractsWithQueue;

	public string $queue = 'emails';
	public int $tries    = 3;
	public int $backoff  = 30;

	public function handle( OrderPaymentSuccessful $event ): void {
		Mail::to( $event->order->user->email )
			->send( new OrderConfirmationMail( $event->order ) );
	}
}
