@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-5">
      <div class="card-premium p-4 mb-4">
        <h4 class="fw-bold mb-4"><i class="bi bi-upload me-2 text-primary"></i>Nộp minh chứng hoạt động</h4>

        <form action="{{ route('minh_chung.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Chọn hoạt động trong trường (nếu có)</label>
            <select name="hoat_dong_id" class="form-select">
              <option value="">-- Hoạt động cá nhân / ngoài trường --</option>
              @foreach($hoatDongs as $hd)
                <option value="{{ $hd->id }}">{{ $hd->ten_hoat_dong }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Hoặc tên hoạt động ngoài trường</label>
            <input type="text" name="ten_hoat_dong_ngoai" class="form-control" placeholder="Hiến máu nhân đạo, Mùa hè xanh...">
          </div>

          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Tiêu chí rèn luyện tích lũy</label>
            <select name="tieu_chi_id" class="form-select">
              <option value="">-- Chọn tiêu chí để tích lũy điểm --</option>
              @foreach($tieuChis as $tc)
                <option value="{{ $tc->id }}">[{{ $tc->ma_tieu_chi }}] {{ $tc->ten_tieu_chi }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Điểm đề xuất cộng</label>
            <input type="number" name="diem_de_xuat" class="form-control" value="2" required>
          </div>

          <div class="mb-4">
            <label class="form-label text-dark fw-semibold">Tải file minh chứng (JPG, PNG, PDF)</label>
            <input type="file" name="file_minh_chung" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-premium w-100 py-2.5">Gửi minh chứng</button>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card-premium p-4">
        <h4 class="fw-bold mb-4"><i class="bi bi-file-earmark-check me-2 text-success"></i>Lịch sử minh chứng đã nộp</h4>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Hoạt động</th>
                <th class="text-center">Điểm đề xuất</th>
                <th class="text-center">Trạng thái</th>
                <th class="text-end">Minh chứng</th>
              </tr>
            </thead>
            <tbody>
              @if($hoSoMinhChungs->isEmpty())
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">Bạn chưa nộp minh chứng nào.</td>
                </tr>
              @else
                @foreach($hoSoMinhChungs as $hs)
                  <tr>
                    <td>
                      @if($hs->hoatDong)
                        <strong>{{ $hs->hoatDong->ten_hoat_dong }}</strong>
                      @else
                        <strong>{{ $hs->ten_hoat_dong_ngoai }}</strong> <span class="badge bg-secondary">Ngoài trường</span>
                      @endif
                      <div class="small text-muted">Tác động tiêu chí: {{ $hs->tieuChi ? $hs->tieuChi->ma_tieu_chi : 'Chưa liên kết' }}</div>
                    </td>
                    <td class="text-center fw-semibold text-primary">+{{ $hs->diem_de_xuat }}</td>
                    <td class="text-center">
                      @if($hs->trang_thai_duyet === 'cho_duyet')
                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                      @elseif($hs->trang_thai_duyet === 'da_duyet')
                        <span class="badge bg-success">Đã duyệt</span>
                      @else
                        <span class="badge bg-danger">Bị từ chối</span>
                        @if($hs->ly_do_tu_choi)
                          <div class="small text-danger mt-1">Lý do: {{ $hs->ly_do_tu_choi }}</div>
                        @endif
                      @endif
                    </td>
                    <td class="text-end">
                      <a href="{{ asset($hs->minhChung->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Xem file</a>
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
</div>
@endsection