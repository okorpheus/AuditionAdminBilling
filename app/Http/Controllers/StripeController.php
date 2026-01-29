<?php

namespace App\Http\Controllers;

use Stripe\Stripe;

class StripeController extends Controller
{
    public function index()
    {
        return view('stripe.index');
    }

    public function checkout()
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
