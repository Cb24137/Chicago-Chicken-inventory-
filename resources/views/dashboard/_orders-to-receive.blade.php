<section class="ccc-card ccc-card-clip {{ $span ?? '' }}" aria-labelledby="orders-to-receive-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="orders-to-receive-title" class="ccc-card-title">
                <x-icon name="purchase" />
                Orders to Receive
            </h2>
            <p class="ccc-card-subtitle">
                {{ $openOrderCount }} pending or ordered · RM {{ number_format($openOrderValue, 2) }} committed · oldest first
            </p>
        </div>
        <a href="{{ route('purchases.index') }}" class="ccc-btn ccc-btn-secondary ccc-btn-sm">
            All purchase orders
        </a>
    </div>

    <div class="ccc-card-body" style="padding: 12px 0 0;">
        @if ($ordersToReceive->isEmpty())
            <x-empty-state icon="purchase" title="No orders waiting" compact>
                Every purchase order has been received or cancelled.
                <x-slot:action>
                    <a href="{{ route('purchases.create') }}" class="ccc-btn ccc-btn-primary ccc-btn-sm">
                        <x-icon name="plus" /> Create Purchase Order
                    </a>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="ccc-table-wrap">
                <table class="ccc-table is-compact">
                    <thead>
                        <tr>
                            <th scope="col">Order / Product</th>
                            <th scope="col">Supplier / Ordered</th>
                            <th scope="col" class="is-num">Total (RM)</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="is-actions"><span class="ccc-visually-hidden">Receive</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ordersToReceive as $purchase)
                            <tr>
                                <td>
                                    <a href="{{ route('purchases.show', $purchase) }}" class="ccc-cell-title ccc-nowrap">{{ $purchase->po_number }}</a>
                                    <span class="ccc-cell-sub">{{ $purchase->item_name }} · {{ (float) $purchase->quantity }} {{ $purchase->unit }}</span>
                                </td>
                                <td>
                                    {{ $purchase->supplier?->name ?? '—' }}
                                    <span class="ccc-cell-sub ccc-nowrap">{{ $purchase->purchase_date->format('d M Y') }}</span>
                                </td>
                                <td class="is-num">{{ number_format((float) $purchase->total_cost, 2) }}</td>
                                <td><x-status-badge :status="$purchase->status" /></td>
                                <td class="is-actions">
                                    {{-- Opens the receive panel, where the delivery's expiry date can be entered --}}
                                    <a href="{{ route('purchases.show', $purchase) }}#receive"
                                       class="ccc-btn ccc-btn-success ccc-btn-sm ccc-btn-icon"
                                       title="Receive {{ $purchase->po_number }}">
                                        <x-icon name="receive" />
                                        <span class="ccc-visually-hidden">Receive {{ $purchase->po_number }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
