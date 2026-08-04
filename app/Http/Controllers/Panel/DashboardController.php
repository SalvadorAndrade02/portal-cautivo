<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AccessAttempt;
use App\Models\AccessSession;
use App\Models\Visitor;
use App\Models\VisitorConsent;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_visitors' => Visitor::query()->count(),

            'visitors_today' => Visitor::query()
                ->whereDate('registered_at', today())
                ->count(),

            'active_visitors' => Visitor::query()
                ->where('status', 'active')
                ->count(),

            'blocked_visitors' => Visitor::query()
                ->where('status', 'blocked')
                ->count(),

            'active_sessions' => AccessSession::query()
                ->where('access_type', 'visitor_registration')
                ->where('status', 'active')
                ->whereNull('ended_at')
                ->count(),

            'sessions_today' => AccessSession::query()
                ->where('access_type', 'visitor_registration')
                ->whereDate('started_at', today())
                ->count(),

            'accepted_today' => AccessAttempt::query()
                ->where('access_type', 'visitor_registration')
                ->where('result', 'accepted')
                ->whereDate('attempted_at', today())
                ->count(),

            'marketing_consents' => VisitorConsent::query()
                ->where('marketing_consent', true)
                ->distinct()
                ->count('visitor_id'),
        ];

        $averageSessionSeconds = (int) round(
            AccessSession::query()
                ->where('access_type', 'visitor_registration')
                ->whereNotNull('duration_seconds')
                ->where('duration_seconds', '>', 0)
                ->avg('duration_seconds') ?? 0
        );

        $stats['average_session_minutes'] = $averageSessionSeconds > 0
            ? (int) round($averageSessionSeconds / 60)
            : 0;

        $recentVisitors = Visitor::query()
            ->with([
                'interestAreas:id,name',
            ])
            ->withCount([
                'devices',
                'accessSessions',
            ])
            ->orderByDesc('registered_at')
            ->limit(6)
            ->get();

        $recentSessions = AccessSession::query()
            ->with([
                'visitor:id,full_name,email,phone',
                'device:id,name,mac_address',
            ])
            ->where('access_type', 'visitor_registration')
            ->orderByDesc('started_at')
            ->limit(6)
            ->get();

        $registrationsByDay = $this->registrationsByDay();

        $maximumDailyRegistrations = max(
            1,
            (int) $registrationsByDay->max('total')
        );

        return view(
            'panel.dashboard',
            compact(
                'stats',
                'recentVisitors',
                'recentSessions',
                'registrationsByDay',
                'maximumDailyRegistrations'
            )
        );
    }

    private function registrationsByDay(): Collection
    {
        $startDate = now()
            ->subDays(6)
            ->startOfDay();

        $registrations = Visitor::query()
            ->selectRaw(
                'DATE(registered_at) AS registration_date, COUNT(*) AS total'
            )
            ->where('registered_at', '>=', $startDate)
            ->groupByRaw('DATE(registered_at)')
            ->pluck('total', 'registration_date');

        return collect(range(6, 0))
            ->map(function (int $daysAgo) use ($registrations): array {
                $date = now()->subDays($daysAgo);

                return [
                    'date' => $date->toDateString(),
                    'label' => $date
                        ->locale('es')
                        ->translatedFormat('D d'),
                    'total' => (int) (
                        $registrations[$date->toDateString()]
                        ?? 0
                    ),
                ];
            });
    }
}
