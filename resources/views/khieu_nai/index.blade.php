@extends('layouts.app')

@section('content')
<div class="container-fluid">
  @if(!$canSubmit)
    <div class="alert alert-warning border-warning shadow-sm mb-4 d-flex align-items-center" role="alert" style="border-radius:12px;">
      <i class="bi bi-exclamation-triangle-fill me-3 fs-4 text-warning"></i>
      <div>
        <strong>Thông báo:</strong> Bạn chưa nộp Phiếu tự đánh giá rèn luyện cho học kỳ này. Vui lòng hoàn tất và nộp phiếu tự đánh giá trước khi thực hiện bổ sung minh chứng ngoài.
      </div>
    </div>
  @endif

  <div class="row">
    <div class="col-lg-5">
      <div class="card-premium p-4 mb-4">
        <h4 class="fw-bold mb-4"><i class="bi bi-plus-circle-dotted me-2 text-primary"></i>Gửi bổ sung minh chứng</h4>

        <form action="{{ route('khieu_nai.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Chọn hoạt động cần bổ sung (nếu có)</label>
            <select name="hoat_dong_id" class="form-select" {{ !$canSubmit ? 'disabled' : '' }}>
              <option value="">-- Chọn hoạt động --</option>
              @foreach($hoatDongs as $hd)
                <option value="{{ $hd->id }}">{{ $hd->ten_hoat_dong }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Lý do / Nội dung bổ sung</label>
            <textarea name="ly_do" class="form-control" rows="4" placeholder="Mô tả chi tiết lý do hoặc nội dung bổ sung..." required {{ !$canSubmit ? 'disabled' : '' }}></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label text-dark fw-semibold">Tải lên tệp minh chứng bổ sung (nếu có)</label>
            <input type="file" name="file_khieu_nai" class="form-control" {{ !$canSubmit ? 'disabled' : '' }}>
          </div>

          <button type="submit" class="btn btn-premium w-100 py-2.5" {{ !$canSubmit ? 'disabled' : '' }}><i class="bi bi-send me-1"></i>Gửi yêu cầu bổ sung</button>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card-premium p-4">
        <h4 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-success"></i>Lịch sử bổ sung minh chứng cá nhân</h4>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Nội dung</th>
                <th>Phản hồi giải quyết</th>
                <th class="text-center">Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              @if($khieuNais->isEmpty())
                <tr>
                  <td colspan="3" class="text-center py-4 text-muted">Bạn chưa gửi yêu cầu bổ sung nào.</td>
                </tr>
              @else
                @foreach($khieuNais as $kn)
                  <tr>
                    <td>
                      <div class="fw-bold text-dark">{{ Str::limit($kn->ly_do, 50) }}</div>
                      @if($kn->hoatDong)
                        <div class="small text-muted">Hoạt động: {{ $kn->hoatDong->ten_hoat_dong }}</div>
                      @endif
                      @if($kn->minhChung)
                        <div class="mt-1 small"><a href="{{ asset($kn->minhChung->file_path) }}" target="_blank" class="text-decoration-none"><i class="bi bi-file-earmark-arrow-down me-1"></i>Xem file đính kèm</a></div>
                      @endif
                    </td>
                    <td>
                      @if($kn->phan_hoi)
                        <span class="text-success fw-semibold">{{ $kn->phan_hoi }}</span>
                      @else
                        <span class="text-muted small">Đang chờ giải quyết</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($kn->trang_thai_xu_ly === 'cho_tiep_nhan')
                        <span class="badge bg-warning text-dark">Chờ tiếp nhận</span>
                      @elseif($kn->trang_thai_xu_ly === 'da_giai_quyet')
                        <span class="badge bg-success">Đã duyệt</span>
                      @else
                        <span class="badge bg-danger">Bị từ chối</span>
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
  </div>

  @if(Auth::user()->role === 'ban_can_su')
    <div class="card-premium p-4 mt-4">
      <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-people-fill me-2 text-primary"></i>Danh sách sinh viên trong lớp bổ sung minh chứng (Chờ lớp duyệt)</h4>
      
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Sinh viên</th>
              <th>Nội dung / Hoạt động</th>
              <th>Minh chứng</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-end">Hành động</th>
            </tr>
          </thead>
          <tbody>
            @if($classReviews->isEmpty())
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">Không có yêu cầu bổ sung nào từ các thành viên trong lớp cần duyệt.</td>
              </tr>
            @else
              @foreach($classReviews as $cr)
                <tr>
                  <td>
                    <strong>{{ $cr->sinhVien->ho_ten }}</strong>
                    <div class="small text-muted">MSSV: {{ $cr->sinhVien->ma_sv }}</div>
                  </td>
                  <td>
                    <div class="text-dark">{{ $cr->ly_do }}</div>
                    @if($cr->hoatDong)
                      <div class="small text-muted">Liên quan: {{ $cr->hoatDong->ten_hoat_dong }}</div>
                    @endif
                  </td>
                  <td>
                    @if($cr->minhChung)
                      <a href="{{ asset($cr->minhChung->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Xem file</a>
                    @else
                      <span class="text-muted small">Không đính kèm</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-success fw-bold me-1" onclick="resolveSupplement({{ $cr->id }}, 'da_giai_quyet')"><i class="bi bi-check-circle"></i> Duyệt</button>
                    <button class="btn btn-sm btn-danger fw-bold" onclick="resolveSupplement({{ $cr->id }}, 'tu_choi')"><i class="bi bi-x-circle"></i> Từ chối</button>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection

@section('scripts')
@if(Auth::user()->role === 'ban_can_su')
<script>
  function resolveSupplement(id, status) {
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
          }
        }
      });
    }
  }
</script>
@endif
@endsection