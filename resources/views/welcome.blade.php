<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>REZEKI SHOES - Warehouse System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.03) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.03) 0px, transparent 50%);
            color: var(--text-dark);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-container {
            max-width: 1140px;
            margin: auto;
            padding: 40px 20px;
        }

        .brand-name {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--primary-blue);
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hero-title {
            font-weight: 800;
            font-size: clamp(2.5rem, 5vw, 3.8rem);
            line-height: 1.05;
            letter-spacing: -2px;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .hero-title span {
            color: var(--primary-blue);
            position: relative;
        }

        .hero-desc {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 2.5rem;
            max-width: 540px;
        }

        /* CARD STYLE */
        .login-card {
            background: #ffffff;
            padding: 45px;
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .form-label {
            font-size: 0.85rem;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .form-control {
            border-radius: 12px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            background-color: #fcfdfe;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            border-color: var(--primary-blue);
            background-color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            box-shadow: 0 10px 20px -10px rgba(37, 99, 235, 0.5);
            transform: translateY(-2px);
        }

        .alert-danger {
            border-radius: 12px;
            border: none;
            background-color: #fef2f2;
            color: #b91c1c;
        }

        .footer-text {
            position: absolute;
            bottom: 30px;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        @media (max-width: 991.98px) {
            body { display: block; padding-top: 40px; }
            .hero-title { text-align: center; }
            .hero-desc { text-align: center; margin-inline: auto; }
            .brand-name { justify-content: center; }
            .login-card { margin-top: 2rem; }
        }
    </style>
</head>
<body>

    <div class="container hero-container">
        <div class="row align-items-center">
            <div class="col-lg-6 pe-lg-5 mb-5 mb-lg-0">
                <div class="brand-name">
                    <i class="fas fa-warehouse"></i> REZEKI SHOES.
                </div>
                <h1 class="hero-title">Manage your <span>warehouse</span> properly.</h1>
                <p class="hero-desc">
                    Sistem manajemen stok dan pengiriman barang yang dirancang untuk kecepatan dan kemudahan penggunaan. Pantau aset Anda secara real-time kapanpun.
                </p>
                
                <div class="d-flex gap-4 align-items-center">
                    <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                        <i class="fas fa-check-circle text-success fs-5"></i> Fast Tracking
                    </div>
                    <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                        <i class="fas fa-check-circle text-success fs-5"></i> Accurate Stock
                    </div>
                </div>
            </div>

            <div class="col-lg-5 offset-lg-1">
                @guest
                <div class="login-card">
                    <div class="mb-4">
                        <h4 class="fw-bold mb-1">Welcome Back</h4>
                        <p class="text-muted small">Please enter your credentials to login.</p>
                    </div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 px-3 small mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@gmail.com" required autofocus>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label fw-bold">Password</label>
                            </div>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">Sign In</button>
                    </form>
                </div>
                @else
                <div class="login-card text-center py-5">
                    <div class="mb-4">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=2563eb&color=fff&bold=true" class="rounded-circle shadow-sm" width="90">
                    </div>
                    <h4 class="fw-bold mb-1">Hello, {{ Auth::user()->name }}!</h4>
                    <p class="text-muted mb-4 small">Sesi Anda masih aktif.</p>
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary w-100 shadow-sm">
                        <i class="fas fa-arrow-right me-2"></i> Go to Dashboard
                    </a>
                </div>
                @endguest
            </div>
        </div>
    </div>

    <div class="footer-text w-100 text-center">
        &copy; 2026 Rezeki Shoes Safety. Modern Warehouse Solutions.
    </div>

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>