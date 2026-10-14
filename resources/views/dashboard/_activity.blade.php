@php
    $activityIcons = ['created' => 'plus', 'updated' => 'edit', 'deleted' => 'trash', 'received' => 'receive'];
@endphp

<section class="ccc-card {{ $span ?? '' }}" aria-labelledby="activity-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="activity-title" class="ccc-card-title">
                <x-icon name="activity" />
                Recent Inventory Activities
            </h2>
            <p class="ccc-card-subtitle">Latest changes across inventory, suppliers, purchases, storage and waste</p>
        </div>
    </div>

    <div class="ccc-card-body">
        @if ($recentActivities->isEmpty())
            <x-empty-state icon="activity" title="No activity recorded yet" compact>
                Adding, editing, deleting or receiving records in any module will be listed here.
            </x-empty-state>
        @else
            <ul class="ccc-activity">
                @foreach ($recentActivities as $activity)
                    <li class="ccc-activity-item">
                        <span class="ccc-activity-icon is-{{ $activity->action }}">
                            <x-icon :name="$activityIcons[$activity->action] ?? 'activity'" />
                        </span>
                        <div>
                            <p class="ccc-activity-text">{{ $activity->description }}</p>
                            <p class="ccc-activity-meta">
                                <span class="ccc-badge is-neutral ccc-badge-sm">{{ $activity->moduleLabel() }}</span>
                                <span>{{ $activity->user?->name ?? 'Unknown user' }}</span>
                                <span aria-hidden="true">·</span>
                                <time datetime="{{ $activity->created_at->toIso8601String() }}"
                                      title="{{ $activity->created_at->format('d M Y, g:i A') }}">
                                    {{ $activity->created_at->diffForHumans() }}
                                </time>
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
