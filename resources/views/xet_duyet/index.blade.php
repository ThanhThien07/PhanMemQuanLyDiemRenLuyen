@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="card-premium p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold text-dark mb-0"><i class="bi bi-check-square me-2 text-primary"></i>Quy trình xét duyệt điểm rèn luyện</h3>
      @if(Auth::user()->role === 'admin')
        <a href="{{ route('xet_duyet.phan_cong') }}" class="btn btn-premium btn-sm fw-bold">
          <i class="bi bi-person-badge-fill me-2"></i>Phân công Cố vấn học tập
        </a>
      @endif
    </div>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h5 class="text-secondary mb-0">Học kỳ hiện tại: <strong>{{ $hocKy->ten_hoc_ky }}</strong></h5>
      @if(!$diemRenLuyens->isEmpty())
        <button type="submit" form="bulk-approve-form" class="btn btn-success fw-bold no-print" id="bulk-approve-btn" disabled onclick="return confirm('Bạn có chắc chắn muốn phê duyệt hàng loạt các phiếu điểm đã chọn?')">
          <i class="bi bi-check-all me-1"></i> Duyệt nhanh hàng loạt đã chọn
        </button>
      @endif
    </div>

    <form action="{{ route('xet_duyet.bulk_approve') }}" method="POST" id="bulk-approve-form">
      @csrf
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 4%;" class="text-center no-print">
                <input type="checkbox" id="select-all" class="form-check-input">
              </th>
              <th>Sinh viên</th>
              <th class="text-center">Điểm tiêu chí (Trần 80)</th>
              <th class="text-center">Điểm học tập (20)</th>
              <th class="text-center">Điểm tổng hợp</th>
              <th class="text-center">Xếp loại</th>
              <th class="text-center">Trạng thái xét duyệt</th>
              <th class="text-end" style="width: 25%;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            @if($diemRenLuyens->isEmpty())
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">Chưa có kết quả điểm rèn luyện nào được tổng hợp.</td>
              </tr>
            @else
              @php
                $user = Auth::user();
              @endphp
              @foreach($diemRenLuyens as $drl)
                @php
                  $canCheck = false;
                  if ($user->role === 'ban_can_su' && ($drl->trang_thai_duyet === 'tam_tinh' || $drl->trang_thai_duyet === 'cho_bcs_duyet')) {
                      $canCheck = true;
                  } elseif ($user->role === 'co_van' && $drl->trang_thai_duyet === 'cho_cvht_duyet') {
                      $canCheck = true;
                  } elseif ($user->role === 'admin' && $drl->trang_thai_duyet !== 'da_khoa') {
                      $canCheck = true;
                  }
                @endphp
                <tr>
                  <td class="text-center no-print">
                    <input type="checkbox" name="ids[]" value="{{ $drl->id }}" class="form-check-input row-checkbox" {{ !$canCheck ? 'disabled' : '' }}>
                  </td>
                  <td>
                    <strong>{{ $drl->sinhVien->ho_ten }}</strong>
                    <div class="small text-muted">MSSV: {{ $drl->sinhVien->ma_sv }} | Lớp (Học kỳ này): <strong>{{ $drl->lop ? $drl->lop->ten_lop : $drl->sinhVien->lop->ten_lop }}</strong></div>
                  </td>
                  <td class="text-center fw-bold text-dark">{{ $drl->tong_diem_tieu_chi }}</td>
                  <td class="text-center fw-bold text-muted">{{ number_format($drl->diem_hoc_tap_quy_doi, 2) }}</td>
                  <td class="text-center fw-bold text-primary">{{ number_format($drl->diem_tong_hop, 2) }}</td>
                  <td class="text-center">
                    <span class="badge bg-success badge-premium">{{ $drl->xep_loai }}</span>
                  </td>
                  <td class="text-center">
                    @if($drl->trang_thai_duyet === 'tam_tinh')
                      <span class="badge bg-light text-dark border">Tạm tính</span>
                    @elseif($drl->trang_thai_duyet === 'cho_bcs_duyet')
                      <span class="badge bg-info text-dark">Lớp đang rà soát</span>
                    @elseif($drl->trang_thai_duyet === 'cho_cvht_duyet')
                      <span class="badge bg-warning text-dark">Chờ cố vấn duyệt</span>
                    @elseif($drl->trang_thai_duyet === 'cho_ctsv_duyet')
                      <span class="badge bg-danger">Chờ CTSV phê duyệt</span>
                    @elseif($drl->trang_thai_duyet === 'da_khoa')
                      <span class="badge bg-success"><i class="bi bi-lock-fill"></i> Đã khóa điểm</span>
                    @endif
                  </td>
                  <td class="text-end">
                    @if(Auth::user()->role === 'ban_can_su' && ($drl->trang_thai_duyet === 'tam_tinh' || $drl->trang_thai_duyet === 'cho_bcs_duyet'))
                      <a href="{{ route('xet_duyet.review_evaluation', $drl->id) }}" class="btn btn-sm btn-outline-primary fw-bold me-1"><i class="bi bi-list-check"></i> Chi tiết</a>
                      <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="updateStage({{ $drl->id }}, 'cho_cvht_duyet')">Duyệt & Gửi Cố vấn</button>
                    @elseif(Auth::user()->role === 'co_van' && $drl->trang_thai_duyet === 'cho_cvht_duyet')
                      <a href="{{ route('xet_duyet.review_evaluation', $drl->id) }}" class="btn btn-sm btn-outline-primary fw-bold me-1"><i class="bi bi-list-check"></i> Chi tiết</a>
                      <button type="button" class="btn btn-sm btn-warning fw-bold" onclick="updateStage({{ $drl->id }}, 'cho_ctsv_duyet')">Duyệt & Gửi CTSV</button>
                      <button type="button" class="btn btn-sm btn-outline-danger fw-bold ms-1" onclick="updateStage({{ $drl->id }}, 'tam_tinh')">Trả lại Lớp</button>
                    @elseif(Auth::user()->role === 'admin' && $drl->trang_thai_duyet === 'cho_ctsv_duyet')
                      <a href="{{ route('xet_duyet.review_evaluation', $drl->id) }}" class="btn btn-sm btn-outline-primary fw-bold me-1"><i class="bi bi-list-check"></i> Chi tiết</a>
                      <button type="button" class="btn btn-sm btn-success fw-bold" onclick="updateStage({{ $drl->id }}, 'da_khoa')">Phê duyệt & Khóa</button>
                      <button type="button" class="btn btn-sm btn-outline-danger fw-bold ms-1" onclick="updateStage({{ $drl->id }}, 'cho_cvht_duyet')">Trả lại Cố vấn</button>
                    @else
                      <a href="{{ route('xet_duyet.review_evaluation', $drl->id) }}" class="btn btn-sm btn-outline-secondary fw-bold me-1"><i class="bi bi-eye"></i> Xem chi tiết</a>
                      @if(Auth::user()->role === 'admin' && $drl->trang_thai_duyet === 'da_khoa')
                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="unlockRecord({{ $drl->id }})">
                          <i class="bi bi-unlock-fill"></i> Mở khóa
                        </button>
                      @endif
                    @endif
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>
    </form>
  </div>
