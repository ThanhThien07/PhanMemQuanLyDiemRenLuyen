@extends('layouts.app')

@section('content')
@php
  $now = now();
  $isOngoing = $now->between($hoatDong->thoi_gian_bat_dau, $hoatDong->thoi_gian_ket_thuc);
@endphp
<div class="container-fluid">
  <div class="mb-4">
    <a href="{{ route('hoat_dong.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="card-premium p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="badge bg-primary bg-opacity-10 text-primary badge-premium fs-6">Dự kiến cộng: +{{ $hoatDong->diem_du_kien }} điểm</span>
          <span class="badge bg-success badge-premium">{{ $hoatDong->trang_thai === 'da_cong_bo' ? 'Mở đăng ký' : 'Đã kết thúc' }}</span>
        </div>
        <h2 class="fw-bold text-dark mb-3">{{ $hoatDong->ten_hoat_dong }}</h2>
        <p class="text-muted"><i class="bi bi-building me-1"></i> Đơn vị tổ chức: <strong>{{ $hoatDong->donViToChuc->ten_don_vi }}</strong></p>

        <hr>

        <h5 class="fw-bold text-dark mt-4 mb-3">Thông tin chi tiết</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="bg-light p-3 rounded">
              <span class="text-muted small d-block">Thời gian diễn ra:</span>
              <strong>{{ $hoatDong->thoi_gian_bat_dau->format('d/m/Y H:i') }} - {{ $hoatDong->thoi_gian_ket_thuc->format('d/m/Y H:i') }}</strong>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-light p-3 rounded">
              <span class="text-muted small d-block">Tiêu chí tích lũy:</span>
              <strong>[{{ $hoatDong->tieuChi->ma_tieu_chi }}] {{ $hoatDong->tieuChi->ten_tieu_chi }}</strong>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-light p-3 rounded">
              <span class="text-muted small d-block">Hình thức điểm danh:</span>
              <strong>{{ strtoupper($hoatDong->hinh_thuc_diem_danh) }} (Mã QR)</strong>
            </div>
          </div>
          <div class="col-md-6">
            <div class="bg-light p-3 rounded">
              <span class="text-muted small d-block">Yêu cầu minh chứng:</span>
              <strong>{{ $hoatDong->yeu_cau_minh_chung ? 'Cần nộp ảnh/file minh chứng' : 'Không yêu cầu' }}</strong>
            </div>
          </div>
        </div>

        @if($hoatDong->hinh_thuc_diem_danh === 'qr')
          <div class="mt-4 pt-4 border-top text-center">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-qr-code me-2 text-primary"></i>Mã QR Điểm danh cố định</h5>
            
            <div class="qr-wrapper p-3 bg-white d-inline-block border rounded shadow-sm">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(route('hoat_dong.diem_danh_qr', $hoatDong->id)) }}" 
                   class="img-fluid @if(!$isOngoing) qr-blurred @endif" 
                   id="qr-image"
                   alt="Mã QR Điểm danh">
            </div>
            
            @if(!$isOngoing)
              <div class="text-danger mt-2 fw-semibold" id="qr-status-msg">
                <i class="bi bi-lock-fill me-1"></i> Mã QR đang bị khóa (Chỉ khả dụng từ {{ $hoatDong->thoi_gian_bat_dau->format('H:i d/m/Y') }} đến {{ $hoatDong->thoi_gian_ket_thuc->format('H:i d/m/Y') }})
              </div>
            @else
              <div class="text-success mt-2 fw-semibold animate-pulse" id="qr-status-msg">
                <i class="bi bi-qr-code-scan me-1"></i> Mã QR đã mở khóa! Quét mã bằng điện thoại để điểm danh ngay.
              </div>
            @endif
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card-premium p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check me-2"></i>Trạng thái đăng ký</h5>
        
        @if(Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su')
          @if($isRegistered)
            <div class="alert alert-success bg-success bg-opacity-10 text-success border-0 py-3 mb-3">
              <i class="bi bi-check-circle-fill me-2"></i>Bạn đã đăng ký hoạt động này.
              <hr class="my-2">
              <span class="small">Điểm danh: <strong>{{ $diemDanhStatus }}</strong></span>
            </div>
            <form action="{{ route('hoat_dong.cancel', $hoatDong->id) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-bold"><i class="bi bi-x-circle me-1"></i> Hủy đăng ký</button>
            </form>
          @else
            @if($hoatDong->trang_thai === 'da_cong_bo')
              <form action="{{ route('hoat_dong.register', $hoatDong->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-premium btn w-100 py-2 fw-bold"><i class="bi bi-person-plus me-1"></i> Đăng ký tham gia</button>
              </form>
            @else
              <button class="btn btn-secondary w-100 py-2 fw-bold" disabled>Hoạt động đã khóa</button>
            @endif
          @endif
        @else
          <div class="alert alert-info bg-info bg-opacity-10 text-info border-0 py-3 mb-3">
            Vai trò của bạn là <strong>{{ Auth::user()->role }}</strong>. Chỉ sinh viên mới có thể đăng ký tham gia.
          </div>
          @if(Auth::user()->role === 'admin' || Auth::user()->role === 'don_vi_to_chuc')
            <a href="{{ route('hoat_dong.attendance', $hoatDong->id) }}" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-qr-code-scan me-1"></i> Điểm danh sinh viên</a>
          @endif
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<style>
  .qr-blurred {
    filter: blur(8px);
    pointer-events: none;
    user-select: none;
  }
  @keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
  }
  .animate-pulse {
    animation: pulse 2s infinite ease-in-out;
  }
</style>

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
                let panel = $('.col-lg-4 .card-premium');
                panel.html(`
                  <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check me-2"></i>Trạng thái đăng ký</h5>
                  <div class="alert alert-success bg-success bg-opacity-10 text-success border-0 py-3 mb-3">
                    <i class="bi bi-check-circle-fill me-2"></i>Bạn đã đăng ký hoạt động này.
                    <hr class="my-2">
                    <span class="small">Điểm danh: <strong>co_mat</strong></span>
                  </div>
                  <form action="{{ route('hoat_dong.cancel', $hoatDong->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-bold"><i class="bi bi-x-circle me-1"></i> Hủy đăng ký</button>
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