<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập - ĐIỂM RÈN LUYỆN</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-color: #0ea5e9; /* Sky Blue (Xanh lam hình 1) */
      --secondary-color: #38bdf8; /* Xanh dương nhạt */
      --dark-color: #0f172a;
    }
    body {
      font-family: 'Outfit', sans-serif;
      background: linear-gradient(135deg, #e0f2fe 0%, #f1f5f9 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #1e293b;
      overflow-x: hidden;
    }
    .login-card {
      background: #ffffff;
      border: 1px solid #bae6fd;
      border-radius: 24px;
      padding: 40px 30px;
      box-shadow: 0 15px 40px rgba(2, 132, 199, 0.08);
      width: 100%;
      max-width: 485px;
    }
    .logo-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 15px;
      border: 2px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
    }
    .form-control {
      background: #ffffff;
      border: 1px solid #cbd5e1;
      color: #0f172a;
      padding: 12px 16px;
      border-radius: 12px;
    }
    .form-control:focus {
      background: #ffffff;
      border-color: var(--primary-color);
      color: #0f172a;
      box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
    }
    .btn-premium {
      background: linear-gradient(135deg, var(--primary-color) 0%, #0284c7 100%);
      color: #fff;
      border-radius: 12px;
      padding: 12px 20px;
      font-weight: 600;
      border: none;
      box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
      transition: all 0.2s ease;
    }
    .btn-premium:hover {
      background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(14, 165, 233, 0.4);
    }
    .quick-login-btn {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      color: #475569;
      font-size: 0.75rem;
      padding: 6px 10px;
      transition: all 0.2s ease;
      font-weight: 500;
    }
    .quick-login-btn:hover {
      background: #e0f2fe;
      border-color: var(--primary-color);
      color: #0369a1;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="text-center mb-4">
      <div class="logo-badge"><i class="bi bi-mortarboard-fill"></i></div>
      <h3 class="fw-bold mb-1 text-dark">ĐIỂM RÈN LUYỆN</h3>
      <p class="text-secondary small">Hệ thống quản lý điểm rèn luyện sinh viên thông minh</p>
    </div>

    @if (session('success'))
      <div class="alert alert-success p-3 mb-4" role="alert">
        {{ session('success') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger p-3 mb-4" role="alert">
        <ul class="mb-0 small">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label class="form-label text-secondary small fw-semibold">Tài khoản Email</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="nhap@example.com" value="{{ old('email') }}" required>
      </div>
      <div class="mb-4">
        <label class="form-label text-secondary small fw-semibold">Mật khẩu</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-premium w-100 mb-4 py-2.5">
        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập hệ thống
      </button>
    </form>

    <div class="pt-3 border-top border-secondary border-opacity-10 text-center">
      <h6 class="small text-secondary mb-2 fw-semibold">Đăng nhập nhanh cho kiểm thử:</h6>
      <div class="d-flex flex-wrap gap-1 justify-content-center">
        <button class="quick-login-btn" onclick="quickLogin('sinhvien@sv.com', 'password')">Sinh viên</button>
        <button class="quick-login-btn" onclick="quickLogin('bcs@sv.com', 'password')">Ban cán sự</button>
        <button class="quick-login-btn" onclick="quickLogin('covan@sv.com', 'password')">Cố vấn</button>
        <button class="quick-login-btn" onclick="quickLogin('ctsv@sv.com', 'password')">CTSV (Admin)</button>
      </div>
    </div>

    <div class="text-center mt-4">
      <span class="text-secondary small">Chưa có tài khoản? </span>
      <a href="{{ route('register') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Đăng ký ngay</a>
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