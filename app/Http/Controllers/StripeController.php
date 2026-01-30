<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function index()
    {
        return view('stripe.index');
    }

    public function checkout(Invoice $invoice)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Invoice {$invoice->invoice_number}",
                        ],
                        'unit_amount' => (int) ($invoice->balance_due * 100),
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('stripe.success', $invoice),
            'cancel_url' => route('invoices.show', $invoice),
            'metadata' => [
                'invoice_id' => $invoice->id,
            ],
        ]);

        return redirect($session->url);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $invoice = Invoice::find($session->metadata->invoice_id);

            if ($invoice) {
                Stripe::setApiKey(config('services.stripe.secret'));

                // Retrieve PaymentIntent with expanded charge and balance_transaction
                $paymentIntent = \Stripe\PaymentIntent::retrieve([
                    'id' => $session->payment_intent,
                    'expand' => ['latest_charge.balance_transaction'],
                ]);

                $feeAmount = $paymentIntent->latest_charge->balance_transaction->fee;

                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_date' => now(),
                    'status' => PaymentStatus::COMPLETED,
                    'payment_method' => PaymentMethod::CARD,
                    'reference' => $session->payment_intent,
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'amount' => $session->amount_total / 100,
                    'fee_amount' => $feeAmount / 100,
                ]);
            }
        }

        return response('OK', 200);
    }

    public function checkoutTutorial()
    {
        Stripe::setApiKey(config('stripe.sk'));

        $session = \Stripe\Checkout\Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'send me money',
                        ],
                        'unit_amount' => 3250, // in cents
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('stripe.success'),
            'cancel_url' => route('stripe.index'),
        ]);

        return redirect()->away($session->url);
    }

    public function success(Invoice $invoice)
    {
        return view('stripe.success', compact('invoice'));
    }
}
