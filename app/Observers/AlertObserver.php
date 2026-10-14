<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\User;
use App\Models\WasteExpiryRecord;
use App\Services\AlertService;
use App\Support\Quiet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

// Turns real business actions into notifications (see AlertService)
class AlertObserver
{
    public function __construct(private AlertService $alerts)
    {
    }

    public function created(Model $model): void
    {
        if (Quiet::active()) {
            return;
        }

        if ($model instanceof InventoryItem) {
            $this->alerts->checkStock($model);
        } elseif ($model instanceof Purchase) {
            // An order created as already received is reported as received
            $model->status === 'received'
                ? $this->alerts->purchaseStatusChanged($model)
                : $this->alerts->purchaseCreated($model);
            $this->alerts->checkExpiry($model);
        } elseif ($model instanceof WasteExpiryRecord) {
            $this->alerts->wasteRecorded($model);
        } elseif ($model instanceof User) {
            $this->staffAccountChanged($model, 'created');
        }
    }

    public function updated(Model $model): void
    {
        if (Quiet::active()) {
            return;
        }

        if ($model instanceof InventoryItem && $model->wasChanged(['quantity', 'minimum_stock'])) {
            $this->alerts->checkStock($model);
        } elseif ($model instanceof Purchase) {
            if ($model->wasChanged('status')) {
                $this->alerts->purchaseStatusChanged($model);
            }

            if ($model->wasChanged(['status', 'expiry_date'])) {
                $this->alerts->checkExpiry($model);
            }
        }
    }

    public function deleted(Model $model): void
    {
        if (! Quiet::active() && $model instanceof User) {
            $this->staffAccountChanged($model, 'deleted');
        }
    }

    // Only staff accounts managed by an admin in the app are reported
    private function staffAccountChanged(User $user, string $action): void
    {
        // New accounts get their role from the database default ("staff"),
        // so it is not on the model yet right after it is created
        $role = $user->role ?? User::whereKey($user->getKey())->value('role');

        if ($role === 'staff' && Auth::user()?->isAdmin()) {
            $this->alerts->staffAccountChanged($user, $action);
        }
    }
}
