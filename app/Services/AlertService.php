<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\User;
use App\Models\WasteExpiryRecord;
use App\Notifications\InventoryAlert;
use App\Support\Quiet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// Decides when an in-app notification is due and who receives it.
// The person who performed an action is never notified about it.
class AlertService
{
    // False while the daily check runs: it is not anyone's action, so the
    // person whose dashboard visit started it must still be notified
    private bool $excludeActor = true;

    // ---------- Stock levels ----------

    // Alerts once when an item becomes low or runs out, and re-arms the
    // alert after the item is restocked above its minimum.
    public function checkStock(InventoryItem $item): void
    {
        $severity = ['low' => 1, 'out' => 2];
        $level = $item->stockLevel();
        $alerted = $item->stock_alert_level;

        if ($level === $alerted) {
            return;
        }

        if ($level !== null && $severity[$level] > ($severity[$alerted] ?? 0)) {
            $this->send($this->everyone(), $level === 'out'
                ? new InventoryAlert(
                    'stock', 'critical', 'Out of stock',
                    "Critical: {$item->item_name} is out of stock.",
                    route('inventory.index', ['search' => $item->item_name], false)
                )
                : new InventoryAlert(
                    'stock', 'warning', 'Low stock',
                    "Warning: {$item->item_name} has reached its minimum stock level "
                    . "({$this->amount($item->quantity)} {$item->unit} left, minimum {$this->amount($item->minimum_stock)} {$item->unit}).",
                    route('inventory.index', ['search' => $item->item_name], false)
                ));
        }

        // Remember the current level (clears it once the item is restocked)
        DB::table('inventory_items')->where('id', $item->id)->update(['stock_alert_level' => $level]);
        $item->stock_alert_level = $level;
        $item->syncOriginalAttribute('stock_alert_level');
    }

    // ---------- Purchase orders ----------

    public function purchaseCreated(Purchase $purchase): void
    {
        $this->send($this->admins(), new InventoryAlert(
            'purchase', 'info', 'New purchase order',
            "New purchase order {$purchase->po_number} has been created: {$this->orderSummary($purchase)} (status: {$purchase->statusLabel()}).",
            route('purchases.show', $purchase, false)
        ));
    }

    public function purchaseStatusChanged(Purchase $purchase): void
    {
        $alert = match ($purchase->status) {
            'ordered' => [$this->admins(), 'info', 'Order placed', "Purchase order {$purchase->po_number} has been ordered from {$purchase->supplier?->name}."],
            'received' => [$this->everyone(), 'success', 'Order received', "Purchase order {$purchase->po_number} has been marked as received: {$this->orderSummary($purchase)}."],
            'cancelled' => [$this->admins(), 'warning', 'Order cancelled', "Purchase order {$purchase->po_number} has been cancelled."],
            default => null,
        };

        if ($alert) {
            [$users, $severity, $title, $message] = $alert;
            $this->send($users, new InventoryAlert('purchase', $severity, $title, $message, route('purchases.show', $purchase, false)));
        }
    }

    // ---------- Expiry ----------

    // Alerts once when a received delivery is within the expiry window
    public function checkExpiry(Purchase $purchase): void
    {
        $days = $purchase->daysUntilExpiry();

        if ($purchase->status !== 'received' || $days === null || $purchase->expiry_alert_sent_at
            || $days > config('inventory.expiry_alert_days') || $days < -7) {
            return;
        }

        $date = $purchase->expiry_date->format('d M Y');
        $when = match (true) {
            $days < 0 => "expired on {$date}",
            $days === 0 => 'expires today',
            $days === 1 => "expires tomorrow ({$date})",
            default => "expires in {$days} days ({$date})",
        };

        $this->send($this->everyone(), new InventoryAlert(
            'expiry', $days < 0 ? 'critical' : 'warning', $days < 0 ? 'Delivery expired' : 'Approaching expiry',
            "Warning: {$purchase->item_name} from {$purchase->po_number} {$when}.",
            route('purchases.show', $purchase, false)
        ));

        DB::table('purchases')->where('id', $purchase->id)->update(['expiry_alert_sent_at' => now()]);
        $purchase->expiry_alert_sent_at = now();
        $purchase->syncOriginalAttribute('expiry_alert_sent_at');
    }

    // ---------- Waste and staff ----------

    public function wasteRecorded(WasteExpiryRecord $record): void
    {
        $this->send($this->admins(), new InventoryAlert(
            'waste', 'warning', 'Waste recorded',
            "New waste record: {$this->amount($record->quantity)} {$record->unit} of {$record->item_name} ({$record->reason}).",
            route('waste-expiry.show', $record, false)
        ));
    }

    public function staffAccountChanged(User $staff, string $action): void
    {
        $this->send($this->admins(), new InventoryAlert(
            'staff', 'info',
            $action === 'created' ? 'Staff account created' : 'Staff account removed',
            $action === 'created'
                ? "A staff account was created for {$staff->name} ({$staff->email})."
                : "The staff account for {$staff->name} ({$staff->email}) was deleted.",
            route('admin.staff.index', [], false)
        ));
    }

    // ---------- Daily check ----------

    // XAMPP does not run Laravel's scheduler, so this runs on the first
    // dashboard visit each day (and from "php artisan inventory:check-alerts").
    // Returns the number of notifications sent.
    public function runDailyChecks(bool $force = false): int
    {
        if (! $force && ! Cache::add('inventory-alerts-checked-' . today()->toDateString(), true, now()->endOfDay())) {
            return 0;
        }

        $before = DB::table('notifications')->count();
        $this->excludeActor = false;

        try {
            InventoryItem::query()->get()->each(fn (InventoryItem $item) => $this->checkStock($item));

            Purchase::expiryWatch()->whereNull('expiry_alert_sent_at')->get()
                ->each(fn (Purchase $purchase) => $this->checkExpiry($purchase));
        } finally {
            $this->excludeActor = true;
        }

        return DB::table('notifications')->count() - $before;
    }

    // ---------- Helpers ----------

    private function everyone(): Collection
    {
        return $this->recipients(adminsOnly: false);
    }

    private function admins(): Collection
    {
        return $this->recipients(adminsOnly: true);
    }

    private function recipients(bool $adminsOnly): Collection
    {
        return User::query()
            ->when($adminsOnly, fn ($query) => $query->where('role', 'admin'))
            ->when($this->excludeActor ? auth()->id() : null, fn ($query, $actorId) => $query->whereKeyNot($actorId))
            ->get();
    }

    private function send(Collection $users, InventoryAlert $alert): void
    {
        if (! Quiet::active() && $users->isNotEmpty()) {
            Notification::send($users, $alert);
        }
    }

    private function orderSummary(Purchase $purchase): string
    {
        return "{$purchase->item_name}, {$this->amount($purchase->quantity)} {$purchase->unit}, RM "
            . number_format((float) $purchase->total_cost, 2) . ' from ' . ($purchase->supplier?->name ?? 'unknown supplier');
    }

    private function amount($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
