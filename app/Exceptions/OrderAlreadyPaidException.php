<?php

namespace App\Exceptions;

use Exception;
use App\Models\Order;

class OrderAlreadyPaidException extends Exception {

	public function __construct( public readonly Order $order ) {
		parent::__construct( "Order [{$order->reference}] has already been paid." );
	}
}
