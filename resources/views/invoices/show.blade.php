<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white p-8 max-w-4xl mx-auto">
    <div class="flex justify-between items-start mb-12">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">INVOICE</h1>
            <p class="text-gray-600 mt-1">{{ $invoice->invoice_number }}</p>
        </div>
        <div class="text-right">
            <p class="font-semibold text-gray-800">eBandroom</p>
            <p class="text-gray-600">540 W. Louse Ave.</p>
            <p class="text-gray-600">Vinita, OK 74301</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-8 mb-12">
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Bill To</h2>
            <p class="text-gray-800 font-medium">{{ $invoice->client->name }}</p>
        </div>
        <div class="text-right">
            <div class="mb-2">
                <span class="text-sm text-gray-500">Invoice Date:</span>
                <span class="text-gray-800 ml-2">{{ $invoice->invoice_date?->format('F j, Y') }}</span>
            </div>
            <div>
                <span class="text-sm text-gray-500">Due Date:</span>
                <span class="text-gray-800 ml-2">{{ $invoice->due_date?->format('F j, Y') }}</span>
            </div>
        </div>
    </div>

    <table class="w-full mb-8">
        <thead>
            <tr class="border-b-2 border-gray-300">
                <th class="text-left py-3 text-sm font-semibold text-gray-600">SKU</th>
                <th class="text-left py-3 text-sm font-semibold text-gray-600">Description</th>
                <th class="text-left py-3 text-sm font-semibold text-gray-600">School Year</th>
                <th class="text-right py-3 text-sm font-semibold text-gray-600">Qty</th>
                <th class="text-right py-3 text-sm font-semibold text-gray-600">Unit Price</th>
                <th class="text-right py-3 text-sm font-semibold text-gray-600">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
                <tr class="border-b border-gray-200">
                    <td class="py-3 text-gray-600">{{ $line->sku }}</td>
                    <td class="py-3 text-gray-800">{{ $line->name }}</td>
                    <td class="py-3 text-gray-600">{{ $line->school_year_formatted }}</td>
                    <td class="py-3 text-right text-gray-600">{{ $line->quantity }}</td>
                    <td class="py-3 text-right text-gray-600">{{ formatMoney($line->unit_price) }}</td>
                    <td class="py-3 text-right text-gray-800">{{ formatMoney($line->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-gray-300">
                <td colspan="5" class="py-4 text-right font-semibold text-gray-800">Total</td>
                <td class="py-4 text-right font-bold text-gray-800 text-lg">{{ formatMoney($invoice->total) }}</td>
            </tr>
        </tfoot>
    </table>

    @php
        $completedPayments = $invoice->payments->where('status', \App\Enums\PaymentStatus::COMPLETED);
        $pendingPayments = $invoice->payments->where('status', '!=', \App\Enums\PaymentStatus::COMPLETED);
    @endphp

    @if($completedPayments->count() > 0)
        <div class="mb-8">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Payments Received</h2>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Date</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Method</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Reference</th>
                        <th class="text-right py-2 text-sm font-semibold text-gray-600">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completedPayments as $payment)
                        <tr class="border-b border-gray-200">
                            <td class="py-2 text-gray-600">{{ $payment->payment_date->format('F j, Y') }}</td>
                            <td class="py-2 text-gray-600">{{ $payment->payment_method->label() }}</td>
                            <td class="py-2 text-gray-600">{{ $payment->reference }}</td>
                            <td class="py-2 text-right text-gray-800">{{ formatMoney($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300">
                        <td colspan="3" class="py-2 text-right font-semibold text-gray-800">Total Payments</td>
                        <td class="py-2 text-right font-semibold text-gray-800">{{ formatMoney($invoice->total_payments) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="py-2 text-right font-bold text-gray-800">Balance Due</td>
                        <td class="py-2 text-right font-bold text-gray-800 text-lg">{{ formatMoney($invoice->balance_due) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    @if($pendingPayments->count() > 0)
        <div class="mb-8">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Pending Payments</h2>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Date</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Method</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Reference</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-right py-2 text-sm font-semibold text-gray-600">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingPayments as $payment)
                        <tr class="border-b border-gray-200">
                            <td class="py-2 text-gray-600">{{ $payment->payment_date->format('F j, Y') }}</td>
                            <td class="py-2 text-gray-600">{{ $payment->payment_method->label() }}</td>
                            <td class="py-2 text-gray-600">{{ $payment->reference }}</td>
                            <td class="py-2 text-gray-600">{{ $payment->status->label() }}</td>
                            <td class="py-2 text-right text-gray-800">{{ formatMoney($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($invoice->balance_due != 0)
        <div class="mb-12 p-6 bg-gray-50 rounded-lg">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">Payment Options</h2>

            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                    <h3 class="font-medium text-gray-800 mb-2">Pay Online</h3>
                    <p class="text-gray-600 text-sm mb-4">Pay securely with your credit or debit card.</p>
                    <form action="{{ route('stripe.checkout', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Pay {{ formatMoney($invoice->balance_due) }} Now
                        </button>
                    </form>
                </div>

                <div class="flex-1">
                    <h3 class="font-medium text-gray-800 mb-2">Pay by Mail</h3>
                    <p class="text-gray-600 text-sm mb-2">Make check payable to:</p>
                    <p class="text-gray-800 font-medium">eBandroom</p>
                    <p class="text-gray-600">540 W. Louse Ave.</p>
                    <p class="text-gray-600">Vinita, OK 74301</p>
                </div>
            </div>
        </div>
    @endif

    @if($invoice->notes)
        <div class="border-t pt-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Notes</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $invoice->notes }}</p>
        </div>
    @endif
</body>
</html>