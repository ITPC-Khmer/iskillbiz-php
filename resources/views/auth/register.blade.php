<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - iSkillBiz</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <div class="auth-container wide">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-plus icon"></i>
                <h2>Create Account</h2>
                <p>Join the iSkillBiz community today</p>
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

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="auth-form-row">
                        <div class="auth-form-group">
                            <label for="first_name">First Name</label>
                            <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}"
                                required placeholder="John" autocomplete="given-name">
                            @error('first_name')
                                <span class="auth-error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="auth-form-group">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}"
                                required placeholder="Doe" autocomplete="family-name">
                            @error('last_name')
                                <span class="auth-error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            placeholder="your@email.com" autocomplete="email">
                        @error('email')
                            <span class="auth-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required
                            placeholder="Create a strong password" autocomplete="new-password">
                        @error('password')
                            <span class="auth-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="password-requirements">
                        <strong style="color: var(--success-color);">Password Requirements:</strong>
                        <div class="password-requirement">
                            <i class="fas fa-circle"></i>
                            <span>At least 8 characters</span>
                        </div>
                        <div class="password-requirement">
                            <i class="fas fa-circle"></i>
                            <span>Contains uppercase letter (A-Z)</span>
                        </div>
                        <div class="password-requirement">
                            <i class="fas fa-circle"></i>
                            <span>Contains lowercase letter (a-z)</span>
                        </div>
                        <div class="password-requirement">
                            <i class="fas fa-circle"></i>
                            <span>Contains number (0-9)</span>
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            placeholder="Confirm your password" autocomplete="new-password">
                        @error('password_confirmation')
                            <span class="auth-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-terms-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                    </div>

                    <button type="submit" class="auth-btn-primary">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="auth-divider">or</div>

                <div class="auth-social-buttons">
                    <a href="{{ route('facebook.login') }}" class="auth-social-btn facebook" title="Register with Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="auth-social-btn google" title="Register with Google">
                        <i class="fab fa-google"></i>
                    </a>
                    <a href="#" class="auth-social-btn github" title="Register with GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                </div>

                <div class="auth-link-text">
                    Already have an account? <a href="{{ route('login') }}">Sign In Here</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
