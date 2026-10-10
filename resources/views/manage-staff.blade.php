<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Staff | Chicago Chicken City</title>
    <link rel="stylesheet" href="{{ asset('css/staff.css') }}">
    <script src="{{ asset('js/auth-history.js') }}" defer></script>
</head>
<body>

<nav>
    <img src="{{ asset('images/chicago-logo-transparent.png') }}"
         alt="Chicago Chicken City">
    <a href="{{ route('dashboard') }}" class="btn btn-light">
        Dashboard
    </a>
</nav>

<main>
    <div class="header">
        <div>
            <h1>Manage Staff</h1>
            <p>Manage employee accounts and access.</p>
        </div>
        <a href="{{ route('admin.staff.create') }}"
           class="btn btn-orange">
            + Add New Staff
        </a>
    </div>

    @if(session('success'))
        <div class="alert success" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Date Created</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td><span class="badge">Staff</span></td>
                        <td>{{ $employee->created_at->format('d M Y') }}</td>

<td>
    <div class="staff-actions">

        <a href="{{ route('admin.staff.edit', $employee) }}"
           class="action-edit">
            Edit
        </a>

        <form method="POST"
              action="{{ route('admin.staff.destroy', $employee) }}"
              onsubmit="return confirm('Are you sure you want to permanently delete this staff account?');">

            @csrf
            @method('DELETE')

            <button type="submit" class="action-delete">
                Delete
            </button>
        </form>

    </div>
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center">
                            No staff accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        @if($staff->previousPageUrl())
            <a href="{{ $staff->previousPageUrl() }}"
               class="btn btn-light">Previous</a>
        @endif

        @if($staff->nextPageUrl())
            <a href="{{ $staff->nextPageUrl() }}"
               class="btn btn-light">Next</a>
        @endif
    </div>
</main>

</body>
</html>