<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'ODRA Sales Portal') }} - @yield('title', 'Sign In')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <style>
        :root {
            --red: #ac2529;
            --red-hover: #8e1e22;
            --nav: #1b242e;
            --bg: #f4f6f8;
            --border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }
        body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
        }
        .login-wrap {
            display: flex;
            min-height: 100vh;
        }
        .login-hero {
            flex: 1.2;
            background: linear-gradient(135deg, #1b242e 0%, #0f172a 100%);
            color: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .login-hero::after {
            content: "";
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(172,37,41,0.25) 0%, rgba(172,37,41,0) 70%);
            border-radius: 50%;
        }
        .login-hero-brand img {
            max-height: 54px;
            background: white;
            padding: 6px 12px;
            border-radius: 6px;
        }
        .login-hero-body {
            max-width: 520px;
            z-index: 1;
        }
        .login-hero-body h1 {
            font-size: 38px;
            line-height: 1.2;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }
        .login-hero-body p {
            font-size: 17px;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 24px;
        }
        .login-hero-features {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .login-hero-feat {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 13px;
        }
        .login-hero-feat b {
            display: block;
            color: white;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .login-hero-footer {
            font-size: 12px;
            color: #64748b;
            z-index: 1;
        }
        .login-panel {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
        }
        .login-header {
            margin-bottom: 28px;
        }
        .login-header h2 {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 8px 0;
        }
        .login-header p {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
        }
        .btn-msft {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 12px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease, border-color 0.15s ease;
            box-sizing: border-box;
        }
        .btn-msft:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        .btn-msft svg {
            width: 20px;
            height: 20px;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }
        .divider::before { margin-right: 12px; }
        .divider::after { margin-left: 12px; }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(172, 37, 41, 0.12);
        }
        .form-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 20px;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--red);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-submit:hover {
            background: var(--red-hover);
        }
        .alert {
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        @media (max-width: 900px) {
            .login-wrap { flex-direction: column; }
            .login-hero { padding: 40px 24px; }
            .login-hero-features { flex-direction: column; }
            .login-panel { padding: 40px 24px; }
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
