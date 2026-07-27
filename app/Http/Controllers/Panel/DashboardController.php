<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\PortalUser;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'plans' => Plan::count(),

            'active_plans' => Plan::query()
                ->where('active', true)
                ->count(),

            'businesses' => Business::count(),

            'active_businesses' => Business::query()
                ->where('status', 'active')
                ->count(),

            'suspended_businesses' => Business::query()
                ->where('status', 'suspended')
                ->count(),

            'portal_users' => PortalUser::count(),

            'active_portal_users' => PortalUser::query()
                ->where('status', 'active')
                ->count(),
        ];

        $alerts = [
            'businesses_without_plan' => Business::query()
                ->whereNull('plan_id')
                ->count(),

            'users_with_inactive_business' => PortalUser::query()
                ->whereHas(
                    'business',
                    fn($query) => $query->where(
                        'status',
                        '!=',
                        'active'
                    )
                )
                ->count(),

            'businesses_with_inactive_plan' => Business::query()
                ->whereHas(
                    'plan',
                    fn($query) => $query->where(
                        'active',
                        false
                    )
                )
                ->count(),
        ];

        return view(
            'panel.dashboard',
            compact('stats', 'alerts')
        );
    }
}
