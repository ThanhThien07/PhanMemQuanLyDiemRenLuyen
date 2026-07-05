@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4 align-items-center">
    <div class="col">
      <h2 class="fw-bold text-dark mb-1"><i class="bi bi-columns-gap me-2"></i>Bảng Điều Khiển</h2>
      <p class="text-muted mb-0">Chào mừng bạn trở lại hệ thống quản lý điểm rèn luyện.</p>
    </div>
  </div>

  @if(isset($no_profile) && $no_profile)
    <div class="alert alert-warning card-premium p-4 border-start border-warning border-4 shadow-sm" role="alert">
      <h5 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Chưa khởi tạo hồ sơ sinh viên!</h5>
      <p class="mb-0">Hệ thống chưa tìm thấy hồ sơ sinh viên cho tài khoản này. Vui lòng liên hệ Phòng CTSV hoặc Cố vấn học tập để cập nhật thông tin.</p>
    </div>
  @elseif(Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su')
    <!-- Student Widgets Grid -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card-premium p-4 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Điểm tổng hợp học kỳ</span>
            <h2 class="fw-bold mt-1 text-primary">{{ $diemRenLuyen ? number_format($diemRenLuyen->diem_tong_hop, 2) : '0.00' }}</h2>
            <span class="badge bg-success badge-premium">{{ $diemRenLuyen ? $diemRenLuyen->xep_loai : 'Chưa xếp loại' }}</span>
          </div>
          <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
            <i class="bi bi-star-fill fs-3"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-premium p-4 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Hoạt động đã tham gia</span>
            <h2 class="fw-bold mt-1 text-success">{{ $hoatDongDaThamGiaCount }}</h2>
            <span class="small text-muted">Có mặt tại sự kiện</span>
          </div>
          <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
            <i class="bi bi-calendar-check fs-3"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-premium p-4 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Minh chứng chờ duyệt</span>
            <h2 class="fw-bold mt-1 text-warning">{{ $minhChungChoDuyetCount }}</h2>
            <span class="small text-muted">Hồ sơ đã nộp</span>
          </div>
          <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
            <i class="bi bi-file-earmark-arrow-up fs-3"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Profile summary -->
    <div class="card-premium p-4 mb-4">
      <div class="row align-items-center">
        <div class="col-auto">
          <div class="bg-primary text-white p-3 rounded-circle fs-3 fw-bold">
            {{ substr($sinhVien->ho_ten, 0, 2) }}
          </div>
        </div>
        <div class="col">
          <h4 class="fw-bold mb-1 text-dark">{{ $sinhVien->ho_ten }}</h4>
          <p class="text-muted mb-0">MSSV: <strong>{{ $sinhVien->ma_sv }}</strong> | Lớp: <strong>{{ $sinhVien->lop->ten_lop }}</strong> | Hệ đào tạo: <strong>{{ $sinhVien->heDaoTao->ten_he }}</strong></p>
        </div>
      </div>
    </div>
  @else
    <!-- Advisor & CTSV Statistics -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card-premium p-4 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Tổng số sinh viên</span>
            <h2 class="fw-bold mt-1">{{ $stats['total_students'] }}</h2>
          </div>
          <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
            <i class="bi bi-people-fill fs-3"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-premium p-4 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Tổng số hoạt động</span>
            <h2 class="fw-bold mt-1 text-success">{{ $stats['total_activities'] }}</h2>
          </div>
          <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
            <i class="bi bi-calendar3 fs-3"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-premium p-4 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Hồ sơ minh chứng chờ duyệt</span>
            <h2 class="fw-bold mt-1 text-warning">{{ $stats['pending_evidences'] }}</h2>
          </div>
          <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
            <i class="bi bi-file-earmark-check fs-3"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-premium p-4 d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Khiếu nại chờ xử lý</span>
            <h2 class="fw-bold mt-1 text-danger">{{ $stats['pending_complaints'] }}</h2>
          </div>
          <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
            <i class="bi bi-exclamation-octagon fs-3"></i>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Notifications Section -->
  <div class="card-premium mb-4">
    <div class="card-premium-header">
      <h5 class="card-premium-title"><i class="bi bi-bell-fill text-warning"></i>Thông báo mới nhất</h5>
    </div>
    <div class="p-4">
      @if($thongBaos->isEmpty())
        <div class="text-center py-4 text-muted">Không có thông báo mới nào.</div>
      @else
        <div class="list-group list-group-flush">
          @foreach($thongBaos as $tb)
            <div class="list-group-item border-0 border-bottom py-3 px-0">
              <div class="d-flex justify-content-between align-items-start">
                <h6 class="fw-bold mb-1 text-dark">{{ $tb->tieu_de }}</h6>
                <span class="small text-muted">{{ $tb->created_at->diffForHumans() }}</span>
              </div>
              <p class="mb-0 text-muted small">{{ $tb->noi_dung }}</p>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
@endsection