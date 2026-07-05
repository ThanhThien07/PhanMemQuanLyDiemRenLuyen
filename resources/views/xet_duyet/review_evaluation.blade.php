@extends('layouts.app')

@section('content')
<div class="container-fluid print-container">
  
  <!-- Printable Official Header (Hidden on Screen) -->
  <div class="print-only w-100 mb-4">
    <div class="d-flex justify-content-between text-center" style="font-family: 'Times New Roman', serif;">
      <div style="width: 45%;">
        <h6 class="fw-bold mb-0 text-uppercase">SỞ GIÁO DỤC VÀ ĐÀO TẠO</h6>
        <h6 class="fw-bold mb-0 text-uppercase">TRƯỜNG ĐẠI HỌC CÔNG NGHỆ</h6>
        <div class="mx-auto my-1" style="width: 80px; border-bottom: 1.5px solid #000;"></div>
      </div>
      <div style="width: 50%;">
        <h6 class="fw-bold mb-0">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</h6>
        <h6 class="fw-bold mb-0" style="font-size: 13px;">Độc lập - Tự do - Hạnh phúc</h6>
        <div class="mx-auto my-1" style="width: 120px; border-bottom: 1.5px solid #000;"></div>
      </div>
    </div>
    <div class="text-center mt-4 mb-3" style="font-family: 'Times New Roman', serif;">
      <h4 class="fw-bold text-uppercase mb-1">PHIẾU ĐÁNH GIÁ CHI TIẾT ĐIỂM RÈN LUYỆN LỚP</h4>
      <h6 class="fw-bold text-uppercase mb-0">HỌC KỲ: {{ $diemRenLuyen->hocKy->ten_hoc_ky }}</h6>
    </div>
    
    <!-- Student Information Block for Print -->
    <div class="row mb-3 print-info-block" style="font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.6;">
      <div class="col-6">
        <div>Họ và tên sinh viên: <strong>{{ $sinhVien->ho_ten }}</strong></div>
        <div>Mã số sinh viên: <strong>{{ $sinhVien->ma_sv }}</strong></div>
      </div>
      <div class="col-6">
        <div>Lớp: <strong>{{ $diemRenLuyen->lop ? $diemRenLuyen->lop->ten_lop : $sinhVien->lop->ten_lop }}</strong></div>
        <div>Hệ đào tạo: <strong>{{ $sinhVien->heDaoTao ? $sinhVien->heDaoTao->ten_he : 'Chính quy' }}</strong></div>
      </div>
    </div>
  </div>

  <!-- Profile & Student Status Header -->
  <div class="card-premium p-4 mb-4 no-print" style="background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 100%); border-left: 5px solid var(--ms-secondary);">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h3 class="fw-bold text-dark mb-1">
          <i class="bi bi-shield-check me-2 text-warning"></i>Đánh Giá Chi Tiết Điểm Rèn Luyện Lớp
        </h3>
        <p class="text-secondary mb-0">
          Sinh viên: <strong>{{ $sinhVien->ho_ten }}</strong> | MSSV: <strong>{{ $sinhVien->ma_sv }}</strong> | Lớp: <strong>{{ $sinhVien->lop->ten_lop }}</strong>
        </p>
        <p class="text-muted small mb-0">
          Học kỳ: <strong>{{ $diemRenLuyen->hocKy->ten_hoc_ky }}</strong> | Trạng thái: 
          @if($diemRenLuyen->trang_thai_duyet === 'tam_tinh')
            <span class="badge bg-light text-dark border">Tạm tính</span>
          @elseif($diemRenLuyen->trang_thai_duyet === 'cho_bcs_duyet')
            <span class="badge bg-info text-dark">Lớp đang rà soát</span>
          @elseif($diemRenLuyen->trang_thai_duyet === 'cho_cvht_duyet')
            <span class="badge bg-warning text-dark">Chờ cố vấn duyệt</span>
          @elseif($diemRenLuyen->trang_thai_duyet === 'cho_ctsv_duyet')
            <span class="badge bg-danger">Chờ CTSV phê duyệt</span>
          @elseif($diemRenLuyen->trang_thai_duyet === 'da_khoa')
            <span class="badge bg-success"><i class="bi bi-lock-fill"></i> Đã khóa điểm</span>
          @endif
        </p>
      </div>
      <div class="col-md-4 text-end">
        <button type="button" onclick="window.print()" class="btn btn-outline-success fw-bold border me-2">
          <i class="bi bi-printer me-2"></i>In phiếu điểm
        </button>
        <div class="p-3 bg-white rounded-3 shadow-sm border d-inline-block text-center me-2 align-middle">
          <div class="small text-muted text-uppercase fw-bold" style="font-size: 10px;">SV Tự Đánh Giá</div>
          <div class="h4 fw-bold text-secondary mb-0">{{ number_format($diemRenLuyen->diem_tong_hop, 1) }}</div>
        </div>
        <div class="p-3 bg-white rounded-3 shadow-sm border d-inline-block text-center align-middle">
          <div class="small text-muted text-uppercase fw-bold" style="font-size: 10px;">Điểm Chấm Hiện Tại</div>
          <div class="h4 fw-bold text-primary mb-0" id="overall-total-display">0.00</div>
          <span class="badge bg-secondary badge-premium mt-1" id="overall-class-display">Yếu</span>
        </div>
      </div>
    </div>
  </div>

  @if($diemRenLuyen->minh_chung_gdrive)
    <div class="alert alert-info border-info d-flex justify-content-between align-items-center mb-4 no-print" style="border-radius:12px;">
      <div>
        <i class="bi bi-google-drive me-2 fs-5 text-primary"></i>
        Liên kết Google Drive minh chứng tổng hợp của sinh viên: 
        <a href="{{ $diemRenLuyen->minh_chung_gdrive }}" target="_blank" class="fw-bold text-decoration-underline">{{ $diemRenLuyen->minh_chung_gdrive }}</a>
      </div>
      <a href="{{ $diemRenLuyen->minh_chung_gdrive }}" target="_blank" class="btn btn-sm btn-primary fw-bold text-white px-3 py-1.5"><i class="bi bi-box-arrow-up-right me-1"></i> Mở Drive</a>
    </div>
  @endif

  <form action="{{ route('xet_duyet.save_review_evaluation', $diemRenLuyen->id) }}" method="POST">
    @csrf
    <div class="row">
      <!-- Main Review Evaluation Form -->
      <div class="col-lg-9 print-full-width">
        <div class="card-premium p-4 mb-4 print-no-border">
          <div class="table-responsive">
            <table class="table table-bordered align-middle print-table" style="border-color: #ccc;">
              <thead class="table-light">
                <tr class="text-center" style="font-size: 13px;">
                  <th style="width: 5%;">STT</th>
                  <th style="width: 35%;" class="text-start">Nội dung đánh giá</th>
                  <th style="width: 8%;">Điểm trần</th>
                  <th style="width: 10%;">SV tự ĐG</th>
                  <th style="width: 15%;" class="no-print">Minh chứng SV</th>
                  <th style="width: 12%;">Chấm điểm</th>
                  <th style="width: 15%;">Ghi chú chấm</th>
                </tr>
              </thead>
              <tbody>
                @foreach($criteria as $secKey => $section)
                  <!-- Section Header Row -->
                  <tr class="table-secondary fw-bold text-dark print-section-header">
                    <td class="text-center">{{ $secKey }}</td>
                    <td colspan="2">
                      {{ $section['name'] }}
                    </td>
                    <td class="text-center font-monospace small print-only">Max {{ $section['max_score'] }}đ</td>
                    <td class="text-center">{{ $section['max_score'] }}</td>
                    <td class="no-print"></td>
                    <td colspan="2" class="text-end text-primary no-print">Tối đa {{ $section['max_score'] }} điểm</td>
                  </tr>

                  @foreach($section['items'] as $itemKey => $item)
                    @php
                      $existing = $existingDetails->get($itemKey);
                      $svScore = $existing ? $existing->diem_sv : 0;
                      $svNote = $existing ? $existing->ghi_chu_sv : '';
                      
                      // Determine reviewer values based on role
                      $reviewScore = 0;
                      $reviewNote = '';
                      if (Auth::user()->role === 'ban_can_su') {
                          $reviewScore = $existing ? $existing->diem_bcs : $svScore;
                          $reviewNote = $existing ? $existing->ghi_chu_bcs : '';
                      } else {
                          // CVHT or Admin
                          $reviewScore = $existing ? $existing->diem_cvht : ($existing ? $existing->diem_bcs : $svScore);
                          $reviewNote = $existing ? $existing->ghi_chu_cvht : '';
                      }
                    @endphp
                    <tr>
                      <td class="text-center font-monospace small">{{ $itemKey }}</td>
                      <td>
                        <span class="fw-medium">{{ $item['name'] }}</span>
                      </td>
                      <td class="text-center fw-bold text-secondary">{{ $item['max_score'] }}</td>
                      <td class="text-center font-monospace">{{ $svScore }}</td>
                      <td class="no-print">
                        @if($svNote)
                          @if(filter_var($svNote, FILTER_VALIDATE_URL))
                            <a href="{{ $svNote }}" target="_blank" class="btn btn-xs btn-outline-info py-0 px-1 me-1" style="font-size: 11px;"><i class="bi bi-box-arrow-up-right"></i> Mở tab</a>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1" style="font-size: 11px;" onclick="previewEvidence('{{ $svNote }}')"><i class="bi bi-eye"></i> Xem nhanh</button>
                          @else
                            <span class="text-muted small" style="display: block; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $svNote }}">{{ $svNote }}</span>
                          @endif
                        @else
                          <span class="text-muted small">-</span>
                        @endif
                      </td>
                      <td>
                        <div class="input-group input-group-sm no-print">
                          <input type="number" 
                                 name="diem_review[{{ $itemKey }}]" 
                                 id="score_{{ $itemKey }}"
                                 class="form-control text-center score-input section-{{ $secKey }}" 
                                 data-section="{{ $secKey }}"
                                 data-max="{{ $item['max_score'] }}"
                                 step="0.5" 
                                 min="0" 
                                 max="{{ $item['max_score'] }}" 
                                 value="{{ $reviewScore }}" 
                                 required>
                        </div>
                        <span class="print-only text-center fw-bold d-block text-dark" id="print_score_{{ $itemKey }}">{{ $reviewScore }}</span>
                      </td>
                      <td>
                        <input type="text" 
                               name="ghi_chu_review[{{ $itemKey }}]" 
                               id="note_{{ $itemKey }}"
                               class="form-control form-control-sm no-print" 
                               placeholder="Lý do điều chỉnh..." 
                               value="{{ $reviewNote }}">
                        <span class="print-only small text-secondary" id="print_note_{{ $itemKey }}">{{ $reviewNote ?: '-' }}</span>
                      </td>
                    </tr>
                  @endforeach
                  <!-- Section Subtotal Row -->
                  <tr class="table-light fw-bold print-section-total">
                    <td></td>
                    <td class="text-end">Cộng tiêu chí {{ $secKey }}:</td>
                    <td class="text-center text-primary" id="section-max-{{ $secKey }}">{{ $section['max_score'] }}</td>
                    <td></td>
                    <td class="no-print"></td>
                    <td class="text-center text-primary font-monospace" id="section-total-{{ $secKey }}">0.00</td>
                    <td><span class="small text-muted no-print" id="section-cap-msg-{{ $secKey }}"></span></td>
                  </tr>
                @endforeach
                
                <!-- Overall Review Score Row -->
                <tr class="fw-bold table-primary text-primary-emphasis border-primary" style="font-size: 15px; border-top: 2px solid #0d6efd !important; background-color: #cfe2ff;">
                  <td class="text-center"></td>
                  <td class="text-end text-dark">ĐIỂM ĐÁNH GIÁ CHUNG HỘI ĐỒNG (Khống chế tối đa 100đ):</td>
                  <td class="text-center text-dark">100</td>
                  <td class="text-center text-dark">Tự ĐG: {{ number_format($diemRenLuyen->diem_tong_hop, 1) }}</td>
                  <td class="no-print"></td>
                  <td class="text-center text-primary font-monospace fs-5" id="print-overall-total-val">0.0</td>
                  <td id="print-overall-class-val" class="text-center text-primary fw-bold">Yếu</td>
                </tr>
              </tbody>
            </table>
          </div>
          
          @if($diemRenLuyen->minh_chung_gdrive)
            <div class="print-only mt-4 mb-4" style="font-family: 'Times New Roman', serif; font-size: 14px; border-top: 1px dashed #000; padding-top: 15px;">
              <strong>Liên kết Google Drive minh chứng tổng hợp của sinh viên:</strong> <u>{{ $diemRenLuyen->minh_chung_gdrive }}</u>
            </div>
          @endif

          <div class="d-flex justify-content-between mt-4 no-print">
            <a href="{{ route('xet_duyet.index') }}" class="btn btn-light fw-bold border"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
            <div>
              <button type="button" onclick="window.print()" class="btn btn-success fw-bold border me-2">
                <i class="bi bi-printer me-2"></i>In phiếu
              </button>
              <button type="submit" class="btn btn-premium fw-bold"><i class="bi bi-save me-2"></i>Lưu Điểm Chi Tiết</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Summary Panel -->
      <div class="col-lg-3 no-print">
        <div class="sticky-top" style="top: 90px; z-index: 1000;">
          <div class="card-premium p-4 mb-4">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-calculator me-2"></i>Bảng Tổng Hợp</h5>
            
            @foreach($criteria as $secKey => $section)
              <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="small text-secondary">Mục {{ $secKey }} (Tối đa {{ $section['max_score'] }}):</span>
                <span class="fw-bold text-dark font-monospace" id="side-total-{{ $secKey }}">0.0</span>
              </div>
            @endforeach

            <div class="p-3 bg-light rounded-3 mt-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-secondary fw-bold">Điểm tổng cộng:</span>
                <span class="h4 fw-bold text-primary mb-0 font-monospace" id="side-overall-total">0.0</span>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <span class="small text-secondary">Xếp loại:</span>
                <span class="badge bg-success badge-premium" id="side-overall-class">Yếu</span>
              </div>
            </div>
          </div>

          <!-- Student Activities Reference Panel -->
          <div class="card-premium p-4">
            <h5 class="fw-bold mb-2 text-dark"><i class="bi bi-bookmark-star me-2"></i>Hoạt động đã tham gia</h5>
            @if($activities->isEmpty())
              <p class="text-muted small mb-0">Sinh viên không tham gia hoạt động rèn luyện nào trong học kỳ này.</p>
            @else
              <ul class="list-unstyled mb-0 small text-secondary">
                @foreach($activities as $act)
                  <li class="mb-2 pb-2 border-bottom">
                    <strong>{{ $act->ten_hoat_dong }}</strong>
                    <div class="text-muted" style="font-size: 10px;">TC: {{ $act->tieuChi->ten_tieu_chi ?? 'N/A' }}</div>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Printable Official Footer Signatures (Hidden on Screen) -->
  <div class="print-only w-100 mt-4" style="font-family: 'Times New Roman', serif;">
    <div class="d-flex justify-content-between text-center" style="font-size: 14px;">
      <div style="width: 30%;">
        <p class="mb-0 fw-bold">SINH VIÊN TỰ ĐÁNH GIÁ</p>
        <p class="text-muted small mb-5">(Ký, ghi rõ họ tên)</p>
        <div style="height: 60px;"></div>
        <p class="fw-bold mt-4">{{ $sinhVien->ho_ten }}</p>
      </div>
      <div style="width: 30%;">
        <p class="mb-0 fw-bold">ĐẠI DIỆN BAN CÁN SỰ LỚP</p>
        <p class="text-muted small mb-5">(Ký, ghi rõ họ tên)</p>
        <div style="height: 60px;"></div>
      </div>
      <div style="width: 30%;">
        <p class="mb-0 fw-bold">CỐ VẤN HỌC TẬP</p>
        <p class="text-muted small mb-5">(Ký, ghi rõ họ tên)</p>
        <div style="height: 60px;"></div>
      </div>
    </div>
  </div>
