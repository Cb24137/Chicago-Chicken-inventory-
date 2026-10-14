<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// One in-app notification (stored in the notifications table).
// Nothing is emailed or texted.
class InventoryAlert extends Notification
{
    // Category key => label used for filtering in the Notification Center
    public const CATEGORIES = [
        'stock' => 'Stock levels',
        'expiry' => 'Expiry',
        'purchase' => 'Purchase orders',
        'waste' => 'Waste',
        'staff' => 'Staff accounts',
    ];

    public function __construct(
        public string $category,
        public string $severity,   // info, success, warning or critical
        public string $title,
        public string $message,
        public ?string $url = null, // relative link to the related record
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category,
            'severity' => $this->severity,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
