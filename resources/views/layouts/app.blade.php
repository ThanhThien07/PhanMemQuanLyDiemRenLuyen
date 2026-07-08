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
  </head>
  <body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900 overflow-x-hidden font-sans">
    <!-- Top Header -->
    <header class="fixed top-0 left-0 right-0 h-[70px] z-[1030] bg-white/80 border-b border-slate-100 flex items-center justify-between px-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.05)] backdrop-blur-md transition-all duration-300">
        <div class="flex items-center">
          <button class="inline-flex items-center justify-center p-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 lg:hidden mr-3 cursor-pointer transition duration-200" id="sidebarToggle">
            <i class="bi bi-list text-lg"></i>
          </button>
          <div class="font-bold text-lg tracking-wider flex items-center">
            <div class="bg-gradient-to-r from-sky-500 to-indigo-600 p-2 rounded-xl text-white mr-3 flex items-center justify-center shadow-sm w-[38px] h-[38px]">
              <i class="bi bi-mortarboard-fill text-lg"></i>
            </div>
            <span class="bg-gradient-to-r from-slate-800 to-slate-950 bg-clip-text text-transparent font-extrabold uppercase tracking-wide text-sm">
              ĐIỂM RÈN LUYỆN <span class="from-sky-500 to-indigo-600 bg-gradient-to-r bg-clip-text text-transparent">SV</span>
            </span>
          </div>
        </div>
        <div class="flex items-center gap-3">
          @auth
            @php
              $layoutThongBaos = \App\Models\ThongBao::latest()->take(5)->get();
              $unreadCount = $layoutThongBaos->count();
            @endphp
            <div class="dropdown me-2">
              <button class="relative inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-50 transition duration-200 shadow-sm w-[38px] h-[38px]" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell text-lg"></i>
                @if($unreadCount > 0)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-full bg-rose-500 text-[9px] min-w-[18px] h-[18px] text-white flex items-center justify-center shadow-sm -translate-x-[30%] -translate-y-[30%]">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                  </span>
                @endif
              </button>
              <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg mt-2 overflow-hidden w-[360px] max-h-[480px] overflow-y-auto rounded-2xl" aria-labelledby="notificationDropdown">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                  <span class="font-bold text-slate-800 text-sm">Thông báo mới nhất</span>
                  <span class="badge bg-danger rounded-full px-2 py-0.5 text-[10px]">{{ $unreadCount }} mới</span>
                </div>
                <div class="divide-y divide-slate-100">
                  @if($layoutThongBaos->isEmpty())
                    <div class="px-4 py-6 text-center text-slate-400">
                      <i class="bi bi-bell-slash text-2xl mb-1 block"></i>
                      <span class="text-xs">Không có thông báo nào</span>
                    </div>
                  @else
                    @foreach($layoutThongBaos as $tb)
                      <div class="px-4 py-3 hover:bg-slate-50/60 transition duration-150 cursor-pointer">
                        <div class="flex items-start gap-3">
                          <div class="bg-amber-50 text-amber-600 p-2.5 rounded-xl mt-0.5">
                            <i class="bi bi-bell-fill text-sm"></i>
                          </div>
                          <div class="flex-grow">
                            <h6 class="font-extrabold text-slate-800 mb-1 text-[13px] leading-snug">{{ $tb->tieu_de }}</h6>
                            <p class="text-slate-500 text-xs mb-1 line-clamp-2 leading-normal">{{ $tb->noi_dung }}</p>
                            <span class="text-[10px] text-slate-400 font-semibold">{{ $tb->created_at->diffForHumans() }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            </div>
            <div class="dropdown">
              <a href="#" class="flex items-center gap-2.5 text-decoration-none dropdown-toggle px-3 py-2 rounded-xl border border-slate-100 hover:bg-slate-50 transition duration-200" id="userMenu" data-bs-toggle="dropdown">
                <div class="rounded-full bg-gradient-to-tr from-sky-400 to-indigo-500 text-white w-8 h-8 flex items-center justify-center font-bold shadow-sm">
                  {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <span class="hidden md:inline text-slate-700 font-semibold text-sm">
                  {{ Auth::user()->name }} 
                  <span class="badge bg-secondary ml-1 text-[10px]">
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
    <aside class="fixed top-[70px] bottom-0 left-0 w-[260px] z-[1020] bg-[#161514] border-r border-[#262422] pt-3 transition-all duration-300 overflow-y-auto ms-sidebar -ml-[260px] lg:ml-0 [&.active]:ml-0">
      @auth
        <div class="flex flex-col gap-4">
          <!-- SECTION 1: KHU VỰC QUẢN LÝ (Management Area) -->
          <div>

            <ul class="flex flex-col mb-0 p-0 list-none gap-1">
              <li>
                <!-- Feature: General dashboard with stats widgets based on roles -->
                <a href="{{ route('dashboard') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('dashboard') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                  <i class="bi bi-speedometer2 mr-3 text-lg"></i>
                  <span>Bảng điều khiển</span>
                </a>
              </li>
              
              @if(in_array(Auth::user()->role, ['ctsv', 'co_van']))
                <li>
                  <!-- Feature: Report & statistics generation/export (CTSV and Academic Advisors only) -->
                  <a href="{{ route('diem_ren_luyen.report') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('diem_ren_luyen.report') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                    <i class="bi bi-file-earmark-bar-graph mr-3 text-lg"></i>
                    <span>Báo cáo & Thống kê</span>
                  </a>
                </li>
              @endif

              @if(Auth::user()->role === 'ctsv')
                <li>
                  <!-- Feature: SQL database backup, restoration, and auto-backup settings (CTSV only) -->
                  <a href="{{ route('backup.index') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('backup.*') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                    <i class="bi bi-shield-lock-fill mr-3 text-lg"></i>
                    <span>Sao lưu dữ liệu</span>
                  </a>
                </li>
              @endif
            </ul>
          </div>

          <!-- SECTION 2: DANH MỤC HỆ THỐNG (System Directories) -->
          <div>

            <ul class="flex flex-col mb-0 p-0 list-none gap-1">
              <li>
                <!-- Feature: Browse activities, register participation, and QR check-in -->
                <a href="{{ route('hoat_dong.index') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('hoat_dong.*') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                  <i class="bi bi-calendar-event mr-3 text-lg"></i>
                  <span>Hoạt động rèn luyện</span>
                </a>
              </li>

              <li>
                <!-- Feature: View training score logs and submit self-evaluation sheet (Students only) -->
                <a href="{{ route('diem_ren_luyen.index') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('diem_ren_luyen.index') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                  <i class="bi bi-journal-text mr-3 text-lg"></i>
                  <span>Bảng điểm rèn luyện</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- SECTION 3: KÍP PHỤC VỤ (Operations/Services) -->
          <div>

            <ul class="flex flex-col mb-3 p-0 list-none gap-1">
              <li>
                <!-- Feature: Upload Google Drive evidence links or review pending student evidences -->
                <a href="{{ route('minh_chung.index') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('minh_chung.*') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                  <i class="bi bi-file-earmark-check mr-3 text-lg"></i>
                  <span>Nộp / Duyệt minh chứng</span>
                </a>
              </li>

              @if(in_array(Auth::user()->role, ['ctsv', 'co_van', 'ban_can_su']))
                <li>
                  <!-- Feature: Grade student self-evaluations (BCS, Advisors, and CTSV) -->
                  <a href="{{ route('xet_duyet.index') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('xet_duyet.*') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                    <i class="bi bi-clipboard-check mr-3 text-lg"></i>
                    <span>Xét duyệt điểm lớp</span>
                  </a>
                </li>
              @endif

              <li>
                <!-- Feature: Student appeals for additional evidence submission and score review -->
                <a href="{{ route('khieu_nai.index') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('khieu_nai.*') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                  <i class="bi bi-plus-circle-dotted mr-3 text-lg"></i>
                  <span>Bổ sung minh chứng</span>
                </a>
              </li>

              @if(Auth::user()->role === 'ctsv')
                <li>
                  <!-- Feature: Configure academic semesters, timelines for opening/closing evaluation portals -->
                  <a href="{{ route('hoc_ky.settings') }}" class="flex items-center mx-3 px-4 py-2.5 rounded-xl hover:text-sky-400 hover:bg-white/5 transition-all duration-200 text-decoration-none {{ Route::is('hoc_ky.settings') ? 'active-sidebar-link bg-sky-500/10 text-sky-400 font-semibold' : 'text-slate-300' }}">
                    <i class="bi bi-gear mr-3 text-lg"></i>
                    <span>Cấu hình Học kỳ</span>
                  </a>
                </li>
              @endif
            </ul>
          </div>
          </div>
        </div>
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
