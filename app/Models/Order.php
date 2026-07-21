<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[Fillable(
	array(
		'user_id',
		'reference',
		'status',
		'total_cents',
		'currency',
		'payment_gateway',
		'payment_intent_id',
		'paid_at',
	)
)]
class Order extends Model {

	protected function casts(): array {
		return array(
			'status'  => OrderStatus::class,
			'paid_at' => 'datetime',
		);
	}

	public function user(): BelongsTo {
		return $this->belongsTo( User::class );
	}

	public function items(): HasMany {
		return $this->hasMany( OrderItem::class );
	}
}
