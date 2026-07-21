<?php
namespace App\DTOs;

final readonly class PaymentConfirmationData {

	public function __construct(
		public string $paymentIntentId,
		public string $paymentGateway,
		public int $amountPaidCents,
		public string $currency,
	) {}
}
