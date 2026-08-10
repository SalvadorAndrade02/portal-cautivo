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

    <title>Conectando a internet</title>

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

        .card {
            width: min(520px, 100%);
            padding: 30px;

            border-radius: 16px;
            text-align: center;

            background: white;

            box-shadow:
                0 14px 38px rgba(16, 24, 40, .10);
        }

        .spinner {
            width: 48px;
            height: 48px;

            margin: 22px auto;

            border:
                5px solid #e4e7ec;

            border-top-color:
                #172033;

            border-radius: 50%;

            animation:
                spin .8s linear infinite;
        }

        .success {
            color: #067647;
        }

        .error {
            color: #b42318;
        }

        .button {
            display: none;

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

        @keyframes spin {
            to {
                transform:
                    rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="card">

        <h1 id="title">
            Registro completado
        </h1>

        <p>
            Gracias,
            {{ $visitorAccess['visitor_name'] }}.
        </p>

        <div
            id="spinner"
            class="spinner">
        </div>

        <p id="status">
            Estamos habilitando tu acceso a internet.
        </p>

        <button
            id="retryButton"
            class="button"
            type="button">
            Reintentar conexión
        </button>

    </div>

    <script>
        const portalOrigin = @json(
            $visitorAccess['portal_origin'] ?? ''
        );

        const username = @json(
            $visitorAccess['username'] ?? ''
        );

        const password = @json(
            $visitorAccess['password'] ?? ''
        );

        const redirectUrl = @json(
            $visitorAccess['redirect_url'] ??
            'http://neverssl.com'
        );

        const titleElement =
            document.getElementById(
                'title'
            );

        const statusElement =
            document.getElementById(
                'status'
            );

        const spinnerElement =
            document.getElementById(
                'spinner'
            );

        const retryButton =
            document.getElementById(
                'retryButton'
            );

        function showError(message) {
            spinnerElement.style.display =
                'none';

            titleElement.textContent =
                'No fue posible habilitar el acceso';

            titleElement.className =
                'error';

            statusElement.textContent =
                message;

            retryButton.style.display =
                'block';
        }

        function createHiddenInput(
            name,
            value
        ) {
            const input =
                document.createElement(
                    'input'
                );

            input.type =
                'hidden';

            input.name =
                name;

            input.value =
                value;

            return input;
        }

        function authenticateInOpnsense() {
            retryButton.style.display =
                'none';

            spinnerElement.style.display =
                'block';

            titleElement.textContent =
                'Conectando';

            titleElement.className =
                '';

            statusElement.textContent =
                'Estamos habilitando tu acceso a internet.';

            if (
                !portalOrigin ||
                !/^https?:\/\//i.test(
                    portalOrigin
                )
            ) {
                showError(
                    'La dirección del portal cautivo no está configurada correctamente.'
                );

                return;
            }

            if (
                !username ||
                !password
            ) {
                showError(
                    'No se encontraron las credenciales temporales.'
                );

                return;
            }

            try {
                const iframeName =
                    'opnsense-login-frame';

                let iframe =
                    document.getElementById(
                        iframeName
                    );

                if (!iframe) {
                    iframe =
                        document.createElement(
                            'iframe'
                        );

                    iframe.id =
                        iframeName;

                    iframe.name =
                        iframeName;

                    iframe.style.display =
                        'none';

                    document.body.appendChild(
                        iframe
                    );
                }

                const form =
                    document.createElement(
                        'form'
                    );

                form.method =
                    'POST';

                form.action =
                    portalOrigin.replace(
                        /\/+$/,
                        ''
                    ) +
                    '/api/captiveportal/access/logon/';

                form.target =
                    iframeName;

                form.style.display =
                    'none';

                form.acceptCharset =
                    'UTF-8';

                form.appendChild(
                    createHiddenInput(
                        'user',
                        username
                    )
                );

                form.appendChild(
                    createHiddenInput(
                        'password',
                        password
                    )
                );

                document.body.appendChild(
                    form
                );

                form.submit();

                titleElement.textContent =
                    'Validando acceso';

                titleElement.className =
                    'success';

                statusElement.textContent =
                    'Estamos terminando de habilitar tu conexión.';

                /*
                 * Como ahora estamos dentro de Chrome,
                 * podemos dejar que OPNsense termine
                 * tranquilamente antes de navegar.
                 */
                window.setTimeout(
                    () => {
                        titleElement.textContent =
                            'Conexión habilitada';

                        statusElement.textContent =
                            'Abriendo contenido recomendado...';
                    },
                    3500
                );

                /*
                 * Esperamos 5 segundos antes de
                 * abrir el destino del interés.
                 */
                window.setTimeout(
                    () => {
                        try {
                            const destination =
                                new URL(
                                    redirectUrl
                                );

                            destination
                                .searchParams
                                .set(
                                    '_portal',
                                    Date.now()
                                    .toString()
                                );

                            window.location.replace(
                                destination
                                .toString()
                            );

                        } catch (error) {
                            window.location.replace(
                                'http://neverssl.com/?_portal=' +
                                Date.now()
                            );
                        }
                    },
                    5000
                );

                window.setTimeout(
                    () => {
                        form.remove();
                    },
                    7000
                );

            } catch (error) {
                console.error(
                    'Captive portal form login:',
                    error
                );

                showError(
                    'No fue posible enviar la solicitud al portal cautivo.'
                );
            }
        }

        retryButton.addEventListener(
            'click',
            authenticateInOpnsense
        );

        /*
         * Ya estamos en Chrome.
         * Autenticamos automáticamente.
         */
        authenticateInOpnsense();
    </script>

</body>

</html>