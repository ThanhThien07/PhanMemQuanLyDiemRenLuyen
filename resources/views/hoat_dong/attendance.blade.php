@extends('layouts.app')

@section('content')
<!-- 
  ========================================================================
  VIEW: ĐIỂM DANH SINH VIÊN THAM GIA HOẠT ĐỘNG
  ========================================================================
  * Trách nhiệm thiết kế:
    - Bố cục chung & Card: Tailwind CSS v4.
    - Bảng danh sách sinh viên: Tái cấu trúc hoàn toàn sang Tailwind Table.
    - Toggle Nút Điểm danh: Sử dụng Tailwind flex, border-radius và state-colors.
  * Logic nghiệp vụ:
    - Điểm danh thông qua AJAX gửi về endpoint `/hoat-dong/diem-danh/{id}`.
    - Trạng thái thay đổi động (Có mặt / Vắng mặt) cập nhật trực tiếp qua DOM JS.
  ========================================================================
-->
<div class="container-fluid">
  <div class="mb-4">
    <a href="{{ route('hoat_dong.show', $hoatDong->id) }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Quay lại chi tiết</a>
  </div>

  <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <h3 class="font-bold text-slate-800 text-xl mb-2"><i class="bi bi-qr-code-scan mr-2 text-sky-500"></i>Điểm danh sinh viên</h3>
    <p class="text-slate-500 text-sm mb-4">Hoạt động: <strong class="text-slate-700">{{ $hoatDong->ten_hoat_dong }}</strong></p>

    <div class="overflow-x-auto mt-6">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">MSSV</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Họ và tên</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lớp</th>
            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Điểm danh</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-100">
          @if($registrations->isEmpty())
            <tr>
              <td colspan="4" class="px-6 py-8 text-center text-slate-400 text-sm">Chưa có sinh viên nào đăng ký hoạt động này.</td>
            </tr>
          @else
            @foreach($registrations as $reg)
              @php
                $status = $reg->diemDanh ? $reg->diemDanh->trang_thai : 'vang_mat';
              @endphp
              <tr class="hover:bg-slate-50/80 transition duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">{{ optional($reg->sinhVien)->ma_sv }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ optional($reg->sinhVien)->ho_ten }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ optional(optional($reg->sinhVien)->lop)->ten_lop }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                  <div class="inline-flex rounded-lg shadow-sm border border-slate-200 overflow-hidden" role="group">
                    <button type="button" 
                            class="px-3 py-1.5 text-xs font-semibold border-r border-slate-200 transition-colors duration-200 cursor-pointer {{ $status === 'co_mat' ? 'bg-green-600 text-white' : 'bg-white text-green-600 hover:bg-green-50' }}" 
                            data-status-btn="co_mat"
                            onclick="markAttendance({{ $reg->id }}, 'co_mat', this)">Có mặt</button>
                    <button type="button" 
                            class="px-3 py-1.5 text-xs font-semibold transition-colors duration-200 cursor-pointer {{ $status === 'vang_mat' ? 'bg-red-600 text-white' : 'bg-white text-red-600 hover:bg-red-50' }}" 
                            data-status-btn="vang_mat"
                            onclick="markAttendance({{ $reg->id }}, 'vang_mat', this)">Vắng mặt</button>
                  </div>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  /**
   * XỬ LÝ AJAX ĐIỂM DANH SINH VIÊN
   * * Gửi yêu cầu cập nhật trạng thái (co_mat / vang_mat) của sinh viên.
   * * Sử dụng jQuery để thao tác trực tiếp trên DOM của các nút bấm.
   * * Thay đổi trạng thái hiển thị bằng cách gỡ bỏ và bổ sung các lớp Tailwind tương ứng.
   */
  function markAttendance(regId, status, button) {
    $.ajax({
      url: '/hoat-dong/diem-danh/' + regId,
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        status: status
      },
      success: function(response) {
        if(response.success) {
          const container = $(button).parent();
          const coMatBtn = container.find('[data-status-btn="co_mat"]');
          const vangMatBtn = container.find('[data-status-btn="vang_mat"]');
          
          // Khôi phục lớp mặc định (Chưa chọn) của cả hai nút bấm bằng các class Tailwind tĩnh
          coMatBtn.removeClass('bg-green-600 text-white').addClass('bg-white text-green-600 hover:bg-green-50');
          vangMatBtn.removeClass('bg-red-600 text-white').addClass('bg-white text-red-600 hover:bg-red-50');
          
          // Kích hoạt màu trạng thái active tương ứng
          if (status === 'co_mat') {
            coMatBtn.removeClass('bg-white text-green-600 hover:bg-green-50').addClass('bg-green-600 text-white');
          } else {
            vangMatBtn.removeClass('bg-white text-red-600 hover:bg-red-50').addClass('bg-red-600 text-white');
          }
        }
      },
      error: function(xhr) {
        let errorMsg = 'Không thể kết nối máy chủ.';
        if (xhr.status === 403) {
          errorMsg = 'Bạn không có quyền thực hiện chức năng này.';
        } else if (xhr.status === 419) {
          errorMsg = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        alert('Lỗi: ' + errorMsg);
      }
    });
  }
</script>
@endsection