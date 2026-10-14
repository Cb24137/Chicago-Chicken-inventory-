@php
    // Sidebar menu: [route name, active pattern, icon, label, count badge]
    $isAdmin = auth()->user()->isAdmin();
    $unreadBadge = ($unreadNotificationCount ?? 0) ?: null;

    $navSections = $isAdmin
        // Administrators: management console
        ? [
            'Overview' => [
                ['dashboard', 'dashboard', 'dashboard', 'Executive Dashboard', null],
                ['reports.index', 'reports.*', 'chart', 'Reports & Analytics', null],
            ],
            'Business Management' => [
                ['inventory.index', 'inventory.*', 'inventory', 'Inventory Overview', null],
                ['purchases.index', 'purchases.*', 'purchase', 'Purchase Orders', null],
                ['suppliers.index', 'suppliers.*', 'supplier', 'Suppliers', null],
                ['storage-locations.index', 'storage-locations.*', 'storage', 'Storage Locations', null],
                ['waste-expiry.index', 'waste-expiry.*', 'waste', 'Waste & Expiry', null],
            ],
            'Administration' => [
                ['admin.staff.index', 'admin.staff.*', 'staff', 'Staff Management', null],
                ['notifications.index', 'notifications.*', 'bell', 'Notifications', $unreadBadge],
            ],
        ]
        // Staff: daily operations
        : [
            'Overview' => [
                ['dashboard', 'dashboard', 'dashboard', 'Dashboard', null],
            ],
            'Inventory Modules' => [
                ['inventory.index', 'inventory.*', 'inventory', 'Food & Beverage Inventory', null],
                ['suppliers.index', 'suppliers.*', 'supplier', 'Supplier Management', null],
                ['purchases.index', 'purchases.*', 'purchase', 'Purchase Orders', null],
                ['storage-locations.index', 'storage-locations.*', 'storage', 'Storage Locations', null],
                ['waste-expiry.index', 'waste-expiry.*', 'waste', 'Waste & Expiry', null],
            ],
            'Insights' => [
                ['reports.index', 'reports.*', 'chart', 'Reports & Analytics', null],
                ['notifications.index', 'notifications.*', 'bell', 'Notifications', $unreadBadge],
            ],
        ];
@endphp

<aside id="ccc-sidebar" class="ccc-sidebar" aria-label="Main navigation">
    <div class="ccc-sidebar-head">
        <span>Menu</span>
        <button type="button" class="ccc-btn ccc-btn-secondary ccc-btn-sm" data-nav-close>
            <x-icon name="close" />
            <span class="ccc-visually-hidden">Close navigation menu</span>
        </button>
    </div>

    <nav>
        @foreach ($navSections as $sectionTitle => $links)
            <div class="ccc-nav-section">
                <p class="ccc-nav-title">{{ $sectionTitle }}</p>

                <ul class="ccc-nav-list">
                    @foreach ($links as [$routeName, $activePattern, $icon, $label, $count])
                        @php $isActive = request()->routeIs($activePattern); @endphp

                        <li>
                            <a href="{{ route($routeName) }}"
                               class="ccc-nav-link {{ $isActive ? 'is-active' : '' }}"
                               @if ($isActive) aria-current="page" @endif>
                                <x-icon :name="$icon" />
                                <span>{{ $label }}</span>
                                @if ($count)
                                    <span class="ccc-nav-count">
                                        {{ $count > 99 ? '99+' : $count }}
                                        <span class="ccc-visually-hidden">unread</span>
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <div class="ccc-sidebar-foot">
        @if ($isAdmin)
            <strong>Management console</strong>
            Monitor performance, export reports and manage staff access.
        @else
            <strong>Chicago Chicken City</strong>
            Track stock, suppliers, purchases, storage and waste in one place.
        @endif
    </div>
</aside>
