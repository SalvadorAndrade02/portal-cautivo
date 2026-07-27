<div class="form-grid">
    <div>
        <label for="business_id">Local</label>

        <select
            id="business_id"
            name="business_id"
            required>
            <option value="">Selecciona un local</option>

            @foreach ($businesses as $business)
            <option
                value="{{ $business->id }}"
                @selected(
                (string) old( 'business_id' ,
                $device->business_id ?? ''
                ) === (string) $business->id
                )
                >
                {{ $business->local_number }}
                — {{ $business->name }}
            </option>
            @endforeach
        </select>

        @error('business_id')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="portal_user_id">
            Usuario relacionado
        </label>

        <select
            id="portal_user_id"
            name="portal_user_id">
            <option value="">
                Sin usuario relacionado
            </option>

            @foreach ($portalUsers as $portalUser)
            <option
                value="{{ $portalUser->id }}"
                data-business="{{ $portalUser->business_id }}"
                @selected(
                (string) old( 'portal_user_id' ,
                $device->portal_user_id ?? ''
                ) === (string) $portalUser->id
                )
                >
                {{ $portalUser->username }}
                — {{ $portalUser->business->name }}
            </option>
            @endforeach
        </select>

        @error('portal_user_id')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="name">Nombre del dispositivo</label>

        <input
            id="name"
            name="name"
            type="text"
            maxlength="150"
            required
            value="{{ old('name', $device->name ?? '') }}">

        @error('name')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="device_type">
            Tipo de dispositivo
        </label>

        <select
            id="device_type"
            name="device_type"
            required>
            @php
            $types = [
            'phone' => 'Celular',
            'laptop' => 'Laptop',
            'pos' => 'Terminal POS',
            'camera' => 'Cámara',
            'printer' => 'Impresora',
            'tv' => 'Televisión',
            'iot' => 'Dispositivo IoT',
            'other' => 'Otro',
            ];
            @endphp

            @foreach ($types as $value => $label)
            <option
                value="{{ $value }}"
                @selected(
                old( 'device_type' ,
                $device->device_type ?? 'other'
                ) === $value
                )
                >
                {{ $label }}
            </option>
            @endforeach
        </select>

        @error('device_type')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="mac_address">Dirección MAC</label>

        <input
            id="mac_address"
            name="mac_address"
            type="text"
            maxlength="17"
            placeholder="AA:BB:CC:DD:EE:FF"
            required
            value="{{ old(
                'mac_address',
                $device->mac_address ?? ''
            ) }}">

        @error('mac_address')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="last_ip_address">
            Última dirección IP
        </label>

        <input
            id="last_ip_address"
            name="last_ip_address"
            type="text"
            maxlength="45"
            placeholder="10.50.0.105"
            value="{{ old(
                'last_ip_address',
                $device->last_ip_address ?? ''
            ) }}">

        <small>
            Posteriormente será actualizada automáticamente por RADIUS.
        </small>

        @error('last_ip_address')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field-full">
        <label for="notes">Notas</label>

        <textarea
            id="notes"
            name="notes"
            maxlength="500">{{ old('notes', $device->notes ?? '') }}</textarea>

        @error('notes')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field-full device-options">
        <div class="checkbox">
            <input name="authorized" type="hidden" value="0">

            <input
                id="authorized"
                name="authorized"
                type="checkbox"
                value="1"
                @checked(
                old( 'authorized' ,
                $device->authorized ?? false
            )
            )
            >

            <label for="authorized">
                Dispositivo autorizado
            </label>
        </div>

        <div class="checkbox">
            <input name="bypass_portal" type="hidden" value="0">

            <input
                id="bypass_portal"
                name="bypass_portal"
                type="checkbox"
                value="1"
                @checked(
                old( 'bypass_portal' ,
                $device->bypass_portal ?? false
            )
            )
            >

            <label for="bypass_portal">
                Permitir acceso sin mostrar el portal
            </label>
        </div>

        <div class="checkbox">
            <input name="blocked" type="hidden" value="0">

            <input
                id="blocked"
                name="blocked"
                type="checkbox"
                value="1"
                @checked(
                old( 'blocked' ,
                $device->blocked ?? false
            )
            )
            >

            <label for="blocked">
                Dispositivo bloqueado
            </label>
        </div>

        <small>
            El bloqueo tiene prioridad sobre la autorización y el acceso
            sin portal.
        </small>
    </div>
</div>

<div class="form-actions">
    <button class="button" type="submit">
        {{ $submitText }}
    </button>

    <a
        class="button button-secondary"
        href="{{ route('panel.dispositivos.index') }}">
        Cancelar
    </a>
</div>

<script>
    const businessSelect = document.getElementById('business_id');
    const portalUserSelect = document.getElementById('portal_user_id');

    function filterPortalUsers() {
        const businessId = businessSelect.value;

        for (const option of portalUserSelect.options) {
            if (!option.value) {
                option.hidden = false;
                continue;
            }

            option.hidden =
                option.dataset.business !== businessId;
        }

        const selected = portalUserSelect.selectedOptions[0];

        if (
            selected &&
            selected.value &&
            selected.dataset.business !== businessId
        ) {
            portalUserSelect.value = '';
        }
    }

    businessSelect.addEventListener(
        'change',
        filterPortalUsers
    );

    filterPortalUsers();
</script>