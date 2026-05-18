<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ProcureX</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            -webkit-font-smoothing: antialiased;
        }
        .auth-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            width: 100%; max-width: 440px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.08);
        }
        .auth-title { font-size: 22px; font-weight: 700; color: #111827; text-align: center; }
        .auth-sub { font-size: 14px; color: #6b7280; text-align: center; margin-top: 6px; }
        .form-group { margin-top: 18px; display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 11.5px; font-weight: 600; color: #111827; letter-spacing: .04em; text-transform: uppercase; }
        .form-input {
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px; color: #111827;
            font-family: inherit;
            transition: border-color .15s, box-shadow .15s;
            width: 100%;
        }
        .form-input:focus { outline: none; border-color: #3b5bdb; box-shadow: 0 0 0 3px rgba(59,91,219,.12); }
        .form-input::placeholder { color: #9ca3af; }
        .input-wrap { position: relative; }
        .input-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #9ca3af;
            padding: 4px;
        }
        .input-toggle:hover { color: #4b5563; }
        .form-row { display: flex; align-items: center; justify-content: space-between; margin-top: 14px; }
        .form-check { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: #374151; cursor: pointer; }
        .form-check input[type=checkbox] { width: 14px; height: 14px; accent-color: #3b5bdb; }
        .link { color: #3b5bdb; text-decoration: none; font-size: 12.5px; font-weight: 500; }
        .link:hover { text-decoration: underline; }
        .btn-submit {
            width: 100%;
            margin-top: 20px;
            padding: 11px;
            background: #3b5bdb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            font-family: inherit;
            transition: background .15s, box-shadow .15s;
        }
        .btn-submit:hover { background: #3451c7; box-shadow: 0 4px 12px rgba(59,91,219,.25); }
        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
        .divider span { font-size: 12px; color: #9ca3af; }
        .auth-footer { text-align: center; font-size: 13px; color: #6b7280; }
        .alert-error {
            background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;
            border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-title">Selamat Datang Kembali</div>
    <div class="auth-sub">Masuk ke sistem purchasing untuk melanjutkan</div>

    @if($errors->any())
    <div class="alert-error" style="margin-top: 16px;">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login.post') }}" method="post">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input class="form-input" type="email" id="email" name="email"
                   value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
                <input class="form-input" type="password" id="password" name="password"
                       placeholder="Masukkan password Anda" required style="padding-right: 40px;">
                <button type="button" class="input-toggle" onclick="togglePass()" title="Show/hide password">
                    <svg id="eye-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-row">
            <label class="form-check">
                <input type="checkbox" name="remember" value="1"> Ingat Saya
            </label>
            <a href="#" class="link">Lupa password?</a>
        </div>

        <button type="submit" class="btn-submit">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Masuk ke Sistem
        </button>
    </form>

    <div class="divider"><span>ATAU</span></div>
    <div class="auth-footer">Belum memiliki akun? <a href="{{ route('register') }}" class="link">Daftar sekarang</a></div>
</div>
<script>
function togglePass() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>