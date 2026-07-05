@extends('layouts.app')

@section('content')
<!-- 
  ========================================================================
  VIEW: THÊM HOẠT ĐỘNG RÈN LUYỆN MỚI
  ========================================================================
  * Trách nhiệm: 
    - Bố cục & Giao diện: Sử dụng 100% Tailwind CSS v4.
    - form-control-custom: Input chuẩn hóa Tailwind v4 định nghĩa trong app.css.
    - btn-primary: Nút submit dạng tái sử dụng, loại bỏ hoàn toàn class .btn.
  * Tương tác: Gửi dữ liệu form qua phương thức POST tới hoat_dong.store.
  ========================================================================
-->
<div class="container-fluid">
  <div class="mb-4">
    <a href="{{ route('hoat_dong.index') }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Quay lại</a>
  </div>

  <!-- Wrapper Card chứa form (Sử dụng Tailwind Card thay thế Bootstrap) -->
  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h3 class="font-bold text-slate-800 text-xl mb-6"><i class="bi bi-plus-circle text-sky-500 mr-2"></i> Thêm Hoạt Động Rèn Luyện Mới</h3>

    <form action="{{ route('hoat_dong.store') }}" method="POST">
      @csrf
      
      <!-- Layout chia 2 cột trên màn hình Medium trở lên dùng Tailwind Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Nhóm Mã hoạt động -->
        <div>
          <label class="form-label text-slate-700 font-semibold mb-2">Mã hoạt động</label>
          <input type="text" name="ma_hoat_dong" class="form-control-custom" placeholder="HD-001" required>
        </div>

        <!-- Nhóm Tên hoạt động -->
        <div>
          <label class="form-label text-slate-700 font-semibold mb-2">Tên hoạt động</label>
          <input type="text" name="ten_hoat_dong" class="form-control-custom" placeholder="Hội thảo kỹ năng sinh viên" required>
        </div>

        <!-- Nhóm Tiêu chí tích lũy điểm -->
        <div>
          <label class="form-label text-slate-700 font-semibold mb-2">Tiêu chí rèn luyện</label>
          <select name="tieu_chi_id" class="form-control-custom" required>
            @foreach($tieuChis as $tc)
              <option value="{{ $tc->id }}">[{{ $tc->ma_tieu_chi }}] {{ $tc->ten_tieu_chi }}</option>
            @endforeach
          </select>
        </div>

        <!-- Nhóm Điểm cộng dự kiến -->
        <div>
          <label class="form-label text-slate-700 font-semibold mb-2">Điểm cộng dự kiến</label>
          <input type="number" name="diem_du_kien" class="form-control-custom" placeholder="5" required>
        </div>

        <!-- Nhóm Thời gian bắt đầu -->
        <div>
          <label class="form-label text-slate-700 font-semibold mb-2">Thời gian bắt đầu</label>
          <input type="datetime-local" name="thoi_gian_bat_dau" class="form-control-custom" required>
        </div>

        <!-- Nhóm Thời gian kết thúc -->
        <div>
          <label class="form-label text-slate-700 font-semibold mb-2">Thời gian kết thúc</label>
          <input type="datetime-local" name="thoi_gian_ket_thuc" class="form-control-custom" required>
        </div>

        <!-- Nhóm Số lượng sinh viên tối đa -->
        <div>
          <label class="form-label text-slate-700 font-semibold mb-2">Số lượng sinh viên tối đa</label>
          <input type="number" name="slot_toi_da" class="form-control-custom" value="100" required>
        </div>

        <!-- Nhóm Yêu cầu minh chứng (Sử dụng form-check của Bootstrap nhưng canh lề bằng Tailwind) -->
        <div class="flex items-center md:pt-6">
          <div class="form-check">
            <input class="form-check-input cursor-pointer" type="checkbox" name="yeu_cau_minh_chung" id="yeu_cau_minh_chung" value="1">
            <label class="form-check-label font-semibold text-slate-700 cursor-pointer ml-1" for="yeu_cau_minh_chung">
              Yêu cầu nộp ảnh/file minh chứng
            </label>
          </div>
        </div>

      </div>

      <!-- Nút submit form (Dùng btn-primary thiết kế sẵn trong app.css) -->
      <div class="mt-6">
        <button type="submit" class="btn-primary px-6">Tạo hoạt động</button>
      </div>
    </form>
  </div>
</div>
@endsection