<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | ProcureX</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }
        .auth-card { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 480px; box-shadow: 0 4px 32px rgba(0,0,0,0.08); }
        .auth-title { font-size: 22px; font-weight: 700; color: #111827; text-align: center; }
        .auth-sub { font-size: 14px; color: #6b7280; text-align: center; margin-top: 6px; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { margin-top: 16px; display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 11.5px; font-weight: 600; color: #111827; letter-spacing: .04em; text-transform: uppercase; }
        .form-input { padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: #111827; font-family: inherit; transition: border-color .15s, box-shadow .15s; width: 100%; }
        .form-input:focus { outline: none; border-color: #3b5bdb; box-shadow: 0 0 0 3px rgba(59,91,219,.12); }
        .form-input::placeholder { color: #9ca3af; }
        .btn-submit { width: 100%; margin-top: 20px; padding: 11px; background: #3b5bdb; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit; transition: background .15s, box-shadow .15s; }
        .btn-submit:hover { background: #3451c7; box-shadow: 0 4px 12px rgba(59,91,219,.25); }
        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
        .divider span { font-size: 12px; color: #9ca3af; }
        .auth-footer { text-align: center; font-size: 13px; color: #6b7280; }
        .link { color: #3b5bdb; text-decoration: none; font-weight: 500; }
        .link:hover { text-decoration: underline; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 16px; }
        .alert-error ul { margin: 4px 0 0 16px; }
        @media (max-width: 480px) { .form-row-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-title">Buat Akun Baru</div>
    <div class="auth-sub">Daftarkan diri sebagai requester sistem pengadaan</div>

    @if($errors->any())
    <div class="alert-error" style="margin-top: 16px;">
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('register.post') }}" method="post">
        @csrf
        <div class="form-group">
            <label class="form-label" for="name">Nama Lengkap</label>
            <input class="form-input" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap Anda" required>
        </div>
        <div class="form-row-2">
            <div class="form-group">
                <label class="form-label" for="department">Departemen</label>
                <input class="form-input" type="text" id="department" name="department" value="{{ old('department') }}" placeholder="IT & Digital" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="role_display">Jabatan</label>
                <input class="form-input" type="text" id="role_display" placeholder="Staff" disabled>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
            <input class="form-input" type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required>
        </div>
        <button type="submit" class="btn-submit">Buat Akun</button>
    </form>

    <div class="divider"><span>ATAU</span></div>
    <div class="auth-footer">Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk di sini</a></div>
</div>
</body>
</html>