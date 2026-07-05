@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="card-premium p-4">
    <h3 class="fw-bold text-dark mb-4"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Danh sách bảng điểm rèn luyện</h3>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>MSSV</th>
            <th>Sinh viên</th>
            <th>Lớp</th>
            <th class="text-center">Điểm rèn luyện (80)</th>
            <th class="text-center">Điểm học tập (20)</th>
            <th class="text-center">Điểm tổng hợp</th>
            <th class="text-center">Xếp loại</th>
          </tr>
        </thead>
        <tbody>
          @if($diemRenLuyens->isEmpty())
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">Chưa có kết quả điểm rèn luyện nào được tổng hợp.</td>
            </tr>
          @else
            @foreach($diemRenLuyens as $drl)
              <tr>
                <td>{{ $drl->sinhVien->ma_sv }}</td>
                <td><strong>{{ $drl->sinhVien->ho_ten }}</strong></td>
                <td>{{ $drl->sinhVien->lop->ten_lop }}</td>
                <td class="text-center">{{ $drl->tong_diem_tieu_chi }}</td>
                <td class="text-center">{{ number_format($drl->diem_hoc_tap_quy_doi, 2) }}</td>
                <td class="text-center fw-bold text-primary">{{ number_format($drl->diem_tong_hop, 2) }}</td>
                <td class="text-center"><span class="badge bg-success badge-premium">{{ $drl->xep_loai }}</span></td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection