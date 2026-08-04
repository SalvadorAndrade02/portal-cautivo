<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Business;
use App\Models\Device;
use App\Models\PortalUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request): View
    {
        $devices = Device::query()
            ->with([
                'business',
                'portalUser',
                'visitor',
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
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere(
                                    'mac_address',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'last_ip_address',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'visitor',
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
                    );
                }
            )
            ->when(
                $request->filled('business_id'),
                fn(Builder $query) => $query->where(
                    'business_id',
                    $request->input('business_id')
                )
            )
            ->when(
                $request->input('status') === 'authorized',
                fn(Builder $query) => $query
                    ->where('authorized', true)
                    ->where('blocked', false)
            )
            ->when(
                $request->input('status') === 'pending',
                fn(Builder $query) => $query
                    ->where('authorized', false)
                    ->where('blocked', false)
            )
            ->when(
                $request->input('status') === 'blocked',
                fn(Builder $query) => $query
                    ->where('blocked', true)
            )
            ->when(
                $request->input('status') === 'bypass',
                fn(Builder $query) => $query
                    ->where('bypass_portal', true)
                    ->where('blocked', false)
            )
            ->orderByDesc('blocked')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $businesses = Business::query()
            ->orderBy('local_number')
            ->get();

        return view(
            'panel.devices.index',
            compact('devices', 'businesses')
        );
    }

    public function create(): View
    {
        $businesses = Business::query()
            ->where('status', '!=', 'cancelled')
            ->orderBy('local_number')
            ->get();

        $portalUsers = PortalUser::query()
            ->with('business')
            ->where('status', '!=', 'disabled')
            ->orderBy('username')
            ->get();

        return view(
            'panel.devices.create',
            compact('businesses', 'portalUsers')
        );
    }

    public function store(
        StoreDeviceRequest $request
    ): RedirectResponse {
        Device::create($request->validated());

        return to_route('panel.dispositivos.index')
            ->with(
                'success',
                'El dispositivo fue creado correctamente.'
            );
    }

    public function edit(Device $device): View
    {
        $businesses = Business::query()
            ->orderBy('local_number')
            ->get();

        $portalUsers = PortalUser::query()
            ->with('business')
            ->orderBy('username')
            ->get();

        return view(
            'panel.devices.edit',
            compact(
                'device',
                'businesses',
                'portalUsers'
            )
        );
    }

    public function update(
        UpdateDeviceRequest $request,
        Device $device
    ): RedirectResponse {
        $device->update($request->validated());

        return to_route('panel.dispositivos.index')
            ->with(
                'success',
                'El dispositivo fue actualizado correctamente.'
            );
    }

    public function destroy(
        Device $device
    ): RedirectResponse {
        $device->delete();

        return to_route('panel.dispositivos.index')
            ->with(
                'success',
                'El dispositivo fue eliminado correctamente.'
            );
    }
}
