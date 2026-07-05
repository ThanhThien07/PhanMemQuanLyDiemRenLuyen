@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col">
      <h2 class="fw-bold text-dark mb-1"><i class="bi bi-person-badge-fill me-2 text-primary"></i>Phân Công Cố Vấn Học Tập</h2>
      <p class="text-muted mb-0">Quản lý và chỉ định Cố vấn học tập (CVHT) phụ trách các lớp học theo từng học kỳ.</p>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success card-premium mb-4">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
  @endif

  <div class="row">
    <!-- Assignment Creation Form -->
    <div class="col-lg-4">
      <div class="card-premium p-4 mb-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle me-2"></i>Thêm phân công mới</h5>
        
        <form action="{{ route('xet_duyet.save_phan_cong') }}" method="POST">
          @csrf
          
          <div class="mb-3">
            <label for="hoc_ky_id" class="form-label small fw-bold text-secondary">Học kỳ</label>
            <select name="hoc_ky_id" id="hoc_ky_id" class="form-select" required>
              @foreach($semesters as $sem)
                <option value="{{ $sem->id }}">{{ $sem->ten_hoc_ky }} ({{ $sem->trang_thai === 'dang_dien_ra' ? 'Đang diễn ra' : 'Đã khóa' }})</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="lop_id" class="form-label small fw-bold text-secondary">Lớp học</label>
            <select name="lop_id" id="lop_id" class="form-select" required>
              <option value="" disabled selected>-- Chọn lớp học --</option>
              @foreach($classes as $lop)
                <option value="{{ $lop->id }}">{{ $lop->ten_lop }} ({{ $lop->nganh->ten_nganh }})</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="user_id" class="form-label small fw-bold text-secondary">Cố vấn học tập (CVHT)</label>
            <select name="user_id" id="user_id" class="form-select" required>
              <option value="" disabled selected>-- Chọn cố vấn --</option>
              @foreach($advisors as $advisor)
                <option value="{{ $advisor->id }}">{{ $advisor->name }} ({{ $advisor->email }})</option>
              @endforeach
            </select>
          </div>

          <button type="submit" class="btn btn-premium w-100 fw-bold mt-2">
            <i class="bi bi-check2-circle me-2"></i>Xác nhận phân công
          </button>
        </form>
      </div>

      <!-- Quick Help Card -->
      <div class="card-premium p-4">
        <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-info-circle me-2"></i>Quy định hệ thống</h6>
        <p class="small text-secondary mb-2">
          - Mỗi lớp học chỉ được phân công tối đa <strong>1 Cố vấn học tập</strong> phụ trách trong cùng một học kỳ.
        </p>
        <p class="small text-secondary mb-0">
          - Nếu chọn lớp học đã có cố vấn phụ trách trước đó, hệ thống sẽ <strong>cập nhật ghi đè</strong> cố vấn mới cho học kỳ đó mà không làm mất lịch sử các học kỳ cũ.
        </p>
      </div>
    </div>

    <!-- Assignments List Table -->
    <div class="col-lg-8">
      <div class="card-premium p-4">
        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-list-task me-2 text-primary"></i>Danh sách phân công hiện tại</h5>
        
        @if($assignments->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="bi bi-clipboard-x fs-1"></i>
            <h6 class="fw-bold mt-3">Chưa có phân công cố vấn học tập nào.</h6>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr class="table-light text-secondary small">
                  <th>Học kỳ</th>
                  <th>Lớp học</th>
                  <th>Cố vấn phụ trách</th>
                  <th>Email</th>
                  <th class="text-end">Hành động</th>
                </tr>
              </thead>
              <tbody>
                @foreach($assignments as $assign)
                  <tr>
                    <td>
                      <span class="badge bg-light text-dark border fw-medium">{{ $assign->hocKy->ten_hoc_ky }}</span>
                    </td>
                    <td>
                      <strong class="text-dark">{{ $assign->lop->ten_lop }}</strong>
                      <div class="small text-muted">{{ $assign->lop->nganh->ten_nganh }}</div>
                    </td>
                    <td>
                      <span class="fw-medium text-dark"><i class="bi bi-person-fill text-secondary me-1"></i>{{ $assign->user->name }}</span>
                    </td>
                    <td>
                      <span class="small font-monospace">{{ $assign->user->email }}</span>
                    </td>
                    <td class="text-end">
                      <form action="{{ route('xet_duyet.delete_phan_cong', $assign->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phân công này?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="bi bi-trash-fill"></i> Xóa
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
