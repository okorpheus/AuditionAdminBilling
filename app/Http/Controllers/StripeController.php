<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;

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

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event->data->object),
            'charge.updated' => $this->handleChargeUpdated($event->data->object),
            default => null,
        };

        return response('OK', 200);
    }

    protected function handleCheckoutSessionCompleted($session): void
    {
        $invoice = Invoice::find($session->metadata->invoice_id);

        if (! $invoice) {
            return;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = \Stripe\PaymentIntent::retrieve([
            'id' => $session->payment_intent,
            'expand' => ['latest_charge.balance_transaction'],
        ]);

        $feeAmount = $paymentIntent->latest_charge?->balance_transaction?->fee ?? 0;

        $contact = $this->findOrCreateContact($session, $invoice);

        Payment::create([
            'invoice_id' => $invoice->id,
            'contact_id' => $contact?->id,
            'payment_date' => now(),
            'status' => PaymentStatus::COMPLETED,
            'payment_method' => PaymentMethod::CARD,
            'reference' => $session->payment_intent,
            'stripe_payment_intent_id' => $session->payment_intent,
            'amount' => $session->amount_total / 100,
            'fee_amount' => $feeAmount / 100,
        ]);
    }

    protected function handleChargeUpdated($charge): void
    {
        if (! $charge->payment_intent || ! $charge->balance_transaction) {
            return;
        }

        $payment = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();

        if (! $payment) {
            return;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $balanceTransaction = \Stripe\BalanceTransaction::retrieve($charge->balance_transaction);

        // Always update the fee - handles both initial capture and any corrections
        $payment->updateQuietly([
            'fee_amount' => $balanceTransaction->fee / 100,
        ]);
    }

    protected function findOrCreateContact($session, Invoice $invoice): ?Contact
    {
        $email = $session->customer_details?->email;

        if (! $email) {
            return null;
        }

        $contact = Contact::where('email', $email)->first();

        if ($contact) {
            return $contact;
        }

        $name = $session->customer_details?->name ?? '';
        $lastSpacePos = strrpos($name, ' ');

        if ($lastSpacePos !== false) {
            $firstName = substr($name, 0, $lastSpacePos);
            $lastName = substr($name, $lastSpacePos + 1);
        } else {
            $firstName = $name;
            $lastName = '';
        }

        $contact = Contact::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        $invoice->client->contacts()->attach($contact);

        return $contact;
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