</div>

<!-- hidden forms for AJAX actions -->
<form id="unlock-form" action="" method="POST" style="display: none;">
  @csrf
</form>
@endsection

@section('scripts')
<script>
  function updateStage(id, status) {
    if (confirm('Xác nhận duyệt chuyển giai đoạn xét duyệt này?')) {
      $.ajax({
        url: '/xet-duyet/update/' + id,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          trang_thai: status
        },
        success: function(response) {
          if (response.success) {
            location.reload();
          } else {
            alert(response.message || 'Có lỗi xảy ra khi cập nhật trạng thái.');
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

  function unlockRecord(id) {
    if (confirm('Bạn có chắc muốn mở khóa phiếu điểm này cho sinh viên và cố vấn sửa lại từ đầu?')) {
      let form = document.getElementById('unlock-form');
      form.action = '/xet-duyet/unlock/' + id;
      form.submit();
    }
  }

  $(document).ready(function() {
    $('#select-all').on('click', function() {
      $('.row-checkbox:not(:disabled)').prop('checked', this.checked);
      toggleBulkBtn();
    });

    $('.row-checkbox').on('change', function() {
      let total = $('.row-checkbox:not(:disabled)').length;
      let checked = $('.row-checkbox:not(:disabled):checked').length;
      $('#select-all').prop('checked', total === checked && total > 0);
      toggleBulkBtn();
    });

    function toggleBulkBtn() {
      let checkedCount = $('.row-checkbox:not(:disabled):checked').length;
      $('#bulk-approve-btn').prop('disabled', checkedCount === 0);
    }
  });
</script>
@endsection