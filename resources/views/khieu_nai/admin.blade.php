@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="card-premium p-4">
    <h3 class="fw-bold text-dark mb-4"><i class="bi bi-chat-left-text me-2 text-primary"></i>Danh sách duyệt bổ sung minh chứng từ sinh viên</h3>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Sinh viên</th>
            <th>Lý do / Nội dung bổ sung</th>
            <th>Minh chứng</th>
            <th class="text-center">Trạng thái</th>
            <th class="text-end">Giải quyết</th>
          </tr>
        </thead>
        <tbody>
          @if($khieuNais->isEmpty())
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">Không có yêu cầu bổ sung nào cần giải quyết.</td>
            </tr>
          @else
            @foreach($khieuNais as $kn)
              <tr>
                <td>
                  <strong>{{ $kn->sinhVien->ho_ten }}</strong>
                  <div class="small text-muted">MSSV: {{ $kn->sinhVien->ma_sv }} | Lớp: {{ $kn->sinhVien->lop->ten_lop }}</div>
                </td>
                <td>
                  <div class="text-dark">{{ $kn->ly_do }}</div>
                  @if($kn->hoatDong)
                    <div class="small text-muted">Liên quan: {{ $kn->hoatDong->ten_hoat_dong }}</div>
                  @endif
                </td>
                <td>
                  @if($kn->minhChung)
                    <a href="{{ asset($kn->minhChung->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Xem file</a>
                  @else
                    <span class="text-muted small">Không đính kèm</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($kn->trang_thai_xu_ly === 'cho_tiep_nhan')
                    <span class="badge bg-warning text-dark">Chờ tiếp nhận</span>
                  @elseif($kn->trang_thai_xu_ly === 'da_giai_quyet')
                    <span class="badge bg-success">Đã duyệt</span>
                  @else
                    <span class="badge bg-danger">Đã từ chối</span>
                  @endif
                </td>
                <td class="text-end">
                  @if($kn->trang_thai_xu_ly === 'cho_tiep_nhan')
                    <button class="btn btn-sm btn-success fw-bold me-1" onclick="resolveComplaint('{{ $kn->id }}', 'da_giai_quyet')"><i class="bi bi-check-circle"></i> Duyệt bổ sung</button>
                    <button class="btn btn-sm btn-danger fw-bold" onclick="resolveComplaint('{{ $kn->id }}', 'tu_choi')"><i class="bi bi-x-circle"></i> Từ chối</button>
                  @else
                    <span class="text-muted small">Đã xử lý</span>
                  @endif
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
  function resolveComplaint(id, status) {
    const feedback = prompt('Nhập nội dung phản hồi duyệt bổ sung minh chứng:');
    if (feedback !== null) {
      $.ajax({
        url: '/khieu-nai/reply/' + id,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          trang_thai: status,
          phan_hoi: feedback
        },
        success: function(response) {
          if (response.success) {
            location.reload();
          } else {
            alert(response.message || 'Có lỗi xảy ra khi xử lý yêu cầu.');
          }
        },
        error: function(xhr) {
          let msg = 'Có lỗi xảy ra khi kết nối máy chủ.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          }
          alert(msg);
        }
      });
    }
  }
</script>
@endsection