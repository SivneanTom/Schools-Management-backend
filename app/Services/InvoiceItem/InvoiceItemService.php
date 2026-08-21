<?php

namespace App\Services\InvoiceItem;

use App\Models\InvoiceItem;

class InvoiceItemService
{
    public function create(array $data): InvoiceItem
    {
        $data['discount_amount'] = $data['discount_amount'] ?? 0;

        $data['line_total'] =
            ($data['quantity'] * $data['unit_amount'])
            - $data['discount_amount'];

        return InvoiceItem::create($data);
    }

    public function update(InvoiceItem $item,array $data): InvoiceItem
    {
        $data = array_merge($item->toArray(),$data);

        $data['line_total'] =
            ($data['quantity'] * $data['unit_amount'])
            - ($data['discount_amount'] ?? 0);

        $item->update($data);

        return $item;
    }
}
