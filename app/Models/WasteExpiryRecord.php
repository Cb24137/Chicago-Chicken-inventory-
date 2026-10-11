<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class WasteExpiryRecord extends Model
{
    protected $fillable = [
        'item_name',
        'quantity',
        'unit',
        'reason',
        'expiry_date',
        'record_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'expiry_date' => 'date',
            'record_date' => 'date',
        ];
    }
}
