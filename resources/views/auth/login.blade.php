<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prisijungimas - Humis</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-white: #ffffff;
            --bg-sidebar: #1e2532;
            --text-primary: #374151;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --accent: #10b981;
            --accent-hover: #059669;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 8px;
            --radius-lg: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .auth-card {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .auth-header {
            padding: 32px 32px 24px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .auth-logo {
            font-size: 32px;
            font-weight: 700;
            color: var(--bg-sidebar);
            margin-bottom: 8px;
        }

        .auth-logo span { color: var(--accent); }

        .auth-title {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .auth-body {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .form-input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--bg-white);
            transition: all 0.15s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .form-input--password {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn--primary {
            background: var(--accent);
            color: white;
        }

        .btn--primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert--danger {
            background: var(--danger-bg);
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .alert--success {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">Hum<span>is</span></div>
                <p class="auth-title">Užduočių perskirstymo sistema</p>
            </div>

            <div class="auth-body">
                @if(session('success'))
                    <div class="alert alert--success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert--danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="password">Slaptažodis</label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-input form-input--password"
                                   required
                                   autofocus
                                   placeholder="••••••••">
                            <button type="button" class="password-toggle" onclick="const i=document.getElementById('password');i.type=i.type==='password'?'text':'password'">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn--primary">
                        Prisijungti
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
