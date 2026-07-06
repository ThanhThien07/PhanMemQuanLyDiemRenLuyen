@extends('layouts.app')

@section('content')
<!-- 
  ========================================================================
  VIEW: CHI TIẾT HOẠT ĐỘNG RÈN LUYỆN & ĐĂNG KÝ THAM GIA
  ========================================================================
  * Trách nhiệm thiết kế:
    - Bố cục lưới: 100% Tailwind Grid (`grid-cols-1 lg:grid-cols-3 gap-6`).
    - Nút bấm: Sử dụng lớp @utility btn-primary và btn-outline-danger trong app.css.
  * Tính năng chính:
    - Hiển thị QR Code điểm danh tự động mở khóa theo khung giờ diễn ra hoạt động.
    - Quá trình đăng ký / hủy đăng ký của sinh viên.
    - Script AJAX tự động thăm dò (polling) mỗi 2s để kiểm tra trạng thái điểm danh.
  ========================================================================
-->
@php
  $now = now();
  $isOngoing = $now->between($hoatDong->thoi_gian_bat_dau, $hoatDong->thoi_gian_ket_thuc);
@endphp
<div class="container-fluid">
  <div class="mb-4">
    <a href="{{ route('hoat_dong.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
          <span class="badge bg-sky-100 text-sky-700 px-3 py-2 text-sm font-semibold rounded-lg border-0">Dự kiến cộng: +{{ $hoatDong->diem_du_kien }} điểm</span>
          <span class="badge bg-green-100 text-green-700 px-3 py-2 text-sm font-semibold rounded-lg border-0">{{ $hoatDong->trang_thai === 'da_cong_bo' ? 'Mở đăng ký' : 'Đã kết thúc' }}</span>
        </div>
        <h2 class="font-bold text-slate-800 text-2xl mb-4">{{ $hoatDong->ten_hoat_dong }}</h2>
        <p class="text-slate-500 mb-4"><i class="bi bi-building mr-1"></i> Đơn vị tổ chức: <strong>{{ $hoatDong->donViToChuc->ten_don_vi }}</strong></p>

        <hr class="border-slate-100 my-4">

        <h5 class="font-bold text-slate-800 mt-6 mb-4">Thông tin chi tiết</h5>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-slate-50 p-4 rounded-xl">
            <span class="text-slate-500 text-xs d-block mb-1">Thời gian diễn ra:</span>
            <strong class="text-slate-700">{{ $hoatDong->thoi_gian_bat_dau->format('d/m/Y H:i') }} - {{ $hoatDong->thoi_gian_ket_thuc->format('d/m/Y H:i') }}</strong>
          </div>
          <div class="bg-slate-50 p-4 rounded-xl">
            <span class="text-slate-500 text-xs d-block mb-1">Tiêu chí tích lũy:</span>
            <strong class="text-slate-700">[{{ $hoatDong->tieuChi->ma_tieu_chi }}] {{ $hoatDong->tieuChi->ten_tieu_chi }}</strong>
          </div>
          <div class="bg-slate-50 p-4 rounded-xl">
            <span class="text-slate-500 text-xs d-block mb-1">Hình thức điểm danh:</span>
            <strong class="text-slate-700">{{ strtoupper($hoatDong->hinh_thuc_diem_danh) }} (Mã QR)</strong>
          </div>
          <div class="bg-slate-50 p-4 rounded-xl">
            <span class="text-slate-500 text-xs d-block mb-1">Yêu cầu minh chứng:</span>
            <strong class="text-slate-700">{{ $hoatDong->yeu_cau_minh_chung ? 'Cần nộp ảnh/file minh chứng' : 'Không yêu cầu' }}</strong>
          </div>
        </div>

        @if($hoatDong->hinh_thuc_diem_danh === 'qr')
          <div class="mt-6 pt-6 border-t border-slate-100 text-center">
            <h5 class="font-bold text-slate-800 mb-4"><i class="bi bi-qr-code mr-2 text-sky-500"></i>Mã QR Điểm danh cố định</h5>
            
            <div class="p-3 bg-white d-inline-block border border-slate-100 rounded-xl shadow-sm">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(route('hoat_dong.diem_danh_qr', $hoatDong->id)) }}" 
                   class="img-fluid @if(!$isOngoing) blur pointer-events-none select-none @endif" 
                   id="qr-image"
                   alt="Mã QR Điểm danh">
            </div>
            
            @if(!$isOngoing)
              <div class="text-danger mt-3 font-semibold" id="qr-status-msg">
                <i class="bi bi-lock-fill mr-1"></i> Mã QR đang bị khóa (Chỉ khả dụng từ {{ $hoatDong->thoi_gian_bat_dau->format('H:i d/m/Y') }} đến {{ $hoatDong->thoi_gian_ket_thuc->format('H:i d/m/Y') }})
              </div>
            @else
              <div class="text-success mt-3 font-semibold animate-pulse" id="qr-status-msg">
                <i class="bi bi-qr-code-scan mr-1"></i> Mã QR đã mở khóa! Quét mã bằng điện thoại để điểm danh ngay.
              </div>
            @endif
          </div>
        @endif
      </div>
    </div>

    <div class="lg:col-span-1">
      <div id="registration-status-card" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        <h5 class="font-bold text-slate-800 mb-4"><i class="bi bi-shield-check mr-2 text-sky-500"></i>Trạng thái đăng ký</h5>
        
        @auth
          @if(Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su')
            @if($isRegistered)
              <div class="alert alert-success bg-success bg-opacity-10 text-success border-0 py-3 mb-4 rounded-xl">
                <i class="bi bi-check-circle-fill mr-2"></i>Bạn đã đăng ký hoạt động này.
                <hr class="my-2 border-success border-opacity-20">
                <span class="small">Điểm danh: <strong>{{ $diemDanhStatus }}</strong></span>
              </div>
              <form action="{{ route('hoat_dong.cancel', $hoatDong->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-outline-danger w-full"><i class="bi bi-x-circle mr-1"></i> Hủy đăng ký</button>
              </form>
            @else
              @if($hoatDong->trang_thai === 'da_cong_bo')
                <form action="{{ route('hoat_dong.register', $hoatDong->id) }}" method="POST">
                  @csrf
                  <button type="submit" class="btn-primary w-full"><i class="bi bi-person-plus mr-1"></i> Đăng ký tham gia</button>
                </form>
              @else
                <button class="w-full py-2.5 font-bold text-slate-400 bg-slate-100 rounded-xl border-0 cursor-not-allowed" disabled>Hoạt động đã khóa</button>
              @endif
            @endif
          @else
            <div class="alert alert-info bg-info bg-opacity-10 text-info border-0 py-3 mb-4 rounded-xl">
              Vai trò của bạn là <strong>{{ Auth::user()->role }}</strong>. Chỉ sinh viên mới có thể đăng ký tham gia.
            </div>
            @if(Auth::user()->role === 'ctsv' || Auth::user()->role === 'don_vi_to_chuc')
              <a href="{{ route('hoat_dong.attendance', $hoatDong->id) }}" class="btn-primary w-full text-decoration-none"><i class="bi bi-qr-code-scan mr-1"></i> Điểm danh sinh viên</a>
            @endif
          @endif
        @endauth
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')

@auth
  @if(Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su')
    <script>
      $(document).ready(function() {
        // Poll every 2 seconds to check attendance status
        let checkInterval = setInterval(function() {
          $.ajax({
            url: '{{ route("hoat_dong.check_attendance", $hoatDong->id) }}',
            type: 'GET',
            success: function(data) {
              if (data.success && data.status === 'co_mat') {
                // Update registration status panel dynamically
                let panel = $('#registration-status-card');
                panel.html(`
                  <h5 class="font-bold text-slate-800 mb-4"><i class="bi bi-shield-check mr-2 text-sky-500"></i>Trạng thái đăng ký</h5>
                  <div class="alert alert-success bg-success bg-opacity-10 text-success border-0 py-3 mb-4 rounded-xl">
                    <i class="bi bi-check-circle-fill mr-2"></i>Bạn đã đăng ký hoạt động này.
                    <hr class="my-2 border-success border-opacity-20">
                    <span class="small">Điểm danh: <strong>co_mat</strong></span>
                  </div>
                  <form action="{{ route('hoat_dong.cancel', $hoatDong->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-outline-danger w-full"><i class="bi bi-x-circle mr-1"></i> Hủy đăng ký</button>
                  </form>
                `);
                clearInterval(checkInterval); // Stop polling once checked in
              }
            }
          });
        }, 2000);
      });
    </script>
  @endif
@endauth
@endsection