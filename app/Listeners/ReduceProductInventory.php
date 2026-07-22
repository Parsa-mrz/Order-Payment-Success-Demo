<?php

namespace App\Listeners;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Events\Attributes\AsListener;
use App\Events\OrderPaymentSuccessful;
use Illuminate\Support\Facades\DB;


#[AsListener( OrderPaymentSuccessful::class )]
class ReduceProductInventory implements ShouldQueue {

	use InteractsWithQueue;

	public string $queue = 'inventory';
	public int $tries    = 5;

	public function handle( OrderPaymentSuccessful $event ): void {
		DB::transaction(
			function () use ( $event ) {
				foreach ( $event->order->items as $item ) {
					Product::whereKey( $item->product_id )
					->decrement( 'stock', $item->quantity );
				}
			}
		);
	}
}
