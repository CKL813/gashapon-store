<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class PaymentController extends Controller
{
    /**
     * Handle Stripe webhook events.
     *
     * This route is excluded from CSRF middleware.
     * Stripe sends events here for: payment success, failure, refunds, etc.
     */
    public function webhook(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        // Verify the webhook signature when a secret is configured
        if ($secret) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (UnexpectedValueException) {
                return response('Invalid payload.', 400);
            } catch (SignatureVerificationException) {
                return response('Invalid signature.', 400);
            }
        } else {
            // Dev mode: parse without signature verification
            $event = \Stripe\Event::constructFrom(json_decode($payload, true));
        }

        match ($event->type) {
            'payment_intent.succeeded'               => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed'          => $this->handlePaymentFailed($event),
            'charge.refunded'                        => $this->handleRefund($event),
            default                                  => null, // Ignore unknown events
        };

        return response('OK', 200);
    }

    // ── Event Handlers ────────────────────────────────────────────────────────

    private function handlePaymentSucceeded(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent = $event->data->object;

        $order = Order::where('stripe_payment_intent_id', $intent->id)->first();
        if (! $order) {
            return;
        }

        $order->update([
            'status'                 => OrderStatus::Processing,
            'stripe_payment_status'  => 'paid',
        ]);
    }

    private function handlePaymentFailed(\Stripe\Event $event): void
    {
        /** @var \Stripe\PaymentIntent $intent */
        $intent = $event->data->object;

        $order = Order::where('stripe_payment_intent_id', $intent->id)->first();
        if (! $order) {
            return;
        }

        $order->update([
            'stripe_payment_status' => 'failed',
        ]);
    }

    private function handleRefund(\Stripe\Event $event): void
    {
        /** @var \Stripe\Charge $charge */
        $charge = $event->data->object;

        $intentId = $charge->payment_intent;
        $order    = Order::where('stripe_payment_intent_id', $intentId)->first();
        if (! $order) {
            return;
        }

        $order->update([
            'status'                => OrderStatus::Refunded,
            'stripe_payment_status' => 'refunded',
        ]);
    }
}
