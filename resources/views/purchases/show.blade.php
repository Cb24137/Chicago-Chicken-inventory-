@extends('layouts.app')

@section('title', $purchase->po_number)

@section('content')
    @php
        $item = $purchase->inventoryItem;
        $supplier = $purchase->supplier;
        $stockPreview = $item
            ? \App\Models\Purchase::convertToInventoryUnit((float) $purchase->quantity, $purchase->unit, $item->unit)
            : null;
        $expiryDays = (int) config('inventory.expiry_alert_days');
        $daysLeft = $purchase->daysUntilExpiry();
    @endphp

    <x-page-header
        eyebrow="Purchase Order"
        :title="$purchase->po_number"
        :description="$purchase->item_name . ' from ' . ($supplier?->name ?? 'unknown supplier') . ', ordered on ' . $purchase->purchase_date->format('d M Y') . '.'"
        :back="route('purchases.index')"
        back-label="Back to purchase orders">
        <x-slot:actions>
            @if ($purchase->canBeReceived())
                <a href="#receive" class="ccc-btn ccc-btn-success">
                    <x-icon name="receive" /> Mark as Received
                </a>
            @endif

            <a href="{{ route('purchases.edit', $purchase) }}" class="ccc-btn ccc-btn-secondary">
                <x-icon name="edit" /> Edit
            </a>

            @unless ($purchase->hasAddedStock())
                <form method="POST"
                      action="{{ route('purchases.destroy', $purchase) }}"
                      class="ccc-inline-form"
                      data-confirm="Delete purchase order {{ $purchase->po_number }}? This cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ccc-btn ccc-btn-danger">
                        <x-icon name="trash" /> Delete
                    </button>
                </form>
            @endunless
        </x-slot:actions>
    </x-page-header>

    <div class="ccc-grid-2">
        <section class="ccc-card" aria-labelledby="order-details-title">
            <div class="ccc-card-header" style="padding-bottom: 14px;">
                <h2 id="order-details-title" class="ccc-card-title">
                    <x-icon name="purchase" /> Order details
                </h2>
                <x-status-badge :status="$purchase->status" />
            </div>

            <dl class="ccc-details">
                <div>
                    <dt>Purchase order number</dt>
                    <dd>{{ $purchase->po_number }}</dd>
                </div>
                <div>
                    <dt>Purchase date</dt>
                    <dd>{{ $purchase->purchase_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt>Supplier</dt>
                    <dd>{{ $supplier?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Product</dt>
                    <dd>{{ $purchase->item_name }}</dd>
                </div>
                <div>
                    <dt>Quantity</dt>
                    <dd>{{ (float) $purchase->quantity }} {{ \App\Models\Purchase::UNITS[$purchase->unit] ?? $purchase->unit }}</dd>
                </div>
                <div>
                    <dt>Unit price</dt>
                    <dd>RM {{ number_format((float) $purchase->unit_price, 2) }}</dd>
                </div>
                <div>
                    <dt>Total cost</dt>
                    <dd style="font-size: 20px;">RM {{ number_format((float) $purchase->total_cost, 2) }}</dd>
                </div>
                <div>
                    <dt>Inventory item</dt>
                    <dd>
                        @if ($item)
                            <a href="{{ route('inventory.edit', $item->id) }}" class="ccc-text-link">{{ $item->item_name }}</a>
                        @else
                            Not linked
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Received on</dt>
                    <dd>{{ $purchase->received_at?->format('d M Y') ?? 'Not received yet' }}</dd>
                </div>
                <div>
                    <dt>Expiry date</dt>
                    <dd>
                        @if ($purchase->expiry_date)
                            {{ $purchase->expiry_date->format('d M Y') }}
                            @if ($purchase->status === 'received' && $daysLeft < 0)
                                <span class="ccc-badge is-danger ccc-badge-sm"><x-icon name="expiry" /> Expired</span>
                            @elseif ($purchase->status === 'received' && $daysLeft <= $expiryDays)
                                <span class="ccc-badge is-warning ccc-badge-sm">
                                    <x-icon name="expiry" />
                                    {{ $daysLeft === 0 ? 'Expires today' : 'Expires in ' . $daysLeft . ' ' . \Illuminate\Support\Str::plural('day', $daysLeft) }}
                                </span>
                            @endif
                        @else
                            Not recorded
                        @endif
                    </dd>
                </div>
                <div class="is-full">
                    <dt>Notes</dt>
                    <dd class="is-plain">{{ $purchase->notes ?: 'No notes added.' }}</dd>
                </div>
            </dl>
        </section>

        <div class="ccc-stack">
            <section id="receive" class="ccc-card" aria-labelledby="receiving-title">
                <div class="ccc-card-header">
                    <h2 id="receiving-title" class="ccc-card-title">
                        <x-icon name="receive" /> Receiving &amp; stock
                    </h2>
                </div>

                <div class="ccc-card-body">
                    @if ($purchase->canBeReceived())
                        <form method="POST"
                              action="{{ route('purchases.receive', $purchase) }}"
                              data-confirm="Confirm that the goods for {{ $purchase->po_number }} have been received?{{ $stockPreview !== null ? ' ' . $stockPreview . ' ' . $item->unit . ' will be added to “' . $item->item_name . '” stock.' : '' }}">
                            @csrf
                            @method('PATCH')

                            <p class="ccc-muted ccc-mb">
                                @if ($stockPreview !== null)
                                    When the goods arrive, select <strong>Mark as Received</strong>.
                                    {{ $stockPreview }} {{ $item->unit }} will be added to “{{ $item->item_name }}” stock
                                    (currently {{ (float) $item->quantity }} {{ $item->unit }}).
                                @elseif ($item)
                                    “{{ $item->item_name }}” is stocked in {{ $item->unit }}, so this order's unit
                                    ({{ $purchase->unit }}) cannot be added to its stock. Edit the order's unit before receiving it.
                                @else
                                    This order is not linked to an inventory item. Marking it as received
                                    will not change stock levels.
                                @endif
                            </p>

                            <div class="ccc-field ccc-mb">
                                <label for="receive_expiry_date" class="ccc-label">
                                    Expiry date of this delivery <span class="ccc-optional">(optional)</span>
                                </label>
                                <input type="date" id="receive_expiry_date" name="expiry_date"
                                       value="{{ old('expiry_date', $purchase->expiry_date?->format('Y-m-d')) }}"
                                       class="ccc-input @error('expiry_date') is-invalid @enderror">
                                <x-field-error name="expiry_date" />
                                <p class="ccc-help">For perishable goods. Everyone is alerted {{ $expiryDays }} days before this date.</p>
                            </div>

                            <button type="submit" class="ccc-btn ccc-btn-success">
                                <x-icon name="receive" /> Mark as Received
                            </button>
                        </form>
                    @elseif ($purchase->hasAddedStock())
                        <div class="ccc-alert is-success ccc-mb-0">
                            <x-icon name="check-circle" />
                            <div class="ccc-alert-content">
                                {{ (float) $purchase->stock_added_quantity }} {{ $item?->unit }} added to
                                “{{ $item?->item_name ?? $purchase->item_name }}” stock on
                                {{ $purchase->stock_added_at->format('d M Y, g:i A') }}.
                            </div>
                        </div>
                    @elseif ($purchase->status === 'received')
                        <div class="ccc-alert is-info ccc-mb-0">
                            <x-icon name="info" />
                            <div class="ccc-alert-content">
                                Received{{ $purchase->received_at ? ' on ' . $purchase->received_at->format('d M Y') : '' }}.
                                This order is not linked to an inventory item, so stock levels were not changed.
                            </div>
                        </div>
                    @elseif ($purchase->status === 'cancelled')
                        <div class="ccc-alert is-warning ccc-mb-0">
                            <x-icon name="alert" />
                            <div class="ccc-alert-content">This order was cancelled. No stock will be added.</div>
                        </div>
                    @endif
                </div>
            </section>

            @if ($supplier)
                <section class="ccc-card" aria-labelledby="supplier-contact-title">
                    <div class="ccc-card-header">
                        <h2 id="supplier-contact-title" class="ccc-card-title">
                            <x-icon name="supplier" /> Supplier contact
                        </h2>
                        <a href="{{ route('purchases.index', ['supplier_id' => $supplier->id]) }}" class="ccc-card-link">
                            All orders <x-icon name="arrow-right" />
                        </a>
                    </div>

                    <dl class="ccc-details" style="margin-top: 12px;">
                        <div class="is-full">
                            <dt>Name</dt>
                            <dd>{{ $supplier->name }}</dd>
                        </div>
                        <div class="is-full">
                            <dt>Phone</dt>
                            <dd>{{ $supplier->phone ?: '—' }}</dd>
                        </div>
                        <div class="is-full">
                            <dt>Email</dt>
                            <dd>{{ $supplier->email ?: '—' }}</dd>
                        </div>
                        <div class="is-full">
                            <dt>Address</dt>
                            <dd class="is-plain">{{ $supplier->address ?: '—' }}</dd>
                        </div>
                    </dl>
                </section>
            @endif
        </div>
    </div>
@endsection
