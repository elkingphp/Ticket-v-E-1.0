<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Forbidden') }} | 403</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }

        .container {
            max-width: 500px;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border-top: 4px solid #ef4444;
        }

        h1 {
            color: #ef4444;
            font-size: 3rem;
            margin: 0;
            line-height: 1;
        }

        h2 {
            font-size: 1.5rem;
            margin-top: 1rem;
            color: #334155;
        }

        p {
            color: #64748b;
            margin-top: 1rem;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            margin-top: 1.5rem;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        a:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>403</h1>
        <h2>{{ __('Access Denied') }}</h2>
        <p>{{ __('You do not have the required permissions to access this page or perform this action.') }}</p>
        <a href="{{ url('/') }}">{{ __('Return to Home') }}</a>
    </div>
</body>

</html>
