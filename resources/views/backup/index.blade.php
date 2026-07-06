@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <!-- Cột trái: Cấu hình, Thống kê, Sao lưu thủ công -->
    <div class="col-lg-5 col-md-12">
      <!-- Cấu hình sao lưu tự động -->
      <div class="card-premium p-4 mb-4">
        <h4 class="fw-bold mb-4 text-dark flex items-center gap-2">
          <i class="bi bi-shield-check text-sky-500"></i>
          <span>Cấu hình Sao lưu Tự động</span>
        </h4>

        <form action="{{ route('backup.settings.update') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label text-dark fw-semibold">Trạng thái tự động sao lưu</label>
            <select name="enabled" class="form-select" required>
              <option value="1" {{ $settings['enabled'] ? 'selected' : '' }}>Kích hoạt (Bật)</option>
              <option value="0" {{ !$settings['enabled'] ? 'selected' : '' }}>Tắt</option>
            </select>
            <div class="form-text text-muted">
              Hệ thống kiểm tra và tự động sao lưu ngầm qua queue khi đến chu kỳ mà không cần người dùng thao tác.
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label text-dark fw-semibold">Chu kỳ sao lưu tự động</label>
            <select name="interval_weeks" class="form-select" required>
              <option value="1" {{ ($settings['interval_weeks'] ?? 1) == 1 ? 'selected' : '' }}>Hàng tuần (1 tuần/lần)</option>
              <option value="2" {{ ($settings['interval_weeks'] ?? 1) == 2 ? 'selected' : '' }}>Mỗi 2 tuần (2 tuần/lần)</option>
            </select>
          </div>

          <button type="submit" class="btn btn-premium w-100 py-2.5">
            <i class="bi bi-save2 me-2"></i>Lưu cấu hình
          </button>
        </form>
      </div>

      <!-- Thống kê cơ sở dữ liệu -->
      <div class="card-premium p-4 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
        <h4 class="fw-bold mb-4 text-dark flex items-center gap-2">
          <i class="bi bi-database-check text-indigo-500"></i>
          <span>Trạng thái & Thống kê dữ liệu</span>
        </h4>

        <div class="list-group list-group-flush mb-3">
          <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-2.5">
            <span class="text-slate-600"><i class="bi bi-database me-2 text-primary"></i>Tên cơ sở dữ liệu:</span>
            <span class="fw-bold text-dark">{{ $dbName }}</span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-2.5">
            <span class="text-slate-600"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Tổng số bảng:</span>
            <span class="badge bg-primary rounded-pill">{{ count($tables) }} bảng</span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-2.5">
            <span class="text-slate-600"><i class="bi bi-hdd-fill me-2 text-primary"></i>Dung lượng dữ liệu:</span>
            <span class="fw-bold text-dark">{{ number_format($dbSize / 1024 / 1024, 2) }} MB</span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-2.5">
            <span class="text-slate-600"><i class="bi bi-calendar-event me-2 text-primary"></i>Lần sao lưu cuối:</span>
            <span class="fw-semibold text-slate-700">
              {{ $settings['last_backup_at'] ? \Carbon\Carbon::parse($settings['last_backup_at'])->format('d/m/Y H:i:s') : 'Chưa có' }}
            </span>
          </div>
          <div class="list-group-item bg-transparent border-0 px-0 py-2.5">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-slate-600"><i class="bi bi-hourglass-split me-2 text-primary"></i>Lần tự động kế tiếp:</span>
              <span class="fw-bold text-success">
                {{ $settings['enabled'] && $settings['next_backup_at'] ? \Carbon\Carbon::parse($settings['next_backup_at'])->format('d/m/Y H:i:s') : 'Đang tắt' }}
              </span>
            </div>
            @if($settings['enabled'] && $settings['next_backup_at'])
              <div class="text-end small text-indigo-600 font-semibold" id="countdown-timer">
                (Đang tải đếm ngược...)
              </div>
            @endif
          </div>
        </div>

        <!-- Xem danh sách bảng -->
        <div class="accordion" id="tablesAccordion">
          <div class="accordion-item border-0 bg-transparent">
            <h2 class="accordion-header" id="headingTables">
              <button class="accordion-button collapsed px-0 bg-transparent text-slate-700 fw-semibold text-sm focus-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTables" aria-expanded="false" aria-controls="collapseTables">
                <i class="bi bi-list-nested me-2"></i>Xem chi tiết danh sách bảng cơ sở dữ liệu
              </button>
            </h2>
            <div id="collapseTables" class="accordion-collapse collapse" aria-labelledby="headingTables" data-bs-parent="#tablesAccordion">
              <div class="accordion-body px-0 pt-2 pb-0">
                <div class="d-flex flex-wrap gap-1.5 max-h-[150px] overflow-y-auto pr-1">
                  @foreach($tables as $table)
                    <span class="badge bg-secondary" style="font-size: 11px;">{{ $table }}</span>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sao lưu khẩn cấp & Tải lên khôi phục -->
      <div class="card-premium p-4 mb-4">
        <h4 class="fw-bold mb-4 text-dark flex items-center gap-2">
          <i class="bi bi-lightning-fill text-amber-500"></i>
          <span>Thao tác sao lưu & khôi phục nhanh</span>
        </h4>

        <div class="mb-4">
          <label class="form-label text-slate-700 fw-semibold mb-2">1. Tạo bản sao lưu khẩn cấp hiện tại</label>
          <form action="{{ route('backup.run') }}" method="POST" id="manualBackupForm">
            @csrf
            <button type="submit" class="btn btn-success w-100 py-2.5 fw-semibold" id="btnManualBackup">
              <i class="bi bi-plus-circle-fill me-2 animate-pulse"></i>Sao lưu toàn bộ dữ liệu ngay
            </button>
          </form>
          <div class="form-text mt-1 text-slate-500 text-xs">
            Bản sao lưu sẽ được xuất ra SQL, nén ZIP và lưu trữ an toàn trong máy chủ hệ thống.
          </div>
        </div>

        <hr class="border-slate-200 my-4">

        <div>
          <label class="form-label text-slate-700 fw-semibold mb-2">2. Khôi phục từ tệp tin bên ngoài</label>
          <form action="{{ route('backup.restore_upload') }}" method="POST" enctype="multipart/form-data" id="uploadRestoreForm">
            @csrf
            <div class="mb-3">
              <div class="input-group">
                <input type="file" name="backup_file" class="form-control" accept=".sql,.zip" required id="restoreFileInput">
              </div>
              <div class="form-text text-danger font-semibold text-xs mt-1">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>Hành động này sẽ ghi đè và thay đổi toàn bộ cơ sở dữ liệu hiện tại!
              </div>
            </div>
            <button type="button" class="btn btn-danger w-100 py-2.5 fw-semibold" onclick="confirmUploadRestore()">
              <i class="bi bi-cloud-arrow-up-fill me-2"></i>Tải lên & Khôi phục ngay
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Cột phải: Danh sách tệp sao lưu & Nhật ký chạy -->
    <div class="col-lg-7 col-md-12">
      <!-- Danh sách các bản sao lưu hiện có -->
      <div class="card-premium p-4 mb-4">
        <h4 class="fw-bold mb-4 text-dark flex items-center gap-2">
          <i class="bi bi-files text-sky-500"></i>
          <span>Danh sách các bản sao lưu trên máy chủ</span>
        </h4>

        @if(count($backups) > 0)
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-middle">
              <thead class="table-light sticky-top" style="z-index: 1;">
                <tr>
                  <th>Tên bản sao lưu</th>
                  <th>Dung lượng</th>
                  <th>Thời gian tạo</th>
                  <th class="text-end">Hành động</th>
                </tr>
              </thead>
              <tbody>
                @foreach($backups as $backup)
                  <tr>
                    <td>
                      <span class="fw-bold text-slate-800 d-block">{{ $backup['name'] }}</span>
                      <span class="badge bg-secondary py-0.5 px-1.5" style="font-size: 9px;">
                        {{ strtoupper($backup['ext']) }}
                      </span>
                    </td>
                    <td>
                      @if($backup['size'] >= 1048576)
                        {{ number_format($backup['size'] / 1048576, 2) }} MB
                      @else
                        {{ number_format($backup['size'] / 1024, 2) }} KB
                      @endif
                    </td>
                    <td>
                      <div class="small text-slate-600">
                        {{ \Carbon\Carbon::parse($backup['created_at'])->format('d/m/Y H:i') }}
                      </div>
                    </td>
                    <td class="text-end">
                      <div class="d-inline-flex gap-1">
                        <!-- Tải xuống -->
                        <a href="{{ route('backup.download', $backup['name']) }}" class="btn btn-sm btn-outline-primary py-1.5 px-2" title="Tải xuống tệp">
                          <i class="bi bi-download"></i>
                        </a>

                        <!-- Khôi phục -->
                        <button type="button" class="btn btn-sm btn-outline-success py-1.5 px-2" title="Khôi phục database từ tệp này" onclick="confirmRestore('{{ $backup['name'] }}')">
                          <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                        </button>

                        <!-- Xóa -->
                        <form action="{{ route('backup.delete', $backup['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản sao lưu này trên máy chủ? Thao tác này không thể hoàn tác.')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger py-1.5 px-2" title="Xóa bản sao lưu">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5 text-muted">
            <i class="bi bi-folder-x display-4 d-block mb-3"></i>
            Chưa có tệp tin sao lưu nào được lưu trên máy chủ.
          </div>
        @endif
      </div>

      <!-- Nhật ký hoạt động sao lưu -->
      <div class="card-premium p-4">
        <h4 class="fw-bold mb-4 text-dark flex items-center gap-2">
          <i class="bi bi-clock-history text-indigo-500"></i>
          <span>Nhật ký hoạt động sao lưu</span>
        </h4>

        @if(count($history) > 0)
          <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
            <table class="table table-hover align-middle" style="font-size: 13px;">
              <thead class="table-light sticky-top" style="z-index: 1;">
                <tr>
                  <th>Thời gian chạy</th>
                  <th>Phân loại</th>
                  <th>Trạng thái</th>
                  <th>Thời gian thực thi</th>
                  <th>Thông tin tệp tin / Lỗi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($history as $item)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y H:i:s') }}</td>
                    <td>
                      <span class="badge {{ $item['type'] === 'Thủ công' ? 'bg-info' : 'bg-primary' }}">
                        {{ $item['type'] }}
                      </span>
                    </td>
                    <td>
                      @if(($item['status'] ?? '') === 'Thành công')
                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Thành công</span>
                      @else
                        <span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i> Thất bại</span>
                      @endif
                    </td>
                    <td>{{ $item['duration'] ?? 0 }} giây</td>
                    <td>
                      @if(($item['status'] ?? '') === 'Thành công')
                        <span class="text-slate-700 font-mono">{{ $item['file_name'] }}</span>
                        <div class="text-xs text-slate-500">Dung lượng: {{ number_format(($item['size'] ?? 0) / 1024, 2) }} KB</div>
                      @else
                        <span class="text-danger font-mono text-xs d-block" style="max-width: 250px; overflow-wrap: break-word;">
                          {{ $item['error'] }}
                        </span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-4 text-muted">
            Chưa ghi nhận hoạt động chạy sao lưu nào trước đây.
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Modal Xác nhận Khôi phục Cơ sở dữ liệu -->
<div class="modal fade" id="restoreConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="restoreConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-danger text-white border-0 py-3">
        <h5 class="modal-title fw-bold flex items-center gap-2" id="restoreConfirmModalLabel">
          <i class="bi bi-exclamation-triangle-fill"></i> XÁC NHẬN KHÔI PHỤC DỮ LIỆU CỰC KỲ QUAN TRỌNG
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-slate-800 fw-semibold mb-3" id="restoreTargetText">
          Bạn đang yêu cầu khôi phục lại cơ sở dữ liệu hệ thống từ tệp tin sao lưu:
        </p>
        <div class="alert alert-danger bg-danger-subtle text-danger border-0 p-3 mb-3" style="font-size: 14px;">
          <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>LƯU Ý QUAN TRỌNG:</strong>
          <ul class="mb-0 mt-1 ps-3">
            <li>Tất cả dữ liệu hiện tại trong cơ sở dữ liệu sẽ bị <strong>XÓA SẠCH</strong>.</li>
            <li>Hệ thống sẽ được khôi phục về trạng thái chính xác tại thời điểm tệp tin sao lưu được tạo.</li>
            <li>Thao tác này <strong>không thể thu hồi</strong> sau khi thực hiện.</li>
          </ul>
        </div>
        
        <form id="restoreConfirmForm" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label text-slate-700 fw-bold">Nhập từ khóa xác nhận</label>
            <input type="text" class="form-control" id="confirmText" placeholder="Nhập KHOIPHUC để tiếp tục" required>
            <div class="form-text">Nhập chính xác <strong>KHOIPHUC</strong> để hệ thống mở khóa nút khôi phục.</div>
          </div>
          
          <div class="d-flex gap-2 justify-content-end mt-4">
            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy bỏ</button>
            <button type="submit" class="btn btn-danger px-4" id="btnExecuteRestore" disabled>
              <i class="bi bi-arrow-counterclockwise me-1"></i>Khôi phục ngay
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Loading che màn hình khi chạy Backup/Restore -->
<div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
      <div class="d-flex flex-column align-items-center">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.3em;">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h5 class="fw-bold text-dark mb-1" id="loadingModalTitle">Đang xử lý dữ liệu</h5>
        <p class="text-slate-500 mb-0 small" id="loadingModalMessage">Vui lòng giữ kết nối internet và không tắt tab trình duyệt này.</p>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // Countdown Timer cho lần sao lưu tự động tiếp theo
  @if($settings['enabled'] && $settings['next_backup_at'])
    (function() {
      var nextBackupTime = new Date("{{ \Carbon\Carbon::parse($settings['next_backup_at'])->toIso8601String() }}").getTime();
      
      function updateCountdown() {
        var now = new Date().getTime();
        var distance = nextBackupTime - now;
        
        if (distance < 0) {
          document.getElementById("countdown-timer").innerHTML = "(Lịch trình sao lưu tự động đang chạy ngầm...)";
          return;
        }
        
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        var timerString = "(Tự động sao lưu sau: ";
        if (days > 0) timerString += days + " ngày " + hours + " giờ " + minutes + " phút)";
        else if (hours > 0) timerString += hours + " giờ " + minutes + " phút " + seconds + " giây)";
        else if (minutes > 0) timerString += minutes + " phút " + seconds + " giây)";
        else timerString += seconds + " giây)";
        
        document.getElementById("countdown-timer").innerHTML = timerString;
      }
      
      updateCountdown();
      setInterval(updateCountdown, 1000);
    })();
  @endif

  // Mở màn hình chờ Loading
  function showLoading(title, message) {
    $('#loadingModalTitle').text(title);
    $('#loadingModalMessage').text(message);
    var myModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    myModal.show();
  }

  // Bắt sự kiện form sao lưu thủ công để hiện spinner
  $('#manualBackupForm').on('submit', function() {
    showLoading('Đang sao lưu dữ liệu', 'Đang kết nối database và xuất tệp SQL nén ZIP, vui lòng đợi...');
  });

  // Modal xác nhận khôi phục
  var restoreModal = new bootstrap.Modal(document.getElementById('restoreConfirmModal'));
  
  function confirmRestore(fileName) {
    $('#restoreTargetText').html('Bạn đang yêu cầu khôi phục lại cơ sở dữ liệu hệ thống từ tệp tin sao lưu trên máy chủ: <strong class="d-block mt-2 font-mono text-danger text-center bg-light p-2 rounded border">' + fileName + '</strong>');
    
    // Set form action
    var actionUrl = "{{ route('backup.restore', ':filename') }}".replace(':filename', fileName);
    $('#restoreConfirmForm').attr('action', actionUrl);
    
    // Reset modal state
    $('#confirmText').val('');
    $('#btnExecuteRestore').prop('disabled', true);
    
    restoreModal.show();
  }

  function confirmUploadRestore() {
    var fileInput = document.getElementById('restoreFileInput');
    if (!fileInput.files.length) {
      alert('Vui lòng chọn tệp tin .sql hoặc .zip trước khi tải lên.');
      return;
    }
    
    var fileName = fileInput.files[0].name;
    $('#restoreTargetText').html('Bạn đang yêu cầu tải lên và khôi phục hệ thống từ tệp tin cục bộ: <strong class="d-block mt-2 font-mono text-danger text-center bg-light p-2 rounded border">' + fileName + '</strong>');
    
    // Set form action for upload
    $('#restoreConfirmForm').attr('action', "{{ route('backup.restore_upload') }}");
    
    // Set type as multipart form-data to receive file
    $('#restoreConfirmForm').attr('enctype', 'multipart/form-data');
    
    // Append the file input into our modal form to be submitted
    $('#restoreConfirmForm').find('input[type="file"]').remove(); // clear existing
    
    // Reset modal state
    $('#confirmText').val('');
    $('#btnExecuteRestore').prop('disabled', true);
    
    restoreModal.show();
  }

  // Kiểm tra chuỗi nhập xác nhận khôi phục
  $('#confirmText').on('input', function() {
    if ($(this).val().trim() === 'KHOIPHUC') {
      $('#btnExecuteRestore').prop('disabled', false);
    } else {
      $('#btnExecuteRestore').prop('disabled', true);
    }
  });

  // Khi submit form khôi phục, đóng modal xác nhận và mở loading
  $('#restoreConfirmForm').on('submit', function(e) {
    restoreModal.hide();
    
    // If it's an upload restore, we must copy the actual file from the original form if it hasn't been cloned
    if ($(this).attr('action') === "{{ route('backup.restore_upload') }}") {
      var fileInput = document.getElementById('restoreFileInput');
      // Clone it to submit
      var clonedInput = fileInput.cloneNode(true);
      clonedInput.style.display = 'none';
      this.appendChild(clonedInput);
    }
    
    showLoading('Đang khôi phục cơ sở dữ liệu', 'Đang thiết lập lại cấu trúc bảng và chèn dữ liệu, hành động này có thể mất vài giây...');
  });
</script>
@endsection
