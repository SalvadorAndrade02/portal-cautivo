<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        http-equiv="Cache-Control"
        content="no-store, no-cache, must-revalidate">

    <title>Acceso Wi-Fi</title>

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

            font-family:
                Arial,
                sans-serif;

            background: #eef2f6;
            color: #172033;
        }

        .card {
            width: min(520px,
                    100%);

            padding: 32px;

            border-radius: 16px;

            text-align: center;

            background: white;

            box-shadow:
                0 14px 38px rgba(16,
                    24,
                    40,
                    .10);
        }

        h1 {
            margin-top: 0;
        }

        .button {
            width: 100%;

            margin-top: 20px;
            padding: 15px;

            border: 0;
            border-radius: 8px;

            background: #172033;
            color: white;

            font: inherit;
            font-weight: bold;

            cursor: pointer;
        }

        .help {
            margin-top: 14px;

            color: #667085;

            font-size: 14px;
            line-height: 1.5;
        }
    </style>

</head>

<body>

    <div class="card">

        <h1>
            Acceso Wi-Fi
        </h1>

        <p>
            Para continuar con tu registro,
            abre el formulario en tu navegador.
        </p>

        <button
            id="openBrowserButton"
            class="button"
            type="button">
            Abrir registro
        </button>

        <p class="help">
            El registro y la conexión a internet
            se completarán desde tu navegador.
        </p>

    </div>

    <script>
        const browserUrl = @json(
            $browserUrl
        );

        const button =
            document.getElementById(
                'openBrowserButton'
            );

        button.addEventListener(
            'click',
            function() {
                button.disabled = true;

                button.textContent =
                    'Abriendo navegador...';

                const destination =
                    new URL(browserUrl);

                const isAndroid =
                    /Android/i.test(
                        navigator.userAgent
                    );

                if (!isAndroid) {
                    window.location.href =
                        destination.toString();

                    return;
                }

                const scheme =
                    destination.protocol
                    .replace(':', '');

                const intentPath =
                    destination.host +
                    destination.pathname +
                    destination.search +
                    destination.hash;

                const fallbackUrl =
                    encodeURIComponent(
                        destination.toString()
                    );

                const intentUrl =
                    'intent://' +
                    intentPath +
                    '#Intent;' +
                    'scheme=' +
                    scheme +
                    ';package=com.android.chrome;' +
                    'S.browser_fallback_url=' +
                    fallbackUrl +
                    ';end';

                window.location.href =
                    intentUrl;
            }
        );
    </script>

</body>

</html>