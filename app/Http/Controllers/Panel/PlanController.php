<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->withCount('businesses')
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        return view('panel.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('panel.plans.create');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($request->validated());

        return to_route('panel.planes.index')
            ->with('success', 'El plan fue creado correctamente.');
    }

    public function edit(Plan $plan): View
    {
        return view('panel.plans.edit', compact('plan'));
    }

    public function update(
        UpdatePlanRequest $request,
        Plan $plan
    ): RedirectResponse {
        $plan->update($request->validated());

        return to_route('panel.planes.index')
            ->with('success', 'El plan fue actualizado correctamente.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->businesses()->exists()) {
            return to_route('panel.planes.index')
                ->with(
                    'error',
                    'El plan no puede eliminarse porque tiene locales asociados.'
                );
        }

        $plan->delete();

        return to_route('panel.planes.index')
            ->with('success', 'El plan fue eliminado correctamente.');
    }
}
