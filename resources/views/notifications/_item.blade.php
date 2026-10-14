{{-- One in-app notification. $actions adds the Open / Mark as read buttons. --}}
@php
    $data = $notification->data;
    $isUnread = $notification->read_at === null;
    $categoryLabel = \App\Notifications\InventoryAlert::CATEGORIES[$data['category'] ?? ''] ?? 'General';
@endphp

<li class="ccc-note {{ $isUnread ? 'is-unread' : '' }}">
    <x-notification-icon :notification="$notification" />

    <div class="ccc-note-body">
        <p class="ccc-note-title">
            <a href="{{ route('notifications.open', $notification->id) }}">{{ $data['title'] ?? 'Notification' }}</a>
            @if ($isUnread)
                <span class="ccc-badge is-brand ccc-badge-sm">New</span>
            @endif
        </p>
        <p class="ccc-note-text">{{ $data['message'] ?? '' }}</p>
        <p class="ccc-note-meta">
            <span class="ccc-badge is-neutral ccc-badge-sm">{{ $categoryLabel }}</span>
            <time datetime="{{ $notification->created_at->toIso8601String() }}"
                  title="{{ $notification->created_at->format('d M Y, g:i A') }}">
                {{ $notification->created_at->diffForHumans() }}
            </time>
            @unless ($isUnread)
                <span aria-hidden="true">·</span>
                <span>Read {{ $notification->read_at->diffForHumans() }}</span>
            @endunless
        </p>
    </div>

    @if ($actions ?? false)
        <div class="ccc-note-actions">
            <a href="{{ route('notifications.open', $notification->id) }}" class="ccc-btn ccc-btn-secondary ccc-btn-sm">
                <x-icon name="eye" /> Open
            </a>

            @if ($isUnread)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="ccc-inline-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="ccc-btn ccc-btn-soft ccc-btn-sm">
                        <x-icon name="check" /> Mark as read
                    </button>
                </form>
            @endif
        </div>
    @endif
</li>
