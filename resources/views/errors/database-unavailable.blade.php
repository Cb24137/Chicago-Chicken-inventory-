{{-- Shown when Laravel cannot reach XAMPP MySQL. Uses no database,
     session or login data, so it works while MySQL is down. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database unavailable | Chicago Chicken City</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            box-sizing: border-box;
            background: #f8f6f2;
            color: #5a3a27;
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
        }

        .box {
            width: min(560px, 100%);
            padding: 32px;
            border: 1px solid #ece2d7;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(66, 33, 11, .06);
        }

        img {
            height: 56px;
        }

        h1 {
            margin: 18px 0 8px;
            color: #42210b;
            font-size: 22px;
        }

        p {
            margin: 0 0 12px;
            line-height: 1.6;
        }

        ol {
            margin: 0 0 18px;
            padding-left: 20px;
            line-height: 1.8;
        }

        a {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 10px;
            background: #c2540a;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }

        pre {
            margin: 18px 0 0;
            padding: 12px;
            overflow-x: auto;
            border-radius: 10px;
            background: #fff0ee;
            color: #b42318;
            font-size: 12px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <main class="box">
        <img src="{{ asset('images/chicago-logo-transparent.png') }}" alt="Chicago Chicken City">

        <h1>Cannot connect to the MySQL database</h1>
        <p>The inventory system keeps all of its data in XAMPP MySQL, which is not responding.</p>

        <ol>
            <li>Open the <strong>XAMPP Control Panel</strong>.</li>
            <li>Click <strong>Start</strong> next to <strong>MySQL</strong>.</li>
            <li>Refresh this page.</li>
        </ol>

        <a href="{{ url()->current() }}">Try again</a>

        @if ($details)
            <pre>{{ $details }}</pre>
        @endif
    </main>
</body>
</html>
