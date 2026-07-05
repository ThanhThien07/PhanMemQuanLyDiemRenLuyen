@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="mb-4">
    <a href="{{ route('hoat_dong.show', $hoatDong->id) }}" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Quay lại chi tiết</a>
  </div>

  <div class="card-premium p-4">
    <h3 class="fw-bold text-dark mb-2"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Điểm danh sinh viên</h3>
    <p class="text-muted">Hoạt động: <strong>{{ $hoatDong->ten_hoat_dong }}</strong></p>

    <div class="table-responsive mt-4">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>MSSV</th>
            <th>Họ và tên</th>
            <th>Lớp</th>
            <th class="text-center">Điểm danh</th>
          </tr>
        </thead>
        <tbody>
          @if($registrations->isEmpty())
            <tr>
              <td colspan="4" class="text-center text-muted py-4">Chưa có sinh viên nào đăng ký hoạt động này.</td>
            </tr>
          @else
            @foreach($registrations as $reg)
              @php
                $status = $reg->diemDanh ? $reg->diemDanh->trang_thai : 'vang_mat';
              @endphp
              <tr>
                <td>{{ optional($reg->sinhVien)->ma_sv }}</td>
                <td>{{ optional($reg->sinhVien)->ho_ten }}</td>
                <td>{{ optional(optional($reg->sinhVien)->lop)->ten_lop }}</td>
                <td class="text-center">
                  <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-{{ $status === 'co_mat' ? 'success' : 'outline-success' }}" onclick="markAttendance({{ $reg->id }}, 'co_mat', this)">Có mặt</button>
                    <button type="button" class="btn btn-sm btn-{{ $status === 'vang_mat' ? 'danger' : 'outline-danger' }}" onclick="markAttendance({{ $reg->id }}, 'vang_mat', this)">Vắng mặt</button>
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
          const row = $(button).closest('tr');
          row.find('.btn-success').removeClass('btn-success').addClass('btn-outline-success');
          row.find('.btn-danger').removeClass('btn-danger').addClass('btn-outline-danger');
          
          if (status === 'co_mat') {
            $(button).removeClass('btn-outline-success').addClass('btn-success');
          } else {
            $(button).removeClass('btn-outline-danger').addClass('btn-danger');
          }
        }
      }
    });
  }
</script>
@endsection