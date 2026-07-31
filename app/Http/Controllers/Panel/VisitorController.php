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
}
