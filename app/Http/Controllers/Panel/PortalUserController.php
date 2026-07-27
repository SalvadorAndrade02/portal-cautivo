<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortalUserRequest;
use App\Http\Requests\UpdatePortalUserRequest;
use App\Models\Business;
use App\Models\PortalUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PortalUserController extends Controller
{
    public function index(): View
    {
        $portalUsers = PortalUser::query()
            ->with([
                'business.plan',
            ])
            ->orderBy('username')
            ->get();

        return view(
            'panel.portal-users.index',
            compact('portalUsers')
        );
    }

    public function create(): View
    {
        $businesses = Business::query()
            ->where('status', '!=', 'cancelled')
            ->orderBy('local_number')
            ->get();

        return view(
            'panel.portal-users.create',
            compact('businesses')
        );
    }

    public function store(
        StorePortalUserRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        PortalUser::create($data);

        return to_route('panel.usuarios.index')
            ->with(
                'success',
                'El usuario del portal fue creado correctamente.'
            );
    }

    public function edit(PortalUser $portalUser): View
    {
        $businesses = Business::query()
            ->orderBy('local_number')
            ->get();

        return view(
            'panel.portal-users.edit',
            compact('portalUser', 'businesses')
        );
    }

    public function update(
        UpdatePortalUserRequest $request,
        PortalUser $portalUser
    ): RedirectResponse {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $portalUser->update($data);

        return to_route('panel.usuarios.index')
            ->with(
                'success',
                'El usuario del portal fue actualizado correctamente.'
            );
    }

    public function destroy(
        PortalUser $portalUser
    ): RedirectResponse {
        $portalUser->delete();

        return to_route('panel.usuarios.index')
            ->with(
                'success',
                'El usuario del portal fue eliminado correctamente.'
            );
    }
}
