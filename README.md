# Order Payment Success — Event-Driven Architecture Demo

A Laravel 11 implementation of an order-payment-success endpoint, built to demonstrate
senior-level architecture: skinny controllers, a transactional Action class, and an
event-driven fan-out to queued listeners for side effects (email, inventory, cart).

## Why this architecture

When a payment succeeds, multiple unrelated things need to happen: confirm the order,
email the customer, decrement stock, clear the cart. Bundling all of that into one
controller method (or one service class) creates a class that grows every time a new
stakeholder wants a new side effect, and couples unrelated concerns to a single point
of failure.

This project separates two concerns:

1. **The fact**: the order was paid. This is the only thing handled synchronously,
   inside a database transaction, with an idempotency guard against duplicate webhook
   calls.
2. **The consequences**: everything that should happen as a result. These are modeled
   as an `OrderPaymentSuccessful` event with independent, queued listeners. Adding a
   new side effect (Slack alert, loyalty points, analytics) means adding a new listener
   class — no changes to the controller, the action, or any existing listener.

Queuing the listeners also means the HTTP response returns immediately rather than
waiting on SMTP or third-party APIs, which matters because payment gateways generally
enforce webhook response-time / retry policies.

## Request flow

```
POST /api/orders/{order:reference}/payment-success
        │
        ▼
ConfirmOrderPaymentRequest   (validates payload, builds a typed DTO)
        │
        ▼
OrderPaymentController        (HTTP concerns only)
        │
        ▼
MarkOrderAsPaid Action        (DB transaction, idempotency guard)
        │
        ▼
OrderPaymentSuccessful event  (dispatched after commit)
        │
        ├──► SendOrderConfirmationEmail   (queue: emails)
        ├──► ReduceProductInventory       (queue: inventory)
        └──► ClearUserCart                (queue: default)
```

## Project structure

```
app/
├── Actions/Orders/
│   └── MarkOrderAsPaid.php              # Transactional status update + idempotency check
├── DataTransferObjects/
│   └── PaymentConfirmationData.php      # Readonly DTO for the validated payload
├── Enums/
│   └── OrderStatus.php                  # Backed enum: pending|paid|failed|refunded
├── Events/
│   └── OrderPaymentSuccessful.php       # Fired once the order is committed as paid
├── Exceptions/
│   └── OrderAlreadyPaidException.php    # Guards against duplicate webhook delivery
├── Http/
│   ├── Controllers/Api/
│   │   └── OrderPaymentController.php   # Skinny, single-action controller
│   ├── Requests/
│   │   └── ConfirmOrderPaymentRequest.php
│   └── Resources/
│       └── OrderResource.php
├── Listeners/
│   ├── SendOrderConfirmationEmail.php   # ShouldQueue, queue: emails
│   ├── ReduceProductInventory.php       # ShouldQueue, queue: inventory
│   └── ClearUserCart.php                # ShouldQueue
├── Mail/
│   └── OrderConfirmationMail.php
└── Models/
    ├── Order.php
    ├── OrderItem.php
    ├── Product.php
    ├── Cart.php
    └── CartItem.php
```

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure the queue driver in `.env`:

```
QUEUE_CONNECTION=database
```

Run migrations:

```bash
php artisan queue:table
php artisan migrate
```

## Running locally

```bash
php artisan serve
```

In a second terminal, run a queue worker so the listeners actually process:

```bash
php artisan queue:work --queue=emails,inventory,default
```

### Example request

```bash
curl -X POST http://127.0.0.1:8000/api/orders/ORD-1234AB/payment-success \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "payment_intent_id": "pi_mock_123",
    "payment_gateway": "mock",
    "amount_paid_cents": 4999,
    "currency": "USD"
  }'
```

Response:

```json
{
    "data": {
        "reference": "ORD-1234AB",
        "status": "paid",
        "total_cents": 4999,
        "currency": "USD",
        "paid_at": "2026-07-23T10:15:00+00:00"
    }
}
```

## Idempotency

Payment webhooks are frequently delivered more than once by gateways. Calling this
endpoint on an order that is already `paid` throws `OrderAlreadyPaidException` rather
than re-processing the payment or re-firing side effects.

## Testing

```bash
php artisan test
```

Covers:

- `MarkOrderAsPaidTest` — the action correctly transitions status, sets `paid_at`,
  dispatches the event, and rejects already-paid orders.
- `OrderPaymentControllerTest` — the full HTTP flow, validation failures, and the
  event dispatch from a real request.

## Extending

To add a new side effect (e.g. a Slack notification):

1. `php artisan make:listener NotifySlackChannel`
2. Implement `ShouldQueue`, add the `#[AsListener(event: OrderPaymentSuccessful::class)]` attribute
3. That's it — no other file needs to change.

## Key design decisions

| Decision                                           | Reasoning                                                                                                                                             |
| -------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Single-action controller                           | Controller has one job: translate HTTP in, HTTP out. No branching logic.                                                                              |
| DTO instead of raw arrays                          | Static typing survives from the Form Request through the Action; no stringly-typed array access downstream.                                           |
| Backed enum for status                             | No magic strings (`'paid'`) scattered across the codebase; IDEs and static analysis catch typos.                                                      |
| Event dispatched after `DB::transaction()` commits | Listeners never act on data that could still be rolled back.                                                                                          |
| Each listener implements `ShouldQueue`             | Response to the payment gateway returns immediately; side effects don't block or risk timing out the webhook.                                         |
| Per-listener queue names                           | Inventory and email volume can be scaled/prioritized independently by dedicating workers per queue.                                                   |
| No repository pattern                              | Eloquent models plus a single Action class is sufficient at this scope — an extra abstraction layer here would be over-engineering, not architecture. |
