<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Acceso Wi-Fi</title>

    <script
        src="https://challenges.cloudflare.com/turnstile/v0/api.js"
        async
        defer></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #eef2f6;
            color: #172033;
        }

        .container {
            width: min(620px, 100%);
        }

        .card {
            padding: 28px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 14px 38px rgba(16, 24, 40, .10);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .subtitle {
            margin-top: 0;
            margin-bottom: 26px;
            color: #667085;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"] {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cfd6df;
            border-radius: 8px;
            font: inherit;
        }

        .interests {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .option {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            border: 1px solid #e3e8ef;
            border-radius: 9px;
            padding: 11px;
        }

        .option label {
            margin: 0;
            font-weight: normal;
        }

        .consents {
            display: grid;
            gap: 12px;
            margin: 22px 0;
        }

        .error,
        .alert {
            color: #b42318;
        }

        .alert {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            background: #fee4e2;
        }

        .button {
            width: 100%;
            margin-top: 20px;
            padding: 13px;
            border: 0;
            border-radius: 8px;
            background: #172033;
            color: white;
            font: inherit;
            font-weight: bold;
            cursor: pointer;
        }

        .honeypot {
            position: absolute;
            left: -10000px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        @media (max-width: 560px) {
            body {
                padding: 14px;
            }

            .card {
                padding: 21px;
            }

            .interests {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>Conéctate a nuestra red Wi-Fi</h1>

            <p class="subtitle">
                Completa tu registro para obtener acceso a internet.
            </p>

            @if ($errors->any())
            <div class="alert">
                Revisa la información e intenta nuevamente.
            </div>
            @endif

            <form
                action="{{ route('wifi.register.store') }}"
                method="POST">
                @csrf

                <div class="field">
                    <label for="full_name">
                        Nombre completo
                    </label>

                    <input
                        id="full_name"
                        name="full_name"
                        type="text"
                        maxlength="150"
                        autocomplete="name"
                        required
                        value="{{ old('full_name') }}">

                    @error('full_name')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="phone">Número de teléfono</label>

                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        maxlength="20"
                        autocomplete="tel"
                        required
                        value="{{ old('phone') }}">

                    @error('phone')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="email">
                        Correo electrónico
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        maxlength="150"
                        autocomplete="email"
                        required
                        value="{{ old('email') }}">

                    @error('email')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label>Áreas de interés</label>

                    <div class="interests">
                        @foreach ($interestAreas as $area)
                        <div class="option">
                            <input
                                id="interest_{{ $area->id }}"
                                name="interest_area_ids[]"
                                type="checkbox"
                                value="{{ $area->id }}"
                                @checked(
                                in_array(
                                $area->id,
                            old(
                            'interest_area_ids',
                            []
                            )
                            )
                            )
                            >

                            <label
                                for="interest_{{ $area->id }}">
                                {{ $area->name }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    @error('interest_area_ids')
                    <div class="error">{{ $message }}</div>
                    @enderror

                    @error('interest_area_ids.*')
                    <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="consents">
                    <div class="option">
                        <input
                            id="privacy_accepted"
                            name="privacy_accepted"
                            type="checkbox"
                            value="1"
                            required
                            @checked(old('privacy_accepted'))>

                        <label for="privacy_accepted">
                            Acepto el aviso de privacidad.
                        </label>
                    </div>

                    <div class="option">
                        <input
                            id="terms_accepted"
                            name="terms_accepted"
                            type="checkbox"
                            value="1"
                            required
                            @checked(old('terms_accepted'))>

                        <label for="terms_accepted">
                            Acepto los términos de uso de la red.
                        </label>
                    </div>

                    <div class="option">
                        <input
                            id="marketing_consent"
                            name="marketing_consent"
                            type="checkbox"
                            value="1"
                            @checked(old('marketing_consent'))>

                        <label for="marketing_consent">
                            Deseo recibir promociones relacionadas con
                            mis áreas de interés.
                        </label>
                    </div>
                </div>

                <div class="honeypot" aria-hidden="true">
                    <label for="website">Sitio web</label>

                    <input
                        id="website"
                        name="website"
                        type="text"
                        tabindex="-1"
                        autocomplete="off">
                </div>

                <div
                    class="cf-turnstile"
                    data-sitekey="{{ config(
                    'services.turnstile.site_key'
                ) }}"
                    data-theme="light"
                    data-language="es"></div>

                @error('turnstile')
                <div class="error">{{ $message }}</div>
                @enderror

                @error('cf-turnstile-response')
                <div class="error">{{ $message }}</div>
                @enderror

                <button class="button" type="submit">
                    Registrarme y conectarme
                </button>
            </form>
        </div>
    </div>
</body>

</html>