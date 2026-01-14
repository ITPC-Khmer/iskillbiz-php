<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required'],
    ]);

    $login = $request->input('login');

    $user = \App\Models\User::where('email', $login)
        ->orWhere('username', $login)
        ->orWhere('phone', $login)
        ->first();

    if ($user && \Illuminate\Support\Facades\Hash::check($request->input('password'), $user->password)) {
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->last_login_at = now();
        $user->save();

        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'login' => 'The provided credentials do not match our records.',
    ])->onlyInput('login');
})->middleware('guest')->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register');

Route::post('/register', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = \App\Models\User::create([
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'email' => $validated['email'],
        'username' => strtolower($validated['first_name'] . $validated['last_name'] . rand(1000,9999)),
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
    ]);

    // Assign default role 'client'
    $user->assignRole('client');

    Auth::login($user);

    return redirect(route('dashboard'));
})->middleware('guest')->name('register.post');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

// Facebook Authentication Routes
Route::get('/auth/facebook', [FacebookController::class, 'login'])->name('facebook.login');
Route::get('/auth/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');
Route::get('/home/facebook_login_back', [FacebookController::class, 'callback'])->name('facebook.facebook_login_back');
Route::post('/facebook/disconnect', [FacebookController::class, 'disconnect'])->name('facebook.disconnect')->middleware('auth');

// Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/facebook/me', [FacebookController::class, 'getMe'])->name('facebook.me');
});