</div>
  <!-- Modal Preview Minh Chứng -->
  <div class="modal fade no-print" id="evidencePreviewModal" tabindex="-1" aria-labelledby="evidencePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="evidencePreviewModalLabel"><i class="bi bi-eye text-primary me-2"></i>Xem nhanh minh chứng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-0" id="evidence-modal-body" style="background-color: #f8f9fa;">
          <!-- Dynamic content: iframe or img -->
        </div>
        <div class="modal-footer">
          <a href="" id="modal-download-link" target="_blank" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-box-arrow-up-right me-1"></i>Mở trong tab mới</a>
          <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Đóng</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  function previewEvidence(url) {
    let container = $('#evidence-modal-body');
    container.empty();
    $('#modal-download-link').attr('href', url);
    
    let isPdf = url.toLowerCase().endsWith('.pdf');
    let isImage = url.toLowerCase().endsWith('.png') || 
                  url.toLowerCase().endsWith('.jpg') || 
                  url.toLowerCase().endsWith('.jpeg') || 
                  url.toLowerCase().endsWith('.webp') || 
                  url.toLowerCase().endsWith('.gif');
                  
    if (isPdf) {
      container.html(`<iframe src="${url}" class="w-100 border-0" style="height: 550px;"></iframe>`);
    } else if (isImage) {
      container.html(`<div class="p-3"><img src="${url}" class="img-fluid rounded shadow-sm" style="max-height: 500px;" alt="Minh chứng"></div>`);
    } else {
      container.html(`<iframe src="${url}" class="w-100 border-0" style="height: 550px;"></iframe>`);
    }
    
    let myModal = new bootstrap.Modal(document.getElementById('evidencePreviewModal'));
    myModal.show();
  }

  function calculateScores() {
    let overallSum = 0;
    
    // List of sections and their max bounds
    const sections = {
      'I': 30,
      'II': 20,
      'III': 15,
      'IV': 25,
      'V': 10,
      'VI': 10
    };

    for (let sec in sections) {
      let maxScore = sections[sec];
      let sum = 0;
      
      // Select all inputs for this section
      $('.score-input.section-' + sec).each(function() {
        let val = parseFloat($(this).val()) || 0;
        let maxItem = parseFloat($(this).data('max')) || 0;
        
        // Enforce bounds on input element
        if (val < 0) {
          val = 0;
          $(this).val(0);
        } else if (val > maxItem) {
          val = maxItem;
          $(this).val(maxItem);
        }
        
        sum += val;
      });

      // Show capped total
      let cappedSum = Math.min(maxScore, sum);
      $('#section-total-' + sec).text(cappedSum.toFixed(1));
      $('#side-total-' + sec).text(cappedSum.toFixed(1));

      if (sum > maxScore) {
        $('#section-cap-msg-' + sec).text('(Khống chế trần ' + maxScore + 'đ)').addClass('text-danger');
      } else {
        $('#section-cap-msg-' + sec).text('').removeClass('text-danger');
      }

      overallSum += cappedSum;
    }

    // Overall capped at 100
    let finalTotal = Math.min(100, overallSum);
    $('#overall-total-display').text(finalTotal.toFixed(1));
    $('#side-overall-total').text(finalTotal.toFixed(1));
    $('#print-overall-total-val').text(finalTotal.toFixed(1));

    // Classify
    let classification = 'Yếu';
    let badgeClass = 'bg-danger';
    if (finalTotal >= 90) {
      classification = 'Xuất sắc';
      badgeClass = 'bg-success';
    } else if (finalTotal >= 80) {
      classification = 'Tốt';
      badgeClass = 'bg-info text-dark';
    } else if (finalTotal >= 65) {
      classification = 'Khá';
      badgeClass = 'bg-warning text-dark';
    } else if (finalTotal >= 50) {
      classification = 'Trung bình';
      badgeClass = 'bg-secondary';
    }

    $('#overall-class-display').text(classification).removeClass().addClass('badge ' + badgeClass + ' badge-premium mt-1');
    $('#side-overall-class').text(classification).removeClass().addClass('badge ' + badgeClass + ' badge-premium');
    $('#print-overall-class-val').text(classification);

    // Sync input values to print-only text spans
    $('.score-input').each(function() {
      let key = this.id.replace('score_', '');
      let val = $(this).val();
      $('#print_score_' + key).text(val || '0');
    });
    
    // Sync notes
    $('input[name^="ghi_chu_review"]').each(function() {
      let key = this.id.replace('note_', '');
      let val = $(this).val();
      $('#print_note_' + key).text(val || '-');
    });
  }

  $(document).ready(function() {
    calculateScores();

    $('.score-input').on('input change', function() {
      calculateScores();
    });
    
    // Sync review notes
    $('input[name^="ghi_chu_review"]').on('input change', function() {
      let key = this.id.replace('note_', '');
      let val = $(this).val();
      $('#print_note_' + key).text(val || '-');
    });
  });
