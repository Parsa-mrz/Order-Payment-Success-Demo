<?php

namespace App\Actions\Orders;

use App\DTOs\PaymentConfirmationData;
use App\Enums\OrderStatus;
use App\Events\OrderPaymentSuccessful;
use App\Exceptions\OrderAlreadyPaidException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

final readonly class MarkOrderAsPaid {

	public function handle( Order $order, PaymentConfirmationData $payment ): Order {
		if ( $order->status === OrderStatus::Paid ) {
			throw new OrderAlreadyPaidException( $order );
		}

		$order = DB::transaction(
			function () use ( $order, $payment ) {
				$order->update(
					array(
						'status'            => OrderStatus::Paid,
						'payment_gateway'   => $payment->paymentGateway,
						'payment_intent_id' => $payment->paymentIntentId,
						'paid_at'           => now(),
					)
				);

				return $order->refresh();
			}
		);

		OrderPaymentSuccessful::dispatch( $order );

		return $order;
	}
}
