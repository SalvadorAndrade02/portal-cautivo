<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Portal cautivo')
    </title>

    <style>
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-user span {
            font-size: 14px;
        }

        .logout-button {
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 7px;
            padding: 7px 10px;
            background: transparent;
            color: white;
            cursor: pointer;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            display: flex;
            flex-direction: column;
            gap: 8px;
            border: 1px solid #e3e8ef;
            border-radius: 12px;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 14px rgba(16, 24, 40, 0.05);
        }

        .stat-card span,
        .stat-card small {
            color: #667085;
        }

        .stat-card strong {
            font-size: 30px;
        }

        .warning-list {
            display: grid;
            gap: 12px;
        }

        .warning-list>div {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 1px solid #e7ebf0;
            padding: 12px 0;
        }

        .warning-list>div:last-child {
            border-bottom: 0;
        }

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .header-content {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        :root {
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
            background: #f4f6f9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
        }

        header {
            background: #172033;
            color: white;
            padding: 18px 28px;
        }

        header h1 {
            margin: 0;
            font-size: 21px;
        }

        main {
            width: min(1180px, calc(100% - 32px));
            margin: 32px auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .page-header h2 {
            margin: 0 0 6px;
        }

        .page-header p {
            margin: 0;
            color: #667085;
        }

        .card {
            background: white;
            border: 1px solid #e3e8ef;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 4px 14px rgba(16, 24, 40, 0.05);
        }

        .button {
            display: inline-block;
            border: 0;
            border-radius: 8px;
            padding: 10px 15px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            background: #172033;
            color: white;
        }

        .button-secondary {
            background: #e9edf3;
            color: #172033;
        }

        .button-danger {
            background: #b42318;
        }

        .button-small {
            padding: 7px 10px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px 11px;
            border-bottom: 1px solid #e7ebf0;
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: #475467;
            font-size: 13px;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .actions form {
            margin: 0;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-active {
            background: #dcfae6;
            color: #067647;
        }

        .badge-inactive {
            background: #fee4e2;
            color: #b42318;
        }

        .alert {
            border-radius: 8px;
            padding: 13px 15px;
            margin-bottom: 18px;
        }

        .alert-success {
            background: #dcfae6;
            color: #067647;
        }

        .alert-error {
            background: #fee4e2;
            color: #b42318;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            border: 1px solid #cfd6df;
            border-radius: 8px;
            padding: 10px 12px;
            font: inherit;
            background: white;
        }

        small {
            color: #667085;
            line-height: 1.5;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .checkbox input {
            width: auto;
        }

        .checkbox label {
            margin: 0;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
        }

        .field-error {
            color: #b42318;
            font-size: 13px;
            margin-top: 5px;
        }

        header {
            background: #172033;
            color: white;
            padding: 18px 28px;
        }

        header h1 {
            margin: 0 0 14px;
            font-size: 21px;
        }

        nav {
            display: flex;
            gap: 18px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        @media (max-width: 760px) {
            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field-full {
                grid-column: auto;
            }

            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="header-content">
            <div>
                <h1>Administración del portal cautivo</h1>

                <nav>
                    <a href="{{ route('panel.dashboard') }}">
                        Dashboard
                    </a>

                    <a href="{{ route('panel.planes.index') }}">
                        Planes
                    </a>

                    <a href="{{ route('panel.locales.index') }}">
                        Locales
                    </a>

                    <a href="{{ route('panel.usuarios.index') }}">
                        Usuarios
                    </a>
                </nav>
            </div>

            <div class="header-user">
                <span>
                    {{ auth()->user()->name }}
                </span>

                <form
                    action="{{ route('logout') }}"
                    method="POST">
                    @csrf

                    <button
                        class="logout-button"
                        type="submit">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main>
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </main>
</body>

</html>