<section class="ccc-card {{ $span ?? '' }}" aria-labelledby="latest-notifications-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="latest-notifications-title" class="ccc-card-title">
                <x-icon name="bell" />
                Your notifications
            </h2>
            <p class="ccc-card-subtitle">
                {{ $unreadCount }} unread · stock, expiry and purchase order alerts
            </p>
        </div>
        <a href="{{ route('notifications.index') }}" class="ccc-card-link">
            Notification Center <x-icon name="arrow-right" />
        </a>
    </div>

    <div class="ccc-card-body">
        @if ($notifications->isEmpty())
            <x-empty-state icon="bell" title="No notifications yet" compact>
                Stock, expiry and delivery alerts will appear here.
            </x-empty-state>
        @else
            <ul class="ccc-note-list">
                @foreach ($notifications as $notification)
                    @include('notifications._item')
                @endforeach
            </ul>
        @endif
    </div>
</section>
