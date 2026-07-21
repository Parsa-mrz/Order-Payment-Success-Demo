<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(
	array(
		'cart_id',
		'product_id',
		'quantity',
	)
)]
class CartItem extends Model {

}
