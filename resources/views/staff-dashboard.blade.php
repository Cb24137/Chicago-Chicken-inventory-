@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
    {{-- Daily operations view. Every figure comes from the database. --}}
    @php
        $plural = fn (string $word, $count) => \Illuminate\Support\Str::plural($word, $count);
        $stockTone = $stock['out'] > 0 ? 'danger' : ($stock['alerts'] > 0 ? 'warning' : 'success');
    @endphp

    <section class="ccc-hero is-compact">
        <div>
            <p class="ccc-eyebrow">Staff workspace</p>
            <h1>Welcome back, {{ $user->name }}</h1>
            <p class="ccc-hero-text">
                Here is what needs attention today: stock to reorder, deliveries to receive and products close to expiry.
            </p>
        </div>

        <div class="ccc-hero-meta">
            <span class="ccc-chip"><x-icon name="calendar" /> {{ now()->format('l, j F Y') }}</span>
            <a href="{{ route('notifications.index', $unreadCount > 0 ? ['filter' => 'unread'] : []) }}" class="ccc-chip is-link">
                <x-icon name="bell" /> {{ $unreadCount }} unread {{ $plural('notification', $unreadCount) }}
            </a>
        </div>
    </section>

    {{-- ========== KPIs ========== --}}
    <section class="ccc-section" aria-labelledby="today-title">
        <div class="ccc-section-head">
            <h2 id="today-title" class="ccc-section-title">Today's operations</h2>
            <p class="ccc-section-text">Live figures from the database</p>
        </div>

        <div class="ccc-stat-grid is-four">
            <x-stat-card
                label="Stock alerts"
                :value="number_format($stock['alerts'])"
                icon="alert"
                :href="route('inventory.index', ['stock' => 'restock'])"
                :tone="$stockTone"
                :meta="$stock['low'] . ' low · ' . $stock['out'] . ' out of stock'"
                :flag="$stock['alerts'] > 0 ? 'Needs attention' : null" />

            <x-stat-card
                label="Orders to receive"
                :value="number_format($openOrderCount)"
                icon="receive"
                :href="route('purchases.index')"
                meta="Pending or ordered purchase orders" />

            <x-stat-card
                label="Expiring soon"
                :value="number_format($expiryWatchCount)"
                icon="expiry"
                href="#expiry-watch"
                :tone="$expiryWatchCount > 0 ? 'warning' : null"
                :meta="'Deliveries within ' . $expiryDays . ' ' . $plural('day', $expiryDays) . ' or expired this week'" />

            <x-stat-card
                label="Waste this week"
                :value="number_format($wasteThisWeek)"
                icon="waste"
                :href="route('waste-expiry.index')"
                :meta="$plural('Record', $wasteThisWeek) . ' in the last 7 days'" />
        </div>
    </section>

    @include('dashboard._quick-actions')

    <div class="ccc-dash-grid">
        @include('dashboard._low-stock', ['span' => 'ccc-span-5'])
        @include('dashboard._orders-to-receive', ['span' => 'ccc-span-7'])
    </div>

    <div class="ccc-dash-grid">
        <div class="ccc-span-7 ccc-stack">
            @include('dashboard._expiry-waste')
            @include('dashboard._activity')
        </div>

        <div class="ccc-span-5 ccc-stack">
            @include('dashboard._stock-status')
            @include('dashboard._notifications')
        </div>
    </div>
@endsection
