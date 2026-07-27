<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Acceso administrativo</title>

    <style>
        :root {
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
            background: #eef2f6;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-container {
            width: min(430px, 100%);
        }

        .login-header {
            margin-bottom: 22px;
            text-align: center;
        }

        .login-header h1 {
            margin: 0 0 8px;
            font-size: 26px;
        }

        .login-header p {
            margin: 0;
            color: #667085;
        }

        .card {
            background: white;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 12px 32px rgba(16, 24, 40, 0.09);
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid #cfd6df;
            border-radius: 8px;
            padding: 11px 12px;
            font: inherit;
        }

        input:focus {
            outline: 2px solid #98a2b3;
            border-color: #667085;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .remember label {
            margin: 0;
            font-weight: normal;
        }

        .button {
            width: 100%;
            border: 0;
            border-radius: 8px;
            padding: 12px;
            background: #172033;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        .error {
            margin-top: 6px;
            color: #b42318;
            font-size: 13px;
        }

        .alert {
            margin-bottom: 18px;
            border-radius: 8px;
            padding: 12px;
            background: #fee4e2;
            color: #b42318;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Portal cautivo</h1>

            <p>Acceso al panel administrativo</p>
        </div>

        <div class="card">
            @if ($errors->any())
            <div class="alert">
                Revisa las credenciales e intenta nuevamente.
            </div>
            @endif

            <form
                action="{{ route('login.store') }}"
                method="POST">
                @csrf

                <div class="field">
                    <label for="email">
                        Correo electrónico
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        autofocus
                        required
                        value="{{ old('email') }}">

                    @error('email')
                    <div class="error">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required>

                    @error('password')
                    <div class="error">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="remember">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        value="1">

                    <label for="remember">
                        Mantener sesión iniciada
                    </label>
                </div>

                <button
                    class="button"
                    type="submit">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</body>

</html>