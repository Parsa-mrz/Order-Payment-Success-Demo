<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Events\OrderPaymentSuccessful;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderPaymentControllerTest extends TestCase {

	use RefreshDatabase;

	public function test_it_marks_the_order_as_paid_and_dispatches_event(): void {
		Event::fake();

		$order = Order::factory()
		->has( OrderItem::factory()->count( 2 ), 'items' )
		->create( array( 'status' => OrderStatus::Pending ) );

		$response = $this->postJson(
			"/api/orders/{$order->reference}/payment-success",
			array(
				'payment_intent_id' => 'pi_mock_123',
				'payment_gateway'   => 'mock',
				'amount_paid_cents' => $order->total_cents,
				'currency'          => 'USD',
			)
		);

		$response->assertOk()
		->assertJsonPath( 'data.status', 'paid' );

		$this->assertEquals( OrderStatus::Paid, $order->fresh()->status );

		Event::assertDispatched(
			OrderPaymentSuccessful::class,
			function ( $event ) use ( $order ) {
				return $event->order->is( $order );
			}
		);
	}

	public function test_it_returns_validation_errors_for_missing_fields(): void {
		$order = Order::factory()->create( array( 'status' => OrderStatus::Pending ) );

		$response = $this->postJson( "/api/orders/{$order->reference}/payment-success", array() );

		$response->assertStatus( 422 )
			->assertJsonValidationErrors(
				array(
					'payment_intent_id',
					'payment_gateway',
					'amount_paid_cents',
					'currency',
				)
			);
	}

	public function test_it_rejects_an_unsupported_payment_gateway(): void {
		$order = Order::factory()->create( array( 'status' => OrderStatus::Pending ) );

		$response = $this->postJson(
			"/api/orders/{$order->reference}/payment-success",
			array(
				'payment_intent_id' => 'pi_mock_123',
				'payment_gateway'   => 'unknown_gateway',
				'amount_paid_cents' => $order->total_cents,
				'currency'          => 'USD',
			)
		);

		$response->assertStatus( 422 )
			->assertJsonValidationErrors( array( 'payment_gateway' ) );
	}
}
