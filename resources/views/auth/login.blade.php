<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - iSkillBiz</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-brain icon"></i>
                <h2>Welcome Back</h2>
                <p>Sign in to your iSkillBiz account</p>
            </div>

            <div class="auth-body">
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

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="auth-form-group">
                        <label for="login">Email, Username or Phone</label>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                            placeholder="Email, Username or Phone" autocomplete="username">
                        @error('login')
                            <span class="auth-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required
                            placeholder="Enter your password" autocomplete="current-password">
                        @error('password')
                            <span class="auth-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-remember-forgot">
                        <div class="auth-checkbox-wrapper">
                            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                        @endif
                    </div>

                    <button type="submit" class="auth-btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="auth-divider">or</div>

                <div class="auth-social-buttons">
                    <a href="{{ route('facebook.login') }}" class="auth-social-btn facebook" title="Login with Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="auth-social-btn google" title="Login with Google">
                        <i class="fab fa-google"></i>
                    </a>
                    <a href="#" class="auth-social-btn github" title="Login with GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                </div>

                <div class="auth-link-text">
                    Don't have an account? <a href="{{ route('register') }}">Sign Up Now</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
