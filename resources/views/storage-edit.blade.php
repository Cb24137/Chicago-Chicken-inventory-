<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Storage Location | Chicago Chicken City</title>
    <link rel="stylesheet" href="{{ asset('css/storage.css') }}">
    <script src="{{ asset('js/auth-history.js') }}" defer></script>
</head>
<body>

<nav>
    <img src="{{ asset('images/chicago-logo-transparent.png') }}"
         alt="Chicago Chicken City">

    <a href="{{ route('storage-locations.index') }}"
       class="btn btn-light">Back to Locations</a>
</nav>

<main>
    <div class="header">
        <div>
            <h1>Edit Storage Location</h1>
            <p>Update storage location information.</p>
        </div>
    </div>

    <div class="card form-card">

        @if($errors->any())
            <div class="alert error" role="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('storage-locations.update', $storageLocation) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Storage Location Name</label>
                <input id="name" type="text" name="name"
                       value="{{ old('name', $storageLocation->name) }}"
                       maxlength="255" required>
            </div>

            <div class="form-group">
                <label for="type">Storage Type</label>
                <select id="type" name="type" required>
                    @foreach(['Freezer', 'Refrigerator', 'Dry Storage'] as $type)
                        <option value="{{ $type }}"
                            @selected(old('type', $storageLocation->type) === $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          maxlength="1000">{{ old('description', $storageLocation->description) }}</textarea>
            </div>

            <div class="form-group">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active" required>
                    <option value="1"
                        @selected((string) old('is_active', (int) $storageLocation->is_active) === '1')>
                        Active
                    </option>
                    <option value="0"
                        @selected((string) old('is_active', (int) $storageLocation->is_active) === '0')>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-orange">
                    Save Changes
                </button>

                <a href="{{ route('storage-locations.index') }}"
                   class="btn btn-light">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</main>

</body>
</html>