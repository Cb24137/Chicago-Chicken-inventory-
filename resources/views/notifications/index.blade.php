@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();

        // Keep the other filter when switching one of them
        $link = fn (array $changes) => route('notifications.index', array_filter(array_merge(
            ['filter' => $filter === 'unread' ? 'unread' : null, 'category' => $category],
            $changes
        )));
    @endphp

    <x-page-header
        eyebrow="Notification Center"
        title="Notifications"
        :description="'In-app alerts about stock levels, expiry dates, purchase orders and waste' . ($isAdmin ? ', and staff accounts' : '') . '. Read notifications stay here as your history.'">
        @if ($unreadCount > 0)
            <x-slot:actions>
                <form method="POST" action="{{ route('notifications.read-all') }}" class="ccc-inline-form">
                    @csrf
                    <button type="submit" class="ccc-btn ccc-btn-primary">
                        <x-icon name="check" /> Mark all as read
                    </button>
                </form>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="ccc-grid-2">
        <section class="ccc-card ccc-card-clip" aria-labelledby="notification-list-title">
            <h2 id="notification-list-title" class="ccc-visually-hidden">Notification history</h2>

            <div class="ccc-note-toolbar">
                <nav class="ccc-tabs is-inline" aria-label="Read status">
                    <a href="{{ $link(['filter' => null]) }}"
                       class="ccc-tab {{ $filter === 'all' ? 'is-active' : '' }}"
                       @if ($filter === 'all') aria-current="page" @endif>
                        All <span class="ccc-tab-count">{{ $totalCount }}</span>
                    </a>
                    <a href="{{ $link(['filter' => 'unread']) }}"
                       class="ccc-tab {{ $filter === 'unread' ? 'is-active' : '' }}"
                       @if ($filter === 'unread') aria-current="page" @endif>
                        Unread <span class="ccc-tab-count">{{ $unreadCount }}</span>
                    </a>
                </nav>

                <nav class="ccc-pill-group" aria-label="Category">
                    <a href="{{ $link(['category' => null]) }}"
                       class="ccc-pill {{ $category ? '' : 'is-active' }}"
                       @unless ($category) aria-current="true" @endunless>
                        All categories
                    </a>
                    @foreach ($categories as $key => $label)
                        <a href="{{ $link(['category' => $key]) }}"
                           class="ccc-pill {{ $category === $key ? 'is-active' : '' }}"
                           @if ($category === $key) aria-current="true" @endif>
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>

            @if ($notifications->isEmpty())
                @if ($filter === 'unread' && ! $category)
                    <x-empty-state icon="check-circle" title="You're all caught up">
                        There are no unread notifications.
                        <x-slot:action>
                            <a href="{{ route('notifications.index') }}" class="ccc-btn ccc-btn-secondary">View all notifications</a>
                        </x-slot:action>
                    </x-empty-state>
                @elseif ($category || $filter === 'unread')
                    <x-empty-state icon="search" title="No notifications match these filters">
                        Try another category or show all notifications.
                        <x-slot:action>
                            <a href="{{ route('notifications.index') }}" class="ccc-btn ccc-btn-secondary">Clear filters</a>
                        </x-slot:action>
                    </x-empty-state>
                @else
                    <x-empty-state icon="bell" title="No notifications yet">
                        Stock, expiry and purchase order alerts will appear here as they happen.
                    </x-empty-state>
                @endif
            @else
                <ul class="ccc-note-list is-page">
                    @foreach ($notifications as $notification)
                        @include('notifications._item', ['actions' => true])
                    @endforeach
                </ul>

                {{ $notifications->links() }}
            @endif
        </section>

        <aside class="ccc-card" aria-labelledby="notification-help-title">
            <div class="ccc-card-header">
                <div>
                    <h2 id="notification-help-title" class="ccc-card-title">
                        <x-icon name="info" />
                        When you are notified
                    </h2>
                    <p class="ccc-card-subtitle">Alerts are shown in this system only. No emails or text messages are sent.</p>
                </div>
            </div>

            <div class="ccc-card-body">
                <ul class="ccc-help-list">
                    <li>
                        <x-notification-icon :notification="(object) ['data' => ['category' => 'stock', 'severity' => 'warning']]" />
                        <span><strong>Low or out of stock</strong> Once when an item reaches its minimum level and again if it runs out. The alert re-arms after restocking.</span>
                    </li>
                    <li>
                        <x-notification-icon :notification="(object) ['data' => ['category' => 'expiry', 'severity' => 'warning']]" />
                        <span><strong>Approaching expiry</strong> Once when a received delivery is within {{ config('inventory.expiry_alert_days') }} days of its expiry date.</span>
                    </li>
                    <li>
                        <x-notification-icon :notification="(object) ['data' => ['category' => 'purchase', 'severity' => 'success']]" />
                        <span>
                            <strong>Purchase orders</strong>
                            {{ $isAdmin ? 'When an order is created, placed, received or cancelled.' : 'When an order is marked as received.' }}
                        </span>
                    </li>
                    @if ($isAdmin)
                        <li>
                            <x-notification-icon :notification="(object) ['data' => ['category' => 'waste', 'severity' => 'warning']]" />
                            <span><strong>Waste recorded</strong> When anyone records wasted, expired or damaged stock.</span>
                        </li>
                        <li>
                            <x-notification-icon :notification="(object) ['data' => ['category' => 'staff', 'severity' => 'info']]" />
                            <span><strong>Staff accounts</strong> When another administrator adds or removes a staff account.</span>
                        </li>
                    @endif
                </ul>
                <p class="ccc-help">You are not notified about changes you make yourself.</p>
            </div>
        </aside>
    </div>
@endsection
