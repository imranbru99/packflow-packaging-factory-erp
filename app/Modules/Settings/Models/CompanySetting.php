<?php

namespace App\Modules\Settings\Models;

use App\Modules\Support\PackflowModel;

class CompanySetting extends PackflowModel
{
    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'name' => 'Padma Convertors Ltd.',
            'name_bn' => 'পদ্মা কনভার্টার্স লিমিটেড',
            'tagline' => 'Boxes, pouches, labels, and cartons from one floor.',
            'currency' => 'BDT',
            'tax_name' => 'VAT',
            'tax_percent' => 15,
            'timezone' => 'Asia/Dhaka',
            'city' => 'Gazipur',
            'country' => 'Bangladesh',
        ]);
    }


    protected function casts(): array
    {
        return [
            'tax_percent' => 'decimal:2',
        ];
    }
}
