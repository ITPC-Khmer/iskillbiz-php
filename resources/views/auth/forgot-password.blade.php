<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - iSkillBiz</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-lock icon"></i>
                <h2>Forgot Password?</h2>
                <p>We'll help you reset it</p>
            </div>

            <div class="auth-body">
                @if (session('status'))
                    <div class="auth-success-message">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-error-message">
                        <strong>{{ __('Whoops!') }}</strong> {{ __('Something went wrong.') }}
                        <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="auth-info-box">
                    <i class="fas fa-info-circle"></i> Enter your email address below, and we'll send you a link to reset your password.
                </div>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="auth-form-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="your@email.com" autocomplete="email">
                        @error('email')
                            <span class="auth-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="auth-btn-primary">
                        <i class="fas fa-envelope"></i> Send Reset Link
                    </button>
                </form>

                <div class="auth-link-text">
                    <a href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
