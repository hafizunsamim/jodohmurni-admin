<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#1f2937">

    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('icons/icon-512.png') }}">

    <link rel="icon" href="{{ asset('icons/icon-192.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: linear-gradient(135deg, #2e7d32, #327b36); height: 100vh; }
        .login-card { max-width: 420px; border-radius: 16px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="card login-card shadow-lg p-4">
    <h3 class="text-center mb-2 fw-bold">Admin Portal</h3>
    <p class="text-center text-muted mb-4">JodohMurni</p>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <small>{{ $errors->first() }}</small>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <button class="btn btn-warning w-100 mt-2">Login</button>
    </form>
</div>

<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js')
    .then(function(reg) {
        console.log('✅ SW registered', reg);
    })
    .catch(function(err) {
        console.log('❌ SW failed', err);
    });
}
</script>

</body>
</html>
