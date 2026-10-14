@php
    $currentUser = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($currentUser->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<header class="ccc-topbar">
    <button type="button"
            class="ccc-menu-toggle"
            aria-controls="ccc-sidebar"
            aria-expanded="false"
            data-nav-toggle>
        <x-icon name="menu" />
        <span class="ccc-visually-hidden">Open navigation menu</span>
    </button>

    <a href="{{ route('dashboard') }}" class="ccc-brand">
        <img src="{{ asset('images/chicago-logo-transparent.png') }}"
             alt="Chicago Chicken City">
        <span class="ccc-brand-text">
            <strong>Inventory System</strong>
            <small>Food &amp; Beverage Management</small>
        </span>
    </a>

    <div class="ccc-topbar-actions">
        @php
            $unread = $unreadNotificationCount ?? 0;
            $latest = $latestNotifications ?? collect();
        @endphp

        {{-- Notification bell --}}
        <div class="ccc-bell" data-bell>
            <button type="button"
                    class="ccc-bell-button"
                    aria-expanded="false"
                    aria-controls="ccc-bell-panel"
                    data-bell-toggle>
                <x-icon name="bell" />
                @if ($unread > 0)
                    <span class="ccc-bell-count" aria-hidden="true">{{ $unread > 99 ? '99+' : $unread }}</span>
                @endif
                <span class="ccc-visually-hidden">Notifications, {{ $unread }} unread</span>
            </button>

            <div class="ccc-bell-panel" id="ccc-bell-panel" hidden>
                <div class="ccc-bell-head">
                    <strong>Notifications</strong>
                    @if ($unread > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="ccc-bell-link">Mark all as read</button>
                        </form>
                    @endif
                </div>

                <div class="ccc-bell-list">
                    @forelse ($latest as $notification)
                        <a href="{{ route('notifications.open', $notification->id) }}"
                           class="ccc-bell-item {{ $notification->read_at ? '' : 'is-unread' }}">
                            <x-notification-icon :notification="$notification" />
                            <span class="ccc-bell-text">
                                <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                <span>{{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 110) }}</span>
                                <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                            </span>
                        </a>
                    @empty
                        <p class="ccc-bell-empty">You're all caught up. New alerts will appear here.</p>
                    @endforelse
                </div>

                <a href="{{ route('notifications.index') }}" class="ccc-bell-footer">View all notifications</a>
            </div>
        </div>

        <div class="ccc-user">
            <span class="ccc-avatar" aria-hidden="true">{{ $initials }}</span>
            <span class="ccc-user-text">
                <strong>{{ $currentUser->name }}</strong>
                <span class="ccc-role-badge">{{ $currentUser->isAdmin() ? 'Administrator' : 'Staff' }}</span>
            </span>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="ccc-logout-form">
            @csrf
            <button type="submit" class="ccc-btn ccc-btn-secondary ccc-btn-sm" aria-label="Logout">
                <x-icon name="logout" />
                <span class="ccc-logout-label">Logout</span>
            </button>
        </form>
    </div>
</header>
