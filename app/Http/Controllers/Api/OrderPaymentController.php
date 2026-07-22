<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\MarkOrderAsPaid;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmOrderPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller {

	public function __invoke(
		ConfirmOrderPaymentRequest $request,
		Order $order,
		MarkOrderAsPaid $markOrderAsPaid,
	): JsonResponse {
		$order = $markOrderAsPaid->handle( $order, $request->toDto() );

		return ( new OrderResource( $order ) )
			->response()
			->setStatusCode( 200 );
	}
}
