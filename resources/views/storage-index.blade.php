<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Storage Locations | Chicago Chicken City</title>
    <link rel="stylesheet" href="{{ asset('css/storage.css') }}">
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
            <h1>Storage Locations</h1>
            <p>Manage food and beverage storage areas.</p>
        </div>

        <a href="{{ route('storage-locations.create') }}"
           class="btn btn-orange">
            + Add Storage Location
        </a>
    </div>

    @if(session('success'))
        <div class="alert success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Location Name</th>
                    <th>Storage Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->type }}</td>
                        <td>{{ $location->description ?? '-' }}</td>

                        <td>
                            <span class="badge {{ $location->is_active ? 'active' : 'inactive' }}">
                                {{ $location->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td>
                            <div class="actions">
                                <a href="{{ route('storage-locations.edit', $location) }}"
                                   class="btn btn-edit">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('storage-locations.destroy', $location) }}"
                                      onsubmit="return confirm('Delete this storage location?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-delete">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px">
                            No storage locations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        @if($locations->previousPageUrl())
            <a href="{{ $locations->previousPageUrl() }}"
               class="btn btn-light">Previous</a>
        @else
            <span></span>
        @endif

        @if($locations->nextPageUrl())
            <a href="{{ $locations->nextPageUrl() }}"
               class="btn btn-light">Next</a>
        @endif
    </div>
</main>

</body>
</html>