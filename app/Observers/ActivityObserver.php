<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\WasteExpiryRecord;
use App\Support\Quiet;
use Illuminate\Database\Eloquent\Model;

// Records create, update and delete actions from all four
// inventory modules for the dashboard's "Recent Activity" list.
class ActivityObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'created', 'Added');
    }

    public function updated(Model $model): void
    {
        $changed = array_diff(
            array_keys($model->getChanges()),
            ['created_at', 'updated_at', 'received_at', 'expiry_alert_sent_at']
        );

        if ($changed !== []) {
            $this->log($model, 'updated', 'Updated', $changed);
        }
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', 'Deleted');
    }

    private function log(Model $model, string $action, string $verb, array $changedFields = []): void
    {
        if (Quiet::active()) {
            return;
        }

        [$module, $subject] = match (true) {
            $model instanceof InventoryItem => ['inventory', "inventory item “{$model->item_name}”"],
            $model instanceof Supplier => ['supplier', "supplier “{$model->name}”"],
            $model instanceof Purchase => ['purchase', "purchase order {$model->po_number} ({$model->item_name})"],
            $model instanceof StorageLocation => ['storage', "storage location “{$model->name}”"],
            $model instanceof WasteExpiryRecord => ['waste', "waste record for “{$model->item_name}”"],
        };

        $description = "{$verb} {$subject}";

        if ($changedFields !== []) {
            $description .= ': ' . implode(', ', array_map(
                fn ($field) => str_replace('_', ' ', $field),
                $changedFields
            ));
        }

        ActivityLog::record($module, $action, $model, $description);
    }
}
