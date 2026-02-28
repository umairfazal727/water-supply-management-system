<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;

    public function mount(): void
    {
        parent::mount();

        $orderId = request()->query('order_id');

        $defaults = [
            'delivery_date' => now(),
            'delivery_time' => now()->format('H:i:s'),
            'delivery_number' => 'DEL-' . date('Ymd') . '-' . rand(1000, 9999),
            'payment_method' => 'credit',
            'status' => 'delivered',
        ];

        if ($orderId) {
            $order = Order::find($orderId);
            $defaults['order_id'] = (int) $orderId;
            if ($order) {
                $defaults['trip_size'] = $order->tanker_size;
                $defaults['total_amount'] = $order->total_price ?? $order->price;
            }
        }

        $this->form->fill($defaults);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
