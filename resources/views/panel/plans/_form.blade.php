<div class="form-grid">
    <div class="field-full">
        <label for="name">Nombre del plan</label>

        <input
            id="name"
            name="name"
            type="text"
            maxlength="100"
            required
            value="{{ old('name', $plan->name ?? '') }}">

        @error('name')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field-full">
        <label for="description">Descripción</label>

        <textarea
            id="description"
            name="description"
            maxlength="255">{{ old('description', $plan->description ?? '') }}</textarea>

        @error('description')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="download_speed_mbps">
            Descarga en Mbps
        </label>

        <input
            id="download_speed_mbps"
            name="download_speed_mbps"
            type="number"
            min="1"
            value="{{ old(
                'download_speed_mbps',
                $plan->download_speed_mbps ?? ''
            ) }}">

        @error('download_speed_mbps')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="upload_speed_mbps">
            Subida en Mbps
        </label>

        <input
            id="upload_speed_mbps"
            name="upload_speed_mbps"
            type="number"
            min="1"
            value="{{ old(
                'upload_speed_mbps',
                $plan->upload_speed_mbps ?? ''
            ) }}">

        @error('upload_speed_mbps')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="session_timeout_minutes">
            Duración máxima de sesión
        </label>

        <input
            id="session_timeout_minutes"
            name="session_timeout_minutes"
            type="number"
            min="1"
            required
            value="{{ old(
                'session_timeout_minutes',
                $plan->session_timeout_minutes ?? 480
            ) }}">

        @error('session_timeout_minutes')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="idle_timeout_minutes">
            Inactividad máxima en minutos
        </label>

        <input
            id="idle_timeout_minutes"
            name="idle_timeout_minutes"
            type="number"
            min="1"
            required
            value="{{ old(
                'idle_timeout_minutes',
                $plan->idle_timeout_minutes ?? 15
            ) }}">

        @error('idle_timeout_minutes')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label for="max_devices">
            Máximo de dispositivos
        </label>

        <input
            id="max_devices"
            name="max_devices"
            type="number"
            min="1"
            required
            value="{{ old(
                'max_devices',
                $plan->max_devices ?? 1
            ) }}">

        @error('max_devices')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="checkbox">
        <input
            name="active"
            type="hidden"
            value="0">

        <input
            id="active"
            name="active"
            type="checkbox"
            value="1"
            @checked(old('active', $plan->active ?? true))
        >

        <label for="active">Plan activo</label>

        @error('active')
        <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-actions">
    <button class="button" type="submit">
        {{ $submitText }}
    </button>

    <a
        class="button button-secondary"
        href="{{ route('panel.planes.index') }}">
        Cancelar
    </a>
</div>