@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="login-wrap">
    <!-- Left Hero Section -->
    <div class="login-hero">
        <div class="login-hero-brand">
            <img src="{{ asset('images/logo.jpg') }}" alt="ODRA Road Sweepers">
        </div>
        
        <div class="login-hero-body">
            <h1>ODRA Road Sweepers<br>Sales Quotation Portal</h1>
            <p>Configure sweepers, generate contract-compliant customer quotations, calculate freight rates, and export official PDF proposals.</p>
            
            <div class="login-hero-features">
                <div class="login-hero-feat">
                    <b>MT4H & MS5H Sweepers</b>
                    Full chassis & enhancement catalog
                </div>
                <div class="login-hero-feat">
                    <b>Cooperative Pricing</b>
                    Sourcewell, H-GAC, BuyBoard, MN
                </div>
            </div>
        </div>

        <div class="login-hero-footer">
            &copy; {{ date('Y') }} ODRA LLC & Challenger Manufacturing Ltd. Internal Commercial Application.
        </div>
    </div>

    <!-- Right Login Form -->
    <div class="login-panel">
        <div class="login-card">
            <div class="login-header">
                <h2>Representative Sign In</h2>
                <p>Sign in with your corporate account to access the Quote Builder.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Microsoft 365 Single Sign-On Button -->
            <a href="{{ route('auth.microsoft') }}" class="btn-msft">
                <svg viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
                    <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
                    <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
                    <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
                </svg>
                Sign in with Microsoft 365
            </a>

            <div class="divider">or continue with company credentials</div>

            <!-- Standard Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="login">Office Email or Company ID</label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        class="form-control" 
                        value="{{ old('login') }}" 
                        placeholder="e.g. it.support@challengermfg.net or ADMIN001" 
                        required 
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your account password" 
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="form-check">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember my session</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    Sign In to Portal
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
