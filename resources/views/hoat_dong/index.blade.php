@extends('layouts.app')

@section('content')
<!-- 
  ========================================================================
  VIEW: DANH SÁCH HOẠT ĐỘNG RÈN LUYỆN
  ========================================================================
  * Trách nhiệm thiết kế:
    - Bố cục & Lưới Grid: 100% Tailwind CSS v4.
    - form-control-custom: Input lọc kết quả được định nghĩa tập trung.
    - btn-primary & btn-outline-primary: Sử dụng lớp @utility để đồng bộ.
  * Tính năng chính:
    - Tìm kiếm và lọc hoạt động theo tên hoặc theo tiêu chí điểm rèn luyện.
    - Hiển thị danh sách thẻ (Cards) hoạt động kèm theo thông tin chi tiết.
  ========================================================================
-->
<div class="container-fluid">
  <!-- Phần tiêu đề trang và nút tạo mới -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
    <div>
      <h2 class="font-bold text-slate-800 text-2xl mb-1"><i class="bi bi-calendar-event mr-2 text-sky-500"></i>Hoạt Động Rèn Luyện</h2>
      <p class="text-slate-500 mb-0">Đăng ký tham gia các hoạt động để ghi nhận điểm rèn luyện.</p>
    </div>
    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'don_vi_to_chuc')
      <a href="{{ route('hoat_dong.create') }}" class="btn-primary flex items-center gap-2 text-decoration-none">
        <i class="bi bi-plus-circle"></i> Tạo hoạt động mới
      </a>
    @endif
  </div>

  <!-- Bộ lọc tìm kiếm hoạt động (Tailwind Custom Card) -->
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
    <form action="{{ route('hoat_dong.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
      <div class="md:col-span-5">
        <input type="text" name="search" class="form-control-custom" placeholder="Tìm kiếm tên hoạt động..." value="{{ request('search') }}">
      </div>
      <div class="md:col-span-4">
        <select name="tieu_chi" class="form-control-custom">
          <option value="">-- Tất cả tiêu chí rèn luyện --</option>
          @foreach($tieuChis as $tc)
            <option value="{{ $tc->id }}" {{ request('tieu_chi') == $tc->id ? 'selected' : '' }}>[{{ $tc->ma_tieu_chi }}] {{ $tc->ten_tieu_chi }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-3">
        <button type="submit" class="btn-primary w-full"><i class="bi bi-search mr-2"></i> Lọc kết quả</button>
      </div>
    </form>
  </div>

  <!-- Danh sách hoạt động (Lưới Grid Responsive của Tailwind) -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @if($hoatDongs->isEmpty())
      <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-100 shadow-sm">
        <i class="bi bi-calendar-x text-5xl text-slate-300"></i>
        <h5 class="font-bold text-slate-400 mt-4">Không tìm thấy hoạt động nào.</h5>
      </div>
    @else
      @foreach($hoatDongs as $hd)
        <!-- Thẻ thông tin hoạt động (Tailwind Card) -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between h-full hover:shadow-md transition-shadow duration-200">
          <div class="p-6">
            <!-- Nhãn phụ hiển thị điểm và hình thức -->
            <div class="flex justify-between items-start mb-4 gap-2">
              <span class="badge bg-sky-100 text-sky-700 px-2.5 py-1.5 text-xs font-semibold rounded-lg border-0">Điểm cộng: +{{ $hd->diem_du_kien }}</span>
              <span class="badge bg-slate-100 text-slate-600 px-2.5 py-1.5 text-xs font-semibold rounded-lg border-0">{{ $hd->loai_hoat_dong === 'trong_truong' ? 'Trong trường' : 'Ngoài trường' }}</span>
            </div>
            
            <h5 class="font-bold text-slate-800 text-lg mb-3 leading-snug">{{ $hd->ten_hoat_dong }}</h5>
            
            <!-- Các chi tiết mô tả ngắn gọn -->
            <p class="text-slate-500 text-xs mb-2"><i class="bi bi-building mr-2 text-slate-400"></i> Đơn vị: <strong class="text-slate-700">{{ $hd->donViToChuc->ten_don_vi }}</strong></p>
            <p class="text-slate-500 text-xs mb-2"><i class="bi bi-calendar mr-2 text-slate-400"></i> Bắt đầu: <strong class="text-slate-700">{{ $hd->thoi_gian_bat_dau->format('d/m/Y H:i') }}</strong></p>
            <p class="text-slate-500 text-xs mb-0"><i class="bi bi-geo-alt mr-2 text-slate-400"></i> Tiêu chí: [{{ $hd->tieuChi->ma_tieu_chi }}] {{ Str::limit($hd->tieuChi->ten_tieu_chi, 40) }}</p>
          </div>
          
          <!-- Phần chân của thẻ hiển thị số lượng và nút chi tiết -->
          <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex justify-between items-center">
            <span class="text-slate-500 text-xs"><i class="bi bi-people mr-1.5 text-slate-400"></i> Số lượng: <strong>{{ $hd->dangKyHoatDongs()->count() }}/{{ $hd->slot_toi_da }}</strong></span>
            <a href="{{ route('hoat_dong.show', $hd->id) }}" class="btn-outline-primary px-3.5 py-1.5 text-xs">Chi tiết <i class="bi bi-arrow-right ml-1"></i></a>
          </div>
        </div>
      @endforeach
      
      <!-- Phân trang danh sách -->
      <div class="col-span-full mt-6">
        {{ $hoatDongs->links() }}
      </div>
    @endif
  </div>
</div>
@endsection