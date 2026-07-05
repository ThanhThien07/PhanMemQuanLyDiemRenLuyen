<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng ký tài khoản - ĐIỂM RÈN LUYỆN</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <!-- Tailwind CSS -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-neutral-950 via-neutral-900 to-rose-950/30 overflow-x-hidden p-6" 
      style="font-family: 'Outfit', sans-serif; color: #fff;">
  <!-- 
    ========================================================================
    VIEW: ĐĂNG KÝ TÀI KHOẢN MỚI
    ========================================================================
    * Thiết kế: Định dạng Dark theme phong cách hiện đại với hiệu ứng mờ kính (Glassmorphism).
    * Reusable dark utilities:
      - form-control-custom-dark: Input tối trong suốt.
      - form-select-custom-dark: Thẻ select tối trong suốt.
      - btn-register: Nút lưu trữ màu đỏ gradient nổi bật.
    ========================================================================
  -->
  <div class="border border-white/15 bg-white/10 shadow-2xl rounded-[24px] p-6 py-8 w-full max-w-[500px] backdrop-blur-lg">
    <div class="text-center mb-6">
      <h3 class="fw-bold text-white tracking-wide">ĐĂNG KÝ TÀI KHOẢN</h3>
      <p class="text-white/50 text-sm">Tham gia hệ thống quản lý điểm rèn luyện sinh viên</p>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger bg-danger/20 border-0 text-white p-3 mb-4 rounded-xl shadow-sm">
        <ul class="mb-0 text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('register') }}" method="POST">
      @csrf
      <div class="mb-4">
        <label class="form-label text-white/80 text-sm fw-semibold">Họ và tên</label>
        <input type="text" name="name" class="form-control-custom-dark" placeholder="Nguyễn Văn A" value="{{ old('name') }}" required>
      </div>
      <div class="mb-4">
        <label class="form-label text-white/80 text-sm fw-semibold">Email</label>
        <input type="email" name="email" class="form-control-custom-dark" placeholder="a@sv.com" value="{{ old('email') }}" required>
      </div>
      <div class="mb-4">
        <label class="form-label text-white/80 text-sm fw-semibold">Vai trò</label>
        <select name="role" class="form-select-custom-dark" style="color: #fff;" required>
          <option value="sinh_vien" style="background: #111; color: #fff;">Sinh viên</option>
          <option value="ban_can_su" style="background: #111; color: #fff;">Ban cán sự lớp</option>
          <option value="co_van" style="background: #111; color: #fff;">Cố vấn học tập</option>
          <option value="ctsv" style="background: #111; color: #fff;">Phòng Công tác sinh viên (CTSV)</option>
          <option value="admin" style="background: #111; color: #fff;">Quản trị viên</option>
        </select>
      </div>

      <!-- Student profile fields (Shown dynamically using JS) -->
      <div id="student-fields" class="d-none">
        <div class="mb-4">
          <label class="form-label text-white/80 text-sm fw-semibold">Mã sinh viên</label>
          <input type="text" name="ma_sv" class="form-control-custom-dark" placeholder="SV220101" value="{{ old('ma_sv') }}">
        </div>
        <div class="mb-4">
          <label class="form-label text-white/80 text-sm fw-semibold">Lớp học</label>
          <select name="lop_id" class="form-select-custom-dark" style="color: #fff;">
            <option value="" style="background: #111; color: #fff;">-- Chọn lớp học --</option>
            @foreach($lops as $lop)
              <option value="{{ $lop->id }}" style="background: #111; color: #fff;" {{ old('lop_id') == $lop->id ? 'selected' : '' }}>{{ $lop->ten_lop }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-4">
          <label class="form-label text-white/80 text-sm fw-semibold">Hệ đào tạo</label>
          <select name="he_dao_tao_id" class="form-select-custom-dark" style="color: #fff;">
            <option value="" style="background: #111; color: #fff;">-- Chọn hệ đào tạo --</option>
            @foreach($heDaoTaos as $hdt)
              <option value="{{ $hdt->id }}" style="background: #111; color: #fff;" {{ old('he_dao_tao_id') == $hdt->id ? 'selected' : '' }}>{{ $hdt->ten_he }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label text-white/80 text-sm fw-semibold">Mật khẩu</label>
        <input type="password" name="password" class="form-control-custom-dark" placeholder="Tối thiểu 6 ký tự" required>
      </div>
      <div class="mb-6">
        <label class="form-label text-white/80 text-sm fw-semibold">Xác nhận mật khẩu</label>
        <input type="password" name="password_confirmation" class="form-control-custom-dark" placeholder="Nhập lại mật khẩu" required>
      </div>
      <button type="submit" class="btn-register">Đăng ký tài khoản</button>
    </form>

    <div class="text-center">
      <span class="text-white/50 text-sm">Đã có tài khoản? </span>
      <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-warning hover:text-warning/80 transition duration-200" style="color: #d4af37 !important;">Đăng nhập ngay</a>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const roleSelect = document.querySelector('select[name="role"]');
      const studentFields = document.getElementById('student-fields');
      const maSvInput = document.querySelector('input[name="ma_sv"]');
      const lopSelect = document.querySelector('select[name="lop_id"]');
      const heDaoTaoSelect = document.querySelector('select[name="he_dao_tao_id"]');

      function toggleStudentFields() {
        const val = roleSelect.value;
        if (val === 'sinh_vien' || val === 'ban_can_su') {
          studentFields.classList.remove('d-none');
          maSvInput.required = true;
          lopSelect.required = true;
          heDaoTaoSelect.required = true;
        } else {
          studentFields.classList.add('d-none');
          maSvInput.required = false;
          lopSelect.required = false;
          heDaoTaoSelect.required = false;
        }
      }

      roleSelect.addEventListener('change', toggleStudentFields);
      toggleStudentFields(); // Run on initial load
    });
  </script>
</body>
</html>