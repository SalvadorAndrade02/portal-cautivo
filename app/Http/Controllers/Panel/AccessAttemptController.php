<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AccessAttempt;
use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccessAttemptController extends Controller
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
            'result' => [
                'nullable',
                Rule::in([
                    'accepted',
                    'rejected',
                ]),
            ],
            'date_from' => [
                'nullable',
                'date',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $attempts = AccessAttempt::query()
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
                                    'ip_address',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'mac_address',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'reason',
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
                !empty($filters['result']),
                fn(Builder $query) => $query->where(
                    'result',
                    $filters['result']
                )
            )
            ->when(
                !empty($filters['date_from']),
                fn(Builder $query) => $query->whereDate(
                    'attempted_at',
                    '>=',
                    $filters['date_from']
                )
            )
            ->when(
                !empty($filters['date_to']),
                fn(Builder $query) => $query->whereDate(
                    'attempted_at',
                    '<=',
                    $filters['date_to']
                )
            )
            ->orderByDesc('attempted_at')
            ->paginate(25)
            ->withQueryString();

        $businesses = Business::query()
            ->orderBy('local_number')
            ->get();

        return view(
            'panel.access-attempts.index',
            compact('attempts', 'businesses')
        );
    }
}
