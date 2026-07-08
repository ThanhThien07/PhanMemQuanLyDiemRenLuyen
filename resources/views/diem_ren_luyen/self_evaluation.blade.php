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
      <h4 class="fw-bold text-uppercase mb-1">PHIẾU TỰ ĐÁNH GIÁ ĐIỂM RÈN LUYỆN</h4>
      <h6 class="fw-bold text-uppercase mb-0">HỌC KỲ: {{ $hocKy->ten_hoc_ky }}</h6>
    </div>
    
    <!-- Student Information Block for Print -->
    <div class="row mb-3 print-info-block" style="font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.6;">
      <div class="col-6">
        <div>Họ và tên: <strong>{{ $sinhVien->ho_ten }}</strong></div>
        <div>Mã số sinh viên: <strong>{{ $sinhVien->ma_sv }}</strong></div>
      </div>
      <div class="col-6">
        <div>Lớp: <strong>{{ $diemRenLuyen->lop ? $diemRenLuyen->lop->ten_lop : $sinhVien->lop->ten_lop }}</strong></div>
        <div>Hệ đào tạo: <strong>{{ $sinhVien->heDaoTao ? $sinhVien->heDaoTao->ten_he : 'Chính quy' }}</strong></div>
      </div>
    </div>
  </div>

  <!-- Profile & Semester Info Header -->
  <div class="card-premium p-4 mb-4 no-print" style="background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 100%); border-left: 5px solid var(--ms-primary);">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h3 class="fw-bold text-dark mb-1">
          <i class="bi bi-pencil-square me-2 text-primary"></i>Phiếu Tự Đánh Giá Điểm Rèn Luyện
        </h3>
        <p class="text-secondary mb-0">
          Sinh viên: <strong>{{ $sinhVien->ho_ten }}</strong> | MSSV: <strong>{{ $sinhVien->ma_sv }}</strong> | Lớp: <strong>{{ $sinhVien->lop->ten_lop }}</strong>
        </p>
        <p class="text-muted small mb-0">
          Học kỳ đánh giá: <strong class="text-primary">{{ $hocKy->ten_hoc_ky }}</strong>
          @if(isset($dotDuyet) && $dotDuyet->gd1_bat_dau && $dotDuyet->gd1_ket_thuc)
            | Thời gian tự đánh giá: <strong class="text-success">{{ \Carbon\Carbon::parse($dotDuyet->gd1_bat_dau)->format('d/m/Y') }}</strong> đến <strong class="text-danger">{{ \Carbon\Carbon::parse($dotDuyet->gd1_ket_thuc)->format('d/m/Y') }} (Hạn chót)</strong>
          @endif
        </p>
      </div>
      <div class="col-md-4 text-end">
        <button type="button" onclick="window.print()" class="btn btn-outline-success fw-bold border me-2">
          <i class="bi bi-printer me-2"></i>In phiếu điểm
        </button>
        <div class="p-3 bg-white rounded-3 shadow-sm border d-inline-block text-center align-middle">
          <div class="small text-muted text-uppercase fw-bold" style="font-size: 11px;">Tổng điểm tự đánh giá</div>
          <div class="h2 fw-bold text-primary mb-0" id="overall-total-display">0.00</div>
          <span class="badge bg-secondary badge-premium mt-1" id="overall-class-display">Yếu</span>
        </div>
      </div>
    </div>
  </div>

  <form action="{{ route('diem_ren_luyen.save_self_evaluation', ['hoc_ky_id' => $selectedHocKy]) }}" method="POST">
    @csrf
    <div class="row">
      <!-- Main Spreadsheet Evaluation Form -->
      <div class="col-lg-12">
        <div class="card-premium p-4 mb-4 print-no-border">
          <div class="table-responsive">
            <table class="table table-bordered align-middle print-table" style="border-color: #ccc;">
              <thead class="table-light">
                <tr class="text-center" style="font-size: 13px;">
                  <th style="width: 5%;">STT</th>
                  <th style="width: 55%;" class="text-start">Nội dung đánh giá (Theo mẫu biểu chi tiết)</th>
                  <th style="width: 10%;">Điểm trần</th>
                  <th style="width: 15%;">Tự đánh giá</th>
                  <th style="width: 15%;">Minh chứng / Ghi chú</th>
                </tr>
              </thead>
              <tbody>
                @foreach($criteria as $secKey => $section)
                  <!-- Section Row Header -->
                  <tr class="table-secondary fw-bold text-dark print-section-header">
                    <td class="text-center">{{ $secKey }}</td>
                    <td colspan="2">
                      {{ $section['name'] }}
                    </td>
                    <td colspan="2" class="text-end text-primary">
                      <span class="no-print">Tối đa {{ $section['max_score'] }} điểm</span>
                      <span class="print-only text-dark">Max {{ $section['max_score'] }}đ</span>
                    </td>
                  </tr>

                  @foreach($section['items'] as $itemKey => $item)
                    @php
                      $existing = $existingDetails->get($itemKey);
                      $currentScore = $existing ? $existing->diem_sv : 0;
                      $currentNote = $existing ? $existing->ghi_chu_sv : '';
                      
                      // Auto fills/suggestions
                      $suggestionText = '';
                      $suggestedScore = null;
                      if ($itemKey === 'I.2') {
                          $suggestedScore = $autoGpaScore;
                          $suggestionText = $gpaNote;
                      } elseif ($itemKey === 'III.1' && count($activities) > 0) {
                          $count = 0;
                          $actNames = [];
                          foreach($activities as $act) {
                              if ($act->tieu_chi_id == 3) {
                                  $count++;
                                  $actNames[] = $act->ten_hoat_dong;
                              }
                          }
                          if ($count > 0) {
                              $suggestedScore = min($item['max_score'], $count * 2);
                              $suggestionText = "Đã tham gia {$count} hđ: " . implode(', ', $actNames);
                          }
                      }
                      
                      // Set default on first load
                      if (!$existing && $suggestedScore !== null) {
                          $currentScore = $suggestedScore;
                          $currentNote = $suggestionText;
                      }
                    @endphp
                    <tr>
                      <td class="text-center font-monospace small">{{ $itemKey }}</td>
                      <td>
                        <span class="fw-medium">{{ $item['name'] }}</span>
                        <div class="small text-muted no-print">{{ $item['description'] }}</div>
                        @if($suggestionText)
                          <div class="mt-2 no-print">
                            <span class="badge bg-sky-50 text-sky-700 border border-sky-100 p-1.5 small inline-flex items-center gap-1" style="font-size: 11px; display: inline-block; white-space: normal; text-align: left;">
                              <i class="bi bi-magic"></i> Gợi ý: {{ $suggestionText }}
                            </span>
                            <button type="button" class="btn-outline-primary py-0.5 px-2 ms-2" style="font-size: 10px; border-radius: 6px;" onclick="applySuggestion('{{ $itemKey }}', {{ $suggestedScore }})">Áp dụng</button>
                          </div>
                        @endif
                      </td>
                      <td class="text-center fw-bold text-secondary">{{ $item['max_score'] }}</td>
                      <td>
                        <div class="input-group input-group-sm no-print">
                          <input type="number" 
                                 name="diem_sv[{{ $itemKey }}]" 
                                 id="score_{{ $itemKey }}"
                                 class="form-control text-center score-input section-{{ $secKey }}" 
                                 data-section="{{ $secKey }}"
                                 data-max="{{ $item['max_score'] }}"
                                 step="0.5" 
                                 min="0" 
                                 max="{{ $item['max_score'] }}" 
                                 value="{{ $currentScore }}" 
                                 required>
                        </div>
                        <span class="print-only text-center fw-bold d-block text-dark" id="print_score_{{ $itemKey }}">{{ $currentScore }}</span>
                      </td>
                      <td>
                        <span class="small text-muted no-print">{{ $suggestionText ?: '-' }}</span>
                        <span class="print-only small text-secondary">{{ $suggestionText ?: '-' }}</span>
                      </td>
                    </tr>
                  @endforeach
                  <!-- Section Subtotal Row -->
                  <tr class="table-light fw-bold print-section-total">
                    <td></td>
                    <td class="text-end">Cộng tiêu chí {{ $secKey }}:</td>
                    <td class="text-center text-primary" id="section-max-{{ $secKey }}">{{ $section['max_score'] }}</td>
                    <td class="text-center text-primary font-monospace" id="section-total-{{ $secKey }}">0.00</td>
                    <td><span class="small text-muted no-print" id="section-cap-msg-{{ $secKey }}"></span></td>
                  </tr>
                @endforeach
                
                <!-- Overall Sheet Score Row -->
                <tr class="fw-bold table-primary text-primary-emphasis border-primary" style="font-size: 15px; border-top: 2px solid #0d6efd !important; background-color: #cfe2ff;">
                  <td class="text-center"></td>
                  <td class="text-end text-dark">TỔNG ĐIỂM RÈN LUYỆN CẢ HỌC KỲ (Khống chế tối đa 100đ):</td>
                  <td class="text-center text-dark">100</td>
                  <td class="text-center text-primary font-monospace fs-5" id="print-overall-total-val">0.0</td>
                  <td id="print-overall-class-val" class="text-center text-primary fw-bold">Yếu</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Consolidated Google Drive Proof Link -->
          <div class="mb-4 p-4 bg-light rounded-3 border border-primary border-opacity-25 no-print mt-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-google-drive text-primary me-2"></i>Liên kết Google Drive minh chứng tổng hợp</h5>
            <input type="url" name="minh_chung_gdrive" class="form-control" placeholder="Dán liên kết chia sẻ thư mục Google Drive của bạn..." value="{{ $diemRenLuyen->minh_chung_gdrive }}">
            <div class="form-text text-muted mt-2 small" style="line-height: 1.6;">
              <i class="bi bi-info-circle-fill me-1 text-info"></i>
              <strong>Hướng dẫn dành cho Sinh viên:</strong> Vui lòng tổng hợp tất cả các tệp minh chứng, đổi tên các tệp minh chứng rõ ràng theo từng phần tương ứng (ví dụ: <code>I.4_ChungNhanHoiThao.pdf</code>, <code>IV.4_GiayHienMau.jpg</code>), tải lên một thư mục trên Google Drive, sau đó dán liên kết của thư mục đó vào ô trên và <strong>chú ý thiết lập quyền truy cập sang công khai (Bất kỳ ai có liên kết đều có thể xem).</strong>
            </div>
          </div>
          
          <div class="print-only mt-4 mb-4" style="font-family: 'Times New Roman', serif; font-size: 14px; border-top: 1px dashed #000; padding-top: 15px;">
            <strong>Liên kết Google Drive minh chứng tổng hợp:</strong> <u>{{ $diemRenLuyen->minh_chung_gdrive ?: 'Không có' }}</u>
          </div>

          <div class="d-flex justify-content-between mt-4 no-print">
            <a href="{{ route('diem_ren_luyen.index') }}" class="btn btn-light fw-bold border"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
            <div>
              <button type="button" onclick="window.print()" class="btn btn-success fw-bold border me-2">
                <i class="bi bi-printer me-2"></i>In phiếu
              </button>
              <button type="submit" class="btn btn-premium fw-bold"><i class="bi bi-send me-2"></i>Nộp Phiếu Đánh Giá</button>
            </div>
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
@endsection

