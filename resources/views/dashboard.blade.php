@extends('layouts.app')

@section('title', 'Dashboard - iSkillBiz')

@section('content')
    <div class="page-header">
        <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
        <p>Here's what's happening with your profile today.</p>
    </div>

    <!-- Stats Row -->
    <div class="row mb-30" style="margin-bottom: 30px;">
        @if(Auth::user()->isConnectedToFacebook() && Auth::user()->hasFacebookPages())
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card" style="border-left: 3px solid #1877f2;">
                <div class="card-body p-4">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Facebook Pages</p>
                            <h3 style="font-size: 28px; font-weight: 700; margin: 5px 0 0 0; color: var(--dark-bg);">{{ count(Auth::user()->getFacebookPages()) }}</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(24, 119, 242, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #1877f2;">
                            <i class="fab fa-facebook"></i>
                        </div>
                    </div>
                    @if(Auth::user()->hasFacebookToken() && !Auth::user()->isFacebookTokenExpired())
                        <div style="margin-top: 10px; font-size: 11px; color: #10b981;">
                            <i class="fas fa-check-circle"></i> Token valid
                        </div>
                    @else
                        <div style="margin-top: 10px; font-size: 11px; color: #dc2626;">
                            <i class="fas fa-exclamation-triangle"></i> Token expired
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body p-4">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Total Skills</p>
                            <h3 style="font-size: 28px; font-weight: 700; margin: 5px 0 0 0; color: var(--dark-bg);">12</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--primary-color);">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body p-4">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Earnings</p>
                            <h3 style="font-size: 28px; font-weight: 700; margin: 5px 0 0 0; color: var(--dark-bg);">$4,250</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--success-color);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body p-4">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Reviews</p>
                            <h3 style="font-size: 28px; font-weight: 700; margin: 5px 0 0 0; color: var(--dark-bg);">4.9 <span style="font-size: 16px;">★</span></h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(250, 204, 21, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fcc417;">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body p-4">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #64748b; margin: 0; font-size: 14px;">Clients</p>
                            <h3 style="font-size: 28px; font-weight: 700; margin: 5px 0 0 0; color: var(--dark-bg);">28</h3>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(248, 80, 50, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--danger-color);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">

        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card" style="text-align: center;">
                <div class="card-body p-4">
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-size: 36px; font-weight: 700;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <h4 style="font-weight: 700; margin-bottom: 5px;">{{ Auth::user()->name }}</h4>
                    <p style="color: #64748b; margin-bottom: 20px; font-size: 14px;">{{ Auth::user()->email }}</p>

                    @if (!Auth::user()->facebook_id)
                        <div style="margin-bottom: 20px; padding: 15px; background: #eff6ff; border-radius: 8px; border-left: 3px solid #1877f2;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <i class="fab fa-facebook" style="font-size: 24px; color: #1877f2;"></i>
                                <div>
                                    <p style="color: #1e40af; margin: 0; font-size: 14px; font-weight: 600;">Connect Facebook</p>
                                    <p style="color: #1e40af; margin: 0; font-size: 12px;">Unlock Facebook features</p>
                                </div>
                            </div>
                            <p style="color: #1e40af; margin: 10px 0; font-size: 12px;">Get access to:</p>
                            <ul style="color: #1e40af; margin: 10px 0; font-size: 12px; padding-left: 20px;">
                                <li>Manage your Facebook pages</li>
                                <li>Auto-sync profile picture</li>
                                <li>60-day long-lived tokens</li>
                            </ul>
                            <a href="{{ route('facebook.login') }}" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #1877f2; color: white; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: all 0.3s ease;">
                                <i class="fab fa-facebook-f"></i> Connect Facebook
                            </a>
                        </div>
                    @else
                        <div style="margin-bottom: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border-left: 3px solid var(--success-color);">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                @if(Auth::user()->facebook_profile_picture)
                                    <img src="{{ Auth::user()->facebook_profile_picture }}" alt="Facebook Profile" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--success-color);">
                                @else
                                    <i class="fab fa-facebook" style="font-size: 24px; color: var(--success-color);"></i>
                                @endif
                                <div style="flex: 1;">
                                    <p style="color: #166534; margin: 0; font-size: 13px; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i> Facebook Connected
                                    </p>
                                    <p style="color: #166534; margin: 0; font-size: 11px;">
                                        @if(Auth::user()->hasFacebookToken())
                                            @if(Auth::user()->isFacebookTokenExpired())
                                                <span style="color: #dc2626;">⚠️ Token expired - reconnect needed</span>
                                            @else
                                                Token expires {{ Auth::user()->facebook_token_expires_at->diffForHumans() }}
                                            @endif
                                        @else
                                            No token stored
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if(Auth::user()->hasFacebookPages())
                                <div style="padding: 10px; background: white; border-radius: 6px; margin-bottom: 10px;">
                                    <p style="margin: 0; font-size: 12px; color: #166534; font-weight: 600;">
                                        <i class="fas fa-flag"></i> {{ count(Auth::user()->getFacebookPages()) }} Facebook Page(s)
                                    </p>
                                    <div style="margin-top: 8px; max-height: 100px; overflow-y: auto;">
                                        @foreach(Auth::user()->getFacebookPages() as $page)
                                            <div style="display: flex; align-items: center; gap: 8px; padding: 5px 0; border-bottom: 1px solid #e2e8f0;">
                                                @if(isset($page['picture_url']))
                                                    <img src="{{ $page['picture_url'] }}" alt="{{ $page['name'] }}" style="width: 24px; height: 24px; border-radius: 4px;">
                                                @else
                                                    <i class="fas fa-file" style="font-size: 16px; color: #64748b;"></i>
                                                @endif
                                                <div style="flex: 1; min-width: 0;">
                                                    <p style="margin: 0; font-size: 11px; color: #166534; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $page['name'] }}</p>
                                                    @if(isset($page['category']))
                                                        <p style="margin: 0; font-size: 10px; color: #64748b;">{{ $page['category'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div style="padding: 10px; background: #fef3c7; border-radius: 6px; margin-bottom: 10px;">
                                    <p style="margin: 0; font-size: 11px; color: #92400e;">
                                        <i class="fas fa-info-circle"></i> No Facebook pages found
                                    </p>
                                </div>
                            @endif

                            <div style="display: flex; gap: 5px;">
                                @if(Auth::user()->hasFacebookToken() && !Auth::user()->isFacebookTokenExpired())
                                    <form action="{{ route('facebook.refresh') }}" method="POST" style="flex: 1;">
                                        @csrf
                                        <button type="submit" style="width: 100%; padding: 8px 12px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('facebook.login') }}" style="flex: 1; text-align: center; padding: 8px 12px; background: #1877f2; color: white; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-plug"></i> Reconnect
                                    </a>
                                @endif
                                <form action="{{ route('facebook.disconnect') }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <button type="submit" style="width: 100%; padding: 8px 12px; background: #dc2626; color: white; border: none; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                        <i class="fas fa-unlink"></i> Disconnect
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                        <div style="padding: 15px; background: var(--light-bg); border-radius: 8px;">
                            <p style="color: #64748b; margin: 0; font-size: 12px;">Total Earnings</p>
                            <h4 style="font-weight: 700; margin: 5px 0 0 0; color: var(--primary-color);">$4,250</h4>
                        </div>
                        <div style="padding: 15px; background: var(--light-bg); border-radius: 8px;">
                            <p style="color: #64748b; margin: 0; font-size: 12px;">Rating</p>
                            <h4 style="font-weight: 700; margin: 5px 0 0 0; color: var(--primary-color);">4.9 ★</h4>
                        </div>
                    </div>

                    <button style="width: 100%; padding: 12px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h5 style="margin: 0; font-weight: 600;">Quick Actions</h5>
                </div>
                <div class="card-body p-4" style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="#" style="padding: 12px; text-align: center; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--primary-color); font-weight: 600; transition: all 0.3s ease;">
                        <i class="fas fa-plus"></i> Add New Skill
                    </a>
                    <a href="#" style="padding: 12px; text-align: center; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--primary-color); font-weight: 600; transition: all 0.3s ease;">
                        <i class="fas fa-file-invoice"></i> View Invoices
                    </a>
                    <a href="#" style="padding: 12px; text-align: center; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--primary-color); font-weight: 600; transition: all 0.3s ease;">
                        <i class="fas fa-download"></i> Download Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row" style="margin-top: 30px;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 style="margin: 0; font-weight: 600;">Recent Activity</h5>
                </div>
                <div class="card-body p-4">
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <div style="display: flex; align-items: flex-start; gap: 15px;">
                            <div style="width: 40px; height: 40px; background: rgba(102, 126, 234, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); flex-shrink: 0;">
                                <i class="fas fa-star"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="margin: 0; font-weight: 600; color: var(--dark-bg);">New 5-star review from John Smith</p>
                                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 13px;">2 hours ago</p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 15px;">
                            <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--success-color); flex-shrink: 0;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="margin: 0; font-weight: 600; color: var(--dark-bg);">Project completed: Web Design for ABC Corp</p>
                                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 13px;">1 day ago</p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: flex-start; gap: 15px;">
                            <div style="width: 40px; height: 40px; background: rgba(250, 204, 21, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fcc417; flex-shrink: 0;">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="margin: 0; font-weight: 600; color: var(--dark-bg);">New order from Sarah Johnson</p>
                                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 13px;">2 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Facebook Pages Section -->
    @if(Auth::user()->isConnectedToFacebook() && Auth::user()->hasFacebookPages())
    <div class="row" style="margin-top: 30px;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h5 style="margin: 0; font-weight: 600;">
                            <i class="fab fa-facebook" style="color: #1877f2;"></i> Your Facebook Pages
                        </h5>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            @if(Auth::user()->hasFacebookToken() && !Auth::user()->isFacebookTokenExpired())
                                <form action="{{ route('facebook.refresh') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="padding: 6px 12px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                        <i class="fas fa-sync-alt"></i> Refresh Pages
                                    </button>
                                </form>
                            @endif
                            <span style="font-size: 13px; color: #64748b;">{{ count(Auth::user()->getFacebookPages()) }} page(s)</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row" style="padding: 20px;">
                        @foreach(Auth::user()->getFacebookPages() as $page)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; transition: all 0.3s ease; height: 100%;">
                                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                    @if(isset($page['picture_url']))
                                        <img src="{{ $page['picture_url'] }}" alt="{{ $page['name'] }}" style="width: 60px; height: 60px; border-radius: 10px; object-fit: cover; border: 2px solid #e2e8f0;">
                                    @else
                                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #1877f2, #0c63d4); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                                            <i class="fab fa-facebook-f"></i>
                                        </div>
                                    @endif
                                    <div style="flex: 1; min-width: 0;">
                                        <h6 style="margin: 0; font-weight: 700; color: var(--dark-bg); font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $page['name'] }}</h6>
                                        @if(isset($page['category']))
                                            <p style="margin: 5px 0 0 0; font-size: 12px; color: #64748b;">{{ $page['category'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;">
                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b;">
                                        <i class="fas fa-key" style="width: 16px; color: #1877f2;"></i>
                                        <span style="flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">ID: {{ $page['id'] }}</span>
                                    </div>
                                    @if(isset($page['access_token']))
                                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #10b981;">
                                            <i class="fas fa-check-circle" style="width: 16px;"></i>
                                            <span>Access token available</span>
                                        </div>
                                    @endif
                                    @if(isset($page['tasks']) && is_array($page['tasks']))
                                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b;">
                                            <i class="fas fa-tasks" style="width: 16px; color: #f59e0b;"></i>
                                            <span>{{ count($page['tasks']) }} permission(s)</span>
                                        </div>
                                    @endif
                                </div>

                                <div style="display: flex; gap: 5px;">
                                    <a href="https://facebook.com/{{ $page['id'] }}" target="_blank" style="flex: 1; text-align: center; padding: 8px; background: #1877f2; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; transition: all 0.3s ease;">
                                        <i class="fab fa-facebook-f"></i> View Page
                                    </a>
                                    <button onclick="copyToClipboard('{{ $page['id'] }}')" style="padding: 8px 12px; background: #f3f4f6; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.3s ease;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @elseif(Auth::user()->isConnectedToFacebook())
    <div class="row" style="margin-top: 30px;">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4" style="text-align: center;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: rgba(24, 119, 242, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fab fa-facebook" style="font-size: 40px; color: #1877f2;"></i>
                    </div>
                    <h5 style="font-weight: 600; margin-bottom: 10px;">No Facebook Pages Found</h5>
                    <p style="color: #64748b; margin-bottom: 20px; font-size: 14px;">You don't have any Facebook pages connected yet, or pages couldn't be fetched.</p>
                    @if(Auth::user()->hasFacebookToken() && !Auth::user()->isFacebookTokenExpired())
                        <form action="{{ route('facebook.refresh') }}" method="POST" style="display: inline-block;">
                            @csrf
                            <button type="submit" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                <i class="fas fa-sync-alt"></i> Try Refreshing Pages
                            </button>
                        </form>
                    @else
                        <a href="{{ route('facebook.login') }}" style="display: inline-block; padding: 10px 20px; background: #1877f2; color: white; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">
                            <i class="fas fa-plug"></i> Reconnect Facebook
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show a temporary success message
                const toast = document.createElement('div');
                toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 8px; font-size: 14px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
                toast.innerHTML = '<i class="fas fa-check"></i> Page ID copied!';
                document.body.appendChild(toast);
                setTimeout(function() {
                    toast.remove();
                }, 2000);
            });
        }
    </script>
@endsection
