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

    <div class="mb-12 p-4 bg-gray-50 rounded">
        <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Payment</h2>
        <p class="text-gray-700">Please make payment to:</p>
        <p class="text-gray-800 font-medium mt-1">eBandroom</p>
        <p class="text-gray-600">540 W. Louse Ave.</p>
        <p class="text-gray-600">Vinita, OK 74301</p>
    </div>

    @if($invoice->notes)
        <div class="border-t pt-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Notes</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $invoice->notes }}</p>
        </div>
    @endif
</body>
</html>