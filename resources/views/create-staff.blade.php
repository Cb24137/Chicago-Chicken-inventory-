<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Staff | Chicago Chicken City</title>
    <link rel="stylesheet" href="{{ asset('css/staff.css') }}">
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
            <h1>Add New Staff</h1>
            <p>Create a new employee account.</p>
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

        <form method="POST" action="{{ route('admin.staff.store') }}">
            @csrf

            <label for="name">Full Name</label>
            <input id="name" type="text" name="name"
                   value="{{ old('name') }}"
                   placeholder="Enter staff name"
                   autocomplete="name" required>

            <label for="email">Email Address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="Enter staff email"
                   autocomplete="email" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password"
                   placeholder="Minimum 5 characters"
                   autocomplete="new-password"
                   minlength="5" required>

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password"
                   name="password_confirmation"
                   placeholder="Confirm password"
                   autocomplete="new-password"
                   minlength="5" required>

            <button type="submit" class="btn btn-orange">
                Create Staff Account
            </button>
        </form>

    </div>
</main>

</body>
</html>