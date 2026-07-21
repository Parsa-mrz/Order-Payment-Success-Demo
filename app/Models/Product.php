<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(
	array(
		'name',
		'price_cents',
		'stock',
	)
)]
class Product extends Model {

}
