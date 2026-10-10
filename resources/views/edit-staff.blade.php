<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Edit Staff | Chicago Chicken City</title>

    <link rel="stylesheet"
          href="{{ asset('css/staff.css') }}">
    <script src="{{ asset('js/auth-history.js') }}" defer></script>
</head>

<body>

<nav>
    <img src="{{ asset('images/chicago-logo-transparent.png') }}"
         alt="Chicago Chicken City">

    <a href="{{ route('admin.staff.index') }}"
       class="btn btn-light">
        Back to Staff
    </a>
</nav>

<main>

    <div class="header">
        <div>
            <h1>Edit Staff</h1>
            <p>Update employee account information.</p>
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
              action="{{ route('admin.staff.update', $staff) }}">

            @csrf
            @method('PUT')

            <label for="name">Full Name</label>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $staff->name) }}"
                required
            >

            <label for="email">Email Address</label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $staff->email) }}"
                required
            >

            <label for="password">New Password</label>

            <input
                id="password"
                type="password"
                name="password"
                placeholder="Leave blank to keep current password"
                minlength="5"
                autocomplete="new-password"
            >

            <label for="password_confirmation">
                Confirm New Password
            </label>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                placeholder="Confirm new password"
                minlength="5"
                autocomplete="new-password"
            >

            <button type="submit" class="btn btn-orange">
                Save Changes
            </button>

            <a href="{{ route('admin.staff.index') }}"
               class="btn btn-light">
                Cancel
            </a>

        </form>
    </div>

</main>

</body>
</html>