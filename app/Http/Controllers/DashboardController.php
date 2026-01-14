<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();

        // Prepare dashboard data
        $dashboardData = [
            'user' => $user,
            'stats' => $this->getUserStats($user),
            'recentOrders' => $this->getRecentOrders($user),
            'activityFeed' => $this->getActivityFeed($user),
        ];

        return view('dashboard', $dashboardData);
    }

    /**
     * Get user statistics for the dashboard.
     *
     * @param \App\Models\User $user
     * @return array
     */
    protected function getUserStats($user): array
    {
        return [
            'total_skills' => 0, // Will be populated when Skills table is created
            'earnings' => 0, // Will be populated when Orders/Transactions table is created
            'reviews' => 0, // Will be populated when Reviews table is created
            'active_clients' => 0, // Will be populated when Clients table is created
        ];
    }

    /**
     * Get recent orders for the user.
     *
     * @param \App\Models\User $user
     * @return array
     */
    protected function getRecentOrders($user): array
    {
        // Placeholder for future Orders/Transactions data
        // TODO: Query Orders table when implemented
        return [];
    }

    /**
     * Get activity feed for the user.
     *
     * @param \App\Models\User $user
     * @return array
     */
    protected function getActivityFeed($user): array
    {
        // Placeholder for future Activity/Timeline data
        // TODO: Query Activity table when implemented
        return [];
    }
}
