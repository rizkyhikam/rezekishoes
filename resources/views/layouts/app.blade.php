<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>RSS Warehouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bg-body: #f4f7f6;
            --sidebar-bg: #ffffff;
            --primary-blue: #2563eb;
            --text-main: #334155;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            display: flex;
        }

        #layoutSidenav_nav {
            width: 250px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .navbar-brand {
            display: block;
            padding: 25px;
            font-weight: 800;
            color: var(--primary-blue) !important;
            text-decoration: none;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.25rem;
        }

        .nav-link {
            display: flex !important;
            align-items: center;
            padding: 12px 25px !important;
            color: #64748b !important;
            font-weight: 500;
            text-decoration: none;
            transition: 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-blue) !important;
            background: rgba(37, 99, 235, 0.05) !important;
        }

        .sb-nav-link-icon {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        #layoutSidenav_content {
            flex-grow: 1;
            margin-left: 250px;
            padding: 40px;
            min-width: 0; /* Biar tabel gak overflow */
        }

        .card {
            background-color: #ffffff !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        }
    </style>
</head>
<body>
    <div id="layoutSidenav_nav">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            REZEKI<span style="color:#64748b; font-weight:300;">SHOES</span>
        </a>
        <nav class="sidebar-nav mt-3">
            <div style="padding: 10px 25px; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">Inventory</div>
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div> Stock Barang
            </a>
            <a class="nav-link {{ request()->routeIs('barang.keluar') ? 'active' : '' }}" href="{{ route('barang.keluar') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-truck-loading"></i></div> Barang Keluar
            </a>

            <div style="padding: 20px 25px 10px; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">System</div>
            <a class="nav-link {{ request()->routeIs('analitik.ekspedisi') ? 'active' : '' }}" href="{{ route('analitik.ekspedisi') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div> Analitik Ekspedisi
            </a>

            <div style="padding: 20px 25px 10px; font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">User Management</div>
            <a class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}" href="{{ route('admin.index') }}">
                <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div> Kelola Admin
            </a>

            <div class="mt-4 px-4">
                <form method="POST" action="{{ route('logout') }}"> 
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm rounded-pill shadow-sm">
                        <i class="fas fa-power-off me-2"></i> Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    </body>
</html>