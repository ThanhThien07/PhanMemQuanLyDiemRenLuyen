@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <!-- Title section -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mb-6">
    <div>
      <h2 class="font-bold text-slate-800 text-2xl mb-1 flex items-center gap-2">
        <i class="bi bi-columns-gap text-sky-500"></i> Bảng Điều Khiển
      </h2>
      <p class="text-slate-500 text-sm mb-0">Chào mừng bạn trở lại hệ thống quản lý điểm rèn luyện.</p>
    </div>
  </div>

  @if(isset($no_profile) && $no_profile)
    <div class="alert alert-warning card-premium p-5 mb-6 border-l-4 border-amber-500 shadow-sm" role="alert">
      <div class="flex items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill text-xl text-amber-500"></i>
        <div>
          <h5 class="font-bold text-amber-800 mb-2">Chưa khởi tạo hồ sơ sinh viên!</h5>
          <p class="mb-0 text-amber-700">Hệ thống chưa tìm thấy hồ sơ sinh viên cho tài khoản này. Vui lòng liên hệ Phòng CTSV hoặc Cố vấn học tập để cập nhật thông tin.</p>
        </div>
      </div>
    </div>
  @elseif(Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su')
    <!-- Student Widgets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      
      <!-- Card 1: Cumulative points -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-gradient-to-b from-sky-400 to-sky-600"></div>
        <div class="pl-2">
          <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block mb-1">Điểm tổng hợp học kỳ</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-2">
            {{ $diemRenLuyen ? number_format($diemRenLuyen->diem_tong_hop, 2) : '0.00' }}
          </h2>
          <span class="badge bg-success">{{ $diemRenLuyen ? $diemRenLuyen->xep_loai : 'Chưa xếp loại' }}</span>
        </div>
        <div class="bg-gradient-to-tr from-sky-50 to-sky-100/50 p-4 rounded-2xl text-sky-500 group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-star-fill text-2xl"></i>
        </div>
      </div>

      <!-- Card 2: Registered Activities -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
        <div class="pl-2">
          <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block mb-1">Hoạt động tham gia</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-2">
            {{ $hoatDongDaThamGiaCount }}
          </h2>
          <span class="text-xs text-slate-500 font-medium">Có mặt tại sự kiện</span>
        </div>
        <div class="bg-gradient-to-tr from-emerald-50 to-emerald-100/50 p-4 rounded-2xl text-emerald-500 group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-calendar-check text-2xl"></i>
        </div>
      </div>

      <!-- Card 3: Evidences pending -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-gradient-to-b from-amber-400 to-amber-600"></div>
        <div class="pl-2">
          <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block mb-1">Minh chứng chờ duyệt</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-2">
            {{ $minhChungChoDuyetCount }}
          </h2>
          <span class="text-xs text-slate-500 font-medium">Hồ sơ đã nộp</span>
        </div>
        <div class="bg-gradient-to-tr from-amber-50 to-amber-100/50 p-4 rounded-2xl text-amber-500 group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-file-earmark-arrow-up text-2xl"></i>
        </div>
      </div>
    </div>

    <!-- Student Profile Summary ID Card -->
    <div class="card-premium p-6 mb-6 relative overflow-hidden">
      <!-- Gradient mesh decoration -->
      <div class="absolute -right-16 -top-16 w-48 h-48 bg-gradient-to-br from-sky-400/10 to-indigo-500/10 rounded-full blur-2xl"></div>
      <div class="absolute -left-16 -bottom-16 w-48 h-48 bg-gradient-to-br from-indigo-400/5 to-purple-500/5 rounded-full blur-2xl"></div>

      <div class="flex flex-col md:flex-row items-center gap-5 relative z-10">
        <div class="flex-shrink-0">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-400 to-indigo-500 text-white flex items-center justify-center font-bold text-2xl shadow-[0_6px_20px_rgba(14,165,233,0.25)]">
            {{ substr($sinhVien->ho_ten, 0, 2) }}
          </div>
        </div>
        <div class="flex-grow text-center md:text-left">
          <h4 class="font-extrabold text-slate-800 text-lg mb-1.5">{{ $sinhVien->ho_ten }}</h4>
          <div class="flex flex-wrap justify-center md:justify-start gap-x-4 gap-y-1.5 text-sm text-slate-500">
            <span>MSSV: <strong class="text-slate-700">{{ $sinhVien->ma_sv }}</strong></span>
            <span class="hidden md:inline text-slate-300">|</span>
            <span>Lớp: <strong class="text-slate-700">{{ $sinhVien->lop->ten_lop }}</strong></span>
            <span class="hidden md:inline text-slate-300">|</span>
            <span>Hệ đào tạo: <strong class="text-slate-700">{{ $sinhVien->heDaoTao->ten_he }}</strong></span>
          </div>
        </div>
      </div>
    </div>
  @else
    <!-- Advisor & CTSV Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      
      <!-- Total Students -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-gradient-to-b from-sky-400 to-sky-600"></div>
        <div class="pl-2">
          <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block mb-1">Tổng số sinh viên</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1">{{ $stats['total_students'] }}</h2>
        </div>
        <div class="bg-gradient-to-tr from-sky-50 to-sky-100/50 p-4 rounded-2xl text-sky-500 group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-people-fill text-2xl"></i>
        </div>
      </div>

      <!-- Total Activities -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-gradient-to-b from-emerald-400 to-emerald-600"></div>
        <div class="pl-2">
          <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block mb-1">Tổng số hoạt động</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1">{{ $stats['total_activities'] }}</h2>
        </div>
        <div class="bg-gradient-to-tr from-emerald-50 to-emerald-100/50 p-4 rounded-2xl text-emerald-500 group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-calendar3 text-2xl"></i>
        </div>
      </div>

      <!-- Evidences Pending -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-gradient-to-b from-amber-400 to-amber-600"></div>
        <div class="pl-2">
          <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block mb-1">Minh chứng chờ duyệt</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 text-amber-600">{{ $stats['pending_evidences'] }}</h2>
        </div>
        <div class="bg-gradient-to-tr from-amber-50 to-amber-100/50 p-4 rounded-2xl text-amber-500 group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-file-earmark-check text-2xl"></i>
        </div>
      </div>

      <!-- Complaints Pending -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div class="absolute top-0 left-0 bottom-0 w-1.5 bg-gradient-to-b from-red-400 to-rose-600"></div>
        <div class="pl-2">
          <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider block mb-1">Khiếu nại chờ xử lý</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 text-rose-600">{{ $stats['pending_complaints'] }}</h2>
        </div>
        <div class="bg-gradient-to-tr from-red-50 to-red-100/50 p-4 rounded-2xl text-rose-500 group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-exclamation-octagon text-2xl"></i>
        </div>
      </div>

    </div>
  @endif

  <!-- Notifications Section -->
  <div class="card-premium mb-6 overflow-hidden">
    <div class="card-premium-header bg-slate-50/50">
      <h5 class="card-premium-title text-slate-800 flex items-center gap-2">
        <i class="bi bi-bell-fill text-amber-500"></i> Thông báo mới nhất
      </h5>
    </div>
    <div class="p-5">
      @if($thongBaos->isEmpty())
        <div class="text-center py-6 text-slate-400">
          <i class="bi bi-chat-left-dots text-3xl mb-2 block text-slate-300"></i>
          Không có thông báo mới nào.
        </div>
      @else
        <div class="flex flex-col gap-4">
          @foreach($thongBaos as $tb)
            <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-slate-50/80 transition duration-200 border border-slate-100">
              <div class="flex-shrink-0 bg-amber-50 text-amber-600 p-3 rounded-2xl">
                <i class="bi bi-bell-fill text-lg"></i>
              </div>
              <div class="flex-grow">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1.5 mb-1.5">
                  <h6 class="font-extrabold text-slate-800 mb-0" style="font-size: 15px;">{{ $tb->tieu_de }}</h6>
                  <span class="text-xs text-slate-400 font-semibold bg-slate-100 px-2.5 py-1 rounded-lg">{{ $tb->created_at->diffForHumans() }}</span>
                </div>
                <p class="mb-0 text-slate-600 text-sm leading-relaxed">{{ $tb->noi_dung }}</p>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
@endsection