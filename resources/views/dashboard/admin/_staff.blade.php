{{-- Section 5: staff management (the routes behind it are admin-only middleware) --}}
@php
    // What each role can open, as enforced by the routes, gates and middleware
    $access = [
        ['Executive dashboard', true, false],
        ['Daily operations dashboard', false, true],
        ['Inventory, suppliers, purchase orders, storage and waste', true, true],
        ['Inventory, storage and waste reports', true, true],
        ['Purchase, supplier and operational summary reports', true, false],
        ['Notifications', true, true],
        ['Staff management', true, false],
    ];
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp

<section class="ccc-card ccc-mb" aria-labelledby="staff-admin-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="staff-admin-title" class="ccc-card-title"><x-icon name="staff" /> Staff management</h2>
            <p class="ccc-card-subtitle">Accounts, roles and access levels · administrators only</p>
        </div>
        <div class="ccc-page-actions">
            <a href="{{ route('admin.staff.index') }}" class="ccc-btn ccc-btn-primary ccc-btn-sm"><x-icon name="staff" /> Manage Staff</a>
            <a href="{{ route('admin.staff.create') }}" class="ccc-btn ccc-btn-secondary ccc-btn-sm"><x-icon name="plus" /> Add Staff</a>
        </div>
    </div>

    <div class="ccc-card-body ccc-staff-grid">
        <div>
            <div class="ccc-staff-figures">
                <div>
                    <strong>{{ $staffAccounts['staff'] }}</strong>
                    <span>Staff {{ \Illuminate\Support\Str::plural('account', $staffAccounts['staff']) }}</span>
                </div>
                <div>
                    <strong>{{ $staffAccounts['admins'] }}</strong>
                    <span>{{ \Illuminate\Support\Str::plural('Administrator', $staffAccounts['admins']) }}</span>
                </div>
            </div>

            <h3 class="ccc-subheading">Recently created staff accounts</h3>
            @if ($staffAccounts['recent']->isEmpty())
                <p class="ccc-muted">No staff accounts yet. Use <strong>Add Staff</strong> to create one.</p>
            @else
                <ul class="ccc-people">
                    @foreach ($staffAccounts['recent'] as $member)
                        <li>
                            <span class="ccc-avatar" aria-hidden="true">{{ $initials($member->name) }}</span>
                            <div>
                                <strong>{{ $member->name }}</strong>
                                <span>{{ $member->email }}</span>
                            </div>
                            <time datetime="{{ $member->created_at?->toIso8601String() }}">
                                Added {{ $member->created_at?->format('d M Y') ?? '—' }}
                            </time>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <h3 class="ccc-subheading">Roles and access levels</h3>
            <div class="ccc-table-wrap">
                <table class="ccc-access-table">
                    <caption class="ccc-visually-hidden">What administrators and staff can open</caption>
                    <thead>
                        <tr>
                            <th scope="col">Area</th>
                            <th scope="col">Administrator</th>
                            <th scope="col">Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($access as [$area, $admin, $staff])
                            <tr>
                                <th scope="row">{{ $area }}</th>
                                @foreach ([$admin, $staff] as $allowed)
                                    <td class="{{ $allowed ? 'is-yes' : 'is-no' }}">
                                        <x-icon :name="$allowed ? 'check' : 'close'" />
                                        <span class="ccc-visually-hidden">{{ $allowed ? 'Yes' : 'No' }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
