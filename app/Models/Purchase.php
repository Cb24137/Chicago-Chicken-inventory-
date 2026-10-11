<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'item_name',
        'quantity',
        'unit',
        'total_cost',
        'purchase_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    // Each purchase belongs to one supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
