@extends('layouts.app')

@section('title', 'Manage Staff')

@section('content')
    <x-page-header
        eyebrow="Administration · Admin only"
        title="Manage Staff"
        description="Manage employee accounts and access.">
        <x-slot:actions>
            <a href="{{ route('admin.staff.create') }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="plus" /> Add New Staff
            </a>
        </x-slot:actions>
    </x-page-header>

    <section class="ccc-card ccc-card-clip" aria-label="Staff accounts">
        @if ($staff->isEmpty())
            <x-empty-state icon="staff" title="No staff accounts found">
                Create a staff account so team members can use the inventory modules.
                <x-slot:action>
                    <a href="{{ route('admin.staff.create') }}" class="ccc-btn ccc-btn-primary">
                        <x-icon name="plus" /> Add New Staff
                    </a>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="ccc-table-wrap">
                <table class="ccc-table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email Address</th>
                            <th scope="col">Role</th>
                            <th scope="col">Date Created</th>
                            <th scope="col" class="is-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($staff as $employee)
                            <tr>
                                <td><span class="ccc-cell-title">{{ $employee->name }}</span></td>
                                <td>{{ $employee->email }}</td>
                                <td><span class="ccc-badge is-brand"><x-icon name="user" /> Staff</span></td>
                                <td class="ccc-nowrap">{{ $employee->created_at->format('d M Y') }}</td>
                                <td class="is-actions">
                                    <div class="ccc-table-actions">
                                        <a href="{{ route('admin.staff.edit', $employee) }}" class="ccc-btn ccc-btn-soft ccc-btn-sm">
                                            <x-icon name="edit" /> Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.staff.destroy', $employee) }}"
                                              class="ccc-inline-form"
                                              data-confirm="Are you sure you want to permanently delete this staff account?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ccc-btn ccc-btn-danger ccc-btn-sm">
                                                <x-icon name="trash" /> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $staff->links() }}
        @endif
    </section>
@endsection
