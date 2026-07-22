<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory {

	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array {
		return array(
			'name'        => $this->faker->words( 3, true ),
			'price_cents' => $this->faker->numberBetween( 500, 5000 ),
			'stock'       => 50,
		);
	}
}
