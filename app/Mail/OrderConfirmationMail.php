<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable {

	use Queueable;
	use SerializesModels;


	public function __construct( public readonly Order $order ) {}

	public function build(): self {
		return $this->subject( "Order {$this->order->reference} confirmed" )
			->view( 'emails.orders.confirmation' );
	}
}
