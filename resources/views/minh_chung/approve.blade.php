@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="card-premium p-4">
    <h3 class="fw-bold text-dark mb-4"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Phê duyệt minh chứng của sinh viên</h3>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Sinh viên</th>
            <th>Hoạt động</th>
            <th>Điểm đề xuất</th>
            <th class="text-center">Minh chứng</th>
            <th class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @if($hoSoMinhChungs->isEmpty())
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">Hiện tại không có hồ sơ minh chứng nào chờ duyệt.</td>
            </tr>
          @else
            @foreach($hoSoMinhChungs as $hs)
              <tr>
                <td>
                  <strong>{{ $hs->sinhVien->ho_ten }}</strong>
                  <div class="small text-muted">MSSV: {{ $hs->sinhVien->ma_sv }} | Lớp: {{ $hs->sinhVien->lop->ten_lop }}</div>
                </td>
                <td>
                  @if($hs->hoatDong)
                    <strong>{{ $hs->hoatDong->ten_hoat_dong }}</strong>
                  @else
                    <strong>{{ $hs->ten_hoat_dong_ngoai }}</strong> <span class="badge bg-secondary">Ngoài trường</span>
                  @endif
                  <div class="small text-muted">Tiêu chí: {{ $hs->tieuChi ? $hs->tieuChi->ten_tieu_chi : 'Chưa gán' }}</div>
                </td>
                <td class="fw-semibold text-primary">+{{ $hs->diem_de_xuat }}</td>
                <td class="text-center">
                  <a href="{{ asset($hs->minhChung->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text"></i> Xem file</a>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-success me-1 fw-bold" onclick="approveProof({{ $hs->id }}, 'da_duyet')"><i class="bi bi-check-circle"></i> Duyệt</button>
                  <button class="btn btn-sm btn-danger fw-bold" onclick="rejectProof({{ $hs->id }})"><i class="bi bi-x-circle"></i> Từ chối</button>
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
  function approveProof(id, status) {
    if (confirm('Bạn có chắc chắn muốn duyệt minh chứng này không?')) {
      $.ajax({
        url: '/minh-chung/duyet/' + id,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          trang_thai: status
        },
        success: function(response) {
          if (response.success) {
            location.reload();
          }
        }
      });
    }
  }

  function rejectProof(id) {
    const reason = prompt('Nhập lý do từ chối:');
    if (reason !== null) {
      $.ajax({
        url: '/minh-chung/duyet/' + id,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          trang_thai: 'tu_choi',
          ly_do: reason
        },
        success: function(response) {
          if (response.success) {
            location.reload();
          }
        }
      });
    }
  }
</script>
@endsection