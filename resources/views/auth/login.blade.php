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
            --bg-body: #f8f9fa;
            --bg-white: #ffffff;
            --bg-sidebar: #1e2532;
            --text-dark: #1a1a2e;
            --text-primary: #374151;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --accent: #10b981;
            --accent-hover: #059669;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --info-bg: #eff6ff;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 8px;
            --radius-lg: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1e2532 0%, #2a3444 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
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
            color: var(--text-dark);
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

        .form-input.is-invalid {
            border-color: var(--danger);
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
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        .form-error {
            color: var(--danger);
            font-size: 13px;
            margin-top: 6px;
        }

        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-checkbox input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
        }

        .form-checkbox label {
            font-size: 14px;
            color: var(--text-secondary);
            cursor: pointer;
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
            text-decoration: none;
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

        .btn--primary:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn--secondary {
            background: var(--bg-body);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn--secondary:hover {
            background: var(--border-color);
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

        .alert--info {
            background: var(--info-bg);
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .welcome-message {
            text-align: center;
            padding: 16px;
            background: var(--info-bg);
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .welcome-message__name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 16px;
        }

        .welcome-message__text {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .step-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--border-color);
        }

        .step-dot--active {
            background: var(--accent);
        }

        .hidden { display: none !important; }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">Humi<span>s</span></div>
                <p class="auth-title">Žmogiškųjų resursų paskirstymo sistema</p>
            </div>
            
            <div class="auth-body">
                @if(session('success'))
                    <div class="alert alert--success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert--danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert--danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="step-indicator">
                    <div class="step-dot step-dot--active" id="step-dot-1"></div>
                    <div class="step-dot" id="step-dot-2"></div>
                </div>

                <div class="welcome-message hidden" id="welcome-message">
                    <div class="welcome-message__name" id="welcome-name"></div>
                    <div class="welcome-message__text" id="welcome-text"></div>
                </div>

                <div id="step-email">
                    <div class="form-group">
                        <label class="form-label" for="email">El. paštas</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus
                               placeholder="jusu@email.lt">
                        <div class="form-error hidden" id="email-error"></div>
                    </div>

                    <button type="button" class="btn btn--primary" id="btn-continue">
                        Tęsti
                    </button>
                </div>

                <form method="POST" action="{{ route('login') }}" id="form-login" class="hidden">
                    @csrf
                    <input type="hidden" name="email" id="login-email">

                    <div class="form-group">
                        <label class="form-label" for="password">Slaptažodis</label>
                        <div class="form-input-wrapper">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input form-input--password" 
                                   required
                                   placeholder="••••••••">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <svg id="password-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-checkbox">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Prisiminti mane (24 val.)</label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button type="button" class="btn btn--secondary" onclick="goBack()" style="width: auto; padding: 12px 20px;">
                            ← Atgal
                        </button>
                        <button type="submit" class="btn btn--primary">
                            Prisijungti
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('register') }}" id="form-register" class="hidden">
                    @csrf
                    <input type="hidden" name="email" id="register-email">

                    <div class="form-group">
                        <label class="form-label" for="new-password">Sukurkite slaptažodį</label>
                        <div class="form-input-wrapper">
                            <input type="password" 
                                   id="new-password" 
                                   name="password" 
                                   class="form-input form-input--password" 
                                   required
                                   minlength="8"
                                   placeholder="••••••••">
                            <button type="button" class="password-toggle" onclick="togglePassword('new-password')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="form-hint">Mažiausiai 8 simboliai</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password-confirm">Pakartokite slaptažodį</label>
                        <div class="form-input-wrapper">
                            <input type="password" 
                                   id="password-confirm" 
                                   name="password_confirmation" 
                                   class="form-input form-input--password" 
                                   required
                                   placeholder="••••••••">
                            <button type="button" class="password-toggle" onclick="togglePassword('password-confirm')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button type="button" class="btn btn--secondary" onclick="goBack()" style="width: auto; padding: 12px 20px;">
                            ← Atgal
                        </button>
                        <button type="submit" class="btn btn--primary">
                            Registruotis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const emailInput = document.getElementById('email');
        const btnContinue = document.getElementById('btn-continue');
        const stepEmail = document.getElementById('step-email');
        const formLogin = document.getElementById('form-login');
        const formRegister = document.getElementById('form-register');
        const emailError = document.getElementById('email-error');
        const welcomeMessage = document.getElementById('welcome-message');
        const welcomeName = document.getElementById('welcome-name');
        const welcomeText = document.getElementById('welcome-text');
        const stepDot1 = document.getElementById('step-dot-1');
        const stepDot2 = document.getElementById('step-dot-2');

        emailInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkEmail();
            }
        });

        btnContinue.addEventListener('click', checkEmail);

        async function checkEmail() {
            const email = emailInput.value.trim();
            
            if (!email || !email.includes('@')) {
                showError('Įveskite teisingą el. paštą');
                return;
            }

            btnContinue.disabled = true;
            btnContinue.textContent = 'Tikrinama...';

            try {
                const response = await fetch('{{ route("check-email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ email }),
                });

                const data = await response.json();

                if (!data.exists) {
                    showError(data.message);
                    btnContinue.disabled = false;
                    btnContinue.textContent = 'Tęsti';
                    return;
                }

                stepEmail.classList.add('hidden');
                stepDot1.classList.remove('step-dot--active');
                stepDot2.classList.add('step-dot--active');

                welcomeName.textContent = 'Sveiki, ' + data.name + '!';
                welcomeMessage.classList.remove('hidden');

                if (data.is_registered) {
                    welcomeText.textContent = 'Įveskite slaptažodį prisijungimui';
                    document.getElementById('login-email').value = email;
                    formLogin.classList.remove('hidden');
                    document.getElementById('password').focus();
                } else {
                    welcomeText.textContent = 'Tai pirmas jūsų prisijungimas. Sukurkite slaptažodį.';
                    document.getElementById('register-email').value = email;
                    formRegister.classList.remove('hidden');
                    document.getElementById('new-password').focus();
                }

            } catch (error) {
                showError('Įvyko klaida. Bandykite dar kartą.');
                btnContinue.disabled = false;
                btnContinue.textContent = 'Tęsti';
            }
        }

        function showError(message) {
            emailError.textContent = message;
            emailError.classList.remove('hidden');
            emailInput.classList.add('is-invalid');
        }

        function goBack() {
            formLogin.classList.add('hidden');
            formRegister.classList.add('hidden');
            welcomeMessage.classList.add('hidden');
            stepEmail.classList.remove('hidden');
            stepDot1.classList.add('step-dot--active');
            stepDot2.classList.remove('step-dot--active');
            btnContinue.disabled = false;
            btnContinue.textContent = 'Tęsti';
            emailError.classList.add('hidden');
            emailInput.classList.remove('is-invalid');
            emailInput.focus();
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
        }
    </script>
</body>
</html>