</script>
<style>
  .btn-xs {
    padding: 1px 5px;
    font-size: 11px;
    border-radius: 3px;
  }

  /* Printable style rules */
  @media screen {
    .print-only {
      display: none !important;
    }
  }

  @media print {
    body {
      background-color: #fff !important;
      color: #000 !important;
      font-family: 'Times New Roman', Times, serif !important;
      font-size: 12px !important;
    }
    
    .no-print {
      display: none !important;
    }
    
    .print-only {
      display: block !important;
    }

    .print-container {
      width: 100% !important;
      max-width: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
    }

    .print-full-width {
      width: 100% !important;
      max-width: 100% !important;
      flex: 0 0 100% !important;
    }

    .print-no-border {
      border: none !important;
      box-shadow: none !important;
      padding: 0 !important;
    }

    .print-table {
      border-color: #000 !important;
      border-collapse: collapse !important;
      width: 100% !important;
    }

    .print-table th, 
    .print-table td {
      border: 1px solid #000 !important;
      padding: 4px 6px !important;
      color: #000 !important;
    }

    .print-section-header {
      background-color: #f2f2f2 !important;
      font-weight: bold !important;
    }

    .print-section-total {
      background-color: #fafafa !important;
      font-weight: bold !important;
    }
    
    .print-overall-row td {
      border: 2px solid #000 !important;
      background-color: #eaeaea !important;
    }

    a {
      text-decoration: none !important;
      color: #000 !important;
    }
  }
</style>
@endsection
