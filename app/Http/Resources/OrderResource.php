<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource {

	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray( Request $request ): array {
		return array(
			'reference'   => $this->reference,
			'status'      => $this->status->value,
			'total_cents' => $this->total_cents,
			'currency'    => $this->currency,
			'paid_at'     => $this->paid_at?->toIso8601String(),
		);
	}
}
