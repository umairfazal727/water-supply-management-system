<!-- resources/views/invoices/invoice.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family:"terminus";
            font-size: 14px;
            margin: 0;
            padding: 0;
        }
        .text-right{
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow:wrap;
        }
        th, td {
            padding-top: 2mm;
            padding-bottom:2mm;
            text-align: left;
            border-left: 0;
            border-right: 0;
        }
        
        .border-b{
            border-bottom: 1px solid #333;
        }
        .pb-1{
            padding-bottom: 1mm;
        }
        .1{
            padding-bottom: 2mm;
        }
        .pt-0{
            padding-top: 0;
        }
        .pb-0{
            padding-bottom: 0;
        }
        .font-bold{
            font-weight: bold;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 3mm;
        }
        .invoice-header h1{
            font-size: 16px;
            margin-top: 3mm;
        }
        .invoice-header p {
            font-size: 14px;
            margin: 0;
        }
        .invoice-footer {
            text-align: center;
            font-size: 10px;
            margin-top: 10mm;
        }
        .text-center{
            text-align: center
        }
        .border-b-d{
            border-bottom: 1px dashed #999;
        }
        h2{
            padding-top:0.5mm; 
            text-align:center; 
            padding-bottom:0mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
        }
        .p-x-1{
            padding-left: 1mm;
            padding-right:1mm;
        }
        .p-x-2{
            padding-left: 2mm;
            padding-right:2mm;
        }
        table th{
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
        }
        .mt-0{
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <span style="margin-top: 2mm">&nbsp;&nbsp;</span>
        <h1 class="text-center" style="margin-bottom:0; font-family: 'Courier New', Courier, monospace">
            {{$site_name}}
        </h1>
        <p style="font-size: 12px;">{{$site_description}}</p>
        <h2>INVOICE</h2>
        <p>Invoice Number: #{{ str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT) }}</p>
        <p>Date: {{ $date }}</p>
        <p>Time: {{ $time }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-center">Rate</th>
                <th class="text-center p-x-1">QTY</th>
                <th class="text-right">Amount</th>
            </tr>
            <tr>
                <th class="border-b-d" colspan="4" style="height: 0; padding:0;"></td>
            </tr>
        </thead>
        <tbody>
            @php 
                $i = 0; 
                $total_price = 0;
                $total_tax = [];
                $grand_total = 0;
            @endphp
            @foreach($items as $item)
                @php $i++; @endphp
                <tr>
                    <td  class="pb-1" style="" colspan="4">{{ $i.'.'.$item->product->name}}</td>
                </tr>
                <tr>
                    <td class="pt-0">VAT {{(int)$item['tax']}}%</td>
                    <td class="text-center pt-0">{{ number_format($item['price'], 2, '.', '')  }}</td>
                    <td class="text-center pt-0">{{ $item['quantity'] }}</td>
                    @php 
                        $tax = $item['tax'];
                        $item_total = $item->price * $item->quantity;
                        $tax_amount = ($item_total * $tax) / 100;
                        $item_total_with_tax = $item_total + $tax_amount;
                        $total_price += $item_total;
                        $total_tax[$tax] = ($total_tax[$tax] ?? 0) + $tax_amount;
                        $grand_total += $item_total_with_tax;
                    @endphp
                    <td class="text-right pt-0" style="">{{ number_format( $item_total_with_tax , 2, '.', '')  }}</td>
                </tr>
                <tr>
                    <td class="border-b-d" colspan="4" style="height: 0; padding:0;"></td>
                </tr>
            @endforeach

            <tr>
                <td class="text-center " colspan="4" style="padding-top:3mm; padding-bottom:0; font-size:16px">
                    INVOICE SUMMARY
                </td>
            </tr>

            <tr>
                <td class="mt-2" colspan="3" style="font-size:16px;">
                    Total 
                </td>
                <td class="pt-0" style="text-align: right; font-size:16px">
                    {{$order->total_price}}
                </td>
            </tr>

            @foreach ($total_tax as $rate => $amount)
                <tr>
                    <td  colspan="3" style="font-size:16px; padding-top:0; padding-bottom:0">
                        VAT/GST @ {{ (int)$rate }}% 
                    </td>
                    <td class="pt-0 pb-0" class="text-right"  style=" padding-top:0; padding-bottom:0; font-size:16px">
                        {{$amount}}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td class="border-b-d" colspan="4" style="height: 0; padding:0; padding-top:2mm"></td>
            </tr>
            <tr>
                <td colspan="3" style="font-size:16px">
                    Grand Total 
                </td>
                <td class="text-right" style=" font-size:16px">
                    {{ number_format($grand_total, 2) }}
                </td>
            <tr>
            <tr>
                <td class="border-b-d" colspan="4" style="height: 0; padding:0;"></td>
            </tr>
            
        </tbody>
    </table>

    <div class="invoice-footer">
        <p>Thank you for your purchase!</p>
    </div>
</body>
</html>
