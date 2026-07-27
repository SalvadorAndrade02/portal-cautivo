<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(): View
    {
        $businesses = Business::query()
            ->with('plan')
            ->withCount('portalUsers')
            ->orderBy('local_number')
            ->get();

        return view(
            'panel.businesses.index',
            compact('businesses')
        );
    }

    public function create(): View
    {
        $plans = Plan::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view(
            'panel.businesses.create',
            compact('plans')
        );
    }

    public function store(
        StoreBusinessRequest $request
    ): RedirectResponse {
        Business::create($request->validated());

        return to_route('panel.locales.index')
            ->with(
                'success',
                'El local fue creado correctamente.'
            );
    }

    public function edit(Business $business): View
    {
        $plans = Plan::query()
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        return view(
            'panel.businesses.edit',
            compact('business', 'plans')
        );
    }

    public function update(
        UpdateBusinessRequest $request,
        Business $business
    ): RedirectResponse {
        $business->update($request->validated());

        return to_route('panel.locales.index')
            ->with(
                'success',
                'El local fue actualizado correctamente.'
            );
    }

    public function destroy(
        Business $business
    ): RedirectResponse {
        if (
            $business->portalUsers()->exists()
            || $business->devices()->exists()
        ) {
            return to_route('panel.locales.index')
                ->with(
                    'error',
                    'El local no puede eliminarse porque tiene usuarios o dispositivos asociados.'
                );
        }

        $business->delete();

        return to_route('panel.locales.index')
            ->with(
                'success',
                'El local fue eliminado correctamente.'
            );
    }
}
