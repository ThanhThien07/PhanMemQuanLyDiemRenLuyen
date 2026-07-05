@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold text-dark mb-1"><i class="bi bi-calendar-event me-2"></i>Hoạt Động Rèn Luyện</h2>
      <p class="text-muted mb-0">Đăng ký tham gia các hoạt động để ghi nhận điểm rèn luyện.</p>
    </div>
    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'don_vi_to_chuc')
      <a href="{{ route('hoat_dong.create') }}" class="btn-premium btn d-flex align-items-center gap-2"><i class="bi bi-plus-circle"></i> Tạo hoạt động mới</a>
    @endif
  </div>

  <div class="card-premium p-4 mb-4">
    <form action="{{ route('hoat_dong.index') }}" method="GET" class="row g-3">
      <div class="col-md-5">
        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tên hoạt động..." value="{{ request('search') }}">
      </div>
      <div class="col-md-4">
        <select name="tieu_chi" class="form-select">
          <option value="">-- Tất cả tiêu chí rèn luyện --</option>
          @foreach($tieuChis as $tc)
            <option value="{{ $tc->id }}" {{ request('tieu_chi') == $tc->id ? 'selected' : '' }}>[{{ $tc->ma_tieu_chi }}] {{ $tc->ten_tieu_chi }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-premium w-100"><i class="bi bi-search me-2"></i> Lọc kết quả</button>
      </div>
    </form>
  </div>

  <div class="row g-4">
    @if($hoatDongs->isEmpty())
      <div class="col-12 text-center py-5">
        <i class="bi bi-calendar-x fs-1 text-muted"></i>
        <h5 class="fw-bold text-muted mt-3">Không tìm thấy hoạt động nào.</h5>
      </div>
    @else
      @foreach($hoatDongs as $hd)
        <div class="col-md-6 col-lg-4">
          <div class="card-premium h-100 d-flex flex-column justify-content-between">
            <div class="p-4">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge bg-primary bg-opacity-10 text-primary badge-premium">Điểm cộng: +{{ $hd->diem_du_kien }}</span>
                <span class="badge bg-secondary badge-premium">{{ $hd->loai_hoat_dong === 'trong_truong' ? 'Trong trường' : 'Ngoài trường' }}</span>
              </div>
              <h5 class="fw-bold text-dark mb-2">{{ $hd->ten_hoat_dong }}</h5>
              <p class="text-muted small mb-3"><i class="bi bi-building me-1"></i> Đơn vị: {{ $hd->donViToChuc->ten_don_vi }}</p>
              <p class="text-muted small mb-1"><i class="bi bi-calendar me-1"></i> Bắt đầu: {{ $hd->thoi_gian_bat_dau->format('d/m/Y H:i') }}</p>
              <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i> Tiêu chí: [{{ $hd->tieuChi->ma_tieu_chi }}] {{ Str::limit($hd->tieuChi->ten_tieu_chi, 40) }}</p>
            </div>
            <div class="border-top p-4 bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
              <span class="text-muted small"><i class="bi bi-people me-1"></i> Số lượng: {{ $hd->dangKyHoatDongs()->count() }}/{{ $hd->slot_toi_da }}</span>
              <a href="{{ route('hoat_dong.show', $hd->id) }}" class="btn btn-sm btn-outline-primary fw-bold">Chi tiết <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      @endforeach
      <div class="col-12 mt-4">
        {{ $hoatDongs->links() }}
      </div>
    @endif
  </div>
</div>
@endsection