@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="mb-4">
    <a href="{{ route('hoat_dong.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Quay lại</a>
  </div>

  <div class="card-premium p-4">
    <h3 class="fw-bold mb-4"><i class="bi bi-plus-circle text-primary"></i> Thêm Hoạt Động Rèn Luyện Mới</h3>

    <form action="{{ route('hoat_dong.store') }}" method="POST">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Mã hoạt động</label>
          <input type="text" name="ma_hoat_dong" class="form-control" placeholder="HD-001" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tên hoạt động</label>
          <input type="text" name="ten_hoat_dong" class="form-control" placeholder="Hội thảo kỹ năng sinh viên" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tiêu chí rèn luyện</label>
          <select name="tieu_chi_id" class="form-select" required>
            @foreach($tieuChis as $tc)
              <option value="{{ $tc->id }}">[{{ $tc->ma_tieu_chi }}] {{ $tc->ten_tieu_chi }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Điểm cộng dự kiến</label>
          <input type="number" name="diem_du_kien" class="form-control" placeholder="5" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Thời gian bắt đầu</label>
          <input type="datetime-local" name="thoi_gian_bat_dau" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Thời gian kết thúc</label>
          <input type="datetime-local" name="thoi_gian_ket_thuc" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Số lượng sinh viên tối đa</label>
          <input type="number" name="slot_toi_da" class="form-control" value="100" required>
        </div>
        <div class="col-md-6 d-flex align-items-center">
          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="yeu_cau_minh_chung" id="yeu_cau_minh_chung" value="1">
            <label class="form-check-label fw-semibold text-dark" for="yeu_cau_minh_chung">
              Yêu cầu nộp ảnh/file minh chứng
            </label>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <button type="submit" class="btn btn-premium px-5 py-2.5">Tạo hoạt động</button>
      </div>
    </form>
  </div>
</div>
@endsection