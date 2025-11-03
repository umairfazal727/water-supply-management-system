<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;

    public function mount(): void
    {
        parent::mount();

        // Pre-fill form with query parameters
        $orderId = request()->query('order_id');
        $deliveryCustomerId = request()->query('delivery_customer_id');

        if ($orderId || $deliveryCustomerId) {
            $this->form->fill([
                'order_id' => $orderId,
                'delivery_customer_id' => $deliveryCustomerId,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
