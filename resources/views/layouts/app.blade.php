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

    <!-- Custom Style Overrides -->
    <style>
      :root {
        --font-outfit: 'Outfit', sans-serif;
      }

      body {
        font-family: var(--font-outfit);
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        color: #0f172a;
        overflow-x: hidden;
        min-height: 100vh;
      }

      #userMenu {
        background: transparent;
      }

      #userMenu:hover, #userMenu[aria-expanded="true"] {
        background-color: #f8fafc !important;
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
    <header class="fixed top-0 left-0 right-0 h-[70px] z-[1030] bg-white/80 border-b border-slate-100 flex items-center justify-between px-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.05)] backdrop-blur-md transition-all duration-300">
        <div class="flex items-center">
          <button class="inline-flex items-center justify-center p-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 lg:hidden mr-3 cursor-pointer transition duration-200" id="sidebarToggle">
            <i class="bi bi-list text-lg"></i>
          </button>
          <div class="font-bold text-lg tracking-wider flex items-center">
            <div class="bg-gradient-to-r from-sky-500 to-indigo-600 p-2 rounded-xl text-white mr-3 flex items-center justify-center shadow-sm" style="width: 38px; height: 38px;">
              <i class="bi bi-mortarboard-fill text-lg"></i>
            </div>
            <span class="bg-gradient-to-r from-slate-800 to-slate-950 bg-clip-text text-transparent font-extrabold uppercase tracking-wide" style="font-size: 14px; letter-spacing: 0.05em;">
              ĐIỂM RÈN LUYỆN <span class="from-sky-500 to-indigo-600 bg-gradient-to-r bg-clip-text text-transparent">SV</span>
            </span>
          </div>
        </div>
        <div class="flex items-center gap-3">
          @auth
            <div class="dropdown">
              <a href="#" class="flex items-center gap-2.5 text-decoration-none dropdown-toggle px-3 py-2 rounded-xl border border-slate-100 hover:bg-slate-50 transition duration-200" id="userMenu" data-bs-toggle="dropdown">
                <div class="rounded-full bg-gradient-to-tr from-sky-400 to-indigo-500 text-white w-8 h-8 flex items-center justify-center font-bold shadow-sm">
                  {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <span class="hidden md:inline text-slate-700 font-semibold" style="font-size: 14px;">
                  {{ Auth::user()->name }} 
                  <span class="badge bg-secondary ml-1" style="font-size:10px;">
                    @if(Auth::user()->role === 'ctsv') Phòng CTSV
                    @elseif(Auth::user()->role === 'sinh_vien') Sinh viên
                    @elseif(Auth::user()->role === 'ban_can_su') BCS Lớp
                    @elseif(Auth::user()->role === 'co_van') Cố vấn học tập
                    @else {{ Auth::user()->role }}
                    @endif
                  </span>
                </span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                <li>
                  <a class="dropdown-item py-2 text-danger flex items-center gap-2" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i>Đăng xuất
                  </a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                  </form>
                </li>
              </ul>
            </div>
          @else
            <a href="{{ route('login') }}" class="btn-premium py-2 px-4 text-sm font-semibold flex items-center gap-2 text-decoration-none shadow-sm">
              <i class="bi bi-box-arrow-in-right"></i>
              <span>Đăng nhập</span>
            </a>
          @endauth
        </div>
    </header>

    <!-- Navigation Sidebar -->
    <aside class="fixed top-[70px] bottom-0 left-0 w-[260px] z-[1020] bg-gradient-to-b from-slate-900 to-slate-950 border-r border-slate-800/60 pt-4 transition-all duration-300 overflow-y-auto ms-sidebar">
      @auth
        <div class="px-5 pt-3 pb-2 text-uppercase text-slate-400 font-bold tracking-wider" style="font-size: 10px; letter-spacing: 0.08em;">
          <i class="bi bi-sliders mr-2 text-sky-400"></i>Chức năng chính
        </div>
        <ul class="flex flex-col mb-3 p-0 list-none gap-0.5">
          <li>
            <a href="{{ route('dashboard') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('dashboard') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-speedometer2 mr-3 text-lg"></i>
              <span>Bảng điều khiển</span>
            </a>
          </li>
          
          <li>
            <a href="{{ route('hoat_dong.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('hoat_dong.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-calendar-event mr-3 text-lg"></i>
              <span>Hoạt động rèn luyện</span>
            </a>
          </li>

          <li>
            <a href="{{ route('minh_chung.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('minh_chung.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-file-earmark-check mr-3 text-lg"></i>
              <span>Nộp / Duyệt minh chứng</span>
            </a>
          </li>

          @if(in_array(Auth::user()->role, ['ctsv', 'co_van', 'ban_can_su']))
            <li>
              <a href="{{ route('xet_duyet.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('xet_duyet.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
                <i class="bi bi-clipboard-check mr-3 text-lg"></i>
                <span>Xét duyệt điểm lớp</span>
              </a>
            </li>
          @endif

          <li>
            <a href="{{ route('diem_ren_luyen.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('diem_ren_luyen.index') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
              <i class="bi bi-journal-text mr-3 text-lg"></i>
              <span>Bảng điểm rèn luyện</span>
            </a>
          </li>

          @if(in_array(Auth::user()->role, ['ctsv', 'co_van']))
            <li>
              <a href="{{ route('diem_ren_luyen.report') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('diem_ren_luyen.report') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
                <i class="bi bi-file-earmark-bar-graph mr-3 text-lg"></i>
                <span>Báo cáo & Thống kê</span>
              </a>
            </li>
          @endif

          @if(Auth::user()->role === 'ctsv')
            <li>
              <a href="{{ route('hoc_ky.settings') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('hoc_ky.settings') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
                <i class="bi bi-gear mr-3 text-lg"></i>
                <span>Cấu hình Học kỳ</span>
              </a>
            </li>
            <li>
              <a href="{{ route('backup.index') }}" class="flex items-center px-5 py-3 text-slate-300 hover:text-sky-400 hover:bg-white/5 border-l-4 transition-all duration-200 text-decoration-none {{ Route::is('backup.*') ? 'border-sky-400 bg-sky-500/10 text-sky-400 font-semibold' : 'border-transparent' }}">
                <i class="bi bi-shield-lock-fill mr-3 text-lg"></i>
                <span>Sao lưu dữ liệu</span>
              </a>
            </li>
          @endif

          <li>
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
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-500 text-center py-4 border-t border-slate-800 text-xs ml-0 lg:ml-[260px]">
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
