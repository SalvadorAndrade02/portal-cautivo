<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Registro completado</title>

    <style>
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

        .card {
            width: min(520px, 100%);
            padding: 30px;
            border-radius: 16px;
            background: white;
            box-shadow: 0 14px 38px rgba(16, 24, 40, .10);
        }

        .success {
            color: #067647;
        }

        .credentials {
            margin-top: 22px;
            padding: 16px;
            border-radius: 10px;
            background: #f2f4f7;
            overflow-wrap: anywhere;
        }

        .credentials p {
            margin: 8px 0;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1 class="success">Registro completado</h1>

        <p>
            Gracias, {{ $visitorAccess['visitor_name'] }}.
            Tu acceso temporal fue generado correctamente.
        </p>

        <div class="credentials">
            <p>
                <strong>Usuario temporal:</strong>
                {{ $visitorAccess['username'] }}
            </p>

            <p>
                <strong>Contraseña temporal:</strong>
                {{ $visitorAccess['password'] }}
            </p>

            <p>
                <strong>Expira:</strong>
                {{ \Illuminate\Support\Carbon::parse(
                $visitorAccess['expires_at']
            )->format('d/m/Y H:i') }}
            </p>
        </div>

        <p>
            Estas credenciales se muestran solamente para la prueba local.
            Posteriormente el portal las enviará automáticamente a OPNsense.
        </p>
    </div>
</body>

</html>