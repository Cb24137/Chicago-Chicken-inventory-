<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    // The old quantity, unit and total_cost columns are kept in the
    // database for historical records, but are no longer edited here.
    // Order quantities and costs now belong to purchase orders.
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'products_supplied',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