@section('scripts')
<script>
  function applySuggestion(key, score) {
    $('#score_' + key).val(score);
    calculateScores();
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
    
    // Sync notes (consolidated gdrive)
    const gdriveVal = $('input[name="minh_chung_gdrive"]').val();
    $('.print-only u').text(gdriveVal || 'Không có');
  }

  const draftKey = 'drl_draft_' + '{{ $sinhVien->id }}' + '_' + '{{ $hocKy->id }}';

  function saveDraft() {
    let draftData = {
      scores: {},
      minh_chung_gdrive: $('input[name="minh_chung_gdrive"]').val()
    };
    $('.score-input').each(function() {
      let key = this.id.replace('score_', '');
      draftData.scores[key] = $(this).val();
    });
    localStorage.setItem(draftKey, JSON.stringify(draftData));
  }

  function restoreDraft() {
    let saved = localStorage.getItem(draftKey);
    if (saved) {
      try {
        let draftData = JSON.parse(saved);
        for (let key in draftData.scores) {
          $('#score_' + key).val(draftData.scores[key]);
        }
        $('input[name="minh_chung_gdrive"]').val(draftData.minh_chung_gdrive || '');
        calculateScores();
      } catch (e) {
        console.error("Lỗi phục hồi bản nháp", e);
      }
    }
  }

  function checkDraft() {
    let saved = localStorage.getItem(draftKey);
    if (saved) {
      let alertHtml = `
        <div id="draft-alert" class="alert alert-warning alert-dismissible fade show no-print mb-4 d-flex justify-content-between align-items-center" role="alert">
          <div>
            <i class="bi bi-info-circle-fill me-2 text-warning fs-5"></i>
            Hệ thống phát hiện bản nháp chưa nộp được lưu trước đó của học kỳ này. Bạn có muốn phục hồi không?
          </div>
          <div>
            <button type="button" class="btn btn-sm btn-primary me-2 fw-bold" onclick="restoreDraftAndDismiss()">Phục hồi nháp</button>
            <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" onclick="dismissDraftAlert()">Bỏ qua</button>
          </div>
        </div>
      `;
      $('.print-container').prepend(alertHtml);
    }
  }

  function restoreDraftAndDismiss() {
    restoreDraft();
    $('#draft-alert').alert('close');
  }

  function dismissDraftAlert() {
    $('#draft-alert').alert('close');
  }

  $(document).ready(function() {
    calculateScores();
    checkDraft();

    $('.score-input').on('input change', function() {
      calculateScores();
      saveDraft();
    });
    
    $('input[name="minh_chung_gdrive"]').on('input change', function() {
      const val = $(this).val();
      $('.print-only u').text(val || 'Không có');
      saveDraft();
    });

    $('form').on('submit', function() {
      localStorage.removeItem(draftKey);
    });
  });
</script>
<style>
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

    /* Keep layout tidy and clean without web colors */
    a {
      text-decoration: none !important;
      color: #000 !important;
    }
  }
</style>
@endsection
