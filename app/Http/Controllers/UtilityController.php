<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Mpdf;
use Illuminate\Http\Request;

class UtilityController extends Controller
{

    public function __construct()
    {
         
        $this->middleware('auth');
    }


    public function print($order_id)
    {   

        $order = Order::with('items')->findOrFail( $order_id );
        $currency_symbol = config('settings.currency_symbol');
        $site_name = config('settings.site_name');
        $site_description = config('settings.site_description');

        $data = [
            'invoiceNumber' => $order->id,
            'date' => $order->created_at->format('M d, Y'),
            'time' => $order->created_at->format('h:i:s A'),
            'items' => $order->items,
            'order' => $order,
            'currency_symbol' => $currency_symbol,
            'site_name' => $site_name,
            'site_description' => $site_description
        ];

        $html = view('invoices.3-invoice', $data)->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        
        $mpdf = new Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                public_path(''),
            ]),
            'fontdata' => $fontData + [  
                'terminus' => [
                    'R' => 'Terminus.ttf',
                ]
            ],
            'default_font' => 'terminus',
            'mode' => 'utf-8',
            'shrink_tables_to_fit' => 0,
            'format' => [75, 200],  
            'orientation' => 'P',
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 3,
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('invoice-'.$order_id.'.pdf', 'I');
    }
}
