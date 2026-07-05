<!doctype html>
<html lang="vi">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hệ thống Quản lý Điểm rèn luyện Sinh viên</title>

    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Premium Custom Styles -->
    <style>
      :root {
        --ms-primary: #0ea5e9; /* Sky Blue (Xanh lam hình 1) */
        --ms-secondary: #38bdf8; /* Lighter Sky Blue (Xanh dương nhạt) */
        --ms-dark: #0f172a;
        --ms-light: #f8fafc;
        --ms-sidebar: #0f172a;
        --font-outfit: 'Outfit', sans-serif;
      }

      body {
        font-family: var(--font-outfit);
        background-color: #f1f5f9;
        color: #1e293b;
        overflow-x: hidden;
      }

      .ms-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 2px solid #e2e8f0;
        color: #0f172a;
        height: 70px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
      }

      .brand-title {
        font-weight: 800;
        font-size: 20px;
        letter-spacing: 1px;
        color: #0f172a;
      }

      .brand-title span {
        color: var(--ms-primary);
      }

      .ms-sidebar {
        background-color: var(--ms-sidebar);
        width: 260px;
        position: fixed;
        top: 70px;
        bottom: 0;
        left: 0;
        z-index: 1020;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        padding-top: 20px;
        transition: all 0.3s ease;
      }

      .nav-menu {
        padding: 0;
        list-style: none;
      }

      .nav-menu-item {
        margin-bottom: 5px;
      }

      .nav-menu-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #c9c9c9;
        text-decoration: none;
        font-weight: 500;
        border-left: 4px solid transparent;
        transition: all 0.2s ease;
      }

      .nav-menu-link i {
        font-size: 18px;
        margin-right: 15px;
        transition: transform 0.2s ease;
      }

      .nav-menu-link:hover {
        background-color: rgba(255, 255, 255, 0.03);
        color: var(--ms-secondary);
        border-left-color: var(--ms-secondary);
      }

      .nav-menu-link:hover i {
        transform: scale(1.15);
      }

      .nav-menu-link.active {
        background-color: rgba(14, 165, 233, 0.1);
        color: var(--ms-secondary);
        border-left-color: var(--ms-secondary);
        font-weight: 600;
      }

      .nav-menu-link.active i {
        color: var(--ms-secondary);
      }

      .ms-main {
        margin-left: 260px;
        margin-top: 70px;
        padding: 30px 40px;
        min-height: calc(100vh - 70px);
        transition: all 0.3s ease;
      }

      .card-premium {
        background: white;
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
      }

      .card-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(14, 165, 233, 0.08);
      }

      .card-premium-header {
        background: transparent;
        border-bottom: 1px solid #f0edf4;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .card-premium-title {
        font-weight: 700;
        font-size: 18px;
        color: var(--ms-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
      }

      .btn-premium {
        background-color: var(--ms-primary);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.2s ease;
      }

      .btn-premium:hover {
        background-color: #0284c7; /* Darker Sky Blue (Xanh lam hình 2) */
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
      }

      #userMenu {
        background: transparent;
      }

      #userMenu:hover, #userMenu[aria-expanded="true"] {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
      }

      .badge-premium {
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 600;
        font-size: 12px;
      }

      .ms-footer {
        background: var(--ms-sidebar);
        color: #999;
        text-align: center;
        padding: 20px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        font-size: 13px;
        margin-left: 260px;
      }

      @media (max-width: 991.98px) {
        .ms-sidebar {
          margin-left: -260px;
        }
        .ms-sidebar.active {
          margin-left: 0;
        }
        .ms-main, .ms-footer {
          margin-left: 0;
        }
      }
    </style>
  </head>
  <body>
    <!-- Top Header -->
    <header class="ms-header d-flex align-items-center px-4">
      <div class="d-flex align-items-center w-100 justify-content-between">
        <div class="d-flex align-items-center">
          <button class="btn btn-outline-dark d-lg-none me-3" id="sidebarToggle">
            <i class="bi bi-list"></i>
          </button>
          <div class="brand-title">
            <i class="bi bi-mortarboard-fill me-2 text-primary"></i>ĐIỂM RÈN LUYỆN <span>SV</span>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          @auth
            <div class="dropdown">
              <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle px-3 py-2 rounded" id="userMenu" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5 text-primary"></i>
                <span class="d-none d-md-inline text-dark fw-semibold">
                  {{ Auth::user()->name }} 
                  <span class="badge bg-secondary ms-1" style="font-size:10px;">
                    @if(Auth::user()->role === 'admin') QL/CTSV
                    @elseif(Auth::user()->role === 'sinh_vien') Sinh viên
                    @elseif(Auth::user()->role === 'ban_can_su') BCS Lớp
                    @elseif(Auth::user()->role === 'co_van') Cố vấn học tập
                    @else {{ Auth::user()->role }}
                    @endif
                  </span>
                </span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                <li>
                  <a class="dropdown-item py-2 text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                  </a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                  </form>
                </li>
              </ul>
            </div>
          @else
            <a href="{{ route('login') }}" class="btn btn-outline-dark border-0 d-flex align-items-center gap-2">
              <i class="bi bi-box-arrow-in-right fs-5"></i>
              <span>Đăng nhập</span>
            </a>
          @endauth
        </div>
      </div>
    </header>

    <!-- Navigation Sidebar -->
    <aside class="ms-sidebar" style="overflow-y: auto;">
      @auth
        <div class="px-3 pt-2 pb-1 text-uppercase text-secondary small fw-bold" style="font-size: 11px; letter-spacing: 1px;">
          <i class="bi bi-sliders me-1 text-info"></i>Chức năng chính
        </div>
        <ul class="nav-menu mb-3">
          <li class="nav-menu-item">
            <a href="{{ route('dashboard') }}" class="nav-menu-link {{ Route::is('dashboard') ? 'active' : '' }}">
              <i class="bi bi-speedometer2"></i>
              <span>Bảng điều khiển</span>
            </a>
          </li>
          
          <li class="nav-menu-item">
            <a href="{{ route('hoat_dong.index') }}" class="nav-menu-link {{ Route::is('hoat_dong.*') ? 'active' : '' }}">
              <i class="bi bi-calendar-event"></i>
              <span>Hoạt động rèn luyện</span>
            </a>
          </li>

          <li class="nav-menu-item">
            <a href="{{ route('minh_chung.index') }}" class="nav-menu-link {{ Route::is('minh_chung.*') ? 'active' : '' }}">
              <i class="bi bi-file-earmark-check"></i>
              <span>Nộp / Duyệt minh chứng</span>
            </a>
          </li>

          @if(Auth::user()->role === 'admin' || Auth::user()->role === 'co_van' || Auth::user()->role === 'ban_can_su')
            <li class="nav-menu-item">
              <a href="{{ route('xet_duyet.index') }}" class="nav-menu-link {{ Route::is('xet_duyet.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i>
                <span>Xét duyệt điểm lớp</span>
              </a>
            </li>
          @endif

          <li class="nav-menu-item">
            <a href="{{ route('diem_ren_luyen.index') }}" class="nav-menu-link {{ Route::is('diem_ren_luyen.index') ? 'active' : '' }}">
              <i class="bi bi-journal-text"></i>
              <span>Bảng điểm rèn luyện</span>
            </a>
          </li>

          @if(Auth::user()->role === 'admin' || Auth::user()->role === 'co_van')
            <li class="nav-menu-item">
              <a href="{{ route('diem_ren_luyen.report') }}" class="nav-menu-link {{ Route::is('diem_ren_luyen.report') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Báo cáo & Thống kê</span>
              </a>
            </li>
          @endif

          @if(Auth::user()->role === 'admin')
            <li class="nav-menu-item">
              <a href="{{ route('hoc_ky.settings') }}" class="nav-menu-link {{ Route::is('hoc_ky.settings') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>Cấu hình Học kỳ</span>
              </a>
            </li>
          @endif

          <li class="nav-menu-item">
            <a href="{{ route('khieu_nai.index') }}" class="nav-menu-link {{ Route::is('khieu_nai.*') ? 'active' : '' }}">
              <i class="bi bi-plus-circle-dotted"></i>
              <span>Bổ sung minh chứng</span>
            </a>
          </li>
        </ul>
      @endauth
    </aside>

    <!-- Main Content Area -->
    <main class="ms-main">
      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius:12px;">
          <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius:12px;">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @yield('content')
    </main>

    <!-- Footer -->
    <footer class="ms-footer">
      <div><strong>ĐIỂM RÈN LUYỆN SV &copy; 2026</strong>. Giải pháp quản lý điểm rèn luyện thông minh, minh bạch, hiệu quả.</div>
    </footer>

    <!-- Bootstrap 5 JavaScript & jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      $('#sidebarToggle').on('click', function() {
        $('.ms-sidebar').toggleClass('active');
      });
    </script>
    @yield('scripts')
  </body>
</html>
