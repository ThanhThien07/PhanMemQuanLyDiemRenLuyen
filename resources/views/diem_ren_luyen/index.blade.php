@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-md-8">
      <h2 class="fw-bold text-dark mb-1"><i class="bi bi-journal-text me-2"></i>Phiếu Điểm Rèn Luyện</h2>
      <p class="text-muted mb-0">Tra cứu bảng điểm chi tiết theo học kỳ của sinh viên.</p>
      @if((Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su') && isset($diemRenLuyen) && $diemRenLuyen->trang_thai_duyet !== 'da_khoa')
        <div class="mt-2">
          <a href="{{ route('diem_ren_luyen.self_evaluation', ['hoc_ky_id' => $selectedHocKy]) }}" class="btn btn-premium btn-sm fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Tự đánh giá rèn luyện học kỳ này
          </a>
        </div>
      @endif
    </div>
    <div class="col-md-4">
      <form action="{{ route('diem_ren_luyen.index') }}" method="GET" class="d-flex gap-2">
        <select name="hoc_ky_id" class="form-select" onchange="this.form.submit()">
          @foreach($hocKys as $hk)
            <option value="{{ $hk->id }}" {{ $selectedHocKy == $hk->id ? 'selected' : '' }}>{{ $hk->ten_hoc_ky }}</option>
          @endforeach
        </select>
      </form>
    </div>
  </div>

  @if(isset($no_profile) && $no_profile)
    <div class="alert alert-warning card-premium p-4">Hồ sơ sinh viên chưa được khởi tạo.</div>
  @elseif(!$diemRenLuyen)
    <div class="card-premium p-5 text-center text-muted">
      <i class="bi bi-clipboard-x fs-1"></i>
      <h5 class="fw-bold mt-3">Chưa có kết quả điểm rèn luyện cho học kỳ này.</h5>
    </div>
    <!-- Stepper Progress Tracker -->
    @php
      $status = $diemRenLuyen->trang_thai_duyet;
      $steps = [
        'tam_tinh' => ['label' => 'Sinh viên', 'desc' => 'Tự đánh giá', 'icon' => 'bi-pencil-square'],
        'cho_bcs_duyet' => ['label' => 'Cán sự lớp', 'desc' => 'Lớp rà soát', 'icon' => 'bi-people-fill'],
        'cho_cvht_duyet' => ['label' => 'Cố vấn học tập', 'desc' => 'GV duyệt điểm', 'icon' => 'bi-person-badge-fill'],
        'cho_ctsv_duyet' => ['label' => 'Phòng CTSV', 'desc' => 'CTSV xét duyệt', 'icon' => 'bi-shield-check'],
        'da_khoa' => ['label' => 'Hoàn thành', 'desc' => 'Đã khóa điểm', 'icon' => 'bi-lock-fill']
      ];
      
      $statusKeys = array_keys($steps);
      $activeIndex = array_search($status, $statusKeys);
      if ($activeIndex === false) { $activeIndex = 0; }
    @endphp
    
    <div class="card-premium p-4 mb-4 bg-white no-print">
      <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-activity text-primary me-2"></i>Tiến trình duyệt hồ sơ điểm rèn luyện</h6>
      <div class="row position-relative py-3">
        <!-- Progress Bar Background Line -->
        <div class="position-absolute top-50 start-0 translate-middle-y w-100 d-none d-md-block" style="height: 4px; background-color: #e9ecef; z-index: 1;">
          <div class="h-100 bg-primary" style="width: {{ ($activeIndex / (count($steps) - 1)) * 100 }}%; transition: width 0.5s ease;"></div>
        </div>
        
        @foreach($steps as $key => $step)
          @php
            $index = array_search($key, $statusKeys);
            $isCompleted = $index < $activeIndex;
            $isActive = $index === $activeIndex;
            $stepColor = $isCompleted ? 'bg-success text-white' : ($isActive ? 'bg-primary text-white' : 'bg-light text-secondary border');
            $labelColor = $isActive ? 'text-primary fw-bold' : ($isCompleted ? 'text-success' : 'text-muted');
          @endphp
          <div class="col-md col-12 text-center mb-3 mb-md-0 position-relative" style="z-index: 2;">
            <div class="d-flex flex-md-column align-items-center justify-content-start justify-content-md-center">
              <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm {{ $stepColor }} mb-0 mb-md-2" style="width: 45px; height: 45px; font-size: 1.2rem;">
                <i class="bi {{ $step['icon'] }}"></i>
              </div>
              <div class="text-start text-md-center ms-3 ms-md-0 mt-0 mt-md-2">
                <div class="small fw-bold {{ $labelColor }}">{{ $step['label'] }}</div>
                <div class="text-muted" style="font-size: 11px;">{{ $step['desc'] }}</div>
                @if($isActive)
                  <span class="badge bg-warning text-dark py-1 px-2 mt-1" style="font-size: 10px;">Hiện tại</span>
                @elseif($isCompleted)
                  <span class="badge bg-success py-1 px-2 mt-1 text-white" style="font-size: 10px;"><i class="bi bi-check-circle-fill me-1"></i>Xong</span>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <!-- Summary score sheet card -->
    <div class="card-premium p-4 mb-4 bg-white">
      <div class="row align-items-center text-center text-md-start">
        <div class="col-md-4 border-end-md py-2">
          <span class="text-muted small text-uppercase fw-bold">Sinh viên</span>
          <h4 class="fw-bold text-dark mt-1">{{ $sinhVien->ho_ten }}</h4>
          <span class="small text-muted">Lớp (Học kỳ này): <strong>{{ $diemRenLuyen->lop ? $diemRenLuyen->lop->ten_lop : $sinhVien->lop->ten_lop }}</strong> | Mã SV: {{ $sinhVien->ma_sv }}</span>
        </div>
        <div class="col-md-4 border-end-md py-2">
          <span class="text-muted small text-uppercase fw-bold">Điểm rèn luyện + Học tập</span>
          <h2 class="fw-bold mt-1 text-primary">{{ number_format($diemRenLuyen->diem_tong_hop, 2) }} <span class="fs-5 text-muted">/ 100</span></h2>
          <span class="badge bg-primary badge-premium">Xếp loại: {{ $diemRenLuyen->xep_loai }}</span>
        </div>
        <div class="col-md-4 py-2">
          <span class="text-muted small text-uppercase fw-bold">Trạng thái phiếu điểm</span>
          <h4 class="fw-bold mt-1">
            @if($diemRenLuyen->trang_thai_duyet === 'da_khoa')
              <span class="text-success"><i class="bi bi-lock-fill"></i> ĐÃ CHÍNH THỨC</span>
            @else
              <span class="text-warning"><i class="bi bi-hourglass-split"></i> ĐANG XÉT DUYỆT</span>
            @endif
          </h4>
          <span class="small text-muted">Giai đoạn: {{ strtoupper($diemRenLuyen->trang_thai_duyet) }}</span>
        </div>
      </div>
    </div>

    <!-- Detailed scores table -->
    <div class="card-premium p-4 mb-4">
      <h5 class="fw-bold text-dark mb-4"><i class="bi bi-list-stars text-primary me-2"></i>Bảng kê chi tiết các hoạt động và điểm cộng</h5>
      
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>Hoạt động / Minh chứng rèn luyện</th>
              <th>Tiêu chí</th>
              <th class="text-center">Điểm rèn luyện</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="3" class="bg-light fw-bold text-dark py-2">1. Hoạt động rèn luyện đã điểm danh có mặt</td>
            </tr>
            @if($activities->isEmpty())
              <tr>
                <td colspan="3" class="text-center text-muted py-2">Chưa ghi nhận hoạt động điểm danh nào.</td>
              </tr>
            @else
              @foreach($activities as $act)
                <tr>
                  <td class="ps-4">{{ $act->ten_hoat_dong }}</td>
                  <td>[{{ $act->tieuChi->ma_tieu_chi }}] {{ Str::limit($act->tieuChi->ten_tieu_chi, 50) }}</td>
                  <td class="text-center text-success fw-bold">+{{ $act->diem_du_kien }}</td>
                </tr>
              @endforeach
            @endif

            <tr>
              <td colspan="3" class="bg-light fw-bold text-dark py-2">2. Minh chứng cá nhân / ngoài trường đã duyệt</td>
            </tr>
            @if($evidences->isEmpty())
              <tr>
                <td colspan="3" class="text-center text-muted py-2">Không có minh chứng nào được duyệt.</td>
              </tr>
            @else
              @foreach($evidences as $ev)
                <tr>
                  <td class="ps-4">
                    {{ $ev->hoatDong ? $ev->hoatDong->ten_hoat_dong : $ev->ten_hoat_dong_ngoai }}
                    <span class="badge bg-secondary ms-1">Minh chứng</span>
                  </td>
                  <td>[{{ $ev->tieuChi->ma_tieu_chi }}] {{ Str::limit($ev->tieuChi->ten_tieu_chi, 50) }}</td>
                  <td class="text-center text-success fw-bold">+{{ $ev->diem_de_xuat }}</td>
                </tr>
              @endforeach
            @endif

            <tr>
              <td colspan="3" class="bg-light fw-bold text-dark py-2">3. Điểm trừ kỷ luật</td>
            </tr>
            @if($disciplines->isEmpty())
              <tr>
                <td colspan="3" class="text-center text-muted py-2">Không có kỷ luật nào trong học kỳ này.</td>
              </tr>
            @else
              @foreach($disciplines as $ds)
                <tr>
                  <td class="ps-4 text-danger">{{ $ds->noi_dung }}</td>
                  <td>[{{ $ds->tieuChi->ma_tieu_chi }}]</td>
                  <td class="text-center text-danger fw-bold">-{{ $ds->diem_tru }}</td>
                </tr>
              @endforeach
            @endif

            <!-- Totals row -->
            <tr class="table-warning fw-bold text-dark">
              <td>TỔNG ĐIỂM RÈN LUYỆN (Áp trần tối đa 80 điểm)</td>
              <td>Tất cả các tiêu chí</td>
              <td class="text-center text-primary fs-5">{{ $diemRenLuyen->tong_diem_tieu_chi }}</td>
            </tr>
            
            <tr class="table-info fw-bold text-dark">
              <td>ĐIỂM HỌC TẬP QUY ĐỔI (Tối đa 20 điểm)</td>
              <td>Kết quả Phòng Đào tạo cung cấp</td>
              <td class="text-center text-primary fs-5">{{ number_format($diemRenLuyen->diem_hoc_tap_quy_doi, 2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection