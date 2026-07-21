<?php

namespace App\Http\Requests;

use App\DTOs\PaymentConfirmationData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmOrderPaymentRequest extends FormRequest {

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, ValidationRule|array<mixed>|string>
	 */
	public function rules(): array {
		return array(
			'payment_intent_id' => array( 'required', 'string', 'max:255' ),
			'payment_gateway'   => array( 'required', 'string', 'in:stripe,paypal,mock' ),
			'amount_paid_cents' => array( 'required', 'integer', 'min:1' ),
			'currency'          => array( 'required', 'string', 'size:3' ),
		);
	}


	public function toDto(): PaymentConfirmationData {
		return new PaymentConfirmationData(
			paymentIntentId: $this->string( 'payment_intent_id' )->toString(),
			paymentGateway: $this->string( 'payment_gateway' )->toString(),
			amountPaidCents: $this->integer( 'amount_paid_cents' ),
			currency: $this->string( 'currency' )->toString(),
		);
	}
}
