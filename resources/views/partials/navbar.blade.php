<style>
    .ccc-navbar {
        background: #ffffff;
        border-bottom: 1px solid #eeeeee;
        width: 100%;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    .ccc-nav-inner {
        max-width: 1100px;
        min-height: 94px;
        margin: 0 auto;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-sizing: border-box;
    }

    .ccc-brand {
        display: flex;
        align-items: center;
        gap: 18px;
        text-decoration: none;
        color: #3b1d0b;
        font-size: 16px;
        font-weight: bold;
    }

    .ccc-brand img {
        width: 105px;
        height: auto;
        object-fit: contain;
    }

    .ccc-nav-actions {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .ccc-role {
        background: #fff0df;
        color: #b45309;
        padding: 9px 16px;
        border-radius: 25px;
        font-size: 13px;
        font-weight: bold;
    }

    .ccc-logout {
        background: white;
        border: 1px solid #e8ddd3;
        color: #3b1d0b;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
    }

    .ccc-logout:hover {
        background: #fff7ed;
    }

    @media (max-width: 600px) {
        .ccc-nav-inner {
            flex-wrap: wrap;
            justify-content: center;
        }

        .ccc-nav-actions {
            justify-content: center;
        }
    }
</style>

<nav class="ccc-navbar">
    <div class="ccc-nav-inner">

        <!-- Chicago Chicken City Logo -->
        <a href="{{ route('dashboard') }}"
           class="ccc-brand">

            <img
                src="{{ asset('images/chicago-logo-transparent.png') }}"
                alt="Chicago Chicken City Logo">

            <span>Inventory System</span>
        </a>

        <!-- Role and Logout -->
        <div class="ccc-nav-actions">

            @auth
                <span class="ccc-role">
                    {{ auth()->user()->isAdmin() ? 'Admin' : 'Staff' }}
                </span>

                <form method="POST"
                      action="{{ route('logout') }}"
                      style="margin: 0;">

                    @csrf

                    <button type="submit"
                            class="ccc-logout">
                        Logout
                    </button>
                </form>
            @endauth

        </div>

    </div>
</nav>
