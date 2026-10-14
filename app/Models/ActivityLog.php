<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    // Module key => label shown on the dashboard
    public const MODULES = [
        'inventory' => 'Inventory',
        'supplier' => 'Suppliers',
        'purchase' => 'Purchase Orders',
        'storage' => 'Storage',
        'waste' => 'Waste & Expiry',
    ];

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'subject_type',
        'subject_id',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Save one activity entry for the signed-in user
    public static function record(string $module, string $action, Model $subject, string $description): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'description' => Str::limit($description, 250),
        ]);
    }

    public function moduleLabel(): string
    {
        return self::MODULES[$this->module] ?? ucfirst($this->module);
    }
}
