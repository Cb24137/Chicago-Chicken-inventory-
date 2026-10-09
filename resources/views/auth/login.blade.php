
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | Chicago Chicken City</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f1f5f2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .login-header {
            background: #173d30;
            color: white;
            padding: 35px 20px;
            text-align: center;
        }

        .login-header h3 {
            font-weight: bold;
            font-size: 23px;
        }

        .login-body {
            padding: 35px;
        }

        .btn-login {
            background: #173d30;
            color: white;
            border: none;
            padding: 12px;
        }

        .btn-login:hover {
            background: #245c47;
            color: white;
        }

        .form-control:focus {
            border-color: #245c47;
            box-shadow: 0 0 0 0.2rem rgba(36,92,71,0.15);
        }
    </style>
</head>

<body>
    <div class="card login-card">

        <div class="login-header">
            <h3>CHICAGO CHICKEN CITY</h3>
            <p class="mb-0">
                Food & Beverage Inventory Management System
            </p>
        </div>

        <div class="login-body">
            <h4 class="fw-bold mb-2">Staff Login</h4>

            <p class="text-muted mb-4">
                Welcome back! Please sign in to continue.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           placeholder="Enter your email"
                           value="{{ old('email') }}"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Enter your password"
                           required>
                </div>

                <button type="submit"
                        class="btn btn-login w-100">
                    Sign In
                </button>
            </form>

            <p class="text-center text-muted mt-4 mb-0 small">
                Authorized staff only
            </p>
        </div>
    </div>
</body>
</html>
