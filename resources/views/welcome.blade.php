<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'iSkillBiz') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing">
    <div class="container">
        <div class="row g-4 align-items-center justify-content-center">
            <div class="col-lg-7">
                <div class="glass-card p-4 p-lg-5 h-100">
                    <div class="mb-3">
                        <span class="chip"><i class="fas fa-bolt"></i> Your skills. Your clients. Your growth.</span>
                    </div>
                    <h1 class="hero-title mb-3">Level up your skill business with a beautiful, focused dashboard.</h1>
                    <p class="hero-subtitle mb-4">Showcase your expertise, manage clients, and grow your brand with a clean, modern experience built for creators, consultants, and freelancers.</p>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="cta-primary d-inline-flex align-items-center gap-2"><i class="fas fa-chart-line"></i> Go to dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="cta-primary d-inline-flex align-items-center gap-2"><i class="fas fa-user-plus"></i> Create account</a>
                            <a href="{{ route('login') }}" class="cta-secondary d-inline-flex align-items-center gap-2"><i class="fas fa-sign-in-alt"></i> Sign in</a>
                        @endauth
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="stat-card p-3 d-flex align-items-center gap-3">
                                <div class="feature-icon"><i class="fas fa-star"></i></div>
                                <div>
                                    <div class="fw-bold text-white">Premium UI/UX</div>
                                    <small class="text-muted">Built with Bootstrap 5</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="stat-card p-3 d-flex align-items-center gap-3">
                                <div class="feature-icon text-warning"><i class="fab fa-facebook"></i></div>
                                <div>
                                    <div class="fw-bold text-white">Facebook ready</div>
                                    <small class="text-muted">One-click connect</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="stat-card p-3 d-flex align-items-center gap-3">
                                <div class="feature-icon text-info"><i class="fas fa-lock"></i></div>
                                <div>
                                    <div class="fw-bold text-white">Secure auth</div>
                                    <small class="text-muted">Sessions & CSRF</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="stat-card p-3 d-flex align-items-center gap-3">
                                <div class="feature-icon text-primary"><i class="fas fa-mobile-alt"></i></div>
                                <div>
                                    <div class="fw-bold text-white">Fully responsive</div>
                                    <small class="text-muted">Optimized for mobile</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass-card p-4 p-lg-5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="feature-icon" style="color:#cbd5e1; background: rgba(255,255,255,0.08);"><i class="fas fa-brain"></i></div>
                            <div>
                                <div class="fw-bold text-white">{{ config('app.name', 'iSkillBiz') }}</div>
                                <small class="text-muted">Skill marketplace platform</small>
                            </div>
                        </div>
                        <span class="badge-soft">Live Preview</span>
                    </div>
                    @guest
                        <h5 class="fw-bold text-white mb-3">Get started in minutes</h5>
                        <p class="text-muted mb-4">Pick your path below to jump into the experience.</p>
                        <div class="d-grid gap-3 mb-3">
                            <a href="{{ route('facebook.login') }}" class="btn btn-outline-light d-flex align-items-center justify-content-center gap-2" style="border-color: rgba(255,255,255,0.2);">
                                <i class="fab fa-facebook-f"></i> Continue with Facebook
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-light text-dark d-flex align-items-center justify-content-center gap-2 fw-semibold">
                                <i class="fas fa-user-plus"></i> Create a new account
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light d-flex align-items-center justify-content-center gap-2" style="border-color: rgba(255,255,255,0.2);">
                                <i class="fas fa-sign-in-alt"></i> Sign in instead
                            </a>
                        </div>
                        <div class="d-flex align-items-start gap-3 mt-3">
                            <div class="feature-icon" style="color: var(--secondary); background: rgba(118,75,162,0.12);"><i class="fas fa-shield-alt"></i></div>
                            <div class="small text-muted">Secure by default with encrypted sessions, CSRF protection, and hashed passwords.</div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="display-6 fw-bold text-white mb-2">Welcome back!</div>
                            <p class="text-muted mb-4">Head straight to your dashboard to manage skills, clients, and earnings.</p>
                            <a href="{{ route('dashboard') }}" class="cta-primary d-inline-flex align-items-center gap-2"><i class="fas fa-arrow-right"></i> Open dashboard</a>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</body>
</html>
