<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AccessSession;
use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccessSessionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
            'business_id' => [
                'nullable',
                'integer',
                Rule::exists('businesses', 'id'),
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'closed',
                    'expired',
                    'disconnected',
                ]),
            ],
        ]);

        $sessions = AccessSession::query()
            ->with([
                'business',
                'portalUser',
                'device',
            ])
            ->when(
                !empty($filters['search']),
                function (Builder $query) use ($filters): void {
                    $search = trim($filters['search']);

                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where(
                                    'username',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'radius_session_id',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'ip_address',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'mac_address',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                !empty($filters['business_id']),
                fn(Builder $query) => $query->where(
                    'business_id',
                    $filters['business_id']
                )
            )
            ->when(
                !empty($filters['status']),
                fn(Builder $query) => $query->where(
                    'status',
                    $filters['status']
                )
            )
            ->orderByRaw(
                "CASE WHEN status = 'active' THEN 0 ELSE 1 END"
            )
            ->orderByDesc('started_at')
            ->paginate(25)
            ->withQueryString();

        $businesses = Business::query()
            ->orderBy('local_number')
            ->get();

        return view(
            'panel.access-sessions.index',
            compact('sessions', 'businesses')
        );
    }
}
