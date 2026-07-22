<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory {

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		return array(
			'order_id'         => Order::factory(),
			'product_id'       => Product::factory(),
			'quantity'         => $this->faker->numberBetween( 1, 5 ),
			'unit_price_cents' => $this->faker->numberBetween( 500, 5000 ),
		);
	}
}
