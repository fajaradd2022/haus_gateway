<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — Mini Helpdesk AI Assist</title>
    @vite(['resources/css/app.css'])
    <style>
        body.auth-page { height: auto; overflow: auto; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-shell">
        <div class="auth-card">

            {{-- Left: Brand --}}
            <div class="auth-brand">
                <div class="auth-logo">
                    <!-- <div class="auth-logo-mark">💬</div> -->
                    <span class="auth-logo-name">Mini Helpdesk</span>
                </div>
                <h1>Solusi helpdesk<br>cerdas berbasis AI</h1>
                <p class="auth-copy">Kelola tiket pelanggan, balasan AI otomatis, dan SLA monitoring dalam satu workspace yang bersih.</p>
                <div class="auth-features">
                    <div class="auth-feature">
                        <span class="auth-feature-icon">🤖</span>
                        AI-powered reply suggestions
                    </div>
                    <div class="auth-feature">
                        <span class="auth-feature-icon">⚡</span>
                        Real-time ticket tracking
                    </div>
                    <div class="auth-feature">
                        <span class="auth-feature-icon">📊</span>
                        SLA & performance dashboard
                    </div>
                    <div class="auth-feature">
                        <span class="auth-feature-icon">🔒</span>
                        Internal notes & audit trail
                    </div>
                </div>
            </div>

            {{-- Right: Form --}}
            <div class="auth-form-wrap">
                <div class="auth-form-title">Selamat datang 👋</div>
                <div class="auth-form-sub">Masuk untuk mengakses workspace Anda</div>

                <form class="auth-form" method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                            placeholder="agent@perusahaan.com"
                        >
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                        >
                    </div>

                    @error('email')
                    <div class="field-error">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </div>
                    @enderror

                    <label class="remember-row" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                        <span>Tetap masuk selama 30 hari</span>
                    </label>

                    <div class="auth-actions">
                        <button class="btn-primary" type="submit">
                            Masuk
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        const theme = localStorage.getItem('helpdesk-theme') ?? 'light';
        document.documentElement.dataset.theme = theme;
    </script>
</body>
</html>
