@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <!-- Page Header -->
  <div class="row mb-4 align-items-center">
    <div class="col-md-7">
      <h2 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Báo Cáo & Thống Kê</h2>
      <p class="text-muted mb-0">Hệ thống phân tích, tra cứu nâng cao và xuất dữ liệu điểm rèn luyện.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
      <a href="{{ route('diem_ren_luyen.export', request()->all()) }}" class="btn btn-premium px-4"><i class="bi bi-file-earmark-excel me-2"></i>Xuất Excel/CSV</a>
    </div>
  </div>

  <!-- Summary Cards -->
  @if($reportType !== 'stage_summary')
  <div class="row g-4 mb-4">
    <div class="col-md-3">
      <div class="card-premium p-4 text-center">
        <span class="text-muted small text-uppercase fw-bold">Tổng số kết quả</span>
        <h2 class="fw-bold text-primary mt-1 mb-0">{{ $summary['total'] }}</h2>
        <span class="small text-muted">Sinh viên phù hợp</span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-premium p-4 text-center">
        <span class="text-muted small text-uppercase fw-bold">Điểm trung bình</span>
        <h2 class="fw-bold text-success mt-1 mb-0">{{ number_format($summary['avg_score'], 2) }}</h2>
        <span class="small text-muted">Thang điểm 100</span>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card-premium p-4">
        <span class="text-muted small text-uppercase fw-bold d-block mb-2">Phân loại học lực / rèn luyện</span>
        <div class="row text-center">
          <div class="col-2.4 col border-end">
            <h5 class="fw-bold text-danger mb-0">{{ $summary['xuat_sac'] }}</h5>
            <span class="small text-muted">X.Sắc</span>
          </div>
          <div class="col-2.4 col border-end">
            <h5 class="fw-bold text-warning mb-0">{{ $summary['tot'] }}</h5>
            <span class="small text-muted">Tốt</span>
          </div>
          <div class="col-2.4 col border-end">
            <h5 class="fw-bold text-primary mb-0">{{ $summary['kha'] }}</h5>
            <span class="small text-muted">Khá</span>
          </div>
          <div class="col-2.4 col border-end">
            <h5 class="fw-bold text-info mb-0">{{ $summary['trung_binh'] }}</h5>
            <span class="small text-muted">T.Bình</span>
          </div>
          <div class="col-2.4 col">
            <h5 class="fw-bold text-secondary mb-0">{{ $summary['yeu'] }}</h5>
            <span class="small text-muted">Yếu</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Search and Filter Panel -->
  <div class="card-premium p-4 mb-4 bg-white">
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-funnel me-2"></i>Bộ lọc báo cáo</h5>
    <form action="{{ route('diem_ren_luyen.report') }}" method="GET">
      <div class="row g-3">
        <!-- Text fields -->
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Mã sinh viên</label>
          <input type="text" name="mssv" class="form-control" placeholder="Tìm theo MSSV..." value="{{ request('mssv') }}">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Họ tên</label>
          <input type="text" name="ho_ten" class="form-control" placeholder="Tìm theo tên..." value="{{ request('ho_ten') }}">
        </div>

        <!-- Academic fields -->
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Học kỳ</label>
          <select name="hoc_ky_id" class="form-select">
            @foreach($hocKys as $hk)
              <option value="{{ $hk->id }}" {{ $selectedHocKy == $hk->id ? 'selected' : '' }}>{{ $hk->ten_hoc_ky }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Lớp</label>
          <select name="lop_id" class="form-select">
            <option value="">-- Tất cả --</option>
            @foreach($lops as $lop)
              <option value="{{ $lop->id }}" {{ request('lop_id') == $lop->id ? 'selected' : '' }}>{{ $lop->ten_lop }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Khoa</label>
          <select name="khoa_id" class="form-select">
            <option value="">-- Tất cả --</option>
            @foreach($khoas as $k)
              <option value="{{ $k->id }}" {{ request('khoa_id') == $k->id ? 'selected' : '' }}>{{ $k->ten_khoa }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Khóa học</label>
          <input type="text" name="khoa_hoc" class="form-control" placeholder="K19, K20..." value="{{ request('khoa_hoc') }}">
        </div>

        <!-- Other filters -->
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Hệ đào tạo</label>
          <select name="he_dao_tao_id" class="form-select">
            <option value="">-- Tất cả --</option>
            @foreach($heDaoTaos as $h)
              <option value="{{ $h->id }}" {{ request('he_dao_tao_id') == $h->id ? 'selected' : '' }}>{{ $h->ten_he }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Xếp loại</label>
          <select name="xep_loai" class="form-select">
            <option value="">-- Tất cả --</option>
            <option value="Xuất sắc" {{ request('xep_loai') === 'Xuất sắc' ? 'selected' : '' }}>Xuất sắc</option>
            <option value="Tốt" {{ request('xep_loai') === 'Tốt' ? 'selected' : '' }}>Tốt</option>
            <option value="Khá" {{ request('xep_loai') === 'Khá' ? 'selected' : '' }}>Khá</option>
            <option value="Trung bình" {{ request('xep_loai') === 'Trung bình' ? 'selected' : '' }}>Trung bình</option>
            <option value="Yếu" {{ request('xep_loai') === 'Yếu' ? 'selected' : '' }}>Yếu / Kém</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Nghiệp vụ đặc thù</label>
          <select name="report_type" class="form-select text-primary fw-semibold">
            <option value="all" {{ $reportType === 'all' ? 'selected' : '' }}>Tất cả sinh viên</option>
            <option value="insufficient" {{ $reportType === 'insufficient' ? 'selected' : '' }}>Sinh viên cần lưu ý (Yếu/Kém hoặc Minh chứng bị từ chối)</option>
            <option value="scholarship" {{ $reportType === 'scholarship' ? 'selected' : '' }}>Đủ điều kiện xét học bổng (Khá/Giỏi & Không kỷ luật)</option>
            <option value="stage_summary" {{ $reportType === 'stage_summary' ? 'selected' : '' }}>Tiến độ xét duyệt cấp lớp</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"><i class="bi bi-search me-1"></i>Tìm kiếm</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Content Section -->
  <div class="card-premium p-4">
    @if($reportType === 'stage_summary')
      <h5 class="fw-bold text-dark mb-4"><i class="bi bi-pie-chart text-success me-2"></i>Báo cáo tiến độ xét duyệt cấp lớp</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle border">
          <thead class="table-light">
            <tr>
              <th>Lớp</th>
              <th>Cố vấn học tập</th>
              <th class="text-center">Sĩ số lớp</th>
              <th class="text-center">Tạm tính (Lớp chưa duyệt)</th>
              <th class="text-center">Chờ Cố vấn duyệt</th>
              <th class="text-center">Chờ CTSV phê duyệt</th>
              <th class="text-center">Đã khóa</th>
            </tr>
          </thead>
          <tbody>
            @foreach($classStages as $cs)
              <tr>
                <td><strong>{{ $cs['class_name'] }}</strong></td>
                <td>{{ $cs['advisor_name'] }}</td>
                <td class="text-center fw-bold">{{ $cs['total_students'] }}</td>
                <td class="text-center"><span class="badge bg-secondary">{{ $cs['tam_tinh'] }}</span></td>
                <td class="text-center"><span class="badge bg-primary">{{ $cs['cho_cvht'] }}</span></td>
                <td class="text-center"><span class="badge bg-warning text-dark">{{ $cs['cho_ctsv'] }}</span></td>
                <td class="text-center"><span class="badge bg-success">{{ $cs['da_khoa'] }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <h5 class="fw-bold text-dark mb-4"><i class="bi bi-list-columns text-primary me-2"></i>Danh sách kết quả chi tiết</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle border">
          <thead class="table-light">
            <tr>
              <th>MSSV</th>
              <th>Họ và Tên</th>
              <th>Lớp</th>
              <th>Khoa</th>
              <th class="text-center">Điểm rèn luyện</th>
              <th class="text-center">Điểm học tập</th>
              <th class="text-center">Tổng điểm</th>
              <th class="text-center">Xếp loại</th>
              <th class="text-center">Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @if($records->isEmpty())
              <tr>
                <td colspan="9" class="text-center py-5 text-muted">Không tìm thấy sinh viên nào khớp với bộ lọc.</td>
              </tr>
            @else
              @foreach($records as $r)
                <tr>
                  <td><strong>{{ $r->sinhVien->ma_sv }}</strong></td>
                  <td>{{ $r->sinhVien->ho_ten }}</td>
                  <td>{{ $r->sinhVien->lop->ten_lop }}</td>
                  <td>{{ $r->sinhVien->lop->nganh->khoa->ten_khoa ?? 'N/A' }}</td>
                  <td class="text-center fw-semibold">{{ $r->tong_diem_tieu_chi }}</td>
                  <td class="text-center fw-semibold text-muted">{{ number_format($r->diem_hoc_tap_quy_doi, 2) }}</td>
                  <td class="text-center fw-bold text-primary">{{ number_format($r->diem_tong_hop, 2) }}</td>
                  <td class="text-center">
                    @if($r->xep_loai === 'Xuất sắc')
                      <span class="badge bg-danger badge-premium">Xuất sắc</span>
                    @elseif($r->xep_loai === 'Tốt')
                      <span class="badge bg-warning text-dark badge-premium">Tốt</span>
                    @elseif($r->xep_loai === 'Khá')
                      <span class="badge bg-primary badge-premium">Khá</span>
                    @elseif($r->xep_loai === 'Trung bình')
                      <span class="badge bg-info text-dark badge-premium">Trung bình</span>
                    @else
                      <span class="badge bg-secondary badge-premium">Yếu</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($r->trang_thai_duyet === 'tam_tinh')
                      <span class="badge bg-secondary">Tạm tính</span>
                    @elseif($r->trang_thai_duyet === 'cho_cvht_duyet')
                      <span class="badge bg-primary">Chờ CVHT</span>
                    @elseif($r->trang_thai_duyet === 'cho_ctsv_duyet')
                      <span class="badge bg-warning text-dark">Chờ CTSV</span>
                    @else
                      <span class="badge bg-success"><i class="bi bi-lock-fill"></i> Đã khóa</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
