<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập - ĐIỂM RÈN LUYỆN</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <!-- Tailwind CSS -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-sky-100 to-slate-100 overflow-x-hidden" style="font-family: 'Outfit', sans-serif;">
  <!-- 
    ========================================================================
    VIEW: ĐĂNG NHẬP HỆ THỐNG
    ========================================================================
    * Bố cục & Kiểu dáng: Tận dụng hoàn toàn Tailwind CSS v4.
    * Reusable Utilities:
      - form-control-custom: Input được styled gọn gàng.
      - btn-primary: Nút lưu trữ đồng bộ.
      - btn-quick-login: Hỗ trợ đăng nhập nhanh sinh viên/admin/cố vấn.
    ========================================================================
  -->
  <div class="bg-white rounded-[24px] border border-slate-100 shadow-xl p-8 w-full max-w-[485px]">
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center rounded-full text-white text-3xl font-bold mb-4 border-2 border-white shadow-md bg-gradient-to-br from-sky-500 to-sky-400" 
           style="width: 70px; height: 70px;">
        <i class="bi bi-mortarboard-fill"></i>
      </div>
      <h3 class="font-bold mb-1 text-slate-800">ĐIỂM RÈN LUYỆN</h3>
      <p class="text-slate-500 text-sm">Hệ thống quản lý điểm rèn luyện sinh viên thông minh</p>
    </div>

    @if (session('success'))
      <div class="alert alert-success p-3 mb-4 border-0 shadow-sm rounded-xl" role="alert">
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger p-3 mb-4 border-0 shadow-sm rounded-xl" role="alert">
        <ul class="mb-0 text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
      @csrf
      <div class="mb-4">
        <label class="form-label text-slate-500 text-xs font-semibold">Tài khoản Email</label>
        <input type="email" name="email" id="email" class="form-control-custom" placeholder="nhap@example.com" value="{{ old('email') }}" required>
      </div>
      <div class="mb-6">
        <label class="form-label text-slate-500 text-xs font-semibold">Mật khẩu</label>
        <input type="password" name="password" id="password" class="form-control-custom" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-primary w-full mb-4">
        <i class="bi bi-box-arrow-in-right mr-2"></i>Đăng nhập hệ thống
      </button>
    </form>

    <div class="pt-4 border-t border-slate-100 text-center">
      <h6 class="text-xs text-slate-500 mb-3 font-semibold">Đăng nhập nhanh cho kiểm thử:</h6>
      <div class="flex flex-wrap gap-2 justify-center">
        <button class="btn-quick-login" onclick="quickLogin('sinhvien@sv.com', 'password')">Sinh viên</button>
        <button class="btn-quick-login" onclick="quickLogin('bcs@sv.com', 'password')">Ban cán sự</button>
        <button class="btn-quick-login" onclick="quickLogin('covan@sv.com', 'password')">Cố vấn</button>
        <button class="btn-quick-login" onclick="quickLogin('ctsv@sv.com', 'password')">CTSV (Admin)</button>
      </div>
    </div>

    <div class="text-center mt-6">
      <span class="text-slate-500 text-sm">Chưa có tài khoản? </span>
      <a href="{{ route('register') }}" class="text-sky-500 hover:text-sky-600 text-decoration-none font-semibold transition duration-200">Đăng ký ngay</a>
    </div>
  </div>

  <script>
    function quickLogin(email, password) {
      document.getElementById('email').value = email;
      document.getElementById('password').value = password;
    }
  </script>
</body>
</html>