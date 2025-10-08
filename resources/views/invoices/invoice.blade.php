<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        background: #fff;
        color: #000;
    }

    .invoice-container {
        max-width: 800px;
        margin: auto;
        border: 1px solid #ccc;
        padding: 40px 50px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #1b73b3;
        padding-bottom: 10px;
    }

    .logo {
        width: 140px;
        height: auto;
    }

    .company-info {
        text-align: right;
        color: #1b73b3;
        font-size: 13px;
    }

    .company-info h2 {
        margin: 0;
        color: #1b73b3;
        font-size: 18px;
        font-weight: bold;
    }

    .company-info p {
        margin: 2px 0;
    }

    /* Title */
    .title {
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        margin-top: 25px;
        text-decoration: underline;
    }

    /* Invoice details */
    .details {
        margin-top: 20px;
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .details td {
        padding: 5px 10px;
        vertical-align: top;
    }

    .details .label {
        font-weight: bold;
        width: 150px;
    }

    /* Table */
    table.invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }

    .invoice-table th, .invoice-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }

    .invoice-table th {
        background-color: #dce9f9;
        font-weight: bold;
    }

    .invoice-table td {
        height: 30px;
    }

    .left-text {
        text-align: left;
    }

    /* Totals */
    .totals {
        margin-top: 10px;
        font-size: 14px;
    }

    .totals .row {
        display: flex;
        justify-content: flex-end;
        padding: 2px 0;
    }

    .totals .row span {
        width: 150px;
        text-align: left;
        display: inline-block;
    }

    .amount-words {
        margin-top: 15px;
        font-size: 13px;
        font-weight: bold;
    }

    /* Footer */
    .footer {
        margin-top: 40px;
        font-size: 13px;
    }

    .footer .signature {
        margin-top: 30px;
    }

    .footer img {
        width: 160px;
        margin-top: 10px;
    }

    .footer strong {
        display: block;
        margin-top: 10px;
    }

</style>
</head>
<body>

<div class="invoice-container">
    <!-- Header -->
    <div class="header">
        <img src="{{asset('invoice-img/logo.png')}}" alt="Company Logo">
        <div class="company-info">
            <h2>ريم الفلج لتجارة المياه المخصصة لاعمال البناء</h2>
            <h2>REEM AL FALAJ CONST. WATER TR</h2>
            <p>Sajja Industrial Area, Sharjah - U.A.E.</p>
            <p>050 8426001 | 055 2496358 | 055 7466868 | 052 9343958</p>
        </div>
    </div>

    <!-- Title -->
    <div class="title">TAX INVOICE</div>

    <!-- Invoice details -->
    <table class="details">
        <tr>
            <td class="label">DATE:</td>
            <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td>
            <td class="label">INV NO:</td>
            <td>{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="label">MONTH:</td>
            <td>{{ strtoupper(\Carbon\Carbon::parse($order->order_date)->format('M-Y')) }}</td>
            <td class="label">TRN NO:</td>
            <td>100614240800003</td>
        </tr>
    </table>

    <p style="margin-top:25px;"><strong>TO</strong><br>{{ strtoupper($order->company_name ?: ($order->customer->company_name ?? '')) }}<br>{{ strtoupper($order->customer->address ?? 'SHARJAH-UAE') }}</p>

    <!-- Table -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th>S/N</th>
                <th>DESCRIPTION</th>
                <th>QTY</th>
                <th>RATE</th>
                <th>AMOUNT AED</th>
            </tr>
        </thead>
        <tbody>
            @php
                $productName = $order->product_type === 'sweet_water' ? 'SWEET WATER' : 'SALT WATER';
                $tankerSize = $order->tanker_size ?? '250GALLON';
                $quantity = 1; // Default quantity, adjust if needed
                $rate = $order->total_price; // Assuming total price is the rate
                $subTotal = $order->total_price;
                $vatPercentage = 5;
                $vat = $subTotal * ($vatPercentage / 100);
                $grandTotal = $subTotal + $vat;
            @endphp
            <tr>
                <td>1</td>
                <td class="left-text">{{ strtoupper($productName) }} ({{ strtoupper($tankerSize) }})</td>
                <td>{{ $quantity }}</td>
                <td>{{ number_format($rate, 2) }}</td>
                <td>{{ number_format($subTotal, 2) }}AED</td>
            </tr>
            <tr>
                <td colspan="4" class="left-text"><strong>SUB TOTAL</strong></td>
                <td><strong>{{ number_format($subTotal, 2) }}AED</strong></td>
            </tr>
            <tr>
                <td colspan="4" class="left-text"><strong>VAT {{ $vatPercentage }}%</strong></td>
                <td><strong>{{ number_format($vat, 2) }}AED</strong></td>
            </tr>
            <tr>
                <td colspan="4" class="left-text"><strong>GRAND TOTAL</strong></td>
                <td><strong>{{ number_format($grandTotal, 2) }}AED</strong></td>
            </tr>
            <tr>
                <td colspan="5">Amount in words: {{ strtoupper(\NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($grandTotal)) }} DIRHAMS ONLY.</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Thanks & best regards<br>
        <strong>REEM AL FALAJ CONST. WATER TR</strong></p>
        <div class="signature">
            <img src="{{asset('invoice-img/stamp.png')}}" alt="Company Stamp">
        </div>
    </div>
</div>

</body>
</html>
