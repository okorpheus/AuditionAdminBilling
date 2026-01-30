<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received - Invoice {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-8">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Received</h1>
        <p class="text-gray-600 mb-6">Thank you for your payment.</p>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-sm text-gray-500">Invoice</p>
            <p class="text-lg font-semibold text-gray-800">{{ $invoice->invoice_number }}</p>
            <p class="text-sm text-gray-500 mt-2">{{ $invoice->client->name }}</p>
        </div>

        <a href="{{ route('invoices.show', $invoice) }}"
           class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
            View Invoice
        </a>
    </div>
</body>
</html>