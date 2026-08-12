<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInterestAreaRequest;
use App\Http\Requests\UpdateInterestAreaRequest;
use App\Models\InterestArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InterestAreaController extends Controller
{
    public function index(): View
    {
        $interestAreas =
            InterestArea::query()
            ->withCount('visitors')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view(
            'panel.interest-areas.index',
            compact('interestAreas')
        );
    }

    public function create(): View
    {
        return view(
            'panel.interest-areas.create'
        );
    }

    public function store(
        StoreInterestAreaRequest $request
    ): RedirectResponse {
        $data =
            $request->validated();

        $data['slug'] =
            Str::slug(
                $data['name']
            );

        $data['sort_order'] =
            (
                InterestArea::query()
                ->max('sort_order')
                ?? 0
            ) + 1;

        InterestArea::create(
            $data
        );

        return to_route(
            'panel.areas-interes.index'
        )->with(
            'success',
            'El área de interés fue creada correctamente.'
        );
    }

    public function edit(
        InterestArea $interestArea
    ): View {
        return view(
            'panel.interest-areas.edit',
            compact('interestArea')
        );
    }

    public function update(
        UpdateInterestAreaRequest $request,
        InterestArea $interestArea
    ): RedirectResponse {
        $data =
            $request->validated();

        $data['slug'] =
            Str::slug(
                $data['name']
            );

        $interestArea->update(
            $data
        );

        return to_route(
            'panel.areas-interes.index'
        )->with(
            'success',
            'El área de interés fue actualizada correctamente.'
        );
    }
}
