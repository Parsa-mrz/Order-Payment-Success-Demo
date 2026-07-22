<?php

namespace Tests\Feature;

use App\Actions\Orders\MarkOrderAsPaid;
use App\DTOs\PaymentConfirmationData;
use App\Enums\OrderStatus;
use App\Exceptions\OrderAlreadyPaidException;
use App\Events\OrderPaymentSuccessful;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MarkOrderAsPaidTest extends TestCase {

	use RefreshDatabase;

	public function test_it_marks_a_pending_order_as_paid(): void {
		Event::fake();

		$order = Order::factory()->create( array( 'status' => OrderStatus::Pending ) );

		$payment = new PaymentConfirmationData(
			paymentIntentId: 'pi_123',
			paymentGateway: 'mock',
			amountPaidCents: $order->total_cents,
			currency: 'USD',
		);

		$result = app( MarkOrderAsPaid::class )->handle( $order, $payment );

		$this->assertEquals( OrderStatus::Paid, $result->status );
		$this->assertNotNull( $result->paid_at );
		$this->assertEquals( 'pi_123', $result->payment_intent_id );

		Event::assertDispatched( OrderPaymentSuccessful::class );
	}

	public function test_it_throws_when_order_is_already_paid(): void {
		$this->expectException( OrderAlreadyPaidException::class );

		$order = Order::factory()->create( array( 'status' => OrderStatus::Paid ) );

		$payment = new PaymentConfirmationData( 'pi_123', 'mock', 1000, 'USD' );

		app( MarkOrderAsPaid::class )->handle( $order, $payment );
	}

	public function test_it_does_not_dispatch_event_when_already_paid(): void {
		Event::fake();

		$order   = Order::factory()->create( array( 'status' => OrderStatus::Paid ) );
		$payment = new PaymentConfirmationData( 'pi_123', 'mock', 1000, 'USD' );

		try {
			app( MarkOrderAsPaid::class )->handle( $order, $payment );
		} catch ( OrderAlreadyPaidException ) {
			// expected
		}

		Event::assertNotDispatched( OrderPaymentSuccessful::class );
	}
}
