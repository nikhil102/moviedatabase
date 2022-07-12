<?php


namespace Common\Billing\Invoices;

use Illuminate\Support\Arr;

class CrupdateInvoice
{
    public function execute($data)
    {
        $invoice = new Invoice([
            'subscription_id' => $data['subscription_id'],
            'paid' => $data['paid'],
            'uuid' => str_random(10),
            'notes' => Arr::get($data, 'notes'),
        ]);

        $invoice->save();

        return $invoice;
    }
}