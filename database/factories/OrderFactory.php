<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory {

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
			return array(
				'user_id'     => User::factory(),
				'reference'   => strtoupper( $this->faker->bothify( 'ORD-####??' ) ),
				'status'      => OrderStatus::Pending,
				'total_cents' => $this->faker->numberBetween( 1000, 20000 ),
				'currency'    => 'USD',
			);
	}
}
