<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorController extends Controller
{
    public function index(Request $request): View
    {
        $visitors = Visitor::query()
            ->with([
                'interestAreas:id,name',
            ])
            ->withCount([
                'devices',
                'accessSessions',
                'accessAttempts',
                'accessTokens',
            ])
            ->when(
                $request->filled('search'),
                function (Builder $query) use ($request): void {
                    $search = trim(
                        (string) $request->input('search')
                    );

                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where(
                                    'full_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                in_array(
                    $request->input('status'),
                    ['active', 'blocked', 'disabled'],
                    true
                ),
                fn(Builder $query) => $query->where(
                    'status',
                    $request->input('status')
                )
            )
            ->orderByDesc('registered_at')
            ->paginate(20)
            ->withQueryString();

        return view(
            'panel.visitors.index',
            compact('visitors')
        );
    }

    public function show(Visitor $visitor): View
    {
        $visitor->load([
            'interestAreas:id,name',

            'consents' => fn($query) => $query
                ->orderByDesc('created_at'),
        ]);

        $devices = $visitor
            ->devices()
            ->withCount([
                'accessSessions',
                'accessAttempts',
            ])
            ->orderByDesc('last_seen_at')
            ->get();

        $sessions = $visitor
            ->accessSessions()
            ->with([
                'device:id,name,mac_address',
            ])
            ->orderByDesc('started_at')
            ->paginate(
                perPage: 10,
                pageName: 'sessions_page'
            )
            ->withQueryString();

        $attempts = $visitor
            ->accessAttempts()
            ->with([
                'device:id,name,mac_address',
            ])
            ->orderByDesc('attempted_at')
            ->paginate(
                perPage: 10,
                pageName: 'attempts_page'
            )
            ->withQueryString();

        $tokens = $visitor
            ->accessTokens()
            ->with([
                'device:id,name,mac_address',
            ])
            ->orderByDesc('created_at')
            ->get();

        $latestConsent = $visitor
            ->consents
            ->first();

        $latestSession = $visitor
            ->accessSessions()
            ->orderByDesc('started_at')
            ->first();

        $summary = [
            'devices' => $devices->count(),

            'sessions' => $visitor
                ->accessSessions()
                ->count(),

            'active_sessions' => $visitor
                ->accessSessions()
                ->where('status', 'active')
                ->whereNull('ended_at')
                ->count(),

            'accepted_attempts' => $visitor
                ->accessAttempts()
                ->where('result', 'accepted')
                ->count(),

            'rejected_attempts' => $visitor
                ->accessAttempts()
                ->where('result', 'rejected')
                ->count(),

            'total_duration_seconds' => (int) $visitor
                ->accessSessions()
                ->sum('duration_seconds'),
        ];

        return view(
            'panel.visitors.show',
            compact(
                'visitor',
                'devices',
                'sessions',
                'attempts',
                'tokens',
                'latestConsent',
                'latestSession',
                'summary'
            )
        );
    }
}
