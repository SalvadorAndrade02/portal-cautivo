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
                old( 'business_id' ,
                $portalUser->business_id ?? ''
                ) == $business->id
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
        <label for="status">Estado</label>

        <select
            id="status"
            name="status"
            required>
            <option
                value="active"
                @selected(
                old( 'status' ,
                $portalUser->status ?? 'active'
                ) === 'active'
                )
                >
                Activo
            </option>

            <option
                value="suspended"
                @selected(
                old( 'status' ,
                $portalUser->status ?? ''
                ) === 'suspended'
                )
                >
                Suspendido
            </option>

            <option
                value="disabled"
                @selected(
                old( 'status' ,
                $portalUser->status ?? ''
                ) === 'disabled'
                )
                >
                Deshabilitado
            </option>
        </select>

        @error('status')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="username">Nombre de usuario</label>

        <input
            id="username"
            name="username"
            type="text"
            maxlength="100"
            autocomplete="off"
            required
            value="{{ old(
                'username',
                $portalUser->username ?? ''
            ) }}">

        @error('username')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="full_name">Nombre completo</label>

        <input
            id="full_name"
            name="full_name"
            type="text"
            maxlength="150"
            value="{{ old(
                'full_name',
                $portalUser->full_name ?? ''
            ) }}">

        @error('full_name')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="password">
            Contraseña
            @isset($portalUser)
            <small>
                (déjala vacía para conservarla)
            </small>
            @endisset
        </label>

        <input
            id="password"
            name="password"
            type="password"
            minlength="8"
            autocomplete="new-password"
            @required(!isset($portalUser))>

        @error('password')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="password_confirmation">
            Confirmar contraseña
        </label>

        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            minlength="8"
            autocomplete="new-password"
            @required(!isset($portalUser))>
    </div>
</div>

<div class="form-actions">
    <button class="button" type="submit">
        {{ $submitText }}
    </button>

    <a
        class="button button-secondary"
        href="{{ route('panel.usuarios.index') }}">
        Cancelar
    </a>
</div>