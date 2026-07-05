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

    <!-- Vite compiled Tailwind CSS and JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Premium Custom Styles -->
    <style>
    <!-- Premium Custom Styles -->
    <style>
      :root {
        --font-outfit: 'Outfit', sans-serif;
      }

      body {
        font-family: var(--font-outfit);
        background-color: #f1f5f9;
        color: #1e293b;
        overflow-x: hidden;
      }

      #userMenu {
        background: transparent;
      }

      #userMenu:hover, #userMenu[aria-expanded="true"] {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
      }

      @media (max-width: 991.98px) {
        .ms-sidebar {
          margin-left: -260px !important;
        }
        .ms-sidebar.active {
          margin-left: 0 !important;
        }
      }
    </style>
  </head>
  <body>
    <!-- Top Header -->
    <header class="fixed top-0 left-0 right-0 h-[70px] z-[1030] bg-white/95 border-b-2 border-slate-200 flex items-center px-6 shadow-sm backdrop-blur-md">
        <div class="flex items-center">
          <button class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 lg:hidden mr-3 cursor-pointer" id="sidebarToggle">
            <i class="bi bi-list"></i>
          </button>
          <div class="font-bold text-lg text-slate-800 tracking-wider">
            <i class="bi bi-mortarboard-fill mr-2 text-sky-500"></i>ĐIỂM RÈN LUYỆN <span class="text-sky-500">SV</span>
          </div>
        </div>
        <div class="flex items-center gap-3">
          @auth
            <div class="dropdown">
              <a href="#" class="flex items-center gap-2 text-decoration-none dropdown-toggle px-3 py-2 rounded" id="userMenu" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle text-xl text-sky-500"></i>
                <span class="hidden md:inline text-slate-700 font-semibold">
                  {{ Auth::user()->name }} 
                  <span class="badge bg-secondary ml-1" style="font-size:10px;">
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
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition duration-200 text-decoration-none">
              <i class="bi bi-box-arrow-in-right text-lg"></i>
              <span>Đăng nhập</span>
            </a>
          @endauth
        </div>
      </div>
    </header>

    <!-- Navigation Sidebar -->
    <aside class="fixed top-[70px] bottom-0 left-0 w-[260px] z-[1020] bg-slate-900 border-r border-slate-800 pt-4 transition-all duration-300 overflow-y-auto ms-sidebar">
      @auth
        <div class="px-5 pt-2 pb-2 text-uppercase text-slate-400 font-bold tracking-wider" style="font-size: 11px;">
          <i class="bi bi-sliders mr-2 text-sky-400"></i>Chức năng chính
        </div>
        <ul class="flex flex-col mb-3 p-0 list-none">
          <li class="mb-1">
            <a href="{{ route('dashboard') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('dashboard') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-speedometer2 mr-3 text-lg"></i>
              <span>Bảng điều khiển</span>
            </a>
          </li>
          
          <li class="mb-1">
            <a href="{{ route('hoat_dong.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('hoat_dong.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-calendar-event mr-3 text-lg"></i>
              <span>Hoạt động rèn luyện</span>
            </a>
          </li>

          <li class="mb-1">
            <a href="{{ route('minh_chung.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('minh_chung.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-file-earmark-check mr-3 text-lg"></i>
              <span>Nộp / Duyệt minh chứng</span>
            </a>
          </li>

          @if(Auth::user()->role === 'admin' || Auth::user()->role === 'co_van' || Auth::user()->role === 'ban_can_su')
            <li class="mb-1">
              <a href="{{ route('xet_duyet.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('xet_duyet.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
                <i class="bi bi-clipboard-check mr-3 text-lg"></i>
                <span>Xét duyệt điểm lớp</span>
              </a>
            </li>
          @endif

          <li class="mb-1">
            <a href="{{ route('diem_ren_luyen.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('diem_ren_luyen.index') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-journal-text mr-3 text-lg"></i>
              <span>Bảng điểm rèn luyện</span>
            </a>
          </li>

          @if(Auth::user()->role === 'admin' || Auth::user()->role === 'co_van')
            <li class="mb-1">
              <a href="{{ route('diem_ren_luyen.report') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('diem_ren_luyen.report') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
                <i class="bi bi-file-earmark-bar-graph mr-3 text-lg"></i>
                <span>Báo cáo & Thống kê</span>
              </a>
            </li>
          @endif

          @if(Auth::user()->role === 'admin')
            <li class="mb-1">
              <a href="{{ route('hoc_ky.settings') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('hoc_ky.settings') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
                <i class="bi bi-gear mr-3 text-lg"></i>
                <span>Cấu hình Học kỳ</span>
              </a>
            </li>
          @endif

          <li class="mb-1">
            <a href="{{ route('khieu_nai.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('khieu_nai.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-plus-circle-dotted mr-3 text-lg"></i>
              <span>Bổ sung minh chứng</span>
            </a>
          </li>
        </ul>
      @endauth
    </aside>

    <!-- Main Content Area -->
    <main class="p-6 md:p-8 min-h-[calc(100vh-70px)] transition-all duration-300 ml-0 lg:ml-[260px] mt-[70px]">
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
    <footer class="bg-slate-900 text-slate-400 text-center py-4 border-t border-slate-800 text-xs ml-0 lg:ml-[260px]">
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
