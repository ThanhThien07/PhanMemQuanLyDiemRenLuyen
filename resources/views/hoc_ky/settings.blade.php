@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <!-- Configuration Form -->
    <div class="col-lg-5">
      <div class="card-premium p-4 mb-4">
        <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-gear-fill me-2 text-primary"></i>Cấu hình Học kỳ & Tỷ lệ điểm</h4>

        <form action="{{ route('hoc_ky.settings.update') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Chọn học kỳ cấu hình</label>
            <select name="hoc_ky_id" class="form-select" id="hoc_ky_id" required>
              <option value="">-- Chọn học kỳ --</option>
              @foreach($hocKys as $hk)
                <option value="{{ $hk->id }}" 
                        data-status="{{ $hk->trang_thai }}"
                        data-renluyen="{{ $hk->ti_le_ren_luyen }}"
                        data-hoctap="{{ $hk->ti_le_hoc_tap }}">
                  {{ $hk->ten_hoc_ky }} ({{ $hk->ma_hoc_ky }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Trạng thái hoạt động</label>
            <select name="trang_thai" id="trang_thai" class="form-select" required>
              <option value="dang_dien_ra">Đang diễn ra (Active)</option>
              <option value="da_khoa">Đã khóa điểm (Locked)</option>
            </select>
            <div class="form-text text-muted">Lưu ý: Chỉ có duy nhất một học kỳ có thể ở trạng thái "Đang diễn ra" tại một thời điểm.</div>
          </div>

          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label text-dark fw-semibold">Tỷ lệ điểm rèn luyện (%)</label>
              <input type="number" step="0.01" name="ti_le_ren_luyen" id="ti_le_ren_luyen" class="form-control" value="80" required min="0" max="100">
            </div>
            <div class="col-md-6">
              <label class="form-label text-dark fw-semibold">Tỷ lệ điểm học tập (%)</label>
              <input type="number" step="0.01" name="ti_le_hoc_tap" id="ti_le_hoc_tap" class="form-control" value="20" required min="0" max="100">
            </div>
            <div class="col-12 mt-2">
              <div class="form-text text-muted" id="ratioHelper">Tổng tỷ lệ điểm rèn luyện và điểm học tập bắt buộc phải bằng 100%.</div>
            </div>
          </div>

          <button type="submit" class="btn btn-premium w-100 py-2.5">Cập nhật cấu hình</button>
        </form>
      </div>
    </div>

    <!-- Current Semesters List -->
    <div class="col-lg-7">
      <div class="card-premium p-4">
        <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-calendar-range me-2 text-success"></i>Danh sách học kỳ hiện tại</h4>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Học kỳ</th>
                <th class="text-center">Tỷ lệ rèn luyện</th>
                <th class="text-center">Tỷ lệ học tập</th>
                <th class="text-center">Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              @foreach($hocKys as $hk)
                <tr class="semester-row" style="cursor: pointer;" onclick="selectSemester({{ $hk->id }})">
                  <td>
                    <strong>{{ $hk->ten_hoc_ky }}</strong>
                    <div class="small text-muted">Mã: {{ $hk->ma_hoc_ky }}</div>
                  </td>
                  <td class="text-center fw-bold text-primary">{{ number_format($hk->ti_le_ren_luyen, 0) }}%</td>
                  <td class="text-center fw-bold text-success">{{ number_format($hk->ti_le_hoc_tap, 0) }}%</td>
                  <td class="text-center">
                    @if($hk->trang_thai === 'dang_dien_ra')
                      <span class="badge bg-success badge-premium"><i class="bi bi-play-circle-fill me-1"></i> Đang diễn ra</span>
                    @else
                      <span class="badge bg-secondary badge-premium"><i class="bi bi-lock-fill me-1"></i> Đã khóa điểm</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function selectSemester(id) {
    $('#hoc_ky_id').val(id).trigger('change');
  }

  $('#hoc_ky_id').on('change', function() {
    var selected = $(this).find('option:selected');
    if (selected.val()) {
      $('#trang_thai').val(selected.data('status'));
      // Parse float to remove trailing zeros
      $('#ti_le_ren_luyen').val(parseFloat(selected.data('renluyen')));
      $('#ti_le_hoc_tap').val(parseFloat(selected.data('hoctap')));
    }
  });

  // Basic client-side check
  $('form').on('submit', function(e) {
    var rl = parseFloat($('#ti_le_ren_luyen').val()) || 0;
    var ht = parseFloat($('#ti_le_hoc_tap').val()) || 0;
    if (rl + ht !== 100) {
      e.preventDefault();
      alert('Tổng tỷ lệ điểm rèn luyện và học tập phải đúng 100%. Hiện tại: ' + (rl + ht) + '%');
    }
  });
</script>
@endsection
