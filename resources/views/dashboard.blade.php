@extends('layouts.app')

@section('title', 'Dashboard - iSkillBiz')

@section('content')
    <div class="page-header">
        <h1>Welcome back, {{ Auth::user()->name }}! 👋</h1>
        <p>Here's what's happening with your profile today.</p>
    </div>

    <!-- Stats Row -->
    <div class="row mb-30" style="margin-bottom: 30px;">
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
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h5 style="margin: 0; font-weight: 600;">Recent Orders</h5>
                        <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 13px;">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; margin: 0;">
                            <thead>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #64748b; font-size: 12px;">Client</th>
                                    <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #64748b; font-size: 12px;">Service</th>
                                    <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #64748b; font-size: 12px;">Amount</th>
                                    <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #64748b; font-size: 12px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 15px 20px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">J</div>
                                            <div>
                                                <p style="margin: 0; font-weight: 500; color: var(--dark-bg);">John Smith</p>
                                                <p style="margin: 0; font-size: 12px; color: #64748b;">john@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 20px;"><span style="color: var(--dark-bg); font-weight: 500;">Web Design</span></td>
                                    <td style="padding: 15px 20px;"><span style="color: var(--dark-bg); font-weight: 600;">$500</span></td>
                                    <td style="padding: 15px 20px;"><span class="badge badge-success">Completed</span></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 15px 20px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #f093fb, #f5576c); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">S</div>
                                            <div>
                                                <p style="margin: 0; font-weight: 500; color: var(--dark-bg);">Sarah Johnson</p>
                                                <p style="margin: 0; font-size: 12px; color: #64748b;">sarah@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 20px;"><span style="color: var(--dark-bg); font-weight: 500;">Logo Design</span></td>
                                    <td style="padding: 15px 20px;"><span style="color: var(--dark-bg); font-weight: 600;">$300</span></td>
                                    <td style="padding: 15px 20px;"><span class="badge badge-primary">In Progress</span></td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 15px 20px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">M</div>
                                            <div>
                                                <p style="margin: 0; font-weight: 500; color: var(--dark-bg);">Mike Davis</p>
                                                <p style="margin: 0; font-size: 12px; color: #64748b;">mike@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 20px;"><span style="color: var(--dark-bg); font-weight: 500;">Copywriting</span></td>
                                    <td style="padding: 15px 20px;"><span style="color: var(--dark-bg); font-weight: 600;">$150</span></td>
                                    <td style="padding: 15px 20px;"><span class="badge badge-primary">In Progress</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

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
                        <div style="margin-bottom: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border-left: 3px solid var(--success-color);">
                            <p style="color: #166534; margin: 0; font-size: 13px;"><strong>Connect Facebook</strong></p>
                            <p style="color: #166534; margin: 5px 0 0 0; font-size: 12px;">Link your Facebook account to get profile information.</p>
                            <a href="{{ route('facebook.login') }}" style="display: inline-block; margin-top: 10px; padding: 8px 15px; background: var(--success-color); color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                <i class="fab fa-facebook-f"></i> Connect Now
                            </a>
                        </div>
                    @else
                        <div style="margin-bottom: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border-left: 3px solid var(--success-color);">
                            <p style="color: #166534; margin: 0; font-size: 13px;"><i class="fas fa-check"></i> <strong>Facebook Connected</strong></p>
                            <p style="color: #166534; margin: 5px 0 0 0; font-size: 12px;">Your Facebook account is linked to your profile.</p>
                            <form action="{{ route('facebook.disconnect') }}" method="POST" style="margin-top: 10px;">
                                @csrf
                                <button type="submit" style="padding: 8px 15px; background: #dc2626; color: white; border: none; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; cursor: pointer;">
                                    <i class="fas fa-unlink"></i> Disconnect
                                </button>
                            </form>
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
@endsection
