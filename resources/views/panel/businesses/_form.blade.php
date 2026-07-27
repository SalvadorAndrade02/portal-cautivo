<div class="form-grid">
    <div>
        <label for="name">Nombre del local</label>

        <input
            id="name"
            name="name"
            type="text"
            maxlength="150"
            required
            value="{{ old('name', $business->name ?? '') }}">

        @error('name')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="local_number">Número de local</label>

        <input
            id="local_number"
            name="local_number"
            type="text"
            maxlength="50"
            required
            value="{{ old(
                'local_number',
                $business->local_number ?? ''
            ) }}">

        @error('local_number')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="plan_id">Plan de internet</label>

        <select
            id="plan_id"
            name="plan_id">
            <option value="">Sin plan asignado</option>

            @foreach ($plans as $plan)
            <option
                value="{{ $plan->id }}"
                @selected(
                (string) old( 'plan_id' ,
                $business->plan_id ?? ''
                ) === (string) $plan->id
                )
                >
                {{ $plan->name }}

                @if (!$plan->active)
                — Inactivo
                @endif
            </option>
            @endforeach
        </select>

        @error('plan_id')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="status">Estado del servicio</label>

        <select
            id="status"
            name="status"
            required>
            <option
                value="active"
                @selected(
                old( 'status' ,
                $business->status ?? 'active'
                ) === 'active'
                )
                >
                Activo
            </option>

            <option
                value="suspended"
                @selected(
                old( 'status' ,
                $business->status ?? ''
                ) === 'suspended'
                )
                >
                Suspendido
            </option>

            <option
                value="cancelled"
                @selected(
                old( 'status' ,
                $business->status ?? ''
                ) === 'cancelled'
                )
                >
                Cancelado
            </option>
        </select>

        @error('status')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="responsible_name">
            Nombre del responsable
        </label>

        <input
            id="responsible_name"
            name="responsible_name"
            type="text"
            maxlength="150"
            value="{{ old(
                'responsible_name',
                $business->responsible_name ?? ''
            ) }}">

        @error('responsible_name')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="phone">Teléfono</label>

        <input
            id="phone"
            name="phone"
            type="text"
            maxlength="30"
            value="{{ old(
                'phone',
                $business->phone ?? ''
            ) }}">

        @error('phone')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="email">Correo electrónico</label>

        <input
            id="email"
            name="email"
            type="email"
            maxlength="150"
            value="{{ old(
                'email',
                $business->email ?? ''
            ) }}">

        @error('email')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="max_devices">
            Límite particular de dispositivos
        </label>

        <input
            id="max_devices"
            name="max_devices"
            type="number"
            min="1"
            max="1000"
            value="{{ old(
                'max_devices',
                $business->max_devices ?? ''
            ) }}">

        <small>
            Déjalo vacío para utilizar el límite definido por el plan.
        </small>

        @error('max_devices')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field-full">
        <label for="address">Dirección o ubicación</label>

        <textarea
            id="address"
            name="address"
            maxlength="255">{{ old('address', $business->address ?? '') }}</textarea>

        @error('address')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-actions">
    <button
        class="button"
        type="submit">
        {{ $submitText }}
    </button>

    <a
        class="button button-secondary"
        href="{{ route('panel.locales.index') }}">
        Cancelar
    </a>
</div>