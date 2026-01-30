<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
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
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Invoice {$invoice->invoice_number}",
                        ],
                        'unit_amount' => $invoice->balance_due * 100, // Already in cents from MoneyCast? Check this
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => route('checkout.success', $invoice),
            'cancel_url' => route('invoice.show', $invoice),
            'metadata' => [
                'invoice_id' => $invoice->id, // Used by webhook to find the invoice
            ],
        ]);

        return redirect($session->url);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
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
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_date' => now(),
                    'status' => PaymentStatus::COMPLETED,
                    'payment_method' => PaymentMethod::CARD,
                    'reference' => $session->payment_intent,
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'amount' => $session->amount_total / 100, // Stripe sends cents
                    'fee_amount' => 0, // Can retrieve actual fee via separate API call if needed
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

    public function success()
    {
        return view('stripe.index');
    }
}
