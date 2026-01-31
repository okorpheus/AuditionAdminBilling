<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 30px; border-radius: 8px;">
        <h1 style="color: #1a1a1a; margin-top: 0; font-size: 24px;">Invoice {{ $invoice->invoice_number }}</h1>

        <p style="margin-bottom: 20px;">Dear {{ $invoice->client->primary_contact?->first_name ?? 'Valued Customer' }},</p>

        <p>Please find below a summary of your invoice from eBandroom.</p>

        <div style="background-color: #ffffff; padding: 20px; border-radius: 6px; margin: 20px 0; border: 1px solid #e9ecef;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #666;">Invoice Number:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Invoice Date:</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoice->invoice_date?->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Due Date:</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoice->due_date?->format('F j, Y') }}</td>
                </tr>
            </table>

            <hr style="border: none; border-top: 1px solid #e9ecef; margin: 15px 0;">

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #e9ecef;">
                        <th style="padding: 10px 0; text-align: left; color: #666; font-weight: 500;">Description</th>
                        <th style="padding: 10px 0; text-align: right; color: #666; font-weight: 500;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->lines as $line)
                    <tr>
                        <td style="padding: 10px 0;">
                            {{ $line->product?->name ?? $line->description }}
                            @if($line->description && $line->product)
                                <br><span style="color: #666; font-size: 14px;">{{ $line->description }}</span>
                            @endif
                        </td>
                        <td style="padding: 10px 0; text-align: right;">${{ number_format($line->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <hr style="border: none; border-top: 2px solid #e9ecef; margin: 15px 0;">

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: 600; font-size: 18px;">Total Due:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: 600; font-size: 18px; color: #2563eb;">${{ number_format($invoice->balance_due, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->notes)
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif

        <p style="margin: 25px 0;">To view your complete invoice or pay online, please click the button below:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $invoiceUrl }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;">View Invoice & Pay Online</a>
        </div>

        <p style="color: #666; font-size: 14px; margin-top: 30px;">
            If you have any questions about this invoice, please don't hesitate to contact us.
        </p>

        <p style="margin-bottom: 0;">
            Thank you for your business,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>

    <div style="text-align: center; padding: 20px; color: #999; font-size: 12px;">
        <p>This email was sent regarding invoice {{ $invoice->invoice_number }}.</p>
    </div>
</body>
</html>
