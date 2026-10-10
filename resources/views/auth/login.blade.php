
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Chicago Chicken City</title>

    <style>
        :root {
            --primary: #d9600b;
            --primary-hover: #b94e08;
            --dark: #38291f;
            --muted: #81776e;
            --border: #e8e3dd;
            --cream: #fff8ef;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f6f3;
            color: var(--dark);
        }

        /* Main Card */

        .login-container {
            display: grid;
            grid-template-columns: 44% 56%;
            width: 920px;
            max-width: 100%;
            min-height: 540px;
            background: var(--white);
            border: 1px solid #eeeae5;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 55px rgba(56, 41, 31, 0.07);
        }

        /* Branding */

        .brand-section {
            background: var(--cream);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-right: 1px solid #f2e9df;
        }

        .brand-logo {
            display: block;
            width: 100%;
            max-width: 300px;
            height: auto;
            object-fit: contain;
            margin-bottom: 38px;
        }

        .brand-section h2 {
            font-size: 25px;
            font-weight: 750;
            letter-spacing: -0.5px;
            margin-bottom: 14px;
        }

        .brand-section p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
            max-width: 290px;
        }

        .brand-line {
            width: 42px;
            height: 4px;
            background: #f7941d;
            border-radius: 10px;
            margin-top: 28px;
        }

        /* Login Form */

        .login-section {
            padding: 65px 65px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            font-size: 32px;
            font-weight: 750;
            letter-spacing: -0.8px;
            margin-bottom: 12px;
        }

        .login-subtitle {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 36px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 650;
            margin-bottom: 9px;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 12px 15px;
            font-family: inherit;
            font-size: 14px;
            color: var(--dark);
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 9px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder {
            color: #aaa39d;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(217, 96, 11, 0.1);
        }

        /* Password Field */

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 75px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            padding: 7px;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        /* Sign In Button */

        .btn-login {
            width: 100%;
            height: 49px;
            margin-top: 8px;
            border: none;
            border-radius: 9px;
            background: var(--primary);
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:focus-visible,
        .toggle-password:focus-visible {
            outline: 3px solid #f9b15e;
            outline-offset: 3px;
        }

        /* Messages */

        .alert-error {
            padding: 13px 15px;
            margin-bottom: 24px;
            border: 1px solid #f1c4bc;
            border-radius: 9px;
            background: #fff3f1;
            color: #a72d20;
            font-size: 13px;
            line-height: 1.6;
        }

        .footer-text {
            text-align: center;
            margin-top: 28px;
            color: #938b84;
            font-size: 12px;
        }

        /* Responsive */

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .login-container {
                grid-template-columns: 1fr;
                max-width: 440px;
            }

            .brand-section {
                padding: 30px 25px;
                border-right: none;
                border-bottom: 1px solid #f2e9df;
            }

            .brand-logo {
                max-width: 220px;
                margin-bottom: 18px;
            }

            .brand-section h2 {
                font-size: 21px;
                margin-bottom: 8px;
            }

            .brand-section p {
                font-size: 12px;
            }

            .brand-line {
                margin-top: 18px;
            }

            .login-section {
                padding: 36px 28px;
            }

            .login-title {
                font-size: 27px;
            }

            .login-subtitle {
                margin-bottom: 26px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .btn-login,
            .form-control {
                transition: none;
            }
        }
    </style>
</head>

<body>

    <main class="login-container">

        <!-- LEFT: BRANDING -->
        <section class="brand-section">

            <img
                src="{{ asset('images/chicago-logo-transparent.png') }}"
                alt="Chicago Chicken City Logo"
                class="brand-logo"
            >

            <h2>Inventory Management</h2>

            <p>
                Food &amp; Beverage Inventory
                Management System
            </p>

            <div class="brand-line"></div>

        </section>

        <!-- RIGHT: LOGIN -->
        <section class="login-section">

            <h1 class="login-title">
                Welcome Back!
            </h1>

            <p class="login-subtitle">
                Please enter your credentials
                to access your dashboard.
            </p>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert-error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Original Laravel Login -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">

                    <label for="email" class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email address"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                    >

                </div>

                <!-- Password -->
                <div class="form-group">

                    <label for="password" class="form-label">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            id="togglePassword"
                            aria-label="Show password"
                            aria-pressed="false"
                            aria-controls="password"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login">
                    Sign In &rarr;
                </button>

            </form>

            <p class="footer-text">
                Authorized Staff Only
                &bull; Chicago Chicken City
            </p>

        </section>

    </main>

    <!-- Password Visibility -->
    <script>
        const toggle = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        toggle.addEventListener('click', function () {
            const isHidden = password.type === 'password';

            password.type = isHidden ? 'text' : 'password';
            toggle.textContent = isHidden ? 'Hide' : 'Show';
            toggle.setAttribute('aria-pressed', String(isHidden));
            toggle.setAttribute(
                'aria-label',
                isHidden ? 'Hide password' : 'Show password'
            );
        });
    </script>

</body>
</html>
