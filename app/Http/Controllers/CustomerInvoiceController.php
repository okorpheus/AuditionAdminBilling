<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

class CustomerInvoiceController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        if ($invoice->status === InvoiceStatus::DRAFT) {
            abort(404);
        }
        if ($invoice->status === InvoiceStatus::VOID) {
            abort(404);
        }

        return view('invoices.show', compact('invoice'));
    }
}
