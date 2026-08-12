<div class="form-grid">

    <div class="field-full">

        <label for="name">
            Nombre
        </label>

        <input
            id="name"
            name="name"
            type="text"
            maxlength="120"
            required
            value="{{ old(
                'name',
                $interestArea->name ?? ''
            ) }}">

        @error('name')
        <div class="field-error">
            {{ $message }}
        </div>
        @enderror

    </div>

    <div class="field-full">

        <label for="description">
            Descripción
        </label>

        <textarea
            id="description"
            name="description"
            maxlength="500">{{ old(
            'description',
            $interestArea->description ?? ''
        ) }}</textarea>

        <small>
            Descripción interna de esta
            categoría de interés.
        </small>

        @error('description')
        <div class="field-error">
            {{ $message }}
        </div>
        @enderror

    </div>

    <div class="field-full">

        <label for="redirect_url">
            URL de redirección
        </label>

        <input
            id="redirect_url"
            name="redirect_url"
            type="url"
            maxlength="2048"
            placeholder="https://..."
            value="{{ old(
                'redirect_url',
                $interestArea->redirect_url ?? ''
            ) }}">

        <small>
            Página que se abrirá después
            de proporcionar acceso a internet.
        </small>

        @error('redirect_url')
        <div class="field-error">
            {{ $message }}
        </div>
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
            @checked(
            old( 'active' ,
            $interestArea->active
        ?? true
        )
        )
        >

        <label for="active">
            Área activa
        </label>

        @error('active')
        <div class="field-error">
            {{ $message }}
        </div>
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
        href="{{ route(
            'panel.areas-interes.index'
        ) }}">
        Cancelar
    </a>

</div>